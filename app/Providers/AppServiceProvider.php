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
        // Jalankan perbaikan skema database, pembuatan tabel sistem & multi-tenant secara otomatis
        \App\Services\DatabaseAutoRepair::repair();
    }
}
