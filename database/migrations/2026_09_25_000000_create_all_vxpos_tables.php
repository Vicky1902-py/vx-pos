<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Tabel Toko (Tenants)
        if (!Schema::hasTable('toko')) {
            Schema::create('toko', function (Blueprint $table) {
                $table->id();
                $table->string('nama_toko');
                $table->string('slug')->unique();
                $table->text('alamat')->nullable();
                $table->string('no_telp', 50)->nullable();
                $table->string('logo')->nullable();
                $table->string('paket', 50)->default('pro'); // starter, pro, enterprise
                $table->string('status', 20)->default('aktif');
                $table->date('expired_at')->nullable();
                $table->timestamps();
            });
        }

        // 2. Tabel Users (Karyawan & Administrator)
        if (!Schema::hasTable('users')) {
            Schema::create('users', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('toko_id')->nullable()->index();
                $table->string('nama');
                $table->string('username', 50)->unique();
                $table->string('password');
                $table->string('role', 30)->default('kasir'); // superadmin, admin, kasir, sales, gudang
                $table->boolean('is_platform_admin')->default(false);
                $table->string('status', 20)->default('aktif');
                $table->text('hak_akses')->nullable(); // JSON
                $table->rememberToken();
                $table->timestamps();
            });
        } else {
            Schema::table('users', function (Blueprint $table) {
                if (!Schema::hasColumn('users', 'toko_id')) {
                    $table->unsignedBigInteger('toko_id')->nullable()->after('id')->index();
                }
                if (!Schema::hasColumn('users', 'nama') && Schema::hasColumn('users', 'name')) {
                    $table->string('nama')->nullable()->after('toko_id');
                }
                if (!Schema::hasColumn('users', 'username')) {
                    $table->string('username', 50)->nullable()->after('nama');
                }
                if (!Schema::hasColumn('users', 'role')) {
                    $table->string('role', 30)->default('kasir')->after('password');
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
        }

        // 3. Tabel Pengaturan Toko
        if (!Schema::hasTable('pengaturan_toko')) {
            Schema::create('pengaturan_toko', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('toko_id')->nullable()->index();
                $table->string('nama_toko')->default('VxPOS');
                $table->string('logo')->nullable();
                $table->text('alamat')->nullable();
                $table->string('telepon', 50)->nullable();
                $table->timestamps();
            });
        }

        // 4. Tabel Master Barang
        if (!Schema::hasTable('barang')) {
            Schema::create('barang', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('toko_id')->nullable()->index();
                $table->string('kode_barang');
                $table->string('nama_barang');
                $table->string('kategori')->default('Umum');
                $table->string('satuan', 50)->default('Pcs');
                $table->string('status', 20)->default('aktif');
                $table->timestamps();
            });
        }

        // 5. Tabel Stok Fisik
        if (!Schema::hasTable('stok')) {
            Schema::create('stok', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('barang_id')->index();
                $table->integer('stok_tersedia')->default(0);
                $table->integer('stok_minimum')->default(0);
                $table->string('status_warning', 20)->default('aman'); // aman, warning
                $table->timestamps();
            });
        }

        // 6. Tabel Harga & Diskon (Anti-Rugi)
        if (!Schema::hasTable('harga')) {
            Schema::create('harga', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('barang_id')->index();
                $table->decimal('harga_modal', 15, 2)->default(0);
                $table->decimal('harga_minimum', 15, 2)->default(0);
                $table->decimal('harga_jual', 15, 2)->default(0);
                $table->decimal('diskon_rupiah', 15, 2)->default(0);
                $table->timestamps();
            });
        }

        // 7. Tabel Induk Transaksi
        if (!Schema::hasTable('transaksi')) {
            Schema::create('transaksi', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('toko_id')->nullable()->index();
                $table->string('no_invoice')->index();
                $table->unsignedBigInteger('sales_id')->nullable()->index();
                $table->unsignedBigInteger('pelanggan_id')->nullable();
                $table->string('nama_pelanggan')->default('Umum');
                $table->string('status', 30)->default('pending'); // pending, disiapkan_gudang, selesai
                $table->decimal('total_transaksi', 15, 2)->default(0);
                $table->decimal('diskon', 15, 2)->default(0);
                $table->decimal('dp', 15, 2)->default(0);
                $table->decimal('piutang', 15, 2)->default(0);
                $table->timestamps();
            });
        }

        // 8. Tabel Detail Transaksi
        if (!Schema::hasTable('detail_transaksi')) {
            Schema::create('detail_transaksi', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('toko_id')->nullable()->index();
                $table->unsignedBigInteger('transaksi_id')->index();
                $table->unsignedBigInteger('barang_id')->index();
                $table->integer('jumlah')->default(1);
                $table->decimal('harga_modal', 15, 2)->default(0); // COGS historis anti-rusak
                $table->decimal('harga_jual', 15, 2)->default(0);
                $table->decimal('diskon_item', 15, 2)->default(0);
                $table->decimal('subtotal', 15, 2)->default(0);
                $table->timestamps();
            });
        }

        // 9. Tabel Permintaan Gudang
        if (!Schema::hasTable('permintaan_gudang')) {
            Schema::create('permintaan_gudang', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('toko_id')->nullable()->index();
                $table->unsignedBigInteger('transaksi_id')->index();
                $table->string('status', 30)->default('menunggu'); // menunggu, disiapkan
                $table->unsignedBigInteger('diperbarui_oleh')->nullable();
                $table->timestamps();
            });
        }

        // 10. Tabel Riwayat Cicilan & Piutang
        if (!Schema::hasTable('riwayat_cicilan')) {
            Schema::create('riwayat_cicilan', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('toko_id')->nullable()->index();
                $table->unsignedBigInteger('transaksi_id')->index();
                $table->decimal('nominal_bayar', 15, 2)->default(0);
                $table->string('keterangan')->nullable();
                $table->dateTime('tanggal_bayar')->nullable();
                $table->timestamps();
            });
        }

        // 11. Tabel Pencairan Bonus Sales
        if (!Schema::hasTable('pencairan_bonus')) {
            Schema::create('pencairan_bonus', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('toko_id')->nullable()->index();
                $table->unsignedBigInteger('sales_id')->index();
                $table->decimal('total_bonus', 15, 2)->default(0);
                $table->string('keterangan')->nullable();
                $table->timestamps();
            });
        }

        // 12. Tabel Penggajian (Payroll)
        if (!Schema::hasTable('penggajian')) {
            Schema::create('penggajian', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('toko_id')->nullable()->index();
                $table->unsignedBigInteger('user_id')->index();
                $table->string('periode_bulan', 2);
                $table->string('periode_tahun', 4);
                $table->decimal('gaji_pokok', 15, 2)->default(0);
                $table->decimal('tunjangan', 15, 2)->default(0);
                $table->decimal('bonus', 15, 2)->default(0);
                $table->decimal('potongan', 15, 2)->default(0);
                $table->string('keterangan_potongan')->nullable();
                $table->decimal('total_gaji', 15, 2)->default(0);
                $table->dateTime('tanggal_cair')->nullable();
                $table->timestamps();
            });
        }

        // 13. SEED DATA AWAL: Toko Utama VxPOS, Pengaturan, & Akun Superadmin
        $tokoCount = DB::table('toko')->count();
        if ($tokoCount === 0) {
            $tokoId = DB::table('toko')->insertGetId([
                'nama_toko'  => 'VxPOS',
                'slug'       => 'vxpos',
                'alamat'     => 'Jl. Sistem Modern No. 1',
                'no_telp'    => '08123456789',
                'paket'      => 'enterprise',
                'status'     => 'aktif',
                'expired_at' => now()->addYears(10),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('pengaturan_toko')->insert([
                'toko_id'    => $tokoId,
                'nama_toko'  => 'VxPOS',
                'alamat'     => 'Jl. Sistem Modern No. 1',
                'telepon'    => '08123456789',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $semuaHakAkses = [
                'master_barang', 'manajemen_harga', 'transaksi_sales',
                'stok_gudang', 'validasi_kasir', 'laporan_penjualan',
                'kelola_bonus', 'manajemen_user'
            ];

            // Akun Administrator Utama
            $adminExists = DB::table('users')->where('username', 'admin')->first();
            if (!$adminExists) {
                DB::table('users')->insert([
                    'toko_id'           => $tokoId,
                    'nama'              => 'Super Admin VxPOS',
                    'username'          => 'admin',
                    'password'          => Hash::make('admin123'),
                    'role'              => 'superadmin',
                    'is_platform_admin' => true,
                    'status'            => 'aktif',
                    'hak_akses'         => json_encode($semuaHakAkses),
                    'created_at'        => now(),
                    'updated_at'        => now(),
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('penggajian');
        Schema::dropIfExists('pencairan_bonus');
        Schema::dropIfExists('riwayat_cicilan');
        Schema::dropIfExists('permintaan_gudang');
        Schema::dropIfExists('detail_transaksi');
        Schema::dropIfExists('transaksi');
        Schema::dropIfExists('harga');
        Schema::dropIfExists('stok');
        Schema::dropIfExists('barang');
        Schema::dropIfExists('pengaturan_toko');
        Schema::dropIfExists('toko');
    }
};
