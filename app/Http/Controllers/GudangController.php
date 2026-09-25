<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Services\TenantManager;

class GudangController extends Controller
{
    // Menampilkan daftar permintaan penyiapan barang
    public function index(Request $request)
    {
        $search = $request->get('search');
        $tokoId = TenantManager::getTokoId();

        // Ambil data permintaan gudang beserta relasinya khusus toko aktif
        $permintaan = DB::table('permintaan_gudang')
            ->join('transaksi', 'permintaan_gudang.transaksi_id', '=', 'transaksi.id')
            ->leftJoin('users as sales', 'transaksi.sales_id', '=', 'sales.id')
            ->select(
                'permintaan_gudang.*', 
                'transaksi.no_invoice', 
                'transaksi.created_at as tgl_transaksi', 
                'sales.nama as nama_sales'
            )
            ->where('permintaan_gudang.toko_id', $tokoId)
            ->when($search, function($query, $search) {
                return $query->where('transaksi.no_invoice', 'like', "%{$search}%");
            })
            // Urutkan: yang statusnya 'menunggu' selalu di atas
            ->orderByRaw("FIELD(permintaan_gudang.status, 'menunggu', 'disiapkan')")
            ->orderBy('permintaan_gudang.id', 'desc')
            ->paginate(10);

        // Optimasi: Tarik semua detail transaksi sekaligus (Mencegah N+1 Query)
        $txIds = $permintaan->pluck('transaksi_id')->toArray();
        $allDetails = DB::table('detail_transaksi')
            ->join('barang', 'detail_transaksi.barang_id', '=', 'barang.id')
            ->select('detail_transaksi.transaksi_id', 'detail_transaksi.jumlah', 'barang.kode_barang', 'barang.nama_barang')
            ->whereIn('detail_transaksi.transaksi_id', $txIds)
            ->get()
            ->groupBy('transaksi_id');

        foreach ($permintaan as $p) {
            $p->detail = $allDetails[$p->transaksi_id] ?? collect();
        }

        return view('superadmin.gudang.index', compact('permintaan'));
    }

    // Aksi validasi: Gudang selesai menyiapkan barang -> Potong Stok
    public function proses($id)
    {
        DB::beginTransaction();
        try {
            // Kunci baris data agar tidak terjadi bentrok saat diklik ganda (Race Condition)
            $permintaan = DB::table('permintaan_gudang')->where('id', $id)->lockForUpdate()->first();
            
            if (!$permintaan || $permintaan->status == 'disiapkan') {
                return redirect()->back()->withErrors('Permintaan tidak valid atau sudah diproses sebelumnya.');
            }

            $detailTransaksi = DB::table('detail_transaksi')->where('transaksi_id', $permintaan->transaksi_id)->get();

            // Lakukan pemotongan stok untuk setiap barang di dalam invoice ini
            foreach ($detailTransaksi as $dt) {
                $stok = DB::table('stok')->where('barang_id', $dt->barang_id)->lockForUpdate()->first();
                
                // Cegah sistem memproses jika ada barang yang jumlahnya kurang secara tiba-tiba
                if (!$stok || $stok->stok_tersedia < $dt->jumlah) {
                    throw new \Exception("Stok fisik tidak mencukupi untuk memproses pesanan ini.");
                }

                $sisaStok = $stok->stok_tersedia - $dt->jumlah;
                $statusWarning = ($sisaStok <= $stok->stok_minimum) ? 'warning' : 'aman';

                // Eksekusi potong stok dan update peringatan
                DB::table('stok')->where('barang_id', $dt->barang_id)->update([
                    'stok_tersedia'  => $sisaStok,
                    'status_warning' => $statusWarning,
                    'updated_at'     => now()
                ]);
            }

            // Update status di tabel permintaan_gudang
            DB::table('permintaan_gudang')->where('id', $id)->update([
                'status'          => 'disiapkan',
                'diperbarui_oleh' => Auth::id(), // Siapa petugas gudang/admin yang menyetujui
                'updated_at'      => now()
            ]);

            // PENTING: Update status induk transaksi agar Kasir tahu barang sudah siap
            DB::table('transaksi')->where('id', $permintaan->transaksi_id)->update([
                'status'     => 'disiapkan_gudang',
                'updated_at' => now()
            ]);

            DB::commit();
            return redirect()->back()->with('success', 'Berhasil! Barang telah disiapkan dan stok gudang otomatis dikurangi.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withErrors('Gagal memproses barang: ' . $e->getMessage());
        }
    }
}