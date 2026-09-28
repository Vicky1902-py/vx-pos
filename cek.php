<?php
/**
 * Script Diagnostik Mandiri VxPOS untuk Hosting Baru (cPanel / Rumahweb)
 */

// Keamanan: Wajib menyertakan parameter key rahasia untuk mengakses diagnostik ini
if (!isset($_GET['key']) || $_GET['key'] !== 'vxpos2026') {
    http_response_code(403);
    die('<!DOCTYPE html><html><head><title>403 Forbidden</title></head><body style="background:#0f172a;color:#ef4444;font-family:sans-serif;padding:50px;text-align:center;"><h2>403 Forbidden: Akses diagnostik server dibatasi.</h2></body></html>');
}

header('Content-Type: text/html; charset=utf-8');
error_reporting(E_ALL);
ini_set('display_errors', '1');

$results = [];

// 1. Cek Versi PHP
$phpVer = PHP_VERSION;
$phpOk = version_compare($phpVer, '8.2.0', '>=');
$results[] = [
    'nama'   => 'Versi PHP Server',
    'status' => $phpOk,
    'detail' => $phpOk 
        ? "PHP {$phpVer} (Memenuhi syarat Laravel 11)" 
        : "PHP {$phpVer} (TERLALU RENDAH! Laravel 11 WAJIB PHP >= 8.2.0. Silakan ubah di cPanel > Select PHP Version ke 8.2 atau 8.3)",
];

// 2. Cek Ekstensi Wajib
$extensions = ['pdo_mysql', 'mbstring', 'openssl', 'curl', 'xml', 'fileinfo', 'bcmath'];
$missingExt = [];
foreach ($extensions as $ext) {
    if (!extension_loaded($ext)) {
        $missingExt[] = $ext;
    }
}
$results[] = [
    'nama'   => 'Ekstensi PHP Wajib',
    'status' => empty($missingExt),
    'detail' => empty($missingExt) 
        ? 'Semua ekstensi lengkap (' . implode(', ', $extensions) . ')' 
        : 'Ekstensi kurang: ' . implode(', ', $missingExt) . ' (Centang di cPanel > Select PHP Version > Extensions)',
];

// 3. Cek vendor/autoload.php
$baseDir = file_exists(__DIR__ . '/vendor/autoload.php') ? __DIR__ : dirname(__DIR__);
$autoloadFile = $baseDir . '/vendor/autoload.php';
$hasAutoload = file_exists($autoloadFile);
$results[] = [
    'nama'   => 'Folder vendor/ dan Autoload',
    'status' => $hasAutoload,
    'detail' => $hasAutoload 
        ? 'Ditemukan di: ' . $autoloadFile 
        : 'TIDAK DITEMUKAN! Folder vendor/ belum ada di folder aplikasi ini. Silakan upload vendor.zip atau jalankan composer install.',
];

// 4. Cek File .env & APP_KEY
$envFile = $baseDir . '/.env';
$hasEnv = file_exists($envFile);
$hasKey = false;
$dbConfig = ['host' => '', 'db' => '', 'user' => '', 'pass' => ''];
if ($hasEnv) {
    $envContent = file_get_contents($envFile);
    if (preg_match('/^APP_KEY=base64:[A-Za-z0-9+\/=]+/m', $envContent)) {
        $hasKey = true;
    }
    if (preg_match('/^DB_HOST=(.*)$/m', $envContent, $m)) $dbConfig['host'] = trim($m[1]);
    if (preg_match('/^DB_DATABASE=(.*)$/m', $envContent, $m)) $dbConfig['db'] = trim($m[1]);
    if (preg_match('/^DB_USERNAME=(.*)$/m', $envContent, $m)) $dbConfig['user'] = trim($m[1]);
    if (preg_match('/^DB_PASSWORD=(.*)$/m', $envContent, $m)) $dbConfig['pass'] = trim($m[1]);
}
$results[] = [
    'nama'   => 'File Konfigurasi (.env) & APP_KEY',
    'status' => ($hasEnv && $hasKey),
    'detail' => (!$hasEnv) 
        ? 'File .env tidak ditemukan! Salin file .env.example menjadi .env' 
        : ($hasKey ? 'File .env aktif dan APP_KEY terisi' : 'APP_KEY kosong di .env! Harap isi APP_KEY'),
];

// 5. Cek Koneksi Database MySQL
$dbOk = false;
$dbMsg = '';
if ($dbConfig['db'] && $dbConfig['user']) {
    try {
        $dsn = "mysql:host={$dbConfig['host']};dbname={$dbConfig['db']};charset=utf8mb4";
        $pdo = new PDO($dsn, $dbConfig['user'], $dbConfig['pass'], [PDO::ATTR_TIMEOUT => 3]);
        $dbOk = true;
        $dbMsg = "Berhasil terhubung ke database `{$dbConfig['db']}` sebagai user `{$dbConfig['user']}`";
    } catch (\Throwable $e) {
        $dbMsg = "GAGAL TERHUBUNG KE DATABASE: " . $e->getMessage() . " (Pastikan nama database & user di .env sesuai cPanel baru)";
    }
} else {
    $dbMsg = "Konfigurasi database di .env belum diisi.";
}
$results[] = [
    'nama'   => 'Koneksi Database MySQL',
    'status' => $dbOk,
    'detail' => $dbMsg,
];

// 6. Cek Izin Tulis Folder Storage & Cache
$storageWritable = is_writable($baseDir . '/storage');
$cacheWritable = is_writable($baseDir . '/bootstrap/cache');
$permOk = ($storageWritable && $cacheWritable);
$results[] = [
    'nama'   => 'Izin Folder (storage & bootstrap/cache)',
    'status' => $permOk,
    'detail' => $permOk 
        ? 'Folder storage dan bootstrap/cache dapat ditulis (Writable)' 
        : 'Folder tidak dapat ditulis! Ubah permission storage dan bootstrap/cache ke 0755',
];

// 7. Tes Bootstrap Laravel
$bootOk = false;
$bootMsg = '';
if ($hasAutoload && $phpOk) {
    try {
        require $autoloadFile;
        $app = require $baseDir . '/bootstrap/app.php';
        $kernel = $app->make(\Illuminate\Contracts\Http\Kernel::class);
        $bootOk = true;
        $bootMsg = 'Laravel Framework berhasil di-booting tanpa error!';
    } catch (\Throwable $e) {
        $bootMsg = 'Error saat Booting Laravel: ' . $e->getMessage() . '<br><small>' . $e->getFile() . ':' . $e->getLine() . '</small>';
    }
} else {
    $bootMsg = 'Tidak dapat mengetes booting karena PHP versi lama atau autoload belum ada.';
}
$results[] = [
    'nama'   => 'Inisialisasi Framework Laravel',
    'status' => $bootOk,
    'detail' => $bootMsg,
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Diagnostik Server VxPOS</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; background: #0f172a; color: #f8fafc; padding: 30px; }
        .container { max-width: 800px; margin: 0 auto; background: #1e293b; border-radius: 16px; padding: 25px; box-shadow: 0 10px 25px rgba(0,0,0,0.5); }
        h1 { font-size: 22px; margin-bottom: 5px; color: #38bdf8; }
        p.desc { font-size: 13px; color: #94a3b8; margin-top: 0; margin-bottom: 25px; }
        .card { padding: 15px; border-radius: 10px; margin-bottom: 12px; border-left: 5px solid; }
        .ok { background: rgba(34, 197, 94, 0.1); border-color: #22c55e; }
        .fail { background: rgba(239, 68, 68, 0.15); border-color: #ef4444; }
        .card-header { display: flex; justify-content: space-between; align-items: center; font-weight: bold; font-size: 15px; }
        .badge { padding: 4px 10px; border-radius: 20px; font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; }
        .badge-ok { background: #22c55e; color: #000; }
        .badge-fail { background: #ef4444; color: #fff; }
        .card-detail { font-size: 13px; color: #cbd5e1; margin-top: 8px; line-height: 1.5; }
        .actions { margin-top: 25px; text-align: center; }
        .btn { display: inline-block; background: #38bdf8; color: #000; padding: 10px 20px; border-radius: 8px; text-decoration: none; font-weight: bold; font-size: 13px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>⚡ Hasil Diagnostik Server VxPOS</h1>
        <p class="desc">Host: <?= htmlspecialchars($_SERVER['HTTP_HOST'] ?? 'localhost') ?> | Waktu: <?= date('d M Y - H:i:s') ?></p>

        <?php foreach ($results as $item): ?>
            <div class="card <?= $item['status'] ? 'ok' : 'fail' ?>">
                <div class="card-header">
                    <span><?= htmlspecialchars($item['nama']) ?></span>
                    <span class="badge <?= $item['status'] ? 'badge-ok' : 'badge-fail' ?>">
                        <?= $item['status'] ? 'NORMAL' : 'PERMASALAHAN' ?>
                    </span>
                </div>
                <div class="card-detail"><?= $item['detail'] ?></div>
            </div>
        <?php endforeach; ?>

        <div class="actions">
            <a href="/" class="btn">Coba Buka Halaman Utama (/)</a>
        </div>
    </div>
</body>
</html>
