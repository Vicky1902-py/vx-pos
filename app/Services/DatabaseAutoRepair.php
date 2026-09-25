<?php

namespace App\Services;

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Schema\Blueprint;

class DatabaseAutoRepair
{
    /**
     * Jalankan self-healing dan perbaikan otomatis database VxPOS
     * Setiap langkah dijalankan secara terisolasi agar kegagalan parsial tidak menghentikan perbaikan lain
     */
    public static function repair(): void
    {
        // 1. Pastikan tabel 'cache', 'cache_locks', dan 'sessions' ada
        try { self::ensureSystemTables(); } catch (\Throwable $e) {}

        // 2. Pastikan tabel 'toko' ada dan berisi default Toko Pusat
        try { self::ensureTokoTable(); } catch (\Throwable $e) {}

        // 3. Pastikan kolom-kolom multi-tenant di tabel 'users' ada
        try { self::ensureUsersColumns(); } catch (\Throwable $e) {}

        // 4. Pastikan kolom 'toko_id' ada di semua tabel operasional
        try { self::ensureTenantColumns(); } catch (\Throwable $e) {}

        // 5. Pastikan akun Superadmin Utama (vicky & admin) tersedia & aktif
        try { self::ensureSuperAdminAccounts(); } catch (\Throwable $e) {}

        // 6. Sinkronisasi nama default VxPOS & hapus file logo chanada lama
        try { self::ensureBranding(); } catch (\Throwable $e) {}

        // 7. Pastikan Toko Demo dan Akun Demo (demo / demo123) tersedia & aktif
        try { \App\Services\DemoStoreService::generate(); } catch (\Throwable $e) {}
    }

    /**
     * 1. Tabel Cache & Session (Anti SQLSTATE 42S02)
     */
    private static function ensureSystemTables(): void
    {
        if (!Schema::hasTable('cache')) {
            Schema::create('cache', function (Blueprint $table) {
                $table->string('key')->primary();
                $table->mediumText('value');
                $table->integer('expiration');
            });
        }

        if (!Schema::hasTable('cache_locks')) {
            Schema::create('cache_locks', function (Blueprint $table) {
                $table->string('key')->primary();
                $table->string('owner');
                $table->integer('expiration');
            });
        }

        if (!Schema::hasTable('sessions')) {
            Schema::create('sessions', function (Blueprint $table) {
                $table->string('id')->primary();
                $table->unsignedBigInteger('user_id')->nullable()->index();
                $table->string('ip_address', 45)->nullable();
                $table->text('user_agent')->nullable();
                $table->longText('payload');
                $table->integer('last_activity')->index();
            });
        }
    }

    /**
     * 2. Tabel Toko (Multi-Tenant Management)
     */
    private static function ensureTokoTable(): void
    {
        if (!Schema::hasTable('toko')) {
            Schema::create('toko', function (Blueprint $table) {
                $table->id();
                $table->string('nama_toko');
                $table->string('slug')->unique();
                $table->text('alamat')->nullable();
                $table->string('no_telp', 50)->nullable();
                $table->string('logo')->nullable();
                $table->string('paket', 50)->default('enterprise');
                $table->string('status', 20)->default('aktif');
                $table->date('expired_at')->nullable();
                $table->timestamps();
            });
        }

        // Pastikan Toko ID 1 (Pusat) ada
        if (Schema::hasTable('toko')) {
            $defaultToko = DB::table('toko')->where('id', 1)->first();
            if (!$defaultToko) {
                $tCols = Schema::getColumnListing('toko');
                $tData = [
                    'id'         => 1,
                    'nama_toko'  => 'VxPOS Pusat',
                    'slug'       => 'vxpos-pusat',
                    'alamat'     => 'Kantor Pusat Eksekutif VxPOS',
                    'no_telp'    => '081234567890',
                    'paket'      => 'enterprise',
                    'status'     => 'aktif',
                ];
                if (in_array('expired_at', $tCols)) $tData['expired_at'] = now()->addYears(10)->toDateString();
                if (in_array('created_at', $tCols)) $tData['created_at'] = now()->toDateTimeString();
                if (in_array('updated_at', $tCols)) $tData['updated_at'] = now()->toDateTimeString();
                
                DB::table('toko')->insert($tData);
            }
        }
    }

    /**
     * 3. Struktur Tabel Users (Ditambahkan per-kolom secara aman)
     */
    private static function ensureUsersColumns(): void
    {
        if (!Schema::hasTable('users')) {
            Schema::create('users', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('toko_id')->nullable()->default(1)->index();
                $table->string('nama')->nullable();
                $table->string('username', 50)->nullable()->unique();
                $table->string('password')->nullable();
                $table->string('role', 30)->default('kasir');
                $table->boolean('is_platform_admin')->default(false);
                $table->string('status', 20)->default('aktif');
                $table->text('hak_akses')->nullable();
                $table->rememberToken();
                $table->timestamps();
            });
            return;
        }

        // Tambah kolom secara individual agar tidak ada kegagalan kaskade
        $cols = [
            'toko_id'           => fn($t) => $t->unsignedBigInteger('toko_id')->nullable()->default(1),
            'nama'              => fn($t) => $t->string('nama', 191)->nullable(),
            'name'              => fn($t) => $t->string('name', 191)->nullable(),
            'username'          => fn($t) => $t->string('username', 50)->nullable(),
            'email'             => fn($t) => $t->string('email', 191)->nullable(),
            'role'              => fn($t) => $t->string('role', 30)->default('admin'),
            'is_platform_admin' => fn($t) => $t->boolean('is_platform_admin')->default(false),
            'status'            => fn($t) => $t->string('status', 20)->default('aktif'),
            'hak_akses'         => fn($t) => $t->text('hak_akses')->nullable(),
        ];

        foreach ($cols as $colName => $fn) {
            try {
                if (!Schema::hasColumn('users', $colName)) {
                    Schema::table('users', function (Blueprint $table) use ($fn) {
                        $fn($table);
                    });
                }
            } catch (\Throwable $e) {}
        }

        // Pastikan kolom email dan name bersifat nullable agar tidak memblokir insert akun baru
        try {
            if (Schema::hasColumn('users', 'email')) {
                DB::statement("ALTER TABLE users MODIFY COLUMN email VARCHAR(255) NULL");
            }
            if (Schema::hasColumn('users', 'name')) {
                DB::statement("ALTER TABLE users MODIFY COLUMN name VARCHAR(255) NULL");
            }
            if (Schema::hasColumn('users', 'password')) {
                DB::statement("ALTER TABLE users MODIFY COLUMN password VARCHAR(255) NULL");
            }
            DB::statement("ALTER TABLE users DROP INDEX users_email_unique");
        } catch (\Throwable $e) {}

        // Sinkronisasi nama/name dan username/email
        try {
            if (Schema::hasColumn('users', 'name') && Schema::hasColumn('users', 'nama')) {
                DB::statement("UPDATE users SET nama = name WHERE (nama IS NULL OR nama = '') AND name IS NOT NULL");
                DB::statement("UPDATE users SET name = nama WHERE (name IS NULL OR name = '') AND nama IS NOT NULL");
            }
            if (Schema::hasColumn('users', 'email') && Schema::hasColumn('users', 'username')) {
                DB::statement("UPDATE users SET username = SUBSTRING_INDEX(email, '@', 1) WHERE (username IS NULL OR username = '') AND email IS NOT NULL");
                DB::statement("UPDATE users SET email = CONCAT(username, '@vxpos.id') WHERE (email IS NULL OR email = '') AND username IS NOT NULL");
            }
        } catch (\Throwable $e) {}
    }

    /**
     * 4. Pastikan Kolom 'toko_id' Ada di Seluruh Tabel Operasional (Anti SQLSTATE 42S22)
     */
    private static function ensureTenantColumns(): void
    {
        $tenantTables = [
            'transaksi',
            'detail_transaksi',
            'barang',
            'stok',
            'harga',
            'pelanggan',
            'pencairan_bonus',
            'penggajian',
            'permintaan_gudang',
            'riwayat_cicilan',
            'pengaturan_toko',
        ];

        foreach ($tenantTables as $tbl) {
            try {
                if (Schema::hasTable($tbl)) {
                    if (!Schema::hasColumn($tbl, 'toko_id')) {
                        Schema::table($tbl, function (Blueprint $table) {
                            $table->unsignedBigInteger('toko_id')->nullable()->default(1);
                        });
                    }

                    // Update data lama agar toko_id terisi 1
                    DB::table($tbl)->whereNull('toko_id')->orWhere('toko_id', 0)->update(['toko_id' => 1]);
                }
            } catch (\Throwable $e) {}
        }
    }

    /**
     * 5. Pastikan Akun Superadmin Platform (vicky & admin) tersedia & aktif
     */
    private static function ensureSuperAdminAccounts(): void
    {
        if (!Schema::hasTable('users')) {
            return;
        }

        $tableCols = Schema::getColumnListing('users');
        $allHakAkses = [
            'master_barang', 'manajemen_harga', 'transaksi_sales',
            'stok_gudang', 'validasi_kasir', 'laporan_penjualan',
            'kelola_bonus', 'manajemen_user'
        ];

        // 1. Akun Pemilik Sistem: vicky
        $vickyData = [
            'username'   => 'vicky',
            'password'   => Hash::make('admin123'),
            'updated_at' => now()->toDateTimeString(),
        ];
        if (in_array('nama', $tableCols)) $vickyData['nama'] = 'Vicky Koroh (Platform Owner)';
        if (in_array('name', $tableCols)) $vickyData['name'] = 'Vicky Koroh (Platform Owner)';
        if (in_array('email', $tableCols)) $vickyData['email'] = 'vicky@vxpos.id';
        if (in_array('toko_id', $tableCols)) $vickyData['toko_id'] = 1;
        if (in_array('role', $tableCols)) $vickyData['role'] = 'superadmin';
        if (in_array('is_platform_admin', $tableCols)) $vickyData['is_platform_admin'] = 1;
        if (in_array('status', $tableCols)) $vickyData['status'] = 'aktif';
        if (in_array('hak_akses', $tableCols)) $vickyData['hak_akses'] = json_encode($allHakAkses);

        $vicky = DB::table('users')->where('username', 'vicky')->first();
        if (!$vicky) {
            if (in_array('created_at', $tableCols)) $vickyData['created_at'] = now()->toDateTimeString();
            DB::table('users')->insert($vickyData);
        } else {
            DB::table('users')->where('id', $vicky->id)->update($vickyData);
        }

        // 2. Akun Super Admin: admin
        $adminData = [
            'username'   => 'admin',
            'password'   => Hash::make('admin123'),
            'updated_at' => now()->toDateTimeString(),
        ];
        if (in_array('nama', $tableCols)) $adminData['nama'] = 'Super Admin Utama';
        if (in_array('name', $tableCols)) $adminData['name'] = 'Super Admin Utama';
        if (in_array('email', $tableCols)) $adminData['email'] = 'admin@vxpos.id';
        if (in_array('toko_id', $tableCols)) $adminData['toko_id'] = 1;
        if (in_array('role', $tableCols)) $adminData['role'] = 'superadmin';
        if (in_array('is_platform_admin', $tableCols)) $adminData['is_platform_admin'] = 1;
        if (in_array('status', $tableCols)) $adminData['status'] = 'aktif';
        if (in_array('hak_akses', $tableCols)) $adminData['hak_akses'] = json_encode($allHakAkses);

        $admin = DB::table('users')->where('username', 'admin')->first();
        if (!$admin) {
            if (in_array('created_at', $tableCols)) $adminData['created_at'] = now()->toDateTimeString();
            DB::table('users')->insert($adminData);
        } else {
            DB::table('users')->where('id', $admin->id)->update($adminData);
        }

        // 3. Upgrade semua user yang memiliki role = 'superadmin'
        if (in_array('is_platform_admin', $tableCols)) {
            DB::table('users')->where('role', 'superadmin')->update([
                'is_platform_admin' => 1,
                'status'            => 'aktif',
            ]);
        }
    }

    /**
     * 6. Pembersihan Branding Lama & Pengaturan Toko
     */
    private static function ensureBranding(): void
    {
        if (Schema::hasTable('pengaturan_toko')) {
            $cek = DB::table('pengaturan_toko')->first();
            if (!$cek) {
                $ptCols = Schema::getColumnListing('pengaturan_toko');
                $ptData = [
                    'toko_id'    => 1,
                    'nama_toko'  => 'VxPOS',
                ];
                if (in_array('logo', $ptCols)) $ptData['logo'] = null;
                if (in_array('created_at', $ptCols)) $ptData['created_at'] = now()->toDateTimeString();
                if (in_array('updated_at', $ptCols)) $ptData['updated_at'] = now()->toDateTimeString();
                
                DB::table('pengaturan_toko')->insert($ptData);
            } else {
                DB::table('pengaturan_toko')
                    ->where(function($q) {
                        $q->where('nama_toko', 'like', '%Chanada%')
                          ->orWhere('nama_toko', 'like', '%Vx-Pos%')
                          ->orWhere('logo', 'like', '%1784947357%');
                    })
                    ->update([
                        'nama_toko' => 'VxPOS',
                        'logo'      => null,
                    ]);
            }
        }
    }
}
