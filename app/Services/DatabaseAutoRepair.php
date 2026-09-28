<?php

namespace App\Services;

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Schema\Blueprint;

class DatabaseAutoRepair
{
    private static bool $isRepairing = false;

    /**
     * Jalankan self-healing dan perbaikan otomatis database VxPOS
     * Setiap langkah dijalankan secara terisolasi agar kegagalan parsial tidak menghentikan perbaikan lain
     */
    public static function repair(): void
    {
        if (self::$isRepairing) {
            return;
        }

        self::$isRepairing = true;

        try {
            // 1. Pastikan tabel 'cache', 'cache_locks', dan 'sessions' ada
            try { self::ensureSystemTables(); } catch (\Throwable $e) {}

            // 2. Pastikan tabel 'toko' ada dan berisi default Toko Pusat
            try { self::ensureTokoTable(); } catch (\Throwable $e) {}

            // 3. Pastikan kolom-kolom multi-tenant di tabel 'users' ada
            try { self::ensureUsersColumns(); } catch (\Throwable $e) {}

            // 4. Pastikan kolom 'toko_id' ada di semua tabel operasional
            try { self::ensureTenantColumns(); } catch (\Throwable $e) {}

            // 4.5 Pastikan kolom baru di tabel operasional ada (migrasi chanada lama)
            try { self::ensureOperationalColumns(); } catch (\Throwable $e) {}

            // 5. Pastikan akun Superadmin Utama (vicky & admin) tersedia & aktif
            try { self::ensureSuperAdminAccounts(); } catch (\Throwable $e) {}

            // 6. Sinkronisasi nama default VxPOS & hapus file logo chanada lama
            try { self::ensureBranding(); } catch (\Throwable $e) {}

            // 7. Pastikan Toko Demo dan Akun Demo (demo / demo123) tersedia & aktif
            try { \App\Services\DemoStoreService::generate(); } catch (\Throwable $e) {}

            // 8. Pastikan tabel 'live_traffic' & 'live_traffic_logs' untuk Live Traffic Monitor
            try { self::ensureLiveTrafficTables(); } catch (\Throwable $e) {}

            // 9. Pastikan tabel 'landing_page_settings' untuk CMS Landing Page
            try { self::ensureLandingPageSettings(); } catch (\Throwable $e) {}
        } finally {
            self::$isRepairing = false;
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
            'gaji_pokok'        => fn($t) => $t->decimal('gaji_pokok', 15, 2)->default(0)->nullable(),
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

        // Set default gaji pokok standar untuk akun demo jika masih 0 / null
        try {
            if (Schema::hasColumn('users', 'gaji_pokok')) {
                DB::table('users')->where('username', 'demo')->where(function($q) { $q->whereNull('gaji_pokok')->orWhere('gaji_pokok', 0); })->update(['gaji_pokok' => 1750000]);
                DB::table('users')->where('username', 'kasir_demo')->where(function($q) { $q->whereNull('gaji_pokok')->orWhere('gaji_pokok', 0); })->update(['gaji_pokok' => 1500000]);
                DB::table('users')->where('username', 'sales_demo')->where(function($q) { $q->whereNull('gaji_pokok')->orWhere('gaji_pokok', 0); })->update(['gaji_pokok' => 1400000]);
                DB::table('users')->where('username', 'gudang_demo')->where(function($q) { $q->whereNull('gaji_pokok')->orWhere('gaji_pokok', 0); })->update(['gaji_pokok' => 1500000]);
            }
        } catch (\Throwable $e) {}

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
     * 4.5 Pastikan kolom baru di tabel operasional ada (migrasi chanada lama)
     */
    private static function ensureOperationalColumns(): void
    {
        if (Schema::hasTable('transaksi')) {
            try {
                Schema::table('transaksi', function (Blueprint $table) {
                    if (!Schema::hasColumn('transaksi', 'pelanggan_id')) $table->unsignedBigInteger('pelanggan_id')->nullable()->after('sales_id');
                    if (!Schema::hasColumn('transaksi', 'nama_pelanggan')) $table->string('nama_pelanggan')->default('Umum')->after('pelanggan_id');
                    if (!Schema::hasColumn('transaksi', 'diskon')) $table->decimal('diskon', 15, 2)->default(0)->after('total_transaksi');
                    if (!Schema::hasColumn('transaksi', 'dp')) $table->decimal('dp', 15, 2)->default(0)->after('diskon');
                    if (!Schema::hasColumn('transaksi', 'piutang')) $table->decimal('piutang', 15, 2)->default(0)->after('dp');
                });
            } catch (\Throwable $e) {}
        }

        if (Schema::hasTable('detail_transaksi')) {
            try {
                Schema::table('detail_transaksi', function (Blueprint $table) {
                    if (!Schema::hasColumn('detail_transaksi', 'harga_modal')) $table->decimal('harga_modal', 15, 2)->default(0)->after('jumlah');
                    if (!Schema::hasColumn('detail_transaksi', 'harga_jual')) $table->decimal('harga_jual', 15, 2)->default(0)->after('harga_modal');
                    if (!Schema::hasColumn('detail_transaksi', 'diskon_item')) $table->decimal('diskon_item', 15, 2)->default(0)->after('harga_jual');
                });
            } catch (\Throwable $e) {}
        }

        if (Schema::hasTable('stok')) {
            try {
                Schema::table('stok', function (Blueprint $table) {
                    if (!Schema::hasColumn('stok', 'stok_minimum')) $table->integer('stok_minimum')->default(0)->after('stok_tersedia');
                    if (!Schema::hasColumn('stok', 'status_warning')) $table->string('status_warning', 20)->default('aman')->after('stok_minimum');
                });
            } catch (\Throwable $e) {}
        }

        if (Schema::hasTable('harga')) {
            try {
                Schema::table('harga', function (Blueprint $table) {
                    if (!Schema::hasColumn('harga', 'harga_minimum')) $table->decimal('harga_minimum', 15, 2)->default(0)->after('harga_modal');
                    if (!Schema::hasColumn('harga', 'diskon_rupiah')) $table->decimal('diskon_rupiah', 15, 2)->default(0)->after('harga_jual');
                });
            } catch (\Throwable $e) {}
        }

        if (!Schema::hasTable('riwayat_cicilan')) {
            try {
                Schema::create('riwayat_cicilan', function (Blueprint $table) {
                    $table->id();
                    $table->unsignedBigInteger('toko_id')->nullable()->default(1);
                    $table->unsignedBigInteger('transaksi_id');
                    $table->decimal('nominal_bayar', 15, 2)->default(0);
                    $table->string('keterangan', 255)->nullable();
                    $table->dateTime('tanggal_bayar')->nullable();
                    $table->timestamps();
                });
            } catch (\Throwable $e) {}
        } else {
            try {
                Schema::table('riwayat_cicilan', function (Blueprint $table) {
                    if (!Schema::hasColumn('riwayat_cicilan', 'toko_id')) $table->unsignedBigInteger('toko_id')->nullable()->default(1);
                    if (!Schema::hasColumn('riwayat_cicilan', 'tanggal_bayar')) $table->dateTime('tanggal_bayar')->nullable();
                    if (!Schema::hasColumn('riwayat_cicilan', 'created_at')) $table->timestamp('created_at')->nullable();
                    if (!Schema::hasColumn('riwayat_cicilan', 'updated_at')) $table->timestamp('updated_at')->nullable();
                });
            } catch (\Throwable $e) {}
        }

        if (!Schema::hasTable('permintaan_gudang')) {
            try {
                Schema::create('permintaan_gudang', function (Blueprint $table) {
                    $table->id();
                    $table->unsignedBigInteger('toko_id')->nullable()->default(1);
                    $table->unsignedBigInteger('transaksi_id');
                    $table->string('status', 30)->default('menunggu');
                    $table->unsignedBigInteger('diperbarui_oleh')->nullable();
                    $table->timestamps();
                });
            } catch (\Throwable $e) {}
        } else {
            try {
                Schema::table('permintaan_gudang', function (Blueprint $table) {
                    if (!Schema::hasColumn('permintaan_gudang', 'toko_id')) $table->unsignedBigInteger('toko_id')->nullable()->default(1);
                    if (!Schema::hasColumn('permintaan_gudang', 'diperbarui_oleh')) $table->unsignedBigInteger('diperbarui_oleh')->nullable();
                    if (!Schema::hasColumn('permintaan_gudang', 'created_at')) $table->timestamp('created_at')->nullable();
                    if (!Schema::hasColumn('permintaan_gudang', 'updated_at')) $table->timestamp('updated_at')->nullable();
                });
            } catch (\Throwable $e) {}
        }

        if (!Schema::hasTable('pencairan_bonus')) {
            try {
                Schema::create('pencairan_bonus', function (Blueprint $table) {
                    $table->id();
                    $table->unsignedBigInteger('toko_id')->nullable()->default(1);
                    $table->unsignedBigInteger('sales_id');
                    $table->decimal('total_bonus', 15, 2)->default(0);
                    $table->string('periode', 100)->nullable();
                    $table->text('keterangan')->nullable();
                    $table->timestamps();
                });
            } catch (\Throwable $e) {}
        } else {
            try {
                Schema::table('pencairan_bonus', function (Blueprint $table) {
                    if (!Schema::hasColumn('pencairan_bonus', 'toko_id')) $table->unsignedBigInteger('toko_id')->nullable()->default(1);
                    if (!Schema::hasColumn('pencairan_bonus', 'periode')) $table->string('periode', 100)->nullable();
                    if (!Schema::hasColumn('pencairan_bonus', 'total_bonus')) $table->decimal('total_bonus', 15, 2)->default(0);
                    if (!Schema::hasColumn('pencairan_bonus', 'keterangan')) $table->text('keterangan')->nullable();
                    if (!Schema::hasColumn('pencairan_bonus', 'created_at')) $table->timestamp('created_at')->nullable();
                    if (!Schema::hasColumn('pencairan_bonus', 'updated_at')) $table->timestamp('updated_at')->nullable();
                });
            } catch (\Throwable $e) {}

            // Ubah kolom periode dan status menjadi NULL DEFAULT NULL agar MySQL strict mode tidak memblokir insert
            try {
                if (Schema::hasColumn('pencairan_bonus', 'periode')) {
                    DB::statement("ALTER TABLE `pencairan_bonus` MODIFY COLUMN `periode` VARCHAR(100) NULL DEFAULT NULL");
                }
                if (Schema::hasColumn('pencairan_bonus', 'status')) {
                    DB::statement("ALTER TABLE `pencairan_bonus` MODIFY COLUMN `status` VARCHAR(50) NULL DEFAULT NULL");
                }
            } catch (\Throwable $e) {}
        }

        if (!Schema::hasTable('penggajian')) {
            try {
                Schema::create('penggajian', function (Blueprint $table) {
                    $table->id();
                    $table->unsignedBigInteger('toko_id')->nullable()->default(1);
                    $table->unsignedBigInteger('user_id');
                    $table->string('periode_bulan', 2);
                    $table->string('periode_tahun', 4);
                    $table->decimal('gaji_pokok', 15, 2)->default(0);
                    $table->decimal('tunjangan', 15, 2)->default(0);
                    $table->decimal('bonus', 15, 2)->default(0);
                    $table->decimal('potongan', 15, 2)->default(0);
                    $table->string('keterangan_potongan', 255)->nullable();
                    $table->decimal('total_gaji', 15, 2)->default(0);
                    $table->dateTime('tanggal_cair')->nullable();
                    $table->timestamps();
                });
            } catch (\Throwable $e) {}
        } else {
            try {
                Schema::table('penggajian', function (Blueprint $table) {
                    if (!Schema::hasColumn('penggajian', 'toko_id')) $table->unsignedBigInteger('toko_id')->nullable()->default(1);
                    if (!Schema::hasColumn('penggajian', 'keterangan_potongan')) $table->string('keterangan_potongan', 255)->nullable();
                    if (!Schema::hasColumn('penggajian', 'tanggal_cair')) $table->dateTime('tanggal_cair')->nullable();
                    if (!Schema::hasColumn('penggajian', 'created_at')) $table->timestamp('created_at')->nullable();
                    if (!Schema::hasColumn('penggajian', 'updated_at')) $table->timestamp('updated_at')->nullable();
                });
            } catch (\Throwable $e) {}
        }

        // Pastikan baris-baris data lama dengan timestamp NULL memiliki nilai default agar query agregasi lancar
        try {
            if (Schema::hasTable('pencairan_bonus') && Schema::hasColumn('pencairan_bonus', 'created_at')) {
                DB::table('pencairan_bonus')->whereNull('created_at')->update(['created_at' => now(), 'updated_at' => now()]);
            }
            if (Schema::hasTable('penggajian') && Schema::hasColumn('penggajian', 'created_at')) {
                DB::table('penggajian')->whereNull('created_at')->update(['created_at' => now(), 'updated_at' => now()]);
            }
            if (Schema::hasTable('riwayat_cicilan') && Schema::hasColumn('riwayat_cicilan', 'created_at')) {
                DB::table('riwayat_cicilan')->whereNull('created_at')->update(['created_at' => now(), 'updated_at' => now()]);
            }
            if (Schema::hasTable('permintaan_gudang') && Schema::hasColumn('permintaan_gudang', 'created_at')) {
                DB::table('permintaan_gudang')->whereNull('created_at')->update(['created_at' => now(), 'updated_at' => now()]);
            }
        } catch (\Throwable $e) {}
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
            // JANGAN overwrite password user yang sudah ada!
            $safeVicky = [
                'status'            => 'aktif',
                'is_platform_admin' => 1,
                'role'              => 'superadmin',
                'updated_at'        => now()->toDateTimeString(),
            ];
            if (in_array('hak_akses', $tableCols)) $safeVicky['hak_akses'] = json_encode($allHakAkses);
            DB::table('users')->where('id', $vicky->id)->update($safeVicky);
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
            // JANGAN overwrite password admin yang sudah ada!
            $safeAdmin = [
                'status'            => 'aktif',
                'is_platform_admin' => 1,
                'role'              => 'superadmin',
                'updated_at'        => now()->toDateTimeString(),
            ];
            if (in_array('hak_akses', $tableCols)) $safeAdmin['hak_akses'] = json_encode($allHakAkses);
            DB::table('users')->where('id', $admin->id)->update($safeAdmin);
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

                // Bersihkan injeksi script deface dari pengaturan toko
                DB::table('pengaturan_toko')
                    ->where('nama_toko', 'like', '%<script%')
                    ->orWhere('nama_toko', 'like', '%defacer%')
                    ->orWhere('nama_toko', 'like', '%SecurityCrewz%')
                    ->orWhere('alamat', 'like', '%<script%')
                    ->update([
                        'nama_toko' => 'Toko Retail Demo (VxPOS)',
                        'alamat'    => 'Kupang NTT Maulafa',
                    ]);
            }
        }
    }

    /**
     * 7. Tabel Live Traffic & Activity Feed Monitoring
     */
    private static function ensureLiveTrafficTables(): void
    {
        if (!Schema::hasTable('live_traffic')) {
            Schema::create('live_traffic', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id')->nullable()->index();
                $table->unsignedBigInteger('toko_id')->nullable()->index();
                $table->string('username')->nullable();
                $table->string('nama')->nullable();
                $table->string('role')->nullable();
                $table->string('url')->nullable();
                $table->string('feature_name')->nullable();
                $table->string('method', 10)->default('GET');
                $table->string('ip_address', 45)->nullable();
                $table->text('user_agent')->nullable();
                $table->timestamp('last_active_at')->nullable()->index();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('live_traffic_logs')) {
            Schema::create('live_traffic_logs', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id')->nullable()->index();
                $table->unsignedBigInteger('toko_id')->nullable()->index();
                $table->string('username')->nullable();
                $table->string('nama')->nullable();
                $table->string('role')->nullable();
                $table->string('url')->nullable();
                $table->string('feature_name')->nullable();
                $table->string('method', 10)->default('GET');
                $table->string('ip_address', 45)->nullable();
                $table->timestamp('created_at')->nullable()->index();
            });
        }

        if (Schema::hasTable('users')) {
            if (!Schema::hasColumn('users', 'last_seen_at')) {
                try { DB::statement("ALTER TABLE `users` ADD COLUMN `last_seen_at` TIMESTAMP NULL DEFAULT NULL"); } catch (\Throwable $e) {}
            }
            if (!Schema::hasColumn('users', 'last_activity_url')) {
                try { DB::statement("ALTER TABLE `users` ADD COLUMN `last_activity_url` VARCHAR(255) NULL DEFAULT NULL"); } catch (\Throwable $e) {}
            }
            if (!Schema::hasColumn('users', 'last_ip')) {
                try { DB::statement("ALTER TABLE `users` ADD COLUMN `last_ip` VARCHAR(45) NULL DEFAULT NULL"); } catch (\Throwable $e) {}
            }
        }
    }

    /**
     * 8. Tabel CMS Pengaturan Landing Page
     */
    private static function ensureLandingPageSettings(): void
    {
        if (!Schema::hasTable('landing_page_settings')) {
            Schema::create('landing_page_settings', function (Blueprint $table) {
                $table->id();
                $table->string('brand_name')->default('VxPOS');
                $table->string('tagline')->default('Sistem Kasir & ERP Terpadu Multi-Toko');
                $table->string('logo')->nullable();
                $table->string('favicon')->nullable();
                $table->string('hero_badge')->nullable();
                $table->text('hero_title')->nullable();
                $table->text('hero_subtitle')->nullable();
                $table->string('cta_btn_primary_text')->nullable();
                $table->string('cta_btn_primary_link')->nullable();
                $table->string('cta_btn_secondary_text')->nullable();
                $table->string('cta_btn_secondary_link')->nullable();
                $table->string('wa_number')->nullable();
                $table->text('wa_message')->nullable();
                $table->string('stat_1_val')->nullable();
                $table->string('stat_1_label')->nullable();
                $table->string('stat_2_val')->nullable();
                $table->string('stat_2_label')->nullable();
                $table->string('stat_3_val')->nullable();
                $table->string('stat_3_label')->nullable();
                $table->string('pricing_starter')->nullable();
                $table->string('pricing_pro')->nullable();
                $table->string('pricing_enterprise')->nullable();
                $table->text('footer_desc')->nullable();
                $table->string('footer_address')->nullable();
                $table->string('footer_phone')->nullable();
                $table->string('footer_email')->nullable();
                $table->string('copyright_text')->nullable();
                $table->timestamps();
            });
        }

        $defaultLandingData = [
            'id'                     => 1,
            'brand_name'             => 'VxPOS',
            'tagline'                => 'Sistem Kasir & ERP Terpadu Multi-Toko',
            'hero_badge'             => '⚡ SOFTWARE ERP RETAIL & KASIR POINT OF SALE #1 DI INDONESIA',
            'hero_title'             => 'Kelola Banyak Toko & Cabang Jadi Lebih Cerdas, Cepat, dan Anti-Rugi.',
            'hero_subtitle'          => 'Aplikasi kasir multi-tenant masa kini dengan proteksi harga modal anti-kebocoran, integrasi gudang real-time, pencatatan kartu piutang otomatis, dan payroll komisi sales terpadu.',
            'cta_btn_primary_text'   => 'Coba Demo Sekarang',
            'cta_btn_primary_link'   => '/demo-login/admin',
            'cta_btn_secondary_text' => 'Konsultasi WhatsApp',
            'cta_btn_secondary_link' => '#konsultasi',
            'wa_number'              => '6281234567890',
            'wa_message'             => 'Halo Admin VxPOS, saya tertarik untuk menggunakan software kasir & ERP toko ini.',
            'stat_1_val'             => '10.000+',
            'stat_1_label'           => 'Transaksi Diproses',
            'stat_2_val'             => '99.9%',
            'stat_2_label'           => 'Akurasi Stok Gudang',
            'stat_3_val'             => '0%',
            'stat_3_label'           => 'Kebocoran Diskon',
            'pricing_starter'        => '99.000',
            'pricing_pro'            => '299.000',
            'pricing_enterprise'     => '799.000',
            'footer_desc'            => 'VxPOS adalah ekosistem Cloud Point of Sale dan Enterprise Resource Planning modern yang didesain untuk mendigitalkan toko grosir, ritel, minimarket, dan cabang usaha di seluruh Indonesia.',
            'footer_address'         => 'Jakarta & Surabaya, Indonesia',
            'footer_phone'           => '+62 812-3456-7890',
            'footer_email'           => 'support@vxpos.id',
            'copyright_text'         => 'VxPOS Point of Sale &bull; Hak Cipta by. Vicky Koroh',
            'created_at'             => now(),
            'updated_at'             => now(),
        ];

        // Pastikan ada setidaknya 1 baris default
        $exists = DB::table('landing_page_settings')->where('id', 1)->first();
        if (!$exists) {
            DB::table('landing_page_settings')->insert($defaultLandingData);
        } else {
            // Auto-Heal: Deteksi dan bersihkan jika pernah terkena script deface / XSS
            $isDefaced = false;
            foreach (['brand_name', 'hero_title', 'copyright_text', 'tagline', 'footer_desc'] as $col) {
                if (isset($exists->$col) && (stripos($exists->$col, '<script') !== false || stripos($exists->$col, 'defacer') !== false || stripos($exists->$col, 'SecurityCrewz') !== false)) {
                    $isDefaced = true;
                    break;
                }
            }
            if ($isDefaced) {
                DB::table('landing_page_settings')->where('id', 1)->update($defaultLandingData);
            }
        }
    }
}
