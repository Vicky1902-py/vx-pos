<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Services\TenantManager;

class SuperAdminController extends Controller
{
    public function dashboard()
    {
        $tokoId = TenantManager::getTokoId();
        $bulanIni = Carbon::now()->month;
        $tahunIni = Carbon::now()->year;

        // 1. Total Omzet
        $totalOmzet = DB::table('transaksi')
            ->where('toko_id', $tokoId)
            ->where('status', 'selesai')
            ->whereMonth('created_at', $bulanIni)
            ->whereYear('created_at', $tahunIni)
            ->sum('total_transaksi');

        // 2. Penjualan Sales
        $penjualanSales = DB::table('transaksi')
            ->where('toko_id', $tokoId)
            ->where('status', 'selesai')
            ->whereMonth('created_at', $bulanIni)
            ->whereYear('created_at', $tahunIni)
            ->count();

        // 3. Stok Rendah
        $stokRendah = DB::table('stok')
            ->join('barang', 'stok.barang_id', '=', 'barang.id')
            ->where('barang.toko_id', $tokoId)
            ->whereRaw('stok.stok_tersedia <= stok.stok_minimum')
            ->count();

        // 4. Bonus Bulan Ini
        $bonusBulanIni = DB::table('pencairan_bonus')
            ->where('toko_id', $tokoId)
            ->whereMonth('created_at', $bulanIni)
            ->whereYear('created_at', $tahunIni)
            ->sum('total_bonus');

        // 5. Data Grafik Arus Kas (Terbayar vs Piutang)
        $kasTerbayar = DB::table('transaksi')->where('toko_id', $tokoId)->sum('dp');
        $kasPiutang = DB::table('transaksi')->where('toko_id', $tokoId)->sum('piutang');

        // 6. Data Grafik Pendapatan 7 Hari Terakhir
        $grafikTanggal = [];
        $grafikPendapatan = [];
        
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $grafikTanggal[] = $date->translatedFormat('D'); 
            
            $totalHariIni = DB::table('transaksi')
                ->where('toko_id', $tokoId)
                ->where('status', 'selesai')
                ->whereDate('created_at', $date->format('Y-m-d'))
                ->sum('total_transaksi');
                
            $grafikPendapatan[] = $totalHariIni;
        }

        // 7. Produk Terlaris
        $produkTerlaris = DB::table('detail_transaksi')
            ->join('transaksi', 'detail_transaksi.transaksi_id', '=', 'transaksi.id')
            ->join('barang', 'detail_transaksi.barang_id', '=', 'barang.id')
            ->where('transaksi.toko_id', $tokoId)
            ->where('transaksi.status', 'selesai')
            ->whereMonth('transaksi.created_at', $bulanIni)
            ->whereYear('transaksi.created_at', $tahunIni)
            ->select('barang.nama_barang', 'barang.kategori', DB::raw('SUM(detail_transaksi.jumlah) as total_terjual'))
            ->groupBy('barang.id', 'barang.nama_barang', 'barang.kategori')
            ->orderByDesc('total_terjual')
            ->limit(5)
            ->get();

        $isPlatformAdmin = TenantManager::isPlatformAdmin();
        $activeToko = TenantManager::getActiveToko();
        $totalSemuaToko = 0;
        $totalTokoAktif = 0;
        $omzetGlobalSaaS = 0;
        $daftarSemuaToko = collect();

        if ($isPlatformAdmin) {
            $totalSemuaToko = DB::table('toko')->count();
            $totalTokoAktif = DB::table('toko')->where('status', 'aktif')->count();
            $omzetGlobalSaaS = DB::table('transaksi')->where('status', 'selesai')->sum('total_transaksi');
            $daftarSemuaToko = DB::table('toko')->orderBy('nama_toko', 'asc')->get();
        }

        return view('superadmin.dashboard', compact(
            'totalOmzet', 
            'penjualanSales', 
            'stokRendah', 
            'bonusBulanIni', 
            'kasTerbayar', 
            'kasPiutang', 
            'grafikTanggal', 
            'grafikPendapatan', 
            'produkTerlaris',
            'isPlatformAdmin',
            'activeToko',
            'totalSemuaToko',
            'totalTokoAktif',
            'omzetGlobalSaaS',
            'daftarSemuaToko'
        ));
    }
}