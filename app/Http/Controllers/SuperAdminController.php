<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SuperAdminController extends Controller
{
    public function dashboard()
    {
        $bulanIni = Carbon::now()->month;
        $tahunIni = Carbon::now()->year;

        // 1. Total Omzet
        $totalOmzet = DB::table('transaksi')
            ->where('status', 'selesai')
            ->whereMonth('created_at', $bulanIni)
            ->whereYear('created_at', $tahunIni)
            ->sum('total_transaksi');

        // 2. Penjualan Sales
        $penjualanSales = DB::table('transaksi')
            ->where('status', 'selesai')
            ->whereMonth('created_at', $bulanIni)
            ->whereYear('created_at', $tahunIni)
            ->count();

        // 3. Stok Rendah
        $stokRendah = DB::table('stok')
            ->whereRaw('stok_tersedia <= stok_minimum')
            ->count();

        // 4. Bonus Bulan Ini
        $bonusBulanIni = DB::table('pencairan_bonus')
            ->whereMonth('created_at', $bulanIni)
            ->whereYear('created_at', $tahunIni)
            ->sum('total_bonus');

        // [BARU] 5. Data Grafik Arus Kas (Terbayar vs Piutang)
        $kasTerbayar = DB::table('transaksi')->sum('dp');
        $kasPiutang = DB::table('transaksi')->sum('piutang');

        // 6. Data Grafik Pendapatan 7 Hari Terakhir
        $grafikTanggal = [];
        $grafikPendapatan = [];
        
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $grafikTanggal[] = $date->translatedFormat('D'); 
            
            $totalHariIni = DB::table('transaksi')
                ->where('status', 'selesai')
                ->whereDate('created_at', $date->format('Y-m-d'))
                ->sum('total_transaksi');
                
            $grafikPendapatan[] = $totalHariIni;
        }

        // 7. Produk Terlaris
        $produkTerlaris = DB::table('detail_transaksi')
            ->join('transaksi', 'detail_transaksi.transaksi_id', '=', 'transaksi.id')
            ->join('barang', 'detail_transaksi.barang_id', '=', 'barang.id')
            ->where('transaksi.status', 'selesai')
            ->whereMonth('transaksi.created_at', $bulanIni)
            ->whereYear('transaksi.created_at', $tahunIni)
            ->select('barang.nama_barang', 'barang.kategori', DB::raw('SUM(detail_transaksi.jumlah) as total_terjual'))
            ->groupBy('barang.id', 'barang.nama_barang', 'barang.kategori')
            ->orderByDesc('total_terjual')
            ->limit(5)
            ->get();

        return view('superadmin.dashboard', compact(
            'totalOmzet', 
            'penjualanSales', 
            'stokRendah', 
            'bonusBulanIni', 
            'kasTerbayar',
            'kasPiutang',
            'grafikTanggal', 
            'grafikPendapatan',
            'produkTerlaris'
        ));
    }
}