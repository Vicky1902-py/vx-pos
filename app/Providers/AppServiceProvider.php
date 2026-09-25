<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Otomatis sinkronisasi penamaan toko menjadi VxPOS jika masih tersimpan data lama
        try {
            if (\Illuminate\Support\Facades\Schema::hasTable('pengaturan_toko')) {
                \Illuminate\Support\Facades\DB::table('pengaturan_toko')
                    ->where(function($q) {
                        $q->where('nama_toko', 'like', '%Chanada%')
                          ->orWhere('nama_toko', 'like', '%Vx-Pos%');
                    })
                    ->update(['nama_toko' => 'VxPOS']);
            }
            if (\Illuminate\Support\Facades\Schema::hasTable('toko')) {
                \Illuminate\Support\Facades\DB::table('toko')
                    ->where(function($q) {
                        $q->where('nama_toko', 'like', '%Chanada%')
                          ->orWhere('nama_toko', 'like', '%Vx-Pos%');
                    })
                    ->update(['nama_toko' => 'VxPOS', 'slug' => 'vxpos']);
            }
        } catch (\Throwable $e) {
            // Abaikan jika database belum siap
        }
    }
}
