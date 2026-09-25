<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;
use App\Services\TenantManager;

class SuperAdminController extends Controller
{
    public function dashboard()
    {
        $tokoId = TenantManager::getTokoId();
        $bulanIni = Carbon::now()->month;
        $tahunIni = Carbon::now()->year;

        // Pemeriksaan ketersediaan kolom toko_id (Double Protection Anti-Crash)
        $hasTransaksiToko = Schema::hasTable('transaksi') && Schema::hasColumn('transaksi', 'toko_id');
        $hasBarangToko = Schema::hasTable('barang') && Schema::hasColumn('barang', 'toko_id');
        $hasBonusToko = Schema::hasTable('pencairan_bonus') && Schema::hasColumn('pencairan_bonus', 'toko_id');

        // 1. Total Omzet Toko
        $totalOmzet = 0;
        if (Schema::hasTable('transaksi')) {
            $totalOmzet = DB::table('transaksi')
                ->when($hasTransaksiToko && $tokoId, function ($q) use ($tokoId) {
                    $q->where('toko_id', $tokoId);
                })
                ->where('status', 'selesai')
                ->whereMonth('created_at', $bulanIni)
                ->whereYear('created_at', $tahunIni)
                ->sum('total_transaksi');
        }

        // 2. Penjualan Sales
        $penjualanSales = 0;
        if (Schema::hasTable('transaksi')) {
            $penjualanSales = DB::table('transaksi')
                ->when($hasTransaksiToko && $tokoId, function ($q) use ($tokoId) {
                    $q->where('toko_id', $tokoId);
                })
                ->where('status', 'selesai')
                ->whereMonth('created_at', $bulanIni)
                ->whereYear('created_at', $tahunIni)
                ->count();
        }

        // 3. Stok Rendah Toko
        $stokRendah = 0;
        if (Schema::hasTable('stok') && Schema::hasTable('barang')) {
            $stokRendah = DB::table('stok')
                ->join('barang', 'stok.barang_id', '=', 'barang.id')
                ->when($hasBarangToko && $tokoId, function ($q) use ($tokoId) {
                    $q->where('barang.toko_id', $tokoId);
                })
                ->whereRaw('stok.stok_tersedia <= stok.stok_minimum')
                ->count();
        }

        // 4. Bonus Bulan Ini
        $bonusBulanIni = 0;
        if (Schema::hasTable('pencairan_bonus')) {
            $bonusBulanIni = DB::table('pencairan_bonus')
                ->when($hasBonusToko && $tokoId, function ($q) use ($tokoId) {
                    $q->where('toko_id', $tokoId);
                })
                ->whereMonth('created_at', $bulanIni)
                ->whereYear('created_at', $tahunIni)
                ->sum('total_bonus');
        }

        // 5. Data Grafik Arus Kas Toko
        $kasTerbayar = 0;
        $kasPiutang = 0;
        if (Schema::hasTable('transaksi')) {
            $kasTerbayar = DB::table('transaksi')
                ->when($hasTransaksiToko && $tokoId, function ($q) use ($tokoId) {
                    $q->where('toko_id', $tokoId);
                })
                ->sum('dp');

            $kasPiutang = DB::table('transaksi')
                ->when($hasTransaksiToko && $tokoId, function ($q) use ($tokoId) {
                    $q->where('toko_id', $tokoId);
                })
                ->sum('piutang');
        }

        // 6. Data Grafik Pendapatan 7 Hari Terakhir
        $grafikTanggal = [];
        $grafikPendapatan = [];
        
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $grafikTanggal[] = $date->translatedFormat('D'); 
            
            $totalHariIni = 0;
            if (Schema::hasTable('transaksi')) {
                $totalHariIni = DB::table('transaksi')
                    ->when($hasTransaksiToko && $tokoId, function ($q) use ($tokoId) {
                        $q->where('toko_id', $tokoId);
                    })
                    ->where('status', 'selesai')
                    ->whereDate('created_at', $date->format('Y-m-d'))
                    ->sum('total_transaksi');
            }
                
            $grafikPendapatan[] = $totalHariIni;
        }

        // 7. Produk Terlaris Toko
        $produkTerlaris = collect();
        if (Schema::hasTable('detail_transaksi') && Schema::hasTable('transaksi') && Schema::hasTable('barang')) {
            $produkTerlaris = DB::table('detail_transaksi')
                ->join('transaksi', 'detail_transaksi.transaksi_id', '=', 'transaksi.id')
                ->join('barang', 'detail_transaksi.barang_id', '=', 'barang.id')
                ->when($hasTransaksiToko && $tokoId, function ($q) use ($tokoId) {
                    $q->where('transaksi.toko_id', $tokoId);
                })
                ->where('transaksi.status', 'selesai')
                ->whereMonth('transaksi.created_at', $bulanIni)
                ->whereYear('transaksi.created_at', $tahunIni)
                ->select('barang.nama_barang', 'barang.kategori', DB::raw('SUM(detail_transaksi.jumlah) as total_terjual'))
                ->groupBy('barang.id', 'barang.nama_barang', 'barang.kategori')
                ->orderByDesc('total_terjual')
                ->limit(5)
                ->get();
        }

        $isPlatformAdmin = TenantManager::isPlatformAdmin();
        $isAssistMode = TenantManager::isAssistMode();
        $activeToko = TenantManager::getActiveToko();

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
            'isAssistMode',
            'activeToko'
        ));
    }
}