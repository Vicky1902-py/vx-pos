<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class BackupController extends Controller
{
    public function index()
    {
        return view('superadmin.backup.index');
    }

    public function download()
    {
        // Matikan batas waktu eksekusi jika database sangat besar
        set_time_limit(300); 

        $dbName = DB::connection()->getDatabaseName();
        $tables = DB::select('SHOW TABLES');
        
        $sql = "-- ===================================================\n";
        $sql .= "-- Sistem Backup Database: VxPOS\n";
        $sql .= "-- Waktu Backup: " . Carbon::now()->format('d M Y - H:i:s') . "\n";
        $sql .= "-- ===================================================\n\n";
        
        // Nonaktifkan pemeriksaan foreign key sementara agar mudah saat di-restore
        $sql .= "SET FOREIGN_KEY_CHECKS=0;\n\n"; 

        foreach ($tables as $tableInfo) {
            // Mengambil nama tabel secara dinamis
            $table = array_values((array)$tableInfo)[0];

            // Mengambil struktur CREATE TABLE
            $createTableInfo = DB::select("SHOW CREATE TABLE `{$table}`")[0];
            $createTableProperty = 'Create Table';
            
            $sql .= "-- Struktur untuk tabel `{$table}`\n";
            $sql .= "DROP TABLE IF EXISTS `{$table}`;\n";
            $sql .= $createTableInfo->$createTableProperty . ";\n\n";

            // Mengambil data isi tabel
            $rows = DB::table($table)->get();
            if ($rows->count() > 0) {
                $sql .= "-- Data untuk tabel `{$table}`\n";
                foreach ($rows as $row) {
                    $rowArray = (array) $row;
                    $columns = array_keys($rowArray);
                    $values = array_values($rowArray);

                    // Membersihkan dan mengamankan nilai string (Escaping)
                    $escapedValues = array_map(function ($value) {
                        if (is_null($value)) {
                            return 'NULL';
                        }
                        // Fungsi quote dari PDO akan otomatis menambahkan tanda kutip aman ('...')
                        return DB::getPdo()->quote($value);
                    }, $values);

                    $sql .= "INSERT INTO `{$table}` (`" . implode("`, `", $columns) . "`) VALUES (" . implode(", ", $escapedValues) . ");\n";
                }
                $sql .= "\n";
            }
        }
        
        // Aktifkan kembali pemeriksaan foreign key
        $sql .= "SET FOREIGN_KEY_CHECKS=1;\n";

        // Penamaan file dinamis berdasarkan waktu
        $fileName = 'Backup_VxPOS_' . Carbon::now()->format('Y-m-d_H-i') . '.sql';

        // Lempar output secara langsung sebagai file unduhan ke browser
        return response($sql)
            ->header('Content-Type', 'application/sql')
            ->header('Content-Disposition', 'attachment; filename=' . $fileName);
    }
}