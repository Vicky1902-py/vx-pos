<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AksesModul
{
    public function handle(Request $request, Closure $next, $modul)
    {
        $user = Auth::user();

        // 1. Superadmin bebas akses ke mana saja (God Mode)
        if ($user->role === 'superadmin') {
            return $next($request);
        }

        // 2. Ambil data centang (JSON) dari database dan ubah jadi Array
        $hakAkses = json_decode($user->hak_akses, true);
        if (!is_array($hakAkses)) {
            $hakAkses = [];
        }

        // 3. Cek apakah modul yang dituju ada di dalam array hak akses
        if (!in_array($modul, $hakAkses)) {
            // Jika tidak ada izin, tendang kembali ke Dashboard dengan pesan error
            return redirect()->route('superadmin.dashboard')->withErrors('Akses Ditolak: Anda tidak memiliki izin untuk membuka modul tersebut.');
        }

        return $next($request);
    }
}