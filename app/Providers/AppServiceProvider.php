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
                          ->orWhere('nama_toko', 'like', '%Vx-Pos%')
                          ->orWhere('logo', 'like', '%1784947357%');
                    })
                    ->update([
                        'nama_toko' => 'VxPOS',
                        'logo' => null,
                    ]);
            }
            if (\Illuminate\Support\Facades\Schema::hasTable('toko')) {
                \Illuminate\Support\Facades\DB::table('toko')
                    ->where(function($q) {
                        $q->where('nama_toko', 'like', '%Chanada%')
                          ->orWhere('nama_toko', 'like', '%Vx-Pos%')
                          ->orWhere('logo', 'like', '%1784947357%');
                    })
                    ->update([
                        'nama_toko' => 'VxPOS',
                        'slug' => 'vxpos',
                        'logo' => null,
                    ]);
            }
        } catch (\Throwable $e) {
            // Abaikan jika database belum siap
        }

        // Auto-create tabel cache, cache_locks, dan sessions jika belum ada (Anti SQLSTATE 42S02)
        try {
            if (!\Illuminate\Support\Facades\Schema::hasTable('cache')) {
                \Illuminate\Support\Facades\Schema::create('cache', function ($table) {
                    $table->string('key')->primary();
                    $table->mediumText('value');
                    $table->integer('expiration');
                });
            }
            if (!\Illuminate\Support\Facades\Schema::hasTable('cache_locks')) {
                \Illuminate\Support\Facades\Schema::create('cache_locks', function ($table) {
                    $table->string('key')->primary();
                    $table->string('owner');
                    $table->integer('expiration');
                });
            }
            if (!\Illuminate\Support\Facades\Schema::hasTable('sessions')) {
                \Illuminate\Support\Facades\Schema::create('sessions', function ($table) {
                    $table->string('id')->primary();
                    $table->unsignedBigInteger('user_id')->nullable()->index();
                    $table->string('ip_address', 45)->nullable();
                    $table->text('user_agent')->nullable();
                    $table->longText('payload');
                    $table->integer('last_activity')->index();
                });
            }
        } catch (\Throwable $e) {
            // Abaikan jika database belum terhubung
        }
    }
}
