<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\SuperAdminController;
use App\Http\Controllers\BarangController;
use App\Http\Controllers\HargaController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\TransaksiController;
use App\Http\Controllers\GudangController;
use App\Http\Controllers\ValidasiKasirController;
use App\Http\Controllers\PengaturanController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\PelangganController;
use App\Http\Controllers\BonusController;
use App\Http\Controllers\GajiController;
use App\Http\Controllers\BackupController;

/*
|--------------------------------------------------------------------------
| 1. JALUR AUTENTIKASI (GUEST)
|--------------------------------------------------------------------------
*/
// Landing Page Publik
Route::get('/', function () {
    return view('welcome');
})->name('landing');

// Jalur Login dengan Proteksi Rate-Limiting Anti Brute-Force
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'prosesLogin'])->middleware('throttle:5,1');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');


/*
|--------------------------------------------------------------------------
| 2. AREA TERLINDUNGI (WAJIB LOGIN & CEK HAK AKSES)
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    
    // --- AREA SUPER ADMIN & MANAJEMEN ---
    Route::prefix('superadmin')->group(function () {
        
        // Dashboard (Semua akun bisa akses selama bisa login)
        Route::get('/dashboard', [SuperAdminController::class, 'dashboard'])->name('superadmin.dashboard');
        
        // ==========================================
        // DATA MASTER
        // ==========================================
        Route::middleware(['akses:master_barang'])->group(function () {
            Route::get('/barang', [BarangController::class, 'index'])->name('superadmin.barang.index');
            Route::post('/barang', [BarangController::class, 'store'])->name('superadmin.barang.store');
            Route::put('/barang/{id}', [BarangController::class, 'update'])->name('superadmin.barang.update');
            Route::delete('/barang/{id}', [BarangController::class, 'destroy'])->name('superadmin.barang.destroy');
            Route::post('/barang/import', [BarangController::class, 'importMassal'])->name('superadmin.barang.import');
        });

        Route::middleware(['akses:manajemen_harga'])->group(function () {
            Route::get('/harga', [HargaController::class, 'index'])->name('superadmin.harga.index');
            Route::post('/harga', [HargaController::class, 'storeOrUpdate'])->name('superadmin.harga.store');
        });
        
        // ==========================================
        // OPERASIONAL
        // ==========================================
        Route::middleware(['akses:transaksi_sales'])->group(function () {
            // Transaksi Penjualan
            Route::get('/transaksi/create', [TransaksiController::class, 'create'])->name('superadmin.transaksi.create');
            Route::post('/transaksi/store', [TransaksiController::class, 'store'])->name('superadmin.transaksi.store');
            Route::get('/transaksi', [TransaksiController::class, 'index'])->name('superadmin.transaksi.index');
            Route::delete('/transaksi/{id}', [TransaksiController::class, 'destroy'])->name('superadmin.transaksi.destroy');
            Route::post('/transaksi/{id}/cicilan', [TransaksiController::class, 'bayarCicilan'])->name('superadmin.transaksi.cicilan');
            Route::get('/transaksi/{id}/print', [TransaksiController::class, 'printNota'])->name('superadmin.transaksi.print');
            
            // Buku Pelanggan
            Route::get('/pelanggan', [PelangganController::class, 'index'])->name('superadmin.pelanggan.index');
            Route::get('/pelanggan/export', [PelangganController::class, 'exportExcel'])->name('superadmin.pelanggan.export');
            Route::get('/pelanggan/detail/{nama}', [PelangganController::class, 'show'])->name('superadmin.pelanggan.show');
        });

        Route::middleware(['akses:stok_gudang'])->group(function () {
            Route::get('/gudang', [GudangController::class, 'index'])->name('superadmin.gudang.index');
            Route::post('/gudang/{id}/proses', [GudangController::class, 'proses'])->name('superadmin.gudang.proses');
        });

        Route::middleware(['akses:validasi_kasir'])->group(function () {
            Route::get('/kasir/validasi', [ValidasiKasirController::class, 'index'])->name('superadmin.kasir.index');
            Route::post('/kasir/validasi/{id}', [ValidasiKasirController::class, 'approve'])->name('superadmin.kasir.approve');
            Route::get('/kasir/nota/{id}', [ValidasiKasirController::class, 'printNota'])->name('superadmin.kasir.nota');
        });

        // ==========================================
        // KEUANGAN
        // ==========================================
        Route::middleware(['akses:laporan_penjualan'])->group(function () {
            Route::get('/laporan', [LaporanController::class, 'index'])->name('superadmin.laporan.index');
            Route::get('/laporan/export', [LaporanController::class, 'exportExcel'])->name('superadmin.laporan.export');
        });

        Route::middleware(['akses:kelola_bonus'])->group(function () {
            Route::get('/bonus', [BonusController::class, 'index'])->name('superadmin.bonus.index');
            Route::post('/bonus', [BonusController::class, 'store'])->name('superadmin.bonus.store');
            Route::delete('/bonus/{id}', [BonusController::class, 'destroy'])->name('superadmin.bonus.destroy');
        });

        // ==========================================
        // SISTEM & HRD (GOD MODE / MANAJEMEN USER)
        // ==========================================
        Route::middleware(['akses:manajemen_user'])->group(function () {
            // Manajemen Akun
            Route::get('/user', [UserController::class, 'index'])->name('superadmin.user.index');
            Route::post('/user', [UserController::class, 'store'])->name('superadmin.user.store');
            Route::put('/user/{id}', [UserController::class, 'update'])->name('superadmin.user.update');
            Route::delete('/user/{id}', [UserController::class, 'destroy'])->name('superadmin.user.destroy');
            
            // Pengaturan Toko
            Route::get('/pengaturan', [PengaturanController::class, 'index'])->name('superadmin.pengaturan.index');
            Route::post('/pengaturan', [PengaturanController::class, 'update'])->name('superadmin.pengaturan.update');
            
            // Penggajian / Payroll
            Route::get('/gaji', [GajiController::class, 'index'])->name('superadmin.gaji.index');
            Route::post('/gaji', [GajiController::class, 'store'])->name('superadmin.gaji.store');
            Route::get('/gaji/slip/{id}', [GajiController::class, 'cetakSlip'])->name('superadmin.gaji.cetak');
            
            // Backup Database
            Route::get('/backup', [BackupController::class, 'index'])->name('superadmin.backup.index');
            Route::post('/backup/download', [BackupController::class, 'download'])->name('superadmin.backup.download');
            
            // Manajemen Multi-Toko (Super Admin Platform)
            Route::get('/toko', [\App\Http\Controllers\TokoController::class, 'index'])->name('superadmin.toko.index');
            Route::post('/toko', [\App\Http\Controllers\TokoController::class, 'store'])->name('superadmin.toko.store');
            Route::put('/toko/{id}', [\App\Http\Controllers\TokoController::class, 'update'])->name('superadmin.toko.update');
            Route::get('/toko/{id}/switch', [\App\Http\Controllers\TokoController::class, 'switchToko'])->name('superadmin.toko.switch');
        });
    });

    // --- AREA ADMIN / KASIR / SALES / GUDANG (Mobile) ---
    // (Bisa Anda kembangkan nanti)
    Route::prefix('kasir')->group(function () {
        Route::get('/dashboard', function () { return '<h2>Halaman Kasir (Versi HP)</h2>'; })->name('kasir.dashboard');
    });
    Route::prefix('sales')->group(function () {
        Route::get('/dashboard', function () { return '<h2>Halaman Sales (Versi HP)</h2>'; })->name('sales.dashboard');
    });
    Route::prefix('gudang')->group(function () {
        Route::get('/dashboard', function () { return '<h2>Halaman Gudang (Versi HP)</h2>'; })->name('gudang.dashboard');
    });

});