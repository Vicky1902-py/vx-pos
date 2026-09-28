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
        // Jalankan perbaikan skema database dengan caching agar loading website super cepat (anti-timeout)
        try {
            if (!\Illuminate\Support\Facades\Cache::has('db_auto_repaired_v5')) {
                \App\Services\DatabaseAutoRepair::repair();
                \Illuminate\Support\Facades\Cache::put('db_auto_repaired_v5', true, now()->addHours(6));
            }
        } catch (\Throwable $e) {
            \App\Services\DatabaseAutoRepair::repair();
        }
    }
}
