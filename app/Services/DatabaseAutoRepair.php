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
     */
    public static function repair(): void
    {
        try {
            // 1. Pastikan tabel 'cache', 'cache_locks', dan 'sessions' ada
            self::ensureSystemTables();

            // 2. Pastikan tabel 'toko' ada dan berisi default Toko Pusat
            self::ensureTokoTable();

            // 3. Pastikan kolom-kolom multi-tenant di tabel 'users' ada
            self::ensureUsersColumns();

            // 4. Pastikan kolom 'toko_id' ada di semua tabel operasional
            self::ensureTenantColumns();

            // 5. Pastikan akun Superadmin Utama (vicky & admin) tersedia & aktif
            self::ensureSuperAdminAccounts();

            // 6. Sinkronisasi nama default VxPOS & hapus file logo chanada lama
            self::ensureBranding();

            // 7. Pastikan Toko Demo dan Akun Demo (demo / demo123) tersedia & aktif
            \App\Services\DemoStoreService::generate();

        } catch (\Throwable $e) {
            // Catat error ke log tanpa menghentikan aplikasi jika database belum siap
            \Illuminate\Support\Facades\Log::warning('DatabaseAutoRepair Error: ' . $e->getMessage());
        }
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
                DB::table('toko')->insert([
                    'id'         => 1,
                    'nama_toko'  => 'VxPOS Pusat',
                    'slug'       => 'vxpos-pusat',
                    'alamat'     => 'Kantor Pusat Eksekutif VxPOS',
                    'no_telp'    => '081234567890',
                    'paket'      => 'enterprise',
                    'status'     => 'aktif',
                    'expired_at' => now()->addYears(10)->toDateString(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    /**
     * 3. Struktur Tabel Users
     */
    private static function ensureUsersColumns(): void
    {
        if (!Schema::hasTable('users')) {
            Schema::create('users', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('toko_id')->nullable()->default(1)->index();
                $table->string('nama');
                $table->string('username', 50)->unique();
                $table->string('password');
                $table->string('role', 30)->default('kasir');
                $table->boolean('is_platform_admin')->default(false);
                $table->string('status', 20)->default('aktif');
                $table->text('hak_akses')->nullable();
                $table->rememberToken();
                $table->timestamps();
            });
            return;
        }

        // Tambah kolom jika belum ada
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'toko_id')) {
                $table->unsignedBigInteger('toko_id')->nullable()->default(1)->after('id')->index();
            }
            if (!Schema::hasColumn('users', 'nama')) {
                $table->string('nama')->nullable()->after('toko_id');
            }
            if (!Schema::hasColumn('users', 'name')) {
                $table->string('name', 191)->nullable()->after('nama');
            }
            if (!Schema::hasColumn('users', 'username')) {
                $table->string('username', 50)->nullable()->after('nama');
            }
            if (!Schema::hasColumn('users', 'email')) {
                $table->string('email', 191)->nullable()->after('username');
            }
            if (!Schema::hasColumn('users', 'role')) {
                $table->string('role', 30)->default('admin')->after('password');
            }
            if (!Schema::hasColumn('users', 'is_platform_admin')) {
                $table->boolean('is_platform_admin')->default(false)->after('role');
            }
            if (!Schema::hasColumn('users', 'status')) {
                $table->string('status', 20)->default('aktif')->after('is_platform_admin');
            }
            if (!Schema::hasColumn('users', 'hak_akses')) {
                $table->text('hak_akses')->nullable()->after('status');
            }
        });

        // Pastikan kolom email dan name bersifat nullable agar tidak memblokir insert akun baru
        try {
            if (Schema::hasColumn('users', 'email')) {
                DB::statement("ALTER TABLE users MODIFY COLUMN email VARCHAR(255) NULL");
            }
            if (Schema::hasColumn('users', 'name')) {
                DB::statement("ALTER TABLE users MODIFY COLUMN name VARCHAR(255) NULL");
            }
            // Hapus index unique email lama agar tidak bentrok jika email null / string kosong
            DB::statement("ALTER TABLE users DROP INDEX users_email_unique");
        } catch (\Throwable $e) {}

        // Sinkronisasi data lama users: jika nama kosong tapi ada name, atau sebaliknya
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
            if (Schema::hasTable($tbl)) {
                if (!Schema::hasColumn($tbl, 'toko_id')) {
                    Schema::table($tbl, function (Blueprint $table) use ($tbl) {
                        $table->unsignedBigInteger('toko_id')->nullable()->default(1)->after('id')->index();
                    });
                }

                // Update data lama agar toko_id terisi 1
                try {
                    DB::table($tbl)->whereNull('toko_id')->orWhere('toko_id', 0)->update(['toko_id' => 1]);
                } catch (\Throwable $e) {}
            }
        }
    }

    /**
     * 5. Pastikan Akun Superadmin Platform (vicky & admin) dan Akun Admin Lama Berfungsi
     */
    private static function ensureSuperAdminAccounts(): void
    {
        if (!Schema::hasTable('users')) {
            return;
        }

        $allHakAkses = [
            'master_barang', 'manajemen_harga', 'transaksi_sales',
            'stok_gudang', 'validasi_kasir', 'laporan_penjualan',
            'kelola_bonus', 'manajemen_user'
        ];

        // 1. Akun Pemilik Sistem: vicky
        $vicky = DB::table('users')->where('username', 'vicky')->first();
        $vickyData = [
            'toko_id'           => 1,
            'nama'              => 'Vicky Koroh (Platform Owner)',
            'username'          => 'vicky',
            'password'          => Hash::make('admin123'),
            'role'              => 'superadmin',
            'is_platform_admin' => 1,
            'status'            => 'aktif',
            'hak_akses'         => json_encode($allHakAkses),
            'updated_at'        => now(),
        ];
        if (Schema::hasColumn('users', 'name')) $vickyData['name'] = 'Vicky Koroh (Platform Owner)';
        if (Schema::hasColumn('users', 'email')) $vickyData['email'] = 'vicky@vxpos.id';

        if (!$vicky) {
            $vickyData['created_at'] = now();
            DB::table('users')->insert($vickyData);
        } else {
            DB::table('users')->where('id', $vicky->id)->update($vickyData);
        }

        // 2. Akun Super Admin: admin
        $admin = DB::table('users')->where('username', 'admin')->first();
        $adminData = [
            'toko_id'           => 1,
            'nama'              => 'Super Admin Utama',
            'username'          => 'admin',
            'password'          => Hash::make('admin123'),
            'role'              => 'superadmin',
            'is_platform_admin' => 1,
            'status'            => 'aktif',
            'hak_akses'         => json_encode($allHakAkses),
            'updated_at'        => now(),
        ];
        if (Schema::hasColumn('users', 'name')) $adminData['name'] = 'Super Admin Utama';
        if (Schema::hasColumn('users', 'email')) $adminData['email'] = 'admin@vxpos.id';

        if (!$admin) {
            $adminData['created_at'] = now();
            DB::table('users')->insert($adminData);
        } else {
            DB::table('users')->where('id', $admin->id)->update($adminData);
        }

        // 3. Upgrade semua user yang memiliki role = 'superadmin' agar langsung mendapatkan hak Platform Admin
        DB::table('users')->where('role', 'superadmin')->update([
            'is_platform_admin' => 1,
            'status'            => 'aktif',
        ]);
    }

    /**
     * 6. Pembersihan Branding Lama & Pengaturan Toko
     */
    private static function ensureBranding(): void
    {
        if (Schema::hasTable('pengaturan_toko')) {
            $cek = DB::table('pengaturan_toko')->first();
            if (!$cek) {
                DB::table('pengaturan_toko')->insert([
                    'toko_id'    => 1,
                    'nama_toko'  => 'VxPOS',
                    'logo'       => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
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
