<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

class DemoStoreService
{
    /**
     * Buat atau Segarkan Toko Demo beserta data simulasi bisnis realistis
     */
    public static function generate(): array
    {
        try {
            // 1. Pastikan tabel toko ada
            if (!Schema::hasTable('toko')) {
                Schema::create('toko', function (\Illuminate\Database\Schema\Blueprint $table) {
                    $table->id();
                    $table->string('nama_toko');
                    $table->string('slug')->unique();
                    $table->text('alamat')->nullable();
                    $table->string('no_telp', 50)->nullable();
                    $table->string('logo')->nullable();
                    $table->string('paket', 50)->default('pro');
                    $table->string('status', 20)->default('aktif');
                    $table->date('expired_at')->nullable();
                    $table->timestamps();
                });
            }

            // Pastikan kolom-kolom penting di tabel users ada
            if (Schema::hasTable('users')) {
                $colsNeeded = [
                    'toko_id'           => 'bigInteger',
                    'nama'              => 'string',
                    'name'              => 'string',
                    'username'          => 'string',
                    'email'             => 'string',
                    'role'              => 'string',
                    'is_platform_admin' => 'boolean',
                    'status'            => 'string',
                    'hak_akses'         => 'text',
                ];
                foreach ($colsNeeded as $c => $t) {
                    try {
                        if (!Schema::hasColumn('users', $c)) {
                            Schema::table('users', function ($table) use ($c, $t) {
                                if ($t === 'bigInteger') $table->unsignedBigInteger($c)->nullable()->default(1);
                                elseif ($t === 'boolean') $table->boolean($c)->default(false);
                                elseif ($t === 'text') $table->text($c)->nullable();
                                else $table->string($c, 191)->nullable();
                            });
                        }
                    } catch (\Throwable $e) {}
                }
            }

            // 2. Buat atau perbarui Toko Demo
            $tokoId = 1;
            if (Schema::hasTable('toko')) {
                $toko = DB::table('toko')->where('slug', 'toko-demo')->first();
                if (!$toko) {
                    $tokoId = DB::table('toko')->insertGetId([
                        'nama_toko'  => 'Toko Retail Demo (VxPOS)',
                        'slug'       => 'toko-demo',
                        'alamat'     => 'Jl. Simulasi Bisnis No. 88, Menteng, Jakarta',
                        'no_telp'    => '0899-DEMO-VXPOS',
                        'paket'      => 'pro',
                        'status'     => 'aktif',
                        'expired_at' => now()->addYears(2)->toDateString(),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                } else {
                    $tokoId = $toko->id;
                    DB::table('toko')->where('id', $tokoId)->update([
                        'nama_toko'  => 'Toko Retail Demo (VxPOS)',
                        'paket'      => 'pro',
                        'status'     => 'aktif',
                        'expired_at' => now()->addYears(2)->toDateString(),
                        'updated_at' => now(),
                    ]);
                }
            }

            // 3. Pengaturan Toko Demo
            if (Schema::hasTable('pengaturan_toko')) {
                $cekPengaturan = DB::table('pengaturan_toko')->where('toko_id', $tokoId)->first();
                if (!$cekPengaturan) {
                    DB::table('pengaturan_toko')->insert([
                        'toko_id'    => $tokoId,
                        'nama_toko'  => 'Toko Retail Demo (VxPOS)',
                        'alamat'     => 'Jl. Simulasi Bisnis No. 88, Menteng, Jakarta',
                        'telepon'    => '0899-DEMO-VXPOS',
                        'logo'       => null,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }

            // 4. Buat Akun Pengguna Demo
            $allHakAkses = [
                'master_barang', 'manajemen_harga', 'transaksi_sales',
                'stok_gudang', 'validasi_kasir', 'laporan_penjualan',
                'kelola_bonus', 'manajemen_user'
            ];

            // Akun 1: Admin Toko Demo
            self::upsertUser($tokoId, 'Budi (Admin Toko Demo)', 'demo', 'demo123', 'admin', $allHakAkses);

            // Akun 2: Kasir Demo
            $kasirUser = self::upsertUser($tokoId, 'Siti (Kasir Toko Demo)', 'kasir_demo', 'demo123', 'kasir', ['transaksi_sales', 'validasi_kasir']);

            // Akun 3: Sales Demo
            $salesUser = self::upsertUser($tokoId, 'Doni (Sales Toko Demo)', 'sales_demo', 'demo123', 'sales', ['transaksi_sales']);

            // Akun 4: Staf Gudang Demo
            self::upsertUser($tokoId, 'Agus (Staf Gudang Demo)', 'gudang_demo', 'demo123', 'gudang', ['stok_gudang']);

            // 5. Sample Master Barang, Stok & Harga
            try {
                self::seedSampleProducts($tokoId);
            } catch (\Throwable $e) {}

            // 6. Sample Transaksi Realistis
            try {
                self::seedSampleTransactions($tokoId, $salesUser ? $salesUser->id : null);
            } catch (\Throwable $e) {}

            return [
                'success' => true,
                'toko_id' => $tokoId,
                'message' => 'Akun demo dan Toko Retail Demo berhasil dibuat dan disinkronkan!',
            ];
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'message' => 'Gagal membuat toko demo: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Helper create or update user dengan perlindungan menyeluruh terhadap kolom dinamis
     */
    private static function upsertUser(int $tokoId, string $nama, string $username, string $password, string $role, array $hakAkses)
    {
        if (!Schema::hasTable('users')) return null;

        $tableCols = Schema::getColumnListing('users');

        $user = DB::table('users')->where('username', $username)->first();

        $userData = [
            'username'   => $username,
            'password'   => Hash::make($password),
            'updated_at' => now(),
        ];

        if (in_array('nama', $tableCols)) $userData['nama'] = $nama;
        if (in_array('name', $tableCols)) $userData['name'] = $nama;
        if (in_array('email', $tableCols)) $userData['email'] = $username . '@vxpos.id';
        if (in_array('role', $tableCols)) $userData['role'] = $role;
        if (in_array('status', $tableCols)) $userData['status'] = 'aktif';
        if (in_array('toko_id', $tableCols)) $userData['toko_id'] = $tokoId;
        if (in_array('is_platform_admin', $tableCols)) $userData['is_platform_admin'] = 0;
        if (in_array('hak_akses', $tableCols)) $userData['hak_akses'] = json_encode($hakAkses);

        if (!$user) {
            if (in_array('created_at', $tableCols)) $userData['created_at'] = now();
            $userId = DB::table('users')->insertGetId($userData);
            return (object) ['id' => $userId, 'nama' => $nama, 'username' => $username];
        } else {
            DB::table('users')->where('id', $user->id)->update($userData);
            return (object) array_merge((array) $user, $userData);
        }
    }

    /**
     * Seeder Barang, Stok, & Harga Demo
     */
    private static function seedSampleProducts(int $tokoId)
    {
        if (!Schema::hasTable('barang')) return;

        $sampleBarang = [
            [
                'kode'     => 'DEMO-001',
                'nama'     => 'Kopi Arabika Premium 250gr',
                'kategori' => 'Minuman',
                'satuan'   => 'Pcs',
                'modal'    => 35000,
                'jual'     => 55000,
                'stok'     => 45,
                'min'      => 5,
            ],
            [
                'kode'     => 'DEMO-002',
                'nama'     => 'Teh Hitam Celup Organik',
                'kategori' => 'Minuman',
                'satuan'   => 'Box',
                'modal'    => 18000,
                'jual'     => 28000,
                'stok'     => 30,
                'min'      => 5,
            ],
            [
                'kode'     => 'DEMO-003',
                'nama'     => 'Kaos Polos Cotton Combed 30s',
                'kategori' => 'Fashion',
                'satuan'   => 'Pcs',
                'modal'    => 40000,
                'jual'     => 75000,
                'stok'     => 25,
                'min'      => 5,
            ],
            [
                'kode'     => 'DEMO-004',
                'nama'     => 'Kemeja Casual Oxford Premium',
                'kategori' => 'Fashion',
                'satuan'   => 'Pcs',
                'modal'    => 85000,
                'jual'     => 145000,
                'stok'     => 15,
                'min'      => 3,
            ],
            [
                'kode'     => 'DEMO-005',
                'nama'     => 'Tumbler Stainless Vacuum 500ml',
                'kategori' => 'Aksesoris',
                'satuan'   => 'Pcs',
                'modal'    => 50000,
                'jual'     => 89000,
                'stok'     => 2, // Rendah (Warning)
                'min'      => 5,
            ],
            [
                'kode'     => 'DEMO-006',
                'nama'     => 'Snack Keripik Singkong Renyah',
                'kategori' => 'Makanan',
                'satuan'   => 'Bungkus',
                'modal'    => 8000,
                'jual'     => 15000,
                'stok'     => 80,
                'min'      => 10,
            ],
        ];

        $hasBarangToko = Schema::hasColumn('barang', 'toko_id');
        $hasStokToko = Schema::hasTable('stok') && Schema::hasColumn('stok', 'toko_id');
        $hasHargaToko = Schema::hasTable('harga') && Schema::hasColumn('harga', 'toko_id');

        foreach ($sampleBarang as $item) {
            $barang = DB::table('barang')
                ->when($hasBarangToko, fn($q) => $q->where('toko_id', $tokoId))
                ->where('kode_barang', $item['kode'])
                ->first();

            if (!$barang) {
                $barangData = [
                    'kode_barang' => $item['kode'],
                    'nama_barang' => $item['nama'],
                    'kategori'    => $item['kategori'],
                    'satuan'      => $item['satuan'],
                    'status'      => 'aktif',
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ];
                if ($hasBarangToko) $barangData['toko_id'] = $tokoId;
                $barangId = DB::table('barang')->insertGetId($barangData);
            } else {
                $barangId = $barang->id;
            }

            // Stok
            if (Schema::hasTable('stok')) {
                $stokData = [
                    'stok_tersedia'  => $item['stok'],
                    'stok_minimum'   => $item['min'],
                    'status_warning' => ($item['stok'] <= $item['min']) ? 'warning' : 'aman',
                    'updated_at'     => now(),
                ];
                if ($hasStokToko) $stokData['toko_id'] = $tokoId;

                DB::table('stok')->updateOrInsert(
                    ['barang_id' => $barangId],
                    $stokData
                );
            }

            // Harga
            if (Schema::hasTable('harga')) {
                $hargaData = [
                    'harga_modal'   => $item['modal'],
                    'harga_minimum' => $item['modal'] * 1.1,
                    'harga_jual'    => $item['jual'],
                    'diskon_rupiah' => 0,
                    'updated_at'    => now(),
                ];
                if ($hasHargaToko) $hargaData['toko_id'] = $tokoId;

                DB::table('harga')->updateOrInsert(
                    ['barang_id' => $barangId],
                    $hargaData
                );
            }
        }
    }

    /**
     * Seeder Transaksi Sampel Toko Demo
     */
    private static function seedSampleTransactions(int $tokoId, ?int $salesId)
    {
        if (!Schema::hasTable('transaksi') || !Schema::hasTable('barang')) return;

        $hasTxToko = Schema::hasColumn('transaksi', 'toko_id');
        $count = DB::table('transaksi')->when($hasTxToko, fn($q) => $q->where('toko_id', $tokoId))->count();
        if ($count >= 2) return;

        $items = DB::table('barang')
            ->when(Schema::hasColumn('barang', 'toko_id'), fn($q) => $q->where('toko_id', $tokoId))
            ->limit(4)
            ->get();

        if ($items->count() < 2) return;

        // Transaksi 1: Selesai Lunas
        self::createTx(
            $tokoId,
            'INV-DEMO-001',
            $salesId,
            'Budi Santoso',
            'selesai',
            165000,
            165000,
            0,
            now(),
            [
                ['barang_id' => $items[0]->id, 'qty' => 2, 'modal' => 35000, 'jual' => 55000],
                ['barang_id' => $items[1]->id, 'qty' => 2, 'modal' => 18000, 'jual' => 28000],
            ]
        );

        // Transaksi 2: Selesai Cicilan / Piutang
        self::createTx(
            $tokoId,
            'INV-DEMO-002',
            $salesId,
            'Ibu Rina Wati',
            'selesai',
            220000,
            100000,
            120000,
            now()->subDays(2),
            [
                ['barang_id' => $items[0]->id, 'qty' => 4, 'modal' => 35000, 'jual' => 55000],
            ]
        );
    }

    private static function createTx(int $tokoId, string $invoice, ?int $salesId, string $namaPelanggan, string $status, float $total, float $dp, float $piutang, Carbon $tanggal, array $items)
    {
        $hasTxToko = Schema::hasColumn('transaksi', 'toko_id');
        $txData = [
            'no_invoice'      => $invoice,
            'sales_id'        => $salesId,
            'pelanggan_id'    => null,
            'nama_pelanggan'  => $namaPelanggan,
            'status'          => $status,
            'total_transaksi' => $total,
            'diskon'          => 0,
            'dp'              => $dp,
            'piutang'         => $piutang,
            'created_at'      => $tanggal,
            'updated_at'      => $tanggal,
        ];
        if ($hasTxToko) $txData['toko_id'] = $tokoId;

        $txId = DB::table('transaksi')->insertGetId($txData);

        if (Schema::hasTable('detail_transaksi')) {
            $hasDetailToko = Schema::hasColumn('detail_transaksi', 'toko_id');
            foreach ($items as $it) {
                $subtotal = $it['qty'] * $it['jual'];
                $detailData = [
                    'transaksi_id' => $txId,
                    'barang_id'    => $it['barang_id'],
                    'jumlah'       => $it['qty'],
                    'harga_modal'  => $it['modal'],
                    'harga_jual'   => $it['jual'],
                    'diskon_item'  => 0,
                    'subtotal'     => $subtotal,
                    'created_at'   => $tanggal,
                    'updated_at'   => $tanggal,
                ];
                if ($hasDetailToko) $detailData['toko_id'] = $tokoId;
                DB::table('detail_transaksi')->insert($detailData);
            }
        }
    }
}
