<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ProtectDemoMode
{
    /**
     * Handle an incoming request.
     * Mencegah akun demo melakukan aksi destruktif atau mengubah konfigurasi kritis
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && ($user->username === 'demo' || $user->username === 'kasir_demo' || $user->toko_id == 30)) {
            $path = trim($request->path(), '/');
            $method = strtoupper($request->method());

            // 1. Blokir akses total ke Platform Master SaaS
            if (str_starts_with($path, 'platform')) {
                return redirect()->route('superadmin.dashboard')
                    ->with('error', 'Akses Ditolak: Akun Demo tidak memiliki izin mengakses Platform Master.');
            }

            // 2. Blokir aksi perubahan/penghapusan pada Manajemen Pengguna (User Management)
            if (str_starts_with($path, 'superadmin/user') && in_array($method, ['POST', 'PUT', 'DELETE'])) {
                return redirect()->back()
                    ->with('error', 'Mode Demo: Pembuatan, pengubahan, atau penghapusan akun karyawan dinonaktifkan.');
            }

            // 3. Blokir aksi Backup & Restore pada akun demo
            if (str_starts_with($path, 'superadmin/backup') && in_array($method, ['POST', 'PUT', 'DELETE'])) {
                return redirect()->back()
                    ->with('error', 'Mode Demo: Fitur Backup dan Restore database dinonaktifkan.');
            }

            // 4. Blokir perubahan Pengaturan Toko
            if (str_starts_with($path, 'superadmin/pengaturan') && in_array($method, ['POST', 'PUT', 'DELETE'])) {
                return redirect()->back()
                    ->with('error', 'Mode Demo: Pengaturan profil toko bersifat Read-Only.');
            }

            // 5. Sanitasi seluruh input string pada request akun demo (Anti XSS)
            $inputs = $request->all();
            $cleaned = false;
            foreach ($inputs as $key => $val) {
                if (is_string($val)) {
                    $stripped = strip_tags($val);
                    if ($stripped !== $val) {
                        $inputs[$key] = $stripped;
                        $cleaned = true;
                    }
                }
            }
            if ($cleaned) {
                $request->merge($inputs);
            }
        }

        return $next($request);
    }
}
