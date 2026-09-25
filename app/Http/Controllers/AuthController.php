<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use App\Models\User;
use App\Services\TenantManager;
use App\Services\DemoStoreService;
use App\Services\DatabaseAutoRepair;

class AuthController extends Controller
{
    public function showLogin()
    {
        // Jika sudah login, arahkan ke portal yang sesuai
        if (Auth::check()) {
            if (TenantManager::isPlatformAdmin()) {
                return redirect()->route('platform.dashboard');
            }
            return redirect()->route('superadmin.dashboard');
        }
        return view('auth.login');
    }

    public function prosesLogin(Request $request)
    {
        $request->validate([
            'username' => ['required'],
            'password' => ['required'],
        ]);

        $loginInput = trim($request->input('username'));
        $password = (string) $request->input('password');
        $lowerInput = strtolower($loginInput);

        $hasEmailColumn = Schema::hasTable('users') && Schema::hasColumn('users', 'email');

        // 1. Cek apakah ada record user berdasarkan username atau email
        $user = User::where(function ($q) use ($loginInput, $lowerInput, $hasEmailColumn) {
            $q->where('username', $loginInput)
              ->orWhereRaw('LOWER(username) = ?', [$lowerInput]);

            if ($hasEmailColumn) {
                $q->orWhere('email', $loginInput)
                  ->orWhereRaw('LOWER(email) = ?', [$lowerInput]);
            }
        })->first();

        // 2. Jika akun demo atau superadmin belum ada di DB, buat saat itu juga (Self-Healing)
        if (!$user) {
            if (in_array($lowerInput, ['demo', 'kasir_demo', 'sales_demo', 'gudang_demo'])) {
                DemoStoreService::generate();
            } elseif (in_array($lowerInput, ['admin', 'vicky'])) {
                DatabaseAutoRepair::repair();
            }

            $hasEmailColumn = Schema::hasTable('users') && Schema::hasColumn('users', 'email');

            $user = User::where(function ($q) use ($loginInput, $lowerInput, $hasEmailColumn) {
                $q->where('username', $loginInput)
                  ->orWhereRaw('LOWER(username) = ?', [$lowerInput]);

                if ($hasEmailColumn) {
                    $q->orWhere('email', $loginInput)
                      ->orWhereRaw('LOWER(email) = ?', [$lowerInput]);
                }
            })->first();
        }

        // 3. Verifikasi Password Multi-Algoritma (Bcrypt, MD5 Legacy, Auto-Sync Demo/Admin)
        $passwordValid = false;

        if ($user) {
            // A. Verifikasi standar Bcrypt / Argon
            if (Hash::check($password, $user->password)) {
                $passwordValid = true;
            }
            // B. Sinkronisasi Darurat Akun Demo (Jika ketik demo123, otomatis sinkron)
            elseif (in_array(strtolower($user->username), ['demo', 'kasir_demo', 'sales_demo', 'gudang_demo']) && $password === 'demo123') {
                $user->password = Hash::make('demo123');
                $user->status = 'aktif';
                $user->save();
                $passwordValid = true;
            }
            // C. Sinkronisasi Darurat Akun Superadmin Platform (Jika ketik admin123, otomatis sinkron)
            elseif (in_array(strtolower($user->username), ['admin', 'vicky']) && $password === 'admin123') {
                $user->password = Hash::make('admin123');
                $user->status = 'aktif';
                $user->save();
                $passwordValid = true;
            }
            // D. Kompatibilitas Database Impor Lama (MD5 Hash)
            elseif (md5($password) === $user->password || md5(md5($password)) === $user->password) {
                // Otomatis upgrade ke hash Bcrypt standar yang aman
                $user->password = Hash::make($password);
                $user->save();
                $passwordValid = true;
            }
            // E. Plaintext legacy fallback
            elseif ($user->password === $password) {
                $user->password = Hash::make($password);
                $user->save();
                $passwordValid = true;
            }
        }

        // 4. Jika password cocok, lakukan proses Login Sesi
        if ($passwordValid && $user) {
            // Cek status keaktifan user
            if ($user->status === 'nonaktif') {
                return back()->with('error', 'Akun Anda berstatus nonaktif. Silakan hubungi Superadmin Platform untuk mengaktifkannya.');
            }

            Auth::login($user, $request->boolean('remember'));
            $request->session()->regenerate();

            // [PEMISAHAN PORTAL]:
            // Superadmin Utama -> Masuk ke Master SaaS Platform Control Panel
            // Admin Toko / Staf -> Masuk ke POS Operasional Toko
            if (TenantManager::isPlatformAdmin()) {
                return redirect()->route('platform.dashboard')
                    ->with('success', 'Selamat datang di Pusat Kendali Master Platform VxPOS!');
            }

            return redirect()->route('superadmin.dashboard')
                ->with('success', "Selamat datang di POS Dashboard Toko ({$user->nama}).");
        }

        return back()->with('error', 'Kredensial login tidak cocok. Pastikan username dan password benar.');
    }

    /**
     * 1-Click Fast Login Langsung ke Akun Toko Demo (Tanpa Perlu Ketik Kredensial)
     */
    public function quickDemoLogin($role = 'admin')
    {
        // Pastikan toko demo dan akun demo tersedia
        DemoStoreService::generate();

        $targetUsername = ($role === 'kasir') ? 'kasir_demo' : 'demo';
        $user = User::where('username', $targetUsername)->first();

        if ($user) {
            Auth::login($user);
            request()->session()->regenerate();
            return redirect()->route('superadmin.dashboard')
                ->with('success', "Berhasil masuk ke Toko Retail Demo (VxPOS) sebagai {$user->nama}!");
        }

        return redirect()->route('login')->with('error', 'Gagal memuat akun demo. Silakan coba lagi.');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }
}