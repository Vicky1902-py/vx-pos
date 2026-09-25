<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ValidasiKasirController extends Controller
{
    // Menampilkan daftar transaksi
    public function index(Request $request)
    {
        $search = $request->get('search');

        $transaksi = DB::table('transaksi')
            ->leftJoin('users as sales', 'transaksi.sales_id', '=', 'sales.id')
            ->select('transaksi.*', 'sales.nama as nama_sales')
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
        DB::beginTransaction();
        try {
            $transaksi = DB::table('transaksi')->where('id', $id)->first();
            
            if ($transaksi) {
                $sisaPelunasan = (float) $transaksi->piutang;

                DB::table('transaksi')->where('id', $id)->update([
                    'status'     => 'selesai',
                    'dp'         => $transaksi->total_transaksi,
                    'piutang'    => 0,
                    'updated_at' => now(),
                ]);

                // Rekam pencatatan pelunasan di riwayat cicilan jika ada sisa piutang
                if ($sisaPelunasan > 0) {
                    DB::table('riwayat_cicilan')->insert([
                        'toko_id'       => $transaksi->toko_id ?? 1,
                        'transaksi_id'  => $id,
                        'nominal_bayar' => $sisaPelunasan,
                        'keterangan'    => 'Pelunasan Akhir Kasir',
                        'tanggal_bayar' => now(),
                        'created_at'    => now(),
                        'updated_at'    => now(),
                    ]);
                }
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
        $transaksi = DB::table('transaksi')
            ->leftJoin('users as sales', 'transaksi.sales_id', '=', 'sales.id')
            ->select('transaksi.*', 'sales.nama as nama_sales')
            ->where('transaksi.id', $id)
            ->first();

        if (!$transaksi) return abort(404, 'Transaksi tidak ditemukan');

        // Ambil data barang beserta field 'satuan'
        $detail = DB::table('detail_transaksi')
            ->join('barang', 'detail_transaksi.barang_id', '=', 'barang.id')
            ->select('detail_transaksi.*', 'barang.nama_barang', 'barang.satuan')
            ->where('transaksi_id', $id)
            ->get();

        // Ambil pengaturan toko
        $pengaturan = DB::table('pengaturan_toko')->first();

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

        return view('superadmin.kasir.nota', compact('transaksi', 'detail', 'pengaturan', 'teksTerbilang'));
    }
}