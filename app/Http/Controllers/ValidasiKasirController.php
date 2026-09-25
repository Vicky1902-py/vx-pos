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
        try {
            // Ambil data transaksi terlebih dahulu untuk mengetahui total tagihannya
            $transaksi = DB::table('transaksi')->where('id', $id)->first();
            
            if($transaksi) {
                // PERBAIKAN: Status di-update menjadi 'selesai', dp menjadi full (sama dengan total), piutang menjadi 0 (Lunas)
                DB::table('transaksi')->where('id', $id)->update([
                    'status'  => 'selesai',
                    'dp'      => $transaksi->total_transaksi,
                    'piutang' => 0
                ]);
            }
            
            return redirect()->route('superadmin.kasir.index')->with('success', 'Pembayaran valid! Transaksi disetujui, Lunas, dan Nota siap dicetak.');
            
        } catch (\Exception $e) {
            dd("SISTEM MENDETEKSI ERROR DATABASE: " . $e->getMessage());
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