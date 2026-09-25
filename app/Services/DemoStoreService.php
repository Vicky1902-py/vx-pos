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
        DB::beginTransaction();
        try {
            // 1. Buat atau perbarui Toko Demo
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

            // 2. Pengaturan Toko Demo
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

            // 3. Buat Akun Pengguna Demo
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

            // 4. Sample Pelanggan Demo
            $pelangganId = null;
            if (Schema::hasTable('pelanggan')) {
                $hasTokoPelanggan = Schema::hasColumn('pelanggan', 'toko_id');
                $pelangganExist = DB::table('pelanggan')
                    ->when($hasTokoPelanggan, fn($q) => $q->where('toko_id', $tokoId))
                    ->where('nama_pelanggan', 'Budi Santoso')
                    ->first();

                if (!$pelangganExist) {
                    $insertData = [
                        'nama_pelanggan' => 'Budi Santoso',
                        'no_hp'          => '081234567891',
                        'alamat'         => 'Jl. Flamboyan No. 12, Jakarta',
                        'created_at'     => now(),
                        'updated_at'     => now(),
                    ];
                    if ($hasTokoPelanggan) $insertData['toko_id'] = $tokoId;
                    $pelangganId = DB::table('pelanggan')->insertGetId($insertData);
                } else {
                    $pelangganId = $pelangganExist->id;
                }
            }

            // 5. Sample Master Barang & Stok & Harga Demo
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

            $savedBarangIds = [];

            if (Schema::hasTable('barang')) {
                $hasBarangToko = Schema::hasColumn('barang', 'toko_id');
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

                    $savedBarangIds[] = [
                        'id'    => $barangId,
                        'nama'  => $item['nama'],
                        'modal' => $item['modal'],
                        'jual'  => $item['jual'],
                    ];

                    // Stok
                    if (Schema::hasTable('stok')) {
                        $hasStokToko = Schema::hasColumn('stok', 'toko_id');
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
                        $hasHargaToko = Schema::hasColumn('harga', 'toko_id');
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

            // 6. Sample Transaksi Realistis Toko Demo
            if (Schema::hasTable('transaksi') && count($savedBarangIds) >= 2) {
                $hasTxToko = Schema::hasColumn('transaksi', 'toko_id');
                $cekTx = DB::table('transaksi')
                    ->when($hasTxToko, fn($q) => $q->where('toko_id', $tokoId))
                    ->count();

                if ($cekTx < 3) {
                    $salesId = $salesUser ? $salesUser->id : null;

                    // Transaksi 1: Selesai Lunas Hari Ini
                    self::createSampleTransaction(
                        $tokoId,
                        'INV-DEMO-' . rand(1000, 9999),
                        $salesId,
                        $pelangganId,
                        'Budi Santoso',
                        'selesai',
                        165000, // total
                        165000, // dp/terbayar
                        0,      // piutang
                        now(),
                        [
                            ['barang_id' => $savedBarangIds[0]['id'], 'qty' => 2, 'modal' => $savedBarangIds[0]['modal'], 'jual' => $savedBarangIds[0]['jual']],
                            ['barang_id' => $savedBarangIds[1]['id'], 'qty' => 2, 'modal' => $savedBarangIds[1]['modal'], 'jual' => $savedBarangIds[1]['jual']],
                        ]
                    );

                    // Transaksi 2: Selesai dengan Piutang / Cicilan
                    self::createSampleTransaction(
                        $tokoId,
                        'INV-DEMO-' . rand(1000, 9999),
                        $salesId,
                        $pelangganId,
                        'Budi Santoso',
                        'selesai',
                        220000, // total
                        100000, // dp terbayar
                        120000, // piutang
                        now()->subDays(2),
                        [
                            ['barang_id' => $savedBarangIds[2]['id'], 'qty' => 2, 'modal' => $savedBarangIds[2]['modal'], 'jual' => $savedBarangIds[2]['jual']],
                            ['barang_id' => $savedBarangIds[3]['id'], 'qty' => 1, 'modal' => $savedBarangIds[3]['modal'], 'jual' => $savedBarangIds[3]['jual']],
                        ]
                    );

                    // Transaksi 3: Pending untuk Simulasi Kasir / Gudang
                    self::createSampleTransaction(
                        $tokoId,
                        'INV-DEMO-' . rand(1000, 9999),
                        $salesId,
                        null,
                        'Pelanggan Umum',
                        'pending',
                        70000,
                        0,
                        70000,
                        now(),
                        [
                            ['barang_id' => $savedBarangIds[5]['id'], 'qty' => 2, 'modal' => $savedBarangIds[5]['modal'], 'jual' => $savedBarangIds[5]['jual']],
                            ['barang_id' => $savedBarangIds[0]['id'], 'qty' => 1, 'modal' => $savedBarangIds[0]['modal'], 'jual' => $savedBarangIds[0]['jual']],
                        ]
                    );
                }
            }

            DB::commit();

            return [
                'success' => true,
                'toko_id' => $tokoId,
                'message' => 'Akun demo dan Toko Retail Demo berhasil dibuat dan disinkronkan dengan data simulasi realistis!',
            ];
        } catch (\Throwable $e) {
            DB::rollBack();
            return [
                'success' => false,
                'message' => 'Gagal membuat toko demo: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Helper create or update user
     */
    private static function upsertUser(int $tokoId, string $nama, string $username, string $password, string $role, array $hakAkses)
    {
        if (!Schema::hasTable('users')) return null;

        $user = DB::table('users')->where('username', $username)->first();
        if (!$user) {
            $userId = DB::table('users')->insertGetId([
                'toko_id'           => $tokoId,
                'nama'              => $nama,
                'username'          => $username,
                'password'          => Hash::make($password),
                'role'              => $role,
                'is_platform_admin' => 0,
                'status'            => 'aktif',
                'hak_akses'         => json_encode($hakAkses),
                'created_at'        => now(),
                'updated_at'        => now(),
            ]);
            return (object) ['id' => $userId];
        } else {
            DB::table('users')->where('id', $user->id)->update([
                'toko_id'           => $tokoId,
                'nama'              => $nama,
                'password'          => Hash::make($password),
                'role'              => $role,
                'is_platform_admin' => 0,
                'status'            => 'aktif',
                'hak_akses'         => json_encode($hakAkses),
                'updated_at'        => now(),
            ]);
            return $user;
        }
    }

    /**
     * Helper create sample transaction
     */
    private static function createSampleTransaction(int $tokoId, string $invoice, ?int $salesId, ?int $pelangganId, string $namaPelanggan, string $status, float $total, float $dp, float $piutang, Carbon $tanggal, array $items)
    {
        $hasTxToko = Schema::hasColumn('transaksi', 'toko_id');
        $txData = [
            'no_invoice'      => $invoice,
            'sales_id'        => $salesId,
            'pelanggan_id'    => $pelangganId,
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

        // Details
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
