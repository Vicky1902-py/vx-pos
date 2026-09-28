<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AksesModul
{
    /**
     * Peta izin default berdasarkan role akun (fallback jika hak_akses di DB belum diset)
     */
    public static function getDefaultPermissionsByRole(string $role): array
    {
        $allStoreModules = [
            'master_barang',
            'manajemen_harga',
            'transaksi_sales',
            'validasi_kasir',
            'stok_gudang',
            'laporan_penjualan',
            'kelola_bonus',
            'manajemen_user',
        ];

        return match ($role) {
            'superadmin'          => $allStoreModules,
            'admin', 'admin_toko' => $allStoreModules,
            'kasir'               => ['transaksi_sales', 'validasi_kasir'],
            'gudang'              => ['master_barang', 'stok_gudang'],
            'sales'               => ['transaksi_sales', 'kelola_bonus'],
            default               => [],
        };
    }

    public function handle(Request $request, Closure $next, $modul)
    {
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login');
        }

        // 1. Superadmin WAJIB FULL AKSES apapun itu (God Mode Lintas Toko & Platform)
        if ($user->role === 'superadmin') {
            return $next($request);
        }

        // 2. Admin Toko WAJIB FULL AKSES di Tokonya Sendiri (Semua modul operasional & manajemen toko)
        if (in_array($user->role, ['admin', 'admin_toko'])) {
            return $next($request);
        }

        // 3. Ambil data hak akses spesifik (JSON) dari database
        $hakAkses = json_decode($user->hak_akses, true);
        if (!is_array($hakAkses)) {
            $hakAkses = [];
        }

        // 4. Gabungkan dengan izin default berbasis tingkatan akun (Role)
        $roleDefaults = self::getDefaultPermissionsByRole($user->role ?? '');
        $effectiveAkses = array_unique(array_merge($hakAkses, $roleDefaults));

        // 5. Validasi apakah modul yang dituju ada di dalam izin aktif
        if (!in_array($modul, $effectiveAkses)) {
            return redirect()->route('superadmin.dashboard')
                ->withErrors('Akses Ditolak: Tingkatan akun ' . strtoupper($user->role) . ' tidak memiliki izin untuk mengakses fitur ini.');
        }

        return $next($request);
    }
}