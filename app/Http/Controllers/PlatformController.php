<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use App\Services\TenantManager;
use App\Services\DatabaseAutoRepair;

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

        return view('platform.dashboard', compact(
            'daftarToko',
            'totalSemuaToko',
            'totalTokoAktif',
            'totalTokoNonaktif',
            'totalPenggunaGlobal',
            'estimasiSaaSMrr',
            'omzetGlobalTransaksi',
            'totalTransaksiGlobal',
            'activeTokoId'
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
}
