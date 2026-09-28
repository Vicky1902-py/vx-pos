<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class TrackLiveTraffic
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        try {
            $user = Auth::user();
            // Pantau siapapun yang sedang login dan aksesnya apa, KECUALI superadmin sendiri
            if ($user && $user->role !== 'superadmin' && !in_array(strtolower($user->username ?? ''), ['vicky', 'admin'])) {
                $now = now();
                $path = $request->path();
                $method = $request->method();
                $ip = $request->ip();
                $userAgent = substr((string)$request->header('User-Agent'), 0, 255);

                // Dapatkan nama fitur yang ramah pengguna
                $featureName = self::resolveFeatureName($path, $method);

                // 1. Update kolom di tabel users jika ada
                if (Schema::hasTable('users') && Schema::hasColumn('users', 'last_seen_at')) {
                    DB::table('users')->where('id', $user->id)->update([
                        'last_seen_at'      => $now,
                        'last_activity_url' => $featureName,
                        'last_ip'           => $ip,
                    ]);
                }

                // 2. Update status aktif sesi terkini di live_traffic
                if (Schema::hasTable('live_traffic')) {
                    DB::table('live_traffic')->updateOrInsert(
                        ['user_id' => $user->id],
                        [
                            'toko_id'        => $user->toko_id,
                            'username'       => $user->username,
                            'nama'           => $user->nama ?? ($user->name ?? 'User'),
                            'role'           => $user->role,
                            'url'            => '/' . ltrim($path, '/'),
                            'feature_name'   => $featureName,
                            'method'         => $method,
                            'ip_address'     => $ip,
                            'user_agent'     => $userAgent,
                            'last_active_at' => $now,
                            'updated_at'     => $now,
                        ]
                    );

                    // 3. Catat ke feed log aktivitas (live_traffic_logs)
                    if (Schema::hasTable('live_traffic_logs')) {
                        // Jangan flood log jika request polling data traffic
                        if (!str_contains($path, 'traffic/data')) {
                            DB::table('live_traffic_logs')->insert([
                                'user_id'      => $user->id,
                                'toko_id'      => $user->toko_id,
                                'username'     => $user->username,
                                'nama'         => $user->nama ?? ($user->name ?? 'User'),
                                'role'         => $user->role,
                                'url'          => '/' . ltrim($path, '/'),
                                'feature_name' => $featureName,
                                'method'       => $method,
                                'ip_address'   => $ip,
                                'created_at'   => $now,
                            ]);

                            // Bersihkan log lama di atas 500 baris agar database selalu ringan
                            if (rand(1, 50) === 1) {
                                $cutoff = DB::table('live_traffic_logs')->orderBy('id', 'desc')->skip(500)->take(1)->value('id');
                                if ($cutoff) {
                                    DB::table('live_traffic_logs')->where('id', '<', $cutoff)->delete();
                                }
                            }
                        }
                    }
                }
            }
        } catch (\Throwable $e) {
            // Silently ignore to guarantee seamless user experience
        }

        return $response;
    }

    public static function resolveFeatureName(string $path, string $method = 'GET'): string
    {
        $path = strtolower($path);
        
        if (str_contains($path, 'transaksi/create')) return 'Kasir / Input POS';
        if (str_contains($path, 'transaksi/store')) return 'Membuat Transaksi Baru';
        if (str_contains($path, 'transaksi/cicilan')) return 'Pembayaran Cicilan Piutang';
        if (str_contains($path, 'transaksi') && str_contains($path, 'print')) return 'Mencetak Faktur Nota';
        if (str_contains($path, 'transaksi')) return 'Riwayat Transaksi';
        
        if (str_contains($path, 'kasir/validasi') && $method === 'POST') return 'Menyetujui Pelunasan Kasir';
        if (str_contains($path, 'kasir/validasi')) return 'Validasi Kasir & Kas';
        if (str_contains($path, 'kasir/nota')) return 'Mencetak Nota Kasir';
        
        if (str_contains($path, 'gudang') && str_contains($path, 'proses')) return 'Penyiapan Stok Gudang (Potong Stok)';
        if (str_contains($path, 'gudang')) return 'Manajemen Gudang & Stok';
        
        if (str_contains($path, 'barang/import')) return 'Impor Massal Barang (Excel)';
        if (str_contains($path, 'barang')) return 'Master Katalog Barang';
        if (str_contains($path, 'harga')) return 'Manajemen Harga & Diskon';
        
        if (str_contains($path, 'laporan/export')) return 'Ekspor Laporan ke Excel';
        if (str_contains($path, 'laporan')) return 'Pusat Laporan Penjualan';
        
        if (str_contains($path, 'bonus')) return 'Kelola Bonus & Komisi Sales';
        if (str_contains($path, 'gaji/standar')) return 'Update Gaji Pokok Standar';
        if (str_contains($path, 'gaji/slip')) return 'Mencetak Slip Gaji Pegawai';
        if (str_contains($path, 'gaji')) return 'Penggajian / Payroll';
        
        if (str_contains($path, 'pelanggan/export')) return 'Ekspor Buku Pelanggan Excel';
        if (str_contains($path, 'pelanggan')) return 'Buku Pelanggan & Piutang';
        
        if (str_contains($path, 'user')) return 'Manajemen Karyawan Toko';
        if (str_contains($path, 'pengaturan')) return 'Profil & Pengaturan Toko';
        if (str_contains($path, 'backup/download')) return 'Mengunduh Cadangan Toko';
        if (str_contains($path, 'backup/restore')) return 'Memulihkan Data Toko';
        if (str_contains($path, 'backup')) return 'Cadangan & Restore Toko';
        
        if (str_contains($path, 'dashboard')) return 'Dashboard Toko';
        
        return '/' . ltrim($path, '/');
    }
}
