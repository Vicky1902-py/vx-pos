<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use App\Services\TenantManager;

class TokoController extends Controller
{
    private function checkAccess(): void
    {
        if (!TenantManager::isPlatformAdmin()) {
            abort(403, 'Akses Ditolak: Halaman Multi-Toko hanya dapat diakses oleh Platform Superadmin.');
        }
    }

    public function index(Request $request)
    {
        $this->checkAccess();
        $search = $request->get('search');

        $toko = DB::table('toko')
            ->when($search, function ($query, $search) {
                return $query->where('nama_toko', 'like', "%{$search}%")
                             ->orWhere('alamat', 'like', "%{$search}%")
                             ->orWhere('no_telp', 'like', "%{$search}%");
            })
            ->orderBy('id', 'desc')
            ->paginate(10);

        foreach ($toko as $item) {
            $item->total_barang = DB::table('barang')->where('toko_id', $item->id)->count();
            $item->total_transaksi = DB::table('transaksi')->where('toko_id', $item->id)->count();
            $item->total_user = DB::table('users')->where('toko_id', $item->id)->count();
            $item->total_omzet = DB::table('transaksi')->where('toko_id', $item->id)->where('status', 'selesai')->sum('total_transaksi');
        }

        $activeTokoId = TenantManager::getTokoId();

        return view('superadmin.toko.index', compact('toko', 'activeTokoId'));
    }

    public function store(Request $request)
    {
        $this->checkAccess();
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

            $tokoId = DB::table('toko')->insertGetId([
                'nama_toko'  => $request->nama_toko,
                'slug'       => $slug,
                'alamat'     => $request->alamat,
                'no_telp'    => $request->no_telp,
                'paket'      => $request->paket,
                'status'     => 'aktif',
                'expired_at' => now()->addYear(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Inisialisasi pengaturan toko baru
            DB::table('pengaturan_toko')->insert([
                'toko_id'    => $tokoId,
                'nama_toko'  => $request->nama_toko,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Buat akun Admin Pemilik Toko
            $semuaHakAkses = [
                'master_barang', 'manajemen_harga', 'transaksi_sales',
                'stok_gudang', 'validasi_kasir', 'laporan_penjualan',
                'kelola_bonus', 'manajemen_user'
            ];

            $tableCols = Schema::getColumnListing('users');
            $newAdminData = [
                'username'   => $request->admin_username,
                'password'   => Hash::make($request->admin_password),
                'updated_at' => now(),
            ];

            if (in_array('created_at', $tableCols)) $newAdminData['created_at'] = now();
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
            return redirect()->back()->with('success', "Toko baru [{$request->nama_toko}] beserta akun Admin Toko berhasil didaftarkan!");
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withErrors('Gagal mendaftarkan toko: ' . $e->getMessage());
        }
    }

    public function update(Request $request, $id)
    {
        $this->checkAccess();

        $request->validate([
            'nama_toko' => 'required|string|max:255',
            'no_telp'   => 'nullable|string|max:50',
            'alamat'    => 'nullable|string',
            'paket'     => 'required|in:starter,pro,enterprise',
            'status'    => 'required|in:aktif,nonaktif,suspended',
        ]);

        DB::table('toko')->where('id', $id)->update([
            'nama_toko'  => $request->nama_toko,
            'no_telp'    => $request->no_telp,
            'alamat'     => $request->alamat,
            'paket'      => $request->paket,
            'status'     => $request->status,
            'updated_at' => now(),
        ]);

        // Sinkronisasi ke nama pengaturan toko
        DB::table('pengaturan_toko')->where('toko_id', $id)->update([
            'nama_toko'  => $request->nama_toko,
            'updated_at' => now(),
        ]);

        return redirect()->back()->with('success', 'Informasi toko berhasil diperbarui.');
    }

    public function switchToko($id)
    {
        $this->checkAccess();

        if (TenantManager::switchToko((int) $id)) {
            $toko = DB::table('toko')->where('id', $id)->first();
            return redirect()->back()->with('success', "Konteks kerja beralih ke: {$toko->nama_toko}");
        }

        return redirect()->back()->withErrors('Gagal beralih toko.');
    }
}
