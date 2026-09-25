<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Buat Tabel Master Toko (Tenants)
        if (!Schema::hasTable('toko')) {
            Schema::create('toko', function (Blueprint $table) {
                $table->id();
                $table->string('nama_toko');
                $table->string('slug')->unique();
                $table->text('alamat')->nullable();
                $table->string('no_telp', 50)->nullable();
                $table->string('logo')->nullable();
                $table->string('paket', 50)->default('pro'); // starter, pro, enterprise
                $table->string('status', 20)->default('aktif'); // aktif, nonaktif, suspended
                $table->date('expired_at')->nullable();
                $table->timestamps();
            });
        }

        // 2. Tambah toko_id dan is_platform_admin ke tabel users
        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                if (!Schema::hasColumn('users', 'toko_id')) {
                    $table->unsignedBigInteger('toko_id')->nullable()->after('id')->index();
                }
                if (!Schema::hasColumn('users', 'is_platform_admin')) {
                    $table->boolean('is_platform_admin')->default(false)->after('role');
                }
            });
        }

        // 3. Tambah toko_id ke tabel barang
        if (Schema::hasTable('barang') && !Schema::hasColumn('barang', 'toko_id')) {
            Schema::table('barang', function (Blueprint $table) {
                $table->unsignedBigInteger('toko_id')->nullable()->after('id')->index();
            });
        }

        // 4. Tambah toko_id ke tabel transaksi
        if (Schema::hasTable('transaksi') && !Schema::hasColumn('transaksi', 'toko_id')) {
            Schema::table('transaksi', function (Blueprint $table) {
                $table->unsignedBigInteger('toko_id')->nullable()->after('id')->index();
            });
        }

        // 5. Tambah toko_id dan harga_modal (COGS historis) ke tabel detail_transaksi
        if (Schema::hasTable('detail_transaksi')) {
            Schema::table('detail_transaksi', function (Blueprint $table) {
                if (!Schema::hasColumn('detail_transaksi', 'toko_id')) {
                    $table->unsignedBigInteger('toko_id')->nullable()->after('id')->index();
                }
                if (!Schema::hasColumn('detail_transaksi', 'harga_modal')) {
                    $table->decimal('harga_modal', 15, 2)->default(0)->after('harga_jual');
                }
            });
        }

        // 6. Tambah toko_id ke tabel permintaan_gudang
        if (Schema::hasTable('permintaan_gudang') && !Schema::hasColumn('permintaan_gudang', 'toko_id')) {
            Schema::table('permintaan_gudang', function (Blueprint $table) {
                $table->unsignedBigInteger('toko_id')->nullable()->after('id')->index();
            });
        }

        // 7. Tambah toko_id ke tabel riwayat_cicilan
        if (Schema::hasTable('riwayat_cicilan') && !Schema::hasColumn('riwayat_cicilan', 'toko_id')) {
            Schema::table('riwayat_cicilan', function (Blueprint $table) {
                $table->unsignedBigInteger('toko_id')->nullable()->after('id')->index();
            });
        }

        // 8. Tambah toko_id ke tabel pencairan_bonus
        if (Schema::hasTable('pencairan_bonus') && !Schema::hasColumn('pencairan_bonus', 'toko_id')) {
            Schema::table('pencairan_bonus', function (Blueprint $table) {
                $table->unsignedBigInteger('toko_id')->nullable()->after('id')->index();
            });
        }

        // 9. Tambah toko_id ke tabel penggajian
        if (Schema::hasTable('penggajian') && !Schema::hasColumn('penggajian', 'toko_id')) {
            Schema::table('penggajian', function (Blueprint $table) {
                $table->unsignedBigInteger('toko_id')->nullable()->after('id')->index();
            });
        }

        // 10. Tambah toko_id ke tabel pengaturan_toko
        if (Schema::hasTable('pengaturan_toko') && !Schema::hasColumn('pengaturan_toko', 'toko_id')) {
            Schema::table('pengaturan_toko', function (Blueprint $table) {
                $table->unsignedBigInteger('toko_id')->nullable()->after('id')->index();
            });
        }

        // 11. Otomatis Migrasikan Toko Eksisting (Vx-Pos) sebagai Toko Utama ID #1
        $tokoCount = DB::table('toko')->count();
        if ($tokoCount === 0) {
            $existingPengaturan = DB::table('pengaturan_toko')->first();
            $namaToko = $existingPengaturan->nama_toko ?? 'Vx-Pos';
            $logo = $existingPengaturan->logo ?? null;

            $tokoId = DB::table('toko')->insertGetId([
                'nama_toko'  => $namaToko,
                'slug'       => 'vx-pos',
                'alamat'     => 'Jl. Raya Otomotif No. 1',
                'no_telp'    => '08123456789',
                'logo'       => $logo,
                'paket'      => 'pro',
                'status'     => 'aktif',
                'expired_at' => now()->addYears(5),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Kaitkan seluruh data lama ke Toko Utama agar tidak ada data hilang
            $tablesToUpdate = [
                'users', 'barang', 'transaksi', 'detail_transaksi',
                'permintaan_gudang', 'riwayat_cicilan', 'pencairan_bonus',
                'penggajian', 'pengaturan_toko'
            ];

            foreach ($tablesToUpdate as $tbl) {
                if (Schema::hasTable($tbl) && Schema::hasColumn($tbl, 'toko_id')) {
                    DB::table($tbl)->whereNull('toko_id')->update(['toko_id' => $tokoId]);
                }
            }

            // Set akun superadmin pertama sebagai Platform Admin
            if (Schema::hasTable('users')) {
                DB::table('users')->where('role', 'superadmin')->update([
                    'is_platform_admin' => true
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Safe rollback
        $tables = [
            'barang', 'transaksi', 'detail_transaksi', 'permintaan_gudang',
            'riwayat_cicilan', 'pencairan_bonus', 'penggajian', 'pengaturan_toko'
        ];

        foreach ($tables as $tbl) {
            if (Schema::hasTable($tbl) && Schema::hasColumn($tbl, 'toko_id')) {
                Schema::table($tbl, function (Blueprint $table) {
                    $table->dropColumn('toko_id');
                });
            }
        }

        if (Schema::hasTable('detail_transaksi') && Schema::hasColumn('detail_transaksi', 'harga_modal')) {
            Schema::table('detail_transaksi', function (Blueprint $table) {
                $table->dropColumn('harga_modal');
            });
        }

        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                if (Schema::hasColumn('users', 'toko_id')) $table->dropColumn('toko_id');
                if (Schema::hasColumn('users', 'is_platform_admin')) $table->dropColumn('is_platform_admin');
            });
        }

        Schema::dropIfExists('toko');
    }
};
