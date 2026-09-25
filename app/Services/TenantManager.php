<?php

namespace App\Services;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class TenantManager
{
    /**
     * Mendapatkan ID Toko yang sedang aktif
     */
    public static function getTokoId(): ?int
    {
        $user = Auth::user();
        if (!$user) {
            return null;
        }

        // Jika user adalah Platform Admin (Super Admin Utama), izinkan berganti konteks toko
        if (self::isPlatformAdmin()) {
            $sessionTokoId = Session::get('active_toko_id');
            if ($sessionTokoId) {
                return (int) $sessionTokoId;
            }
        }

        // Default ke toko_id milik user
        return $user->toko_id ? (int) $user->toko_id : 1;
    }

    /**
     * Cek apakah user adalah Super Admin Platform (Pemilik Sistem)
     */
    public static function isPlatformAdmin(): bool
    {
        $user = Auth::user();
        if (!$user) return false;
        
        return !empty($user->is_platform_admin) 
            || $user->username === 'admin' 
            || $user->username === 'vicky' 
            || ($user->role === 'superadmin' && (empty($user->toko_id) || $user->toko_id == 1));
    }

    /**
     * Cek apakah Superadmin sedang dalam mode asistensi/pantau toko tertentu
     */
    public static function isAssistMode(): bool
    {
        if (!self::isPlatformAdmin()) return false;
        return Session::has('active_toko_id');
    }

    /**
     * Kembalikan konteks kerja Superadmin ke Platform Master
     */
    public static function resetToMaster(): void
    {
        Session::forget('active_toko_id');
    }

    /**
     * Mendapatkan Data Toko yang sedang aktif
     */
    public static function getActiveToko()
    {
        $tokoId = self::getTokoId();
        if (!$tokoId) return null;

        return DB::table('toko')->where('id', $tokoId)->first();
    }

    /**
     * Mendapatkan semua daftar toko (khusus Superadmin Platform)
     */
    public static function getAllToko()
    {
        return DB::table('toko')->orderBy('nama_toko', 'asc')->get();
    }

    /**
     * Ganti Toko Aktif (untuk Superadmin Platform)
     */
    public static function switchToko(int $tokoId): bool
    {
        if (!self::isPlatformAdmin()) {
            return false;
        }

        $exists = DB::table('toko')->where('id', $tokoId)->exists();
        if ($exists) {
            Session::put('active_toko_id', $tokoId);
            return true;
        }

        return false;
    }
}
