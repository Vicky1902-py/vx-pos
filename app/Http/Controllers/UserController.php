<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use App\Services\TenantManager;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->get('search');
        $tokoId = TenantManager::getTokoId();
        $isPlatform = TenantManager::isPlatformAdmin();
        
        $users = DB::table('users')
            ->leftJoin('toko', 'users.toko_id', '=', 'toko.id')
            ->select('users.*', 'toko.nama_toko')
            ->where('users.role', '!=', 'superadmin')
            ->whereNotIn('users.username', ['vicky', 'admin'])
            ->when(!$isPlatform, function ($q) use ($tokoId) {
                return $q->where('users.toko_id', $tokoId);
            })
            ->when($search, function ($query, $search) {
                return $query->where(function($sub) use ($search) {
                    $sub->where('users.nama', 'like', "%{$search}%")
                        ->orWhere('users.username', 'like', "%{$search}%")
                        ->orWhere('users.role', 'like', "%{$search}%");
                });
            })
            ->orderBy('users.id', 'desc')
            ->paginate(10);

        return view('superadmin.user.index', compact('users'));
    }

    public function store(Request $request)
    {
        $authUser = auth()->user();
        if ($authUser && ($authUser->username === 'demo' || $authUser->username === 'kasir_demo' || $authUser->toko_id == 30)) {
            return redirect()->back()->withErrors('Mode Demo: Pembuatan akun karyawan dinonaktifkan.');
        }

        $tokoId = TenantManager::getTokoId();
        $toko = TenantManager::getActiveToko();
        $isPlatform = TenantManager::isPlatformAdmin();

        // Enforce Kuota Paket Starter (Maksimal 3 User Karyawan)
        if (!$isPlatform && $toko && $toko->paket === 'starter') {
            $userCount = DB::table('users')->where('toko_id', $tokoId)->count();
            if ($userCount >= 3) {
                return redirect()->back()->with('error', 'Batas kuota Paket Starter tercapai (Maksimal 3 User). Silakan upgrade ke Paket Pro untuk Unlimited User!');
            }
        }

        $allowedRoles = $isPlatform ? 'superadmin,admin,kasir,sales,gudang' : 'admin,kasir,sales,gudang';

        $request->validate([
            'nama'      => 'required|string|max:255',
            'username'  => 'required|string|max:50|unique:users,username',
            'password'  => 'required|string|min:3',
            'role'       => 'required|in:' . $allowedRoles,
            'status'     => 'required|in:aktif,nonaktif',
            'gaji_pokok' => 'nullable|numeric|min:0',
            'hak_akses'  => 'nullable|array' // Menangkap data checkbox
        ]);

        if (!$isPlatform && $request->role === 'superadmin') {
            return redirect()->back()->withErrors('Anda tidak memiliki wewenang untuk membuat akun Platform Superadmin.');
        }

        $tableCols = Schema::getColumnListing('users');
        $userData = [
            'username'   => $request->username,
            'password'   => Hash::make($request->password),
            'updated_at' => now()->toDateTimeString(),
        ];

        if (in_array('created_at', $tableCols)) $userData['created_at'] = now()->toDateTimeString();
        if (in_array('nama', $tableCols)) $userData['nama'] = $request->nama;
        if (in_array('name', $tableCols)) $userData['name'] = $request->nama;
        if (in_array('email', $tableCols)) $userData['email'] = $request->username . '@' . ($tokoId ?? '1') . '.vxpos.local';
        if (in_array('toko_id', $tableCols)) $userData['toko_id'] = $tokoId;
        if (in_array('role', $tableCols)) $userData['role'] = $request->role;
        if (in_array('gaji_pokok', $tableCols)) $userData['gaji_pokok'] = (float)($request->gaji_pokok ?: 0);
        $hakAksesToSave = (!empty($request->hak_akses) && is_array($request->hak_akses))
            ? $request->hak_akses
            : \App\Http\Middleware\AksesModul::getDefaultPermissionsByRole($request->role);

        if (in_array('status', $tableCols)) $userData['status'] = $request->status;
        if (in_array('hak_akses', $tableCols)) $userData['hak_akses'] = json_encode($hakAksesToSave);

        DB::table('users')->insert($userData);

        return redirect()->back()->with('success', 'Akun pegawai baru beserta hak aksesnya berhasil dibuat.');
    }

    public function update(Request $request, $id)
    {
        $authUser = auth()->user();
        if ($authUser && ($authUser->username === 'demo' || $authUser->username === 'kasir_demo' || $authUser->toko_id == 30)) {
            return redirect()->back()->withErrors('Mode Demo: Perubahan akun karyawan dinonaktifkan.');
        }

        $isPlatform = TenantManager::isPlatformAdmin();
        $tokoId = TenantManager::getTokoId();

        $targetUser = DB::table('users')->where('id', $id)->first();
        if (!$targetUser) {
            return redirect()->back()->withErrors('Data pengguna tidak ditemukan.');
        }

        if (!$isPlatform) {
            if ($targetUser->toko_id != $tokoId || $targetUser->role === 'superadmin' || in_array($targetUser->username, ['vicky', 'admin'])) {
                return redirect()->back()->withErrors('Anda tidak memiliki wewenang untuk mengedit akun ini.');
            }
        }

        $allowedRoles = $isPlatform ? 'superadmin,admin,kasir,sales,gudang' : 'admin,kasir,sales,gudang';

        $request->validate([
            'nama'       => 'required|string|max:255',
            'username'   => 'required|string|max:50|unique:users,username,'.$id,
            'role'       => 'required|in:' . $allowedRoles,
            'status'     => 'required|in:aktif,nonaktif',
            'gaji_pokok' => 'nullable|numeric|min:0',
            'hak_akses'  => 'nullable|array'
        ]);

        $hakAksesToSave = (!empty($request->hak_akses) && is_array($request->hak_akses))
            ? $request->hak_akses
            : \App\Http\Middleware\AksesModul::getDefaultPermissionsByRole($request->role);

        $updateData = [
            'nama'       => strip_tags($request->nama),
            'username'   => strip_tags($request->username),
            'role'       => $request->role,
            'status'     => $request->status,
            'gaji_pokok' => (float)($request->gaji_pokok ?: 0),
            'hak_akses'  => json_encode($hakAksesToSave),
            'updated_at' => now()->toDateTimeString(),
        ];

        if (Schema::hasColumn('users', 'name')) {
            $updateData['name'] = strip_tags($request->nama);
        }

        // Jika password diisi, berarti Super Admin ingin mereset password
        if ($request->filled('password')) {
            $request->validate(['password' => 'string|min:3']);
            $updateData['password'] = Hash::make($request->password);
        }

        DB::table('users')->where('id', $id)->update($updateData);

        return redirect()->back()->with('success', 'Data akun dan hak akses berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $authUser = auth()->user();
        if ($authUser && ($authUser->username === 'demo' || $authUser->username === 'kasir_demo' || $authUser->toko_id == 30)) {
            return redirect()->back()->withErrors('Mode Demo: Penghapusan akun karyawan dinonaktifkan.');
        }

        $isPlatform = TenantManager::isPlatformAdmin();
        $tokoId = TenantManager::getTokoId();

        if (auth()->id() == $id) {
            return redirect()->back()->withErrors('Anda tidak dapat menghapus akun Anda sendiri saat sedang login.');
        }

        $targetUser = DB::table('users')->where('id', $id)->first();
        if (!$targetUser) {
            return redirect()->back()->withErrors('Data pengguna tidak ditemukan.');
        }

        if ($targetUser->role === 'superadmin' || in_array($targetUser->username, ['vicky', 'admin'])) {
            return redirect()->back()->withErrors('Akun Platform Superadmin Utama tidak dapat dihapus.');
        }

        if (!$isPlatform && $targetUser->toko_id != $tokoId) {
            return redirect()->back()->withErrors('Anda tidak memiliki wewenang untuk menghapus akun pengguna dari toko lain.');
        }

        DB::table('users')->where('id', $id)->delete();
        return redirect()->back()->with('success', 'Akun berhasil dihapus permanen.');
    }
}