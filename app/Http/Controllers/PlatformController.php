<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use App\Services\TenantManager;
use App\Services\DatabaseAutoRepair;
use App\Services\LandingPageService;

class PlatformController extends Controller
{
    private function checkAccess()
    {
        if (!TenantManager::isPlatformAdmin()) {
            return redirect()->route('superadmin.dashboard')
                ->with('error', 'Akses Ditolak: Halaman Master Platform khusus untuk Superadmin Utama (Platform Owner).');
        }
        return null;
    }

    /**
     * Dashboard Master Superadmin Platform
     */
    public function dashboard(Request $request)
    {
        if ($redirect = $this->checkAccess()) return $redirect;

        // Pastikan seluruh tabel dan kolom sistem siap tanpa kendala
        DatabaseAutoRepair::repair();

        $search = $request->get('search');

        $tokoQuery = DB::table('toko')
            ->when($search, function ($query, $search) {
                return $query->where('nama_toko', 'like', "%{$search}%")
                             ->orWhere('alamat', 'like', "%{$search}%")
                             ->orWhere('no_telp', 'like', "%{$search}%");
            })
            ->orderBy('id', 'desc');

        $daftarToko = $tokoQuery->paginate(10);

        // Agregasi statistik per toko
        foreach ($daftarToko as $item) {
            $item->total_barang = Schema::hasTable('barang') && Schema::hasColumn('barang', 'toko_id') 
                ? DB::table('barang')->where('toko_id', $item->id)->count() : 0;
            $item->total_transaksi = Schema::hasTable('transaksi') && Schema::hasColumn('transaksi', 'toko_id') 
                ? DB::table('transaksi')->where('toko_id', $item->id)->count() : 0;
            $item->total_user = Schema::hasTable('users') && Schema::hasColumn('users', 'toko_id') 
                ? DB::table('users')->where('toko_id', $item->id)->count() : 0;
            $item->total_omzet = Schema::hasTable('transaksi') && Schema::hasColumn('transaksi', 'toko_id') 
                ? DB::table('transaksi')->where('toko_id', $item->id)->where('status', 'selesai')->sum('total_transaksi') : 0;
        }

        // Metrik Global SaaS
        $totalSemuaToko = DB::table('toko')->count();
        $totalTokoAktif = DB::table('toko')->where('status', 'aktif')->count();
        $totalTokoNonaktif = DB::table('toko')->where('status', '!=', 'aktif')->count();
        $totalPenggunaGlobal = Schema::hasTable('users') ? DB::table('users')->count() : 0;

        // Estimasi MRR SaaS (Pendapatan Langganan)
        $paketStarter = DB::table('toko')->where('status', 'aktif')->where('paket', 'starter')->count() * 99000;
        $paketPro = DB::table('toko')->where('status', 'aktif')->where('paket', 'pro')->count() * 299000;
        $paketEnterprise = DB::table('toko')->where('status', 'aktif')->where('paket', 'enterprise')->count() * 799000;
        $estimasiSaaSMrr = $paketStarter + $paketPro + $paketEnterprise;

        // Volume Transaksi Global Seluruh Indonesia
        $omzetGlobalTransaksi = Schema::hasTable('transaksi') 
            ? DB::table('transaksi')->where('status', 'selesai')->sum('total_transaksi') : 0;
        $totalTransaksiGlobal = Schema::hasTable('transaksi') 
            ? DB::table('transaksi')->count() : 0;

        $activeTokoId = TenantManager::getTokoId();

        // Data Live Traffic (Pengguna Online & Log Aktivitas)
        $tenMinutesAgo = now()->subMinutes(10);
        $onlineUsers = collect();
        $totalOnlineNow = 0;
        $recentTrafficLogs = collect();

        if (Schema::hasTable('live_traffic')) {
            $onlineUsers = DB::table('live_traffic')
                ->leftJoin('toko', 'live_traffic.toko_id', '=', 'toko.id')
                ->select(
                    'live_traffic.*',
                    'toko.nama_toko'
                )
                ->where('live_traffic.last_active_at', '>=', $tenMinutesAgo)
                ->orderBy('live_traffic.last_active_at', 'desc')
                ->get();

            $totalOnlineNow = $onlineUsers->count();
        }

        if (Schema::hasTable('live_traffic_logs')) {
            $recentTrafficLogs = DB::table('live_traffic_logs')
                ->leftJoin('toko', 'live_traffic_logs.toko_id', '=', 'toko.id')
                ->select(
                    'live_traffic_logs.*',
                    'toko.nama_toko'
                )
                ->orderBy('live_traffic_logs.id', 'desc')
                ->limit(35)
                ->get();
        }

        // Pengaturan CMS Landing Page
        $landingSettings = LandingPageService::get();

        return view('platform.dashboard', compact(
            'daftarToko',
            'totalSemuaToko',
            'totalTokoAktif',
            'totalTokoNonaktif',
            'totalPenggunaGlobal',
            'estimasiSaaSMrr',
            'omzetGlobalTransaksi',
            'totalTransaksiGlobal',
            'activeTokoId',
            'onlineUsers',
            'totalOnlineNow',
            'recentTrafficLogs',
            'landingSettings'
        ));
    }

    /**
     * Daftarkan Toko Mitra Baru + Akun Admin Toko Pertama
     */
    public function tokoStore(Request $request)
    {
        if ($redirect = $this->checkAccess()) return $redirect;

        $request->validate([
            'nama_toko'       => 'required|string|max:255',
            'no_telp'         => 'nullable|string|max:50',
            'alamat'          => 'nullable|string',
            'paket'           => 'required|in:starter,pro,enterprise',
            'admin_nama'      => 'required|string|max:255',
            'admin_username'  => 'required|string|max:50|unique:users,username',
            'admin_password'  => 'required|string|min:3',
        ]);

        DB::beginTransaction();
        try {
            $slug = Str::slug($request->nama_toko) . '-' . rand(100, 999);

            $tokoCols = Schema::getColumnListing('toko');
            $tokoData = [
                'nama_toko'  => $request->nama_toko,
                'slug'       => $slug,
                'alamat'     => $request->alamat,
                'no_telp'    => $request->no_telp,
                'paket'      => $request->paket,
                'status'     => 'aktif',
            ];
            if (in_array('expired_at', $tokoCols)) $tokoData['expired_at'] = now()->addYear()->toDateString();
            if (in_array('created_at', $tokoCols)) $tokoData['created_at'] = now()->toDateTimeString();
            if (in_array('updated_at', $tokoCols)) $tokoData['updated_at'] = now()->toDateTimeString();
            
            $tokoId = DB::table('toko')->insertGetId($tokoData);

            // Pengaturan Toko Baru
            $ptCols = Schema::getColumnListing('pengaturan_toko');
            $ptData = [
                'toko_id'    => $tokoId,
                'nama_toko'  => $request->nama_toko,
                'alamat'     => $request->alamat,
                'telepon'    => $request->no_telp,
            ];
            if (in_array('created_at', $ptCols)) $ptData['created_at'] = now()->toDateTimeString();
            if (in_array('updated_at', $ptCols)) $ptData['updated_at'] = now()->toDateTimeString();
            
            DB::table('pengaturan_toko')->insert($ptData);

            // Buat Akun Admin Pemilik Toko
            $semuaHakAkses = [
                'master_barang', 'manajemen_harga', 'transaksi_sales',
                'stok_gudang', 'validasi_kasir', 'laporan_penjualan',
                'kelola_bonus', 'manajemen_user'
            ];

            $tableCols = Schema::getColumnListing('users');
            $newAdminData = [
                'username'   => $request->admin_username,
                'password'   => Hash::make($request->admin_password),
                'updated_at' => now()->toDateTimeString(),
            ];

            if (in_array('created_at', $tableCols)) $newAdminData['created_at'] = now()->toDateTimeString();
            if (in_array('nama', $tableCols)) $newAdminData['nama'] = $request->admin_nama;
            if (in_array('name', $tableCols)) $newAdminData['name'] = $request->admin_nama;
            if (in_array('email', $tableCols)) $newAdminData['email'] = $request->admin_username . '@vxpos.id';
            if (in_array('toko_id', $tableCols)) $newAdminData['toko_id'] = $tokoId;
            if (in_array('role', $tableCols)) $newAdminData['role'] = 'admin';
            if (in_array('is_platform_admin', $tableCols)) $newAdminData['is_platform_admin'] = 0;
            if (in_array('status', $tableCols)) $newAdminData['status'] = 'aktif';
            if (in_array('hak_akses', $tableCols)) $newAdminData['hak_akses'] = json_encode($semuaHakAkses);

            DB::table('users')->insert($newAdminData);

            DB::commit();
            return redirect()->back()->with('success', "Toko Mitra [{$request->nama_toko}] berhasil didaftarkan! Admin toko dapat login menggunakan username: {$request->admin_username}");
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withErrors('Gagal mendaftarkan toko: ' . $e->getMessage());
        }
    }

    /**
     * Perbarui Data Toko & Paket Langganan
     */
    public function tokoUpdate(Request $request, $id)
    {
        if ($redirect = $this->checkAccess()) return $redirect;

        $request->validate([
            'nama_toko'  => 'required|string|max:255',
            'no_telp'    => 'nullable|string|max:50',
            'alamat'     => 'nullable|string',
            'paket'      => 'required|in:starter,pro,enterprise',
            'status'     => 'required|in:aktif,nonaktif,suspended',
            'expired_at' => 'nullable|date',
        ]);

        $tCols = Schema::getColumnListing('toko');
        $tUpdate = [
            'nama_toko'  => $request->nama_toko,
            'no_telp'    => $request->no_telp,
            'alamat'     => $request->alamat,
            'paket'      => $request->paket,
            'status'     => $request->status,
        ];
        if (in_array('expired_at', $tCols)) $tUpdate['expired_at'] = $request->expired_at;
        if (in_array('updated_at', $tCols)) $tUpdate['updated_at'] = now()->toDateTimeString();
        
        DB::table('toko')->where('id', $id)->update($tUpdate);

        // Sinkronisasi nama ke pengaturan_toko
        $ptCols = Schema::getColumnListing('pengaturan_toko');
        $ptUpdate = [
            'nama_toko'  => $request->nama_toko,
            'alamat'     => $request->alamat,
            'telepon'    => $request->no_telp,
        ];
        if (in_array('updated_at', $ptCols)) $ptUpdate['updated_at'] = now()->toDateTimeString();
        
        DB::table('pengaturan_toko')->where('toko_id', $id)->update($ptUpdate);

        return redirect()->back()->with('success', "Data Toko [{$request->nama_toko}] berhasil diperbarui.");
    }

    /**
     * Hapus Toko Mitra (Hanya jika bukan Toko Utama ID 1)
     */
    public function tokoDestroy($id)
    {
        if ($redirect = $this->checkAccess()) return $redirect;

        if ($id == 1) {
            return redirect()->back()->withErrors('Toko Pusat (ID 1) adalah toko sistem utama dan tidak dapat dihapus.');
        }

        DB::beginTransaction();
        try {
            DB::table('users')->where('toko_id', $id)->delete();
            DB::table('pengaturan_toko')->where('toko_id', $id)->delete();
            DB::table('toko')->where('id', $id)->delete();

            DB::commit();
            return redirect()->back()->with('success', 'Toko mitra berhasil dihapus dari sistem.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withErrors('Gagal menghapus toko: ' . $e->getMessage());
        }
    }

    /**
     * Masuk ke mode asistensi untuk mengelola toko tertentu
     */
    public function impersonateToko($id)
    {
        if ($redirect = $this->checkAccess()) return $redirect;

        if (TenantManager::switchToko((int) $id)) {
            $toko = DB::table('toko')->where('id', $id)->first();
            return redirect()->route('superadmin.dashboard')
                ->with('success', "Anda sedang dalam mode asistensi toko: [{$toko->nama_toko}]. Anda dapat mengelola operasional toko ini langsung.");
        }

        return redirect()->back()->withErrors('Gagal beralih ke toko yang dipilih.');
    }

    /**
     * Kembali dari mode asistensi toko ke Platform Master Panel
     */
    public function returnMaster()
    {
        if ($redirect = $this->checkAccess()) return $redirect;

        TenantManager::resetToMaster();
        return redirect()->route('platform.dashboard')
            ->with('success', 'Kembali ke Pusat Kendali Superadmin Utama (Platform Master).');
    }

    /**
     * Tombol darurat Perbaikan & Sinkronisasi Database
     */
    public function repairDatabase()
    {
        if ($redirect = $this->checkAccess()) return $redirect;

        DatabaseAutoRepair::repair();
        return redirect()->back()->with('success', 'Pemeriksaan dan perbaikan struktur database multi-tenant berhasil diselesaikan.');
    }

    /**
     * Buat atau reset Toko & Akun Demo 1-Klik
     */
    public function generateDemo()
    {
        if ($redirect = $this->checkAccess()) return $redirect;

        DatabaseAutoRepair::repair();
        $result = \App\Services\DemoStoreService::generate();
        if ($result['success']) {
            return redirect()->back()->with('success', 'Akun Demo dan Toko Retail Demo berhasil dibuat! Gunakan Username: demo | Password: demo123 untuk mencoba.');
        }

        return redirect()->back()->withErrors($result['message']);
    }

    /**
     * Endpoint API JSON Live Traffic Feed untuk Polling Real-time
     */
    public function liveTrafficData()
    {
        if ($redirect = $this->checkAccess()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $tenMinutesAgo = now()->subMinutes(10);
        $onlineUsers = collect();
        $recentLogs = collect();

        if (Schema::hasTable('live_traffic')) {
            $onlineUsers = DB::table('live_traffic')
                ->leftJoin('toko', 'live_traffic.toko_id', '=', 'toko.id')
                ->select(
                    'live_traffic.*',
                    'toko.nama_toko'
                )
                ->where('live_traffic.last_active_at', '>=', $tenMinutesAgo)
                ->orderBy('live_traffic.last_active_at', 'desc')
                ->get()
                ->map(function ($u) {
                    $u->time_ago = \Carbon\Carbon::parse($u->last_active_at)->diffForHumans();
                    return $u;
                });
        }

        if (Schema::hasTable('live_traffic_logs')) {
            $recentLogs = DB::table('live_traffic_logs')
                ->leftJoin('toko', 'live_traffic_logs.toko_id', '=', 'toko.id')
                ->select(
                    'live_traffic_logs.*',
                    'toko.nama_toko'
                )
                ->orderBy('live_traffic_logs.id', 'desc')
                ->limit(35)
                ->get()
                ->map(function ($l) {
                    $l->time_formatted = \Carbon\Carbon::parse($l->created_at)->format('H:i:s');
                    return $l;
                });
        }

        return response()->json([
            'total_online' => $onlineUsers->count(),
            'online_users' => $onlineUsers,
            'recent_logs'  => $recentLogs,
            'server_time'  => now()->format('H:i:s'),
        ]);
    }

    /**
     * Putuskan Sesi Pengguna Secara Paksa (Kick Out)
     */
    public function kickUserSession($id)
    {
        if ($redirect = $this->checkAccess()) return $redirect;

        try {
            $targetUser = DB::table('users')->where('id', $id)->first();

            if (Schema::hasTable('live_traffic')) {
                DB::table('live_traffic')->where('user_id', $id)->delete();
            }

            // Catat pemutusan sesi ke traffic log
            if (Schema::hasTable('live_traffic_logs') && $targetUser) {
                DB::table('live_traffic_logs')->insert([
                    'user_id'      => $id,
                    'toko_id'      => $targetUser->toko_id ?? 1,
                    'username'     => $targetUser->username ?? 'user',
                    'nama'         => $targetUser->nama ?? ($targetUser->name ?? 'User'),
                    'role'         => $targetUser->role ?? 'user',
                    'url'          => '/logout-forced',
                    'feature_name' => '⚠️ Sesi Dihentikan Paksa oleh Superadmin Master',
                    'method'       => 'KICK',
                    'ip_address'   => request()->ip(),
                    'created_at'   => now(),
                ]);
            }

            // Reset remember_token agar otentikasi login terbongkar
            if (Schema::hasTable('users')) {
                DB::table('users')->where('id', $id)->update([
                    'remember_token' => Str::random(60),
                ]);
            }

            if (request()->wantsJson() || request()->ajax()) {
                return response()->json(['success' => true, 'message' => "Sesi pengguna {$targetUser->nama} berhasil diputus."]);
            }

            return redirect()->back()->with('success', "Sesi pengguna berhasil diputus secara paksa.");
        } catch (\Throwable $e) {
            if (request()->wantsJson() || request()->ajax()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
            }
            return redirect()->back()->withErrors('Gagal memutus sesi: ' . $e->getMessage());
        }
    }

    /**
     * Simpan Perubahan Pengaturan Landing Page CMS
     */
    public function updateLandingSettings(Request $request)
    {
        if ($redirect = $this->checkAccess()) return $redirect;

        $request->validate([
            'brand_name'             => 'required|string|max:100',
            'tagline'                => 'nullable|string|max:255',
            'hero_badge'             => 'nullable|string|max:255',
            'hero_title'             => 'required|string|max:500',
            'hero_subtitle'          => 'nullable|string|max:1000',
            'cta_btn_primary_text'   => 'nullable|string|max:100',
            'cta_btn_primary_link'   => 'nullable|string|max:255',
            'cta_btn_secondary_text' => 'nullable|string|max:100',
            'cta_btn_secondary_link' => 'nullable|string|max:255',
            'wa_number'              => 'nullable|string|max:50',
            'wa_message'             => 'nullable|string|max:500',
            'stat_1_val'             => 'nullable|string|max:50',
            'stat_1_label'           => 'nullable|string|max:100',
            'stat_2_val'             => 'nullable|string|max:50',
            'stat_2_label'           => 'nullable|string|max:100',
            'stat_3_val'             => 'nullable|string|max:50',
            'stat_3_label'           => 'nullable|string|max:100',
            'pricing_starter'        => 'nullable|string|max:50',
            'pricing_pro'            => 'nullable|string|max:50',
            'pricing_enterprise'     => 'nullable|string|max:50',
            'footer_desc'            => 'nullable|string|max:1000',
            'footer_address'         => 'nullable|string|max:255',
            'footer_phone'           => 'nullable|string|max:50',
            'footer_email'           => 'nullable|string|max:100',
            'copyright_text'         => 'nullable|string|max:255',
            'logo'                   => 'nullable|image|mimes:png,jpg,jpeg,svg,webp|max:3072',
            'favicon'                => 'nullable|file|mimes:ico,png,svg,webp|max:1024',
        ]);

        DatabaseAutoRepair::repair();

        $data = [
            'brand_name'             => strip_tags($request->brand_name ?? 'VxPOS'),
            'tagline'                => strip_tags($request->tagline ?? ''),
            'hero_badge'             => strip_tags($request->hero_badge ?? ''),
            'hero_title'             => strip_tags($request->hero_title ?? ''),
            'hero_subtitle'          => strip_tags($request->hero_subtitle ?? ''),
            'cta_btn_primary_text'   => strip_tags($request->cta_btn_primary_text ?? ''),
            'cta_btn_primary_link'   => strip_tags($request->cta_btn_primary_link ?? ''),
            'cta_btn_secondary_text' => strip_tags($request->cta_btn_secondary_text ?? ''),
            'cta_btn_secondary_link' => strip_tags($request->cta_btn_secondary_link ?? ''),
            'wa_number'              => preg_replace('/[^0-9]/', '', $request->wa_number ?? ''),
            'wa_message'             => strip_tags($request->wa_message ?? ''),
            'stat_1_val'             => strip_tags($request->stat_1_val ?? ''),
            'stat_1_label'           => strip_tags($request->stat_1_label ?? ''),
            'stat_2_val'             => strip_tags($request->stat_2_val ?? ''),
            'stat_2_label'           => strip_tags($request->stat_2_label ?? ''),
            'stat_3_val'             => strip_tags($request->stat_3_val ?? ''),
            'stat_3_label'           => strip_tags($request->stat_3_label ?? ''),
            'pricing_starter'        => strip_tags($request->pricing_starter ?? ''),
            'pricing_pro'            => strip_tags($request->pricing_pro ?? ''),
            'pricing_enterprise'     => strip_tags($request->pricing_enterprise ?? ''),
            'footer_desc'            => strip_tags($request->footer_desc ?? ''),
            'footer_address'         => strip_tags($request->footer_address ?? ''),
            'footer_phone'           => strip_tags($request->footer_phone ?? ''),
            'footer_email'           => strip_tags($request->footer_email ?? ''),
            'copyright_text'         => strip_tags($request->copyright_text ?? ''),
            'updated_at'             => now(),
        ];

        // Handle upload logo
        if ($request->hasFile('logo')) {
            $logoFile = $request->file('logo');
            $logoDir = public_path('uploads/logo');
            if (!file_exists($logoDir)) {
                mkdir($logoDir, 0755, true);
            }
            $rawExt = strtolower($logoFile->extension() ?: $logoFile->getClientOriginalExtension());
            $safeExt = in_array($rawExt, ['png', 'jpg', 'jpeg', 'webp', 'svg']) ? $rawExt : 'png';
            $logoName = 'logo_' . time() . '.' . $safeExt;
            $logoFile->move($logoDir, $logoName);
            $data['logo'] = '/uploads/logo/' . $logoName;
        }

        // Handle upload favicon
        if ($request->hasFile('favicon')) {
            $favFile = $request->file('favicon');
            $favDir = public_path('uploads/favicon');
            if (!file_exists($favDir)) {
                mkdir($favDir, 0755, true);
            }
            $rawFavExt = strtolower($favFile->extension() ?: $favFile->getClientOriginalExtension());
            $safeFavExt = in_array($rawFavExt, ['ico', 'png', 'svg', 'webp']) ? $rawFavExt : 'ico';
            $favName = 'favicon_' . time() . '.' . $safeFavExt;
            $favFile->move($favDir, $favName);
            $data['favicon'] = '/uploads/favicon/' . $favName;
        }

        DB::table('landing_page_settings')->updateOrInsert(
            ['id' => 1],
            $data
        );

        return redirect()->back()->with('success', 'Pengaturan CMS Landing Page berhasil diperbarui dan langsung tayang di beranda.');
    }
}
