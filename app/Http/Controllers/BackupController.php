<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Services\TenantManager;

class BackupController extends Controller
{
    public function index()
    {
        $tokoId = TenantManager::getTokoId();
        $toko = TenantManager::getActiveToko();
        $isPlatform = TenantManager::isPlatformAdmin();

        $stats = [
            'total_barang'    => Schema::hasTable('barang') ? DB::table('barang')->where('toko_id', $tokoId)->count() : 0,
            'total_transaksi' => Schema::hasTable('transaksi') ? DB::table('transaksi')->where('toko_id', $tokoId)->count() : 0,
            'total_user'      => Schema::hasTable('users') ? DB::table('users')->where('toko_id', $tokoId)->where('role', '!=', 'superadmin')->count() : 0,
            'total_cicilan'   => Schema::hasTable('riwayat_cicilan') ? DB::table('riwayat_cicilan')->where('toko_id', $tokoId)->count() : 0,
            'total_gaji'      => Schema::hasTable('penggajian') ? DB::table('penggajian')->where('toko_id', $tokoId)->count() : 0,
        ];

        return view('superadmin.backup.index', compact('toko', 'tokoId', 'stats', 'isPlatform'));
    }

    /**
     * Unduh Cadangan Data Khusus Toko Aktif (.vxbackup) dengan Protect ID
     */
    public function downloadStoreBackup()
    {
        set_time_limit(300);

        $tokoId = TenantManager::getTokoId();
        $toko = TenantManager::getActiveToko();
        $namaToko = $toko ? $toko->nama_toko : 'Toko';
        $slugToko = Str::slug($namaToko);
        $waktuSekarang = Carbon::now()->toDateTimeString();

        // 1. Kumpulkan Data Milik Toko Aktif
        $dataToko = [];

        // Pengaturan Toko
        if (Schema::hasTable('pengaturan_toko')) {
            $dataToko['pengaturan_toko'] = DB::table('pengaturan_toko')->where('toko_id', $tokoId)->get()->toArray();
        }

        // Master Barang & Relasi (Stok & Harga)
        $barangIds = [];
        if (Schema::hasTable('barang')) {
            $barangList = DB::table('barang')->where('toko_id', $tokoId)->get();
            $dataToko['barang'] = $barangList->toArray();
            $barangIds = $barangList->pluck('id')->toArray();
        }

        if (Schema::hasTable('stok') && !empty($barangIds)) {
            $dataToko['stok'] = DB::table('stok')->whereIn('barang_id', $barangIds)->get()->toArray();
        } else {
            $dataToko['stok'] = [];
        }

        if (Schema::hasTable('harga') && !empty($barangIds)) {
            $dataToko['harga'] = DB::table('harga')->whereIn('barang_id', $barangIds)->get()->toArray();
        } else {
            $dataToko['harga'] = [];
        }

        // Transaksi & Detail
        $transaksiIds = [];
        if (Schema::hasTable('transaksi')) {
            $transaksiList = DB::table('transaksi')->where('toko_id', $tokoId)->get();
            $dataToko['transaksi'] = $transaksiList->toArray();
            $transaksiIds = $transaksiList->pluck('id')->toArray();
        }

        if (Schema::hasTable('detail_transaksi')) {
            $detailQuery = DB::table('detail_transaksi');
            if (Schema::hasColumn('detail_transaksi', 'toko_id')) {
                $detailQuery->where('toko_id', $tokoId);
            } elseif (!empty($transaksiIds)) {
                $detailQuery->whereIn('transaksi_id', $transaksiIds);
            } else {
                $detailQuery->whereRaw('1 = 0');
            }
            $dataToko['detail_transaksi'] = $detailQuery->get()->toArray();
        } else {
            $dataToko['detail_transaksi'] = [];
        }

        // Riwayat Cicilan
        if (Schema::hasTable('riwayat_cicilan')) {
            $cicilanQuery = DB::table('riwayat_cicilan');
            if (Schema::hasColumn('riwayat_cicilan', 'toko_id')) {
                $cicilanQuery->where('toko_id', $tokoId);
            } elseif (!empty($transaksiIds)) {
                $cicilanQuery->whereIn('transaksi_id', $transaksiIds);
            } else {
                $cicilanQuery->whereRaw('1 = 0');
            }
            $dataToko['riwayat_cicilan'] = $cicilanQuery->get()->toArray();
        } else {
            $dataToko['riwayat_cicilan'] = [];
        }

        // Permintaan Gudang
        if (Schema::hasTable('permintaan_gudang')) {
            $gudangQuery = DB::table('permintaan_gudang');
            if (Schema::hasColumn('permintaan_gudang', 'toko_id')) {
                $gudangQuery->where('toko_id', $tokoId);
            } elseif (!empty($transaksiIds)) {
                $gudangQuery->whereIn('transaksi_id', $transaksiIds);
            } else {
                $gudangQuery->whereRaw('1 = 0');
            }
            $dataToko['permintaan_gudang'] = $gudangQuery->get()->toArray();
        } else {
            $dataToko['permintaan_gudang'] = [];
        }

        // Bonus Sales
        if (Schema::hasTable('pencairan_bonus')) {
            $dataToko['pencairan_bonus'] = DB::table('pencairan_bonus')->where('toko_id', $tokoId)->get()->toArray();
        } else {
            $dataToko['pencairan_bonus'] = [];
        }

        // Penggajian Pegawai
        if (Schema::hasTable('penggajian')) {
            $dataToko['penggajian'] = DB::table('penggajian')->where('toko_id', $tokoId)->get()->toArray();
        } else {
            $dataToko['penggajian'] = [];
        }

        // Pegawai Toko (Kecuali Superadmin Global)
        if (Schema::hasTable('users')) {
            $dataToko['users'] = DB::table('users')
                ->where('toko_id', $tokoId)
                ->where('role', '!=', 'superadmin')
                ->whereNotIn('username', ['admin', 'vicky'])
                ->get()
                ->toArray();
        } else {
            $dataToko['users'] = [];
        }

        // 2. Buat Tanda Tangan Digital Proteksi ID (Protect ID & Signature)
        $protectSignature = hash_hmac('sha256', "vxpos_protect_id_{$tokoId}_{$namaToko}_{$waktuSekarang}", config('app.key') ?: 'vxpos_secret_protect_key');

        $backupPayload = [
            'app'             => 'VxPOS',
            'version'         => '2.5',
            'backup_type'     => 'store_snapshot',
            'toko_id'         => (int) $tokoId,
            'nama_toko'       => $namaToko,
            'slug_toko'       => $slugToko,
            'created_at'      => $waktuSekarang,
            'protect_id'      => (int) $tokoId,
            'signature'       => $protectSignature,
            'summary'         => [
                'total_barang'           => count($dataToko['barang']),
                'total_transaksi'        => count($dataToko['transaksi']),
                'total_detail_transaksi' => count($dataToko['detail_transaksi']),
                'total_riwayat_cicilan'  => count($dataToko['riwayat_cicilan']),
                'total_permintaan_gudang' => count($dataToko['permintaan_gudang']),
                'total_bonus'            => count($dataToko['pencairan_bonus']),
                'total_penggajian'       => count($dataToko['penggajian']),
                'total_pegawai'          => count($dataToko['users']),
            ],
            'data'            => $dataToko,
        ];

        $jsonContent = json_encode($backupPayload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        // Penamaan file sesuai spesifikasi user:
        // Nama hasil backup disesuaikan dengan toko dan protect id
        $dateFormatted = Carbon::now()->format('Y-m-d_H-i');
        $fileName = "VxPOS_Backup_{$slugToko}_ID{$tokoId}_{$dateFormatted}.vxbackup";

        return response($jsonContent)
            ->header('Content-Type', 'application/json')
            ->header('Content-Disposition', 'attachment; filename="' . $fileName . '"');
    }

    /**
     * Pulihkan Data Toko (.vxbackup) dengan Validasi Protect ID
     */
    public function restoreStoreBackup(Request $request)
    {
        $request->validate([
            'backup_file'  => 'required|file|max:25600', // Maks 25MB
            'restore_mode' => 'required|in:replace,merge',
        ]);

        $file = $request->file('backup_file');
        $extension = strtolower($file->getClientOriginalExtension());
        
        if (!in_array($extension, ['vxbackup', 'json', 'txt'])) {
            return redirect()->back()->withErrors('Format berkas tidak didukung. Harap unggah file cadangan berekstensi .vxbackup atau .json.');
        }

        $rawContent = file_get_contents($file->getRealPath());
        $backup = json_decode($rawContent, true);

        if (!$backup || !isset($backup['app']) || $backup['app'] !== 'VxPOS' || !isset($backup['toko_id']) || !isset($backup['data'])) {
            return redirect()->back()->withErrors('Berkas cadangan tidak valid atau rusak. Pastikan file berasal dari fitur Cadangan VxPOS.');
        }

        $currentTokoId = (int) TenantManager::getTokoId();
        $fileTokoId = (int) $backup['toko_id'];
        $fileTokoNama = $backup['nama_toko'] ?? 'Toko Lain';

        // =========================================================================
        // PROTEKSI ID MUTLAK (ANTI-TERTUKAR & ANTI-RESTORE LINTAS TOKO)
        // =========================================================================
        if ($fileTokoId !== $currentTokoId) {
            return redirect()->back()->withErrors(
                "PROTEKSI KEAMANAN DITOLAK: Berkas cadangan ini terdaftar milik toko [{$fileTokoNama}] (ID Toko: {$fileTokoId}). " .
                "Berkas ini TIDAK BISA dipulihkan ke toko Anda yang sedang aktif saat ini (ID Toko: {$currentTokoId}) " .
                "untuk mencegah kerusakan database atau data tertukar antar toko yang berbeda!"
            );
        }

        $data = $backup['data'];
        $mode = $request->restore_mode; // 'replace' or 'merge'

        DB::beginTransaction();
        try {
            // Jika mode Replace, bersihkan data operasional & katalog lama toko ini terlebih dahulu
            if ($mode === 'replace') {
                $oldBarangIds = DB::table('barang')->where('toko_id', $currentTokoId)->pluck('id')->toArray();
                if (!empty($oldBarangIds)) {
                    if (Schema::hasTable('stok')) DB::table('stok')->whereIn('barang_id', $oldBarangIds)->delete();
                    if (Schema::hasTable('harga')) DB::table('harga')->whereIn('barang_id', $oldBarangIds)->delete();
                }

                $oldTxIds = DB::table('transaksi')->where('toko_id', $currentTokoId)->pluck('id')->toArray();
                if (!empty($oldTxIds)) {
                    if (Schema::hasTable('detail_transaksi')) DB::table('detail_transaksi')->whereIn('transaksi_id', $oldTxIds)->delete();
                    if (Schema::hasTable('riwayat_cicilan')) DB::table('riwayat_cicilan')->whereIn('transaksi_id', $oldTxIds)->delete();
                    if (Schema::hasTable('permintaan_gudang')) DB::table('permintaan_gudang')->whereIn('transaksi_id', $oldTxIds)->delete();
                }

                if (Schema::hasTable('transaksi')) DB::table('transaksi')->where('toko_id', $currentTokoId)->delete();
                if (Schema::hasTable('barang')) DB::table('barang')->where('toko_id', $currentTokoId)->delete();
                if (Schema::hasTable('pencairan_bonus')) DB::table('pencairan_bonus')->where('toko_id', $currentTokoId)->delete();
                if (Schema::hasTable('penggajian')) DB::table('penggajian')->where('toko_id', $currentTokoId)->delete();
            }

            // 1. Pulihkan Master Barang, Stok, dan Harga dengan Mapping ID Baru yang Aman
            $barangMap = []; // [old_id => new_id]
            if (!empty($data['barang'])) {
                foreach ($data['barang'] as $b) {
                    $bArray = (array)$b;
                    $oldId = $bArray['id'] ?? null;
                    unset($bArray['id']); // Buang ID lama agar auto-increment bersih
                    $bArray['toko_id'] = $currentTokoId;

                    $newId = DB::table('barang')->insertGetId($bArray);
                    if ($oldId) $barangMap[$oldId] = $newId;
                }
            }

            if (!empty($data['stok'])) {
                foreach ($data['stok'] as $s) {
                    $sArray = (array)$s;
                    unset($sArray['id']);
                    $oldBarangId = $sArray['barang_id'] ?? null;
                    if ($oldBarangId && isset($barangMap[$oldBarangId])) {
                        $sArray['barang_id'] = $barangMap[$oldBarangId];
                        DB::table('stok')->insert($sArray);
                    }
                }
            }

            if (!empty($data['harga'])) {
                foreach ($data['harga'] as $h) {
                    $hArray = (array)$h;
                    unset($hArray['id']);
                    $oldBarangId = $hArray['barang_id'] ?? null;
                    if ($oldBarangId && isset($barangMap[$oldBarangId])) {
                        $hArray['barang_id'] = $barangMap[$oldBarangId];
                        if (Schema::hasColumn('harga', 'toko_id')) $hArray['toko_id'] = $currentTokoId;
                        DB::table('harga')->insert($hArray);
                    }
                }
            }

            // 2. Pulihkan Transaksi, Detail Transaksi, Riwayat Cicilan, Permintaan Gudang
            $txMap = []; // [old_tx_id => new_tx_id]
            if (!empty($data['transaksi'])) {
                foreach ($data['transaksi'] as $tx) {
                    $txArray = (array)$tx;
                    $oldTxId = $txArray['id'] ?? null;
                    unset($txArray['id']);
                    $txArray['toko_id'] = $currentTokoId;

                    $newTxId = DB::table('transaksi')->insertGetId($txArray);
                    if ($oldTxId) $txMap[$oldTxId] = $newTxId;
                }
            }

            if (!empty($data['detail_transaksi'])) {
                foreach ($data['detail_transaksi'] as $dt) {
                    $dtArray = (array)$dt;
                    unset($dtArray['id']);
                    $oldTxId = $dtArray['transaksi_id'] ?? null;
                    $oldBarangId = $dtArray['barang_id'] ?? null;

                    if ($oldTxId && isset($txMap[$oldTxId])) {
                        $dtArray['transaksi_id'] = $txMap[$oldTxId];
                        if ($oldBarangId && isset($barangMap[$oldBarangId])) {
                            $dtArray['barang_id'] = $barangMap[$oldBarangId];
                        }
                        if (Schema::hasColumn('detail_transaksi', 'toko_id')) {
                            $dtArray['toko_id'] = $currentTokoId;
                        }
                        DB::table('detail_transaksi')->insert($dtArray);
                    }
                }
            }

            if (!empty($data['riwayat_cicilan'])) {
                foreach ($data['riwayat_cicilan'] as $rc) {
                    $rcArray = (array)$rc;
                    unset($rcArray['id']);
                    $oldTxId = $rcArray['transaksi_id'] ?? null;
                    if ($oldTxId && isset($txMap[$oldTxId])) {
                        $rcArray['transaksi_id'] = $txMap[$oldTxId];
                        if (Schema::hasColumn('riwayat_cicilan', 'toko_id')) {
                            $rcArray['toko_id'] = $currentTokoId;
                        }
                        DB::table('riwayat_cicilan')->insert($rcArray);
                    }
                }
            }

            if (!empty($data['permintaan_gudang'])) {
                foreach ($data['permintaan_gudang'] as $pg) {
                    $pgArray = (array)$pg;
                    unset($pgArray['id']);
                    $oldTxId = $pgArray['transaksi_id'] ?? null;
                    if ($oldTxId && isset($txMap[$oldTxId])) {
                        $pgArray['transaksi_id'] = $txMap[$oldTxId];
                        if (Schema::hasColumn('permintaan_gudang', 'toko_id')) {
                            $pgArray['toko_id'] = $currentTokoId;
                        }
                        DB::table('permintaan_gudang')->insert($pgArray);
                    }
                }
            }

            // 3. Pulihkan Pencairan Bonus
            if (!empty($data['pencairan_bonus'])) {
                foreach ($data['pencairan_bonus'] as $pb) {
                    $pbArray = (array)$pb;
                    unset($pbArray['id']);
                    $pbArray['toko_id'] = $currentTokoId;
                    DB::table('pencairan_bonus')->insert($pbArray);
                }
            }

            // 4. Pulihkan Penggajian
            if (!empty($data['penggajian'])) {
                foreach ($data['penggajian'] as $gj) {
                    $gjArray = (array)$gj;
                    unset($gjArray['id']);
                    if (Schema::hasColumn('penggajian', 'toko_id')) {
                        $gjArray['toko_id'] = $currentTokoId;
                    }
                    DB::table('penggajian')->insert($gjArray);
                }
            }

            // 5. Pulihkan Pengaturan Toko
            if (!empty($data['pengaturan_toko'])) {
                foreach ($data['pengaturan_toko'] as $pt) {
                    $ptArray = (array)$pt;
                    unset($ptArray['id']);
                    $ptArray['toko_id'] = $currentTokoId;
                    
                    DB::table('pengaturan_toko')->updateOrInsert(
                        ['toko_id' => $currentTokoId],
                        $ptArray
                    );
                }
            }

            DB::commit();

            $totalBarangRestored = count($data['barang'] ?? []);
            $totalTxRestored = count($data['transaksi'] ?? []);

            return redirect()->route('superadmin.backup.index')->with(
                'success',
                "Pemulihan Sukses! Data toko [{$fileTokoNama}] berhasil dipulihkan secara aman ({$totalBarangRestored} Produk, {$totalTxRestored} Transaksi). ID Toko terlindungi valid."
            );
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withErrors('Gagal memulihkan data: ' . $e->getMessage());
        }
    }

    /**
     * Cadangan Global SQL (Khusus Superadmin Platform Utama / SaaS Master)
     */
    public function downloadGlobalSql()
    {
        if (!TenantManager::isPlatformAdmin()) {
            return redirect()->route('superadmin.dashboard')
                ->with('error', 'Akses Ditolak: Fitur Cadangan Database Global hanya dapat diakses oleh Superadmin Utama (Platform Owner).');
        }

        set_time_limit(300); 

        $tables = DB::select('SHOW TABLES');
        
        $sql = "-- ===================================================\n";
        $sql .= "-- Sistem Backup Database Global: VxPOS Platform\n";
        $sql .= "-- Waktu Backup: " . Carbon::now()->format('d M Y - H:i:s') . "\n";
        $sql .= "-- ===================================================\n\n";
        
        $sql .= "SET FOREIGN_KEY_CHECKS=0;\n\n"; 

        foreach ($tables as $tableInfo) {
            $table = array_values((array)$tableInfo)[0];
            $createTableInfo = DB::select("SHOW CREATE TABLE `{$table}`")[0];
            $createTableProperty = 'Create Table';
            
            $sql .= "-- Struktur tabel `{$table}`\n";
            $sql .= "DROP TABLE IF EXISTS `{$table}`;\n";
            $sql .= $createTableInfo->$createTableProperty . ";\n\n";

            $rows = DB::table($table)->get();
            if ($rows->count() > 0) {
                $sql .= "-- Data untuk tabel `{$table}`\n";
                foreach ($rows as $row) {
                    $rowArray = (array) $row;
                    $columns = array_keys($rowArray);
                    $values = array_values($rowArray);

                    $escapedValues = array_map(function ($value) {
                        if (is_null($value)) return 'NULL';
                        return DB::getPdo()->quote($value);
                    }, $values);

                    $sql .= "INSERT INTO `{$table}` (`" . implode("`, `", $columns) . "`) VALUES (" . implode(", ", $escapedValues) . ");\n";
                }
                $sql .= "\n";
            }
        }
        
        $sql .= "SET FOREIGN_KEY_CHECKS=1;\n";

        $fileName = 'Backup_VxPOS_Global_' . Carbon::now()->format('Y-m-d_H-i') . '.sql';

        return response($sql)
            ->header('Content-Type', 'application/sql')
            ->header('Content-Disposition', 'attachment; filename=' . $fileName);
    }
}