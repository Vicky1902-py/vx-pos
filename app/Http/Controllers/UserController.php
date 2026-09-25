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
            ->when(!$isPlatform, function ($q) use ($tokoId) {
                return $q->where('users.toko_id', $tokoId);
            })
            ->when($search, function ($query, $search) {
                return $query->where('users.nama', 'like', "%{$search}%")
                             ->orWhere('users.username', 'like', "%{$search}%")
                             ->orWhere('users.role', 'like', "%{$search}%");
            })
            ->orderBy('users.id', 'desc')
            ->paginate(10);

        return view('superadmin.user.index', compact('users'));
    }

    public function store(Request $request)
    {
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

        $request->validate([
            'nama'      => 'required|string|max:255',
            'username'  => 'required|string|max:50|unique:users,username',
            'password'  => 'required|string|min:3',
            'role'      => 'required|in:superadmin,admin,kasir,sales,gudang',
            'status'    => 'required|in:aktif,nonaktif',
            'hak_akses' => 'nullable|array' // Menangkap data checkbox
        ]);

        $userData = [
            'toko_id'    => $tokoId,
            'nama'       => $request->nama,
            'username'   => $request->username,
            'password'   => Hash::make($request->password), // Enkripsi sandi
            'role'       => $request->role,
            'status'     => $request->status,
            'hak_akses'  => json_encode($request->hak_akses ?? []), // Konversi ke JSON
            'created_at' => now(),
            'updated_at' => now(),
        ];

        if (Schema::hasColumn('users', 'name')) {
            $userData['name'] = $request->nama;
        }
        if (Schema::hasColumn('users', 'email')) {
            $userData['email'] = $request->username . '@' . ($tokoId ?? '1') . '.vxpos.local';
        }

        DB::table('users')->insert($userData);

        return redirect()->back()->with('success', 'Akun pegawai baru beserta hak aksesnya berhasil dibuat.');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'nama'      => 'required|string|max:255',
            'username'  => 'required|string|max:50|unique:users,username,'.$id,
            'role'      => 'required|in:superadmin,admin,kasir,sales,gudang',
            'status'    => 'required|in:aktif,nonaktif',
            'hak_akses' => 'nullable|array'
        ]);

        $updateData = [
            'nama'       => $request->nama,
            'username'   => $request->username,
            'role'       => $request->role,
            'status'     => $request->status,
            'hak_akses'  => json_encode($request->hak_akses ?? []),
            'updated_at' => now(),
        ];

        if (Schema::hasColumn('users', 'name')) {
            $updateData['name'] = $request->nama;
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
        if (auth()->id() == $id) {
            return redirect()->back()->withErrors('Anda tidak dapat menghapus akun Anda sendiri saat sedang login.');
        }

        DB::table('users')->where('id', $id)->delete();
        return redirect()->back()->with('success', 'Akun berhasil dihapus permanen.');
    }
}