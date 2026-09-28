<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use App\Services\TenantManager;

class TransaksiController extends Controller
{
    public function create(Request $request)
    {
        $search = $request->get('search');
        $tokoId = TenantManager::getTokoId();

        $listSales = DB::table('users')
            ->where('toko_id', $tokoId)
            ->where('role', '!=', 'superadmin')
            ->whereNotIn('username', ['vicky', 'admin'])
            ->select('id', 'nama', 'role')
            ->get(); 

        $barang = DB::table('barang')
            ->join('stok', 'barang.id', '=', 'stok.barang_id')
            ->join('harga', 'barang.id', '=', 'harga.barang_id')
            ->select('barang.id', 'barang.kode_barang', 'barang.nama_barang', 'barang.kategori', 'stok.stok_tersedia', 'harga.harga_jual', 'harga.diskon_rupiah', 'harga.harga_minimum')
            ->where('barang.status', 'aktif')
            ->where('barang.toko_id', $tokoId)
            ->where('stok.stok_tersedia', '>', 0)
            ->whereNotNull('harga.harga_jual')
            ->when($search, function ($query, $search) {
                return $query->where('barang.nama_barang', 'like', "%{$search}%")
                             ->orWhere('barang.kode_barang', 'like', "%{$search}%");
            })
            ->get();

        return view('superadmin.transaksi.create', compact('barang', 'listSales'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'cart_data'      => 'required|string',
            'nama_pelanggan' => 'nullable|string|max:255',
            'sales_id'       => 'nullable|integer', 
            'dp'             => 'nullable|numeric|min:0'
            // Validasi 'diskon' global dihapus karena sudah beralih ke diskon per item
        ]);

        $cart = json_decode($request->cart_data, true);

        if (empty($cart)) {
            return redirect()->back()->withErrors('Keranjang belanja masih kosong!');
        }

        DB::beginTransaction();
        try {
            $bulan = date('m');
            $tahun = date('Y');
            $lastTransaksi = DB::table('transaksi')->orderBy('id', 'desc')->first();
            $urut = $lastTransaksi ? ($lastTransaksi->id + 1) : 1;
            $noInvoice = 'INV/' . $tahun . '/' . $bulan . '/' . str_pad($urut, 4, '0', STR_PAD_LEFT);

            $subtotalSeluruhBarang = 0; 
            $totalDiskonTransaksi = 0; // [BARU] Menampung akumulasi total seluruh diskon di nota ini
            $dataDetailSementara = [];

            foreach ($cart as $item) {
                $jumlahBarang = (int) $item['jumlah'];
                $barangId = (int) $item['id'];

                if ($jumlahBarang <= 0) continue; 

                $hargaDb = DB::table('harga')->where('barang_id', $barangId)->first();
                $hargaJualRiil = $hargaDb ? (float) $hargaDb->harga_jual : 0;
                $hargaMinimum = $hargaDb ? (float) $hargaDb->harga_minimum : 0; // [BARU] Ambil harga batas bawah
                
                // [BARU] Ambil diskon dari keranjang kasir (gabungan promo + persentase kasir)
                $diskonItem = isset($item['diskon_item']) ? (float) $item['diskon_item'] : 0; 
                
                $hargaFinal = $hargaJualRiil - $diskonItem;
                
                // [PENGAMANAN BACKEND ANTI-RUGI]
                // Jika keranjang dimanipulasi dan harga final tembus batas bawah, paksa kembali ke harga minimum
                if ($hargaFinal < $hargaMinimum) {
                    $hargaFinal = $hargaMinimum;
                    $diskonItem = $hargaJualRiil - $hargaMinimum;
                }
                
                $subtotal = $hargaFinal * $jumlahBarang;
                $subtotalSeluruhBarang += $subtotal;
                $totalDiskonTransaksi += ($diskonItem * $jumlahBarang);

                $hargaModal = $hargaDb ? (float) $hargaDb->harga_modal : 0;

                $dataDetailSementara[] = [
                    'barang_id'   => $barangId,
                    'jumlah'      => $jumlahBarang,
                    'harga_modal' => $hargaModal,
                    'harga_jual'  => $hargaJualRiil,
                    'diskon_item' => $diskonItem, 
                    'subtotal'    => $subtotal,
                ];
            }

            if ($subtotalSeluruhBarang <= 0) {
                return redirect()->back()->withErrors('Gagal memproses transaksi. Jumlah barang tidak valid.');
            }

            $tokoId = TenantManager::getTokoId();

            // Total Akhir kini murni dari subtotal yang sudah dipotong diskon masing-masing item
            $totalAkhir = $subtotalSeluruhBarang;

            $dp = $request->dp ? (float) $request->dp : 0;
            $piutang = $totalAkhir - $dp;
            if ($piutang < 0) $piutang = 0; 

            $idSalesDitunjuk = $request->sales_id ? $request->sales_id : Auth::id();

            $transaksiId = DB::table('transaksi')->insertGetId([
                'toko_id'         => $tokoId,
                'no_invoice'      => $noInvoice,
                'sales_id'        => $idSalesDitunjuk, 
                'pelanggan_id'    => null,
                'nama_pelanggan'  => $request->nama_pelanggan ?: 'Umum',
                'status'          => 'pending',
                'total_transaksi' => $totalAkhir, 
                'diskon'          => $totalDiskonTransaksi, // [BARU] Simpan total kerugian promo/diskon
                'dp'              => $dp,
                'piutang'         => $piutang,
                'created_at'      => now(),
                'updated_at'      => now(),
            ]);

            if ($dp > 0 && Schema::hasTable('riwayat_cicilan')) {
                if (!Schema::hasColumn('riwayat_cicilan', 'created_at')) {
                    try { DB::statement("ALTER TABLE `riwayat_cicilan` ADD COLUMN `created_at` TIMESTAMP NULL DEFAULT NULL"); } catch (\Throwable $e) {}
                }
                if (!Schema::hasColumn('riwayat_cicilan', 'updated_at')) {
                    try { DB::statement("ALTER TABLE `riwayat_cicilan` ADD COLUMN `updated_at` TIMESTAMP NULL DEFAULT NULL"); } catch (\Throwable $e) {}
                }

                $cols = Schema::getColumnListing('riwayat_cicilan');
                $cicilanData = [
                    'transaksi_id'  => $transaksiId,
                    'nominal_bayar' => $dp,
                    'keterangan'    => 'Pembayaran Uang Muka (DP Awal)',
                ];
                if (in_array('toko_id', $cols)) $cicilanData['toko_id'] = $tokoId;
                if (in_array('tanggal_bayar', $cols)) $cicilanData['tanggal_bayar'] = now();
                if (in_array('created_at', $cols)) $cicilanData['created_at'] = now();
                if (in_array('updated_at', $cols)) $cicilanData['updated_at'] = now();

                DB::table('riwayat_cicilan')->insert($cicilanData);
            }

            $detailData = [];
            foreach ($dataDetailSementara as $detail) {
                $detailData[] = [
                    'toko_id'      => $tokoId,
                    'transaksi_id' => $transaksiId,
                    'barang_id'    => $detail['barang_id'],
                    'jumlah'       => $detail['jumlah'],
                    'harga_modal'  => $detail['harga_modal'],
                    'harga_jual'   => $detail['harga_jual'],
                    'diskon_item'  => $detail['diskon_item'], 
                    'subtotal'     => $detail['subtotal'],
                    'created_at'   => now(),
                    'updated_at'   => now(),
                ];
            }
            DB::table('detail_transaksi')->insert($detailData);

            $pgCols = Schema::hasTable('permintaan_gudang') ? Schema::getColumnListing('permintaan_gudang') : [];
            $pgData = [
                'transaksi_id' => $transaksiId,
                'status'       => 'menunggu',
            ];
            if (in_array('toko_id', $pgCols)) $pgData['toko_id'] = $tokoId;
            if (in_array('created_at', $pgCols)) $pgData['created_at'] = now();
            if (in_array('updated_at', $pgCols)) $pgData['updated_at'] = now();

            DB::table('permintaan_gudang')->insert($pgData);

            DB::commit();
            return redirect()->back()->with('success', "Transaksi berhasil dibuat dengan No. Invoice: $noInvoice. Menunggu penyiapan Gudang.");
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withErrors('Terjadi kesalahan sistem: ' . $e->getMessage());
        }
    }
    
    public function index(Request $request)
    {
        $search = $request->get('search');
        $tokoId = TenantManager::getTokoId();
        
        $transaksi = DB::table('transaksi')
            ->leftJoin('users as sales', 'transaksi.sales_id', '=', 'sales.id')
            ->select('transaksi.*', 'sales.nama as nama_sales')
            ->where('transaksi.toko_id', $tokoId)
            ->when($search, function($query, $search) {
                return $query->where('transaksi.no_invoice', 'like', "%{$search}%");
            })
            ->orderBy('transaksi.id', 'desc')
            ->paginate(10);
            
        return view('superadmin.transaksi.index', compact('transaksi'));
    }

    public function destroy($id)
    {
        $tokoId = TenantManager::getTokoId();
        DB::beginTransaction();
        try {
            $query = DB::table('transaksi')->where('id', $id);
            if (!TenantManager::isPlatformAdmin()) {
                $query->where('toko_id', $tokoId);
            }
            $transaksi = $query->first();
            if (!$transaksi) {
                return redirect()->back()->withErrors('Transaksi tidak ditemukan atau bukan milik toko Anda.');
            }

            // Jika pesanan sudah disiapkan gudang atau selesai, kembalikan stok fisik ke gudang!
            if (in_array($transaksi->status, ['disiapkan_gudang', 'selesai'])) {
                $details = DB::table('detail_transaksi')->where('transaksi_id', $id)->get();
                foreach ($details as $dt) {
                    $stok = DB::table('stok')->where('barang_id', $dt->barang_id)->first();
                    if ($stok) {
                        $stokBaru = $stok->stok_tersedia + $dt->jumlah;
                        $statusWarning = ($stokBaru <= $stok->stok_minimum) ? 'warning' : 'aman';
                        DB::table('stok')->where('barang_id', $dt->barang_id)->update([
                            'stok_tersedia'  => $stokBaru,
                            'status_warning' => $statusWarning,
                            'updated_at'     => now(),
                        ]);
                    }
                }
            }

            DB::table('detail_transaksi')->where('transaksi_id', $id)->delete();
            DB::table('permintaan_gudang')->where('transaksi_id', $id)->delete();
            DB::table('riwayat_cicilan')->where('transaksi_id', $id)->delete();
            DB::table('transaksi')->where('id', $id)->delete();

            DB::commit();
            return redirect()->back()->with('success', 'Data transaksi berhasil dihapus dan stok gudang otomatis dikembalikan.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withErrors('Gagal menghapus transaksi: ' . $e->getMessage());
        }
    }
    
    public function printNota($id)
    {
        $tokoId = TenantManager::getTokoId();
        $query = DB::table('transaksi')
            ->leftJoin('users as sales', 'transaksi.sales_id', '=', 'sales.id')
            ->select('transaksi.*', 'sales.nama as nama_sales')
            ->where('transaksi.id', $id);

        if (!TenantManager::isPlatformAdmin()) {
            $query->where('transaksi.toko_id', $tokoId);
        }

        $transaksi = $query->first();

        if (!$transaksi) {
            return redirect()->back()->withErrors('Data transaksi tidak ditemukan atau bukan milik toko Anda.');
        }

        $detail = DB::table('detail_transaksi')
            ->join('barang', 'detail_transaksi.barang_id', '=', 'barang.id')
            ->select('detail_transaksi.*', 'barang.nama_barang', 'barang.kode_barang', 'barang.satuan')
            ->where('transaksi_id', $id)
            ->get();

        $riwayatCicilan = DB::table('riwayat_cicilan')
            ->where('transaksi_id', $id)
            ->orderBy('tanggal_bayar', 'asc')
            ->get();

        $notaTokoId = $transaksi->toko_id ?? $tokoId;
        $pengaturan = DB::table('pengaturan_toko')->where('toko_id', $notaTokoId)->first() ?? DB::table('pengaturan_toko')->first();
        
        $teksTerbilang = $this->terbilang($transaksi->total_transaksi) . ' Rupiah';

        return view('superadmin.kasir.nota', compact('transaksi', 'detail', 'riwayatCicilan', 'pengaturan', 'teksTerbilang'));
    }

    public function bayarCicilan(Request $request, $id)
    {
        $request->validate([
            'nominal_cicilan' => 'required|numeric|min:1'
        ]);

        $tokoId = TenantManager::getTokoId();
        DB::beginTransaction();
        try {
            $query = DB::table('transaksi')->where('id', $id);
            if (!TenantManager::isPlatformAdmin()) {
                $query->where('toko_id', $tokoId);
            }
            $transaksi = $query->first();
            if (!$transaksi) return redirect()->back()->withErrors('Transaksi tidak ditemukan atau bukan milik toko Anda.');
            if ($transaksi->piutang <= 0) return redirect()->back()->withErrors('Transaksi ini sudah dilunasi.');

            $nominalCicilan = (float) $request->nominal_cicilan;
            
            $dpBaru = $transaksi->dp + $nominalCicilan;
            $piutangBaru = $transaksi->total_transaksi - $dpBaru;
            $statusBaru = $transaksi->status;

            if ($piutangBaru <= 0) {
                $piutangBaru = 0;
                $dpBaru = $transaksi->total_transaksi;
                $statusBaru = 'selesai'; 
            }

            DB::table('transaksi')->where('id', $id)->update([
                'dp'         => $dpBaru,
                'piutang'    => $piutangBaru,
                'status'     => $statusBaru,
                'updated_at' => now(),
            ]);

            if (Schema::hasTable('riwayat_cicilan')) {
                if (!Schema::hasColumn('riwayat_cicilan', 'created_at')) {
                    try { DB::statement("ALTER TABLE `riwayat_cicilan` ADD COLUMN `created_at` TIMESTAMP NULL DEFAULT NULL"); } catch (\Throwable $e) {}
                }
                if (!Schema::hasColumn('riwayat_cicilan', 'updated_at')) {
                    try { DB::statement("ALTER TABLE `riwayat_cicilan` ADD COLUMN `updated_at` TIMESTAMP NULL DEFAULT NULL"); } catch (\Throwable $e) {}
                }

                $cols = Schema::getColumnListing('riwayat_cicilan');
                $cicilanData = [
                    'transaksi_id'  => $id,
                    'nominal_bayar' => $nominalCicilan,
                    'keterangan'    => 'Pembayaran Cicilan Piutang',
                ];
                if (in_array('toko_id', $cols)) $cicilanData['toko_id'] = $transaksi->toko_id ?? $tokoId;
                if (in_array('tanggal_bayar', $cols)) $cicilanData['tanggal_bayar'] = now();
                if (in_array('created_at', $cols)) $cicilanData['created_at'] = now();
                if (in_array('updated_at', $cols)) $cicilanData['updated_at'] = now();

                DB::table('riwayat_cicilan')->insert($cicilanData);
            }

            DB::commit();
            $pesan = $piutangBaru == 0 
                ? 'Pembayaran berhasil! Transaksi kini telah sepenuhnya LUNAS.' 
                : 'Cicilan berhasil ditambahkan! Sisa piutang saat ini: Rp ' . number_format($piutangBaru, 0, ',', '.');

            return redirect()->back()->with('success', $pesan);

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withErrors('Terjadi kesalahan sistem: ' . $e->getMessage());
        }
    }

    private function terbilang($angka) {
        $angka = abs($angka);
        $baca = array("", "Satu", "Dua", "Tiga", "Empat", "Lima", "Enam", "Tujuh", "Delapan", "Sembilan", "Sepuluh", "Sebelas");
        $terbilang = "";
        
        if ($angka < 12) {
            $terbilang = " " . $baca[$angka];
        } else if ($angka < 20) {
            $terbilang = $this->terbilang($angka - 10) . " Belas";
        } else if ($angka < 100) {
            $terbilang = $this->terbilang($angka / 10) . " Puluh" . $this->terbilang($angka % 10);
        } else if ($angka < 200) {
            $terbilang = " Seratus" . $this->terbilang($angka - 100);
        } else if ($angka < 1000) {
            $terbilang = $this->terbilang($angka / 100) . " Ratus" . $this->terbilang($angka % 100);
        } else if ($angka < 2000) {
            $terbilang = " Seribu" . $this->terbilang($angka - 1000);
        } else if ($angka < 1000000) {
            $terbilang = $this->terbilang($angka / 1000) . " Ribu" . $this->terbilang($angka % 1000);
        } else if ($angka < 1000000000) {
            $terbilang = $this->terbilang($angka / 1000000) . " Juta" . $this->terbilang($angka % 1000000);
        }
        return $terbilang;
    }
}