<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Services\TenantManager;

class ValidasiKasirController extends Controller
{
    // Menampilkan daftar transaksi
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
            // PERUBAHAN: Menggunakan kata 'selesai' sesuai standar database
            ->whereIn('transaksi.status', ['disiapkan_gudang', 'selesai'])
            ->orderByRaw("FIELD(transaksi.status, 'disiapkan_gudang', 'selesai')")
            ->orderBy('transaksi.id', 'desc')
            ->paginate(10);

        return view('superadmin.kasir.index', compact('transaksi'));
    }

    // Aksi Kasir: Menyetujui transaksi
    public function approve($id)
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

            $sisaPelunasan = (float) $transaksi->piutang;

            DB::table('transaksi')->where('id', $id)->update([
                'status'     => 'selesai',
                'dp'         => $transaksi->total_transaksi,
                'piutang'    => 0,
                'updated_at' => now(),
            ]);

            // Rekam pencatatan pelunasan di riwayat cicilan jika ada sisa piutang
            if ($sisaPelunasan > 0 && Schema::hasTable('riwayat_cicilan')) {
                // Self-healing: Pastikan kolom created_at dan updated_at ada
                if (!Schema::hasColumn('riwayat_cicilan', 'created_at')) {
                    try { DB::statement("ALTER TABLE `riwayat_cicilan` ADD COLUMN `created_at` TIMESTAMP NULL DEFAULT NULL"); } catch (\Throwable $e) {}
                }
                if (!Schema::hasColumn('riwayat_cicilan', 'updated_at')) {
                    try { DB::statement("ALTER TABLE `riwayat_cicilan` ADD COLUMN `updated_at` TIMESTAMP NULL DEFAULT NULL"); } catch (\Throwable $e) {}
                }

                $cols = Schema::getColumnListing('riwayat_cicilan');
                $data = [
                    'transaksi_id'  => $id,
                    'nominal_bayar' => $sisaPelunasan,
                    'keterangan'    => 'Pelunasan Akhir Kasir',
                ];
                if (in_array('toko_id', $cols)) $data['toko_id'] = $transaksi->toko_id ?? $tokoId;
                if (in_array('tanggal_bayar', $cols)) $data['tanggal_bayar'] = now();
                if (in_array('created_at', $cols)) $data['created_at'] = now();
                if (in_array('updated_at', $cols)) $data['updated_at'] = now();

                DB::table('riwayat_cicilan')->insert($data);
            }

            DB::commit();
            return redirect()->route('superadmin.kasir.index')->with('success', 'Pembayaran valid! Transaksi disetujui, Lunas, dan Nota siap dicetak.');
            
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withErrors('Gagal menyetujui transaksi: ' . $e->getMessage());
        }
    }

    // Halaman Cetak Nota / Invoice
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

        if (!$transaksi) return abort(404, 'Transaksi tidak ditemukan atau bukan milik toko Anda.');

        // Ambil data barang beserta field 'satuan'
        $detail = DB::table('detail_transaksi')
            ->join('barang', 'detail_transaksi.barang_id', '=', 'barang.id')
            ->select('detail_transaksi.*', 'barang.nama_barang', 'barang.satuan')
            ->where('transaksi_id', $id)
            ->get();

        $riwayatCicilan = DB::table('riwayat_cicilan')
            ->where('transaksi_id', $id)
            ->orderBy('tanggal_bayar', 'asc')
            ->get();

        $notaTokoId = $transaksi->toko_id ?? $tokoId;
        // Ambil pengaturan toko khusus toko aktif (fallback ke baris pertama jika belum diset)
        $pengaturan = DB::table('pengaturan_toko')->where('toko_id', $notaTokoId)->first() 
            ?? DB::table('pengaturan_toko')->first();

        // Fungsi internal untuk mengubah angka menjadi teks (Terbilang)
        $terbilang = function ($angka) use (&$terbilang) {
            $angka = abs($angka);
            $baca  = ["", "Satu", "Dua", "Tiga", "Empat", "Lima", "Enam", "Tujuh", "Delapan", "Sembilan", "Sepuluh", "Sebelas"];
            $hasil = "";
            if ($angka < 12) { $hasil = " " . $baca[$angka]; }
            else if ($angka < 20) { $hasil = $terbilang($angka - 10) . " Belas"; }
            else if ($angka < 100) { $hasil = $terbilang($angka / 10) . " Puluh" . $terbilang($angka % 10); }
            else if ($angka < 200) { $hasil = " Seratus" . $terbilang($angka - 100); }
            else if ($angka < 1000) { $hasil = $terbilang($angka / 100) . " Ratus" . $terbilang($angka % 100); }
            else if ($angka < 2000) { $hasil = " Seribu" . $terbilang($angka - 1000); }
            else if ($angka < 1000000) { $hasil = $terbilang($angka / 1000) . " Ribu" . $terbilang($angka % 1000); }
            else if ($angka < 1000000000) { $hasil = $terbilang($angka / 1000000) . " Juta" . $terbilang($angka % 1000000); }
            return $hasil;
        };

        // Buat string terbilang (contoh: "Satu Juta Dua Ratus Lima Puluh Ribu Rupiah")
        $teksTerbilang = trim($terbilang($transaksi->total_transaksi)) . " Rupiah";

        return view('superadmin.kasir.nota', compact('transaksi', 'detail', 'riwayatCicilan', 'pengaturan', 'teksTerbilang'));
    }
}