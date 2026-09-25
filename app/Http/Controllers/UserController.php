<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->get('search');
        
        $users = DB::table('users')
            ->when($search, function ($query, $search) {
                return $query->where('nama', 'like', "%{$search}%")
                             ->orWhere('username', 'like', "%{$search}%")
                             ->orWhere('role', 'like', "%{$search}%");
            })
            ->orderBy('id', 'desc')
            ->paginate(10);

        return view('superadmin.user.index', compact('users'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama'      => 'required|string|max:255',
            'username'  => 'required|string|max:50|unique:users,username',
            'password'  => 'required|string|min:6',
            'role'      => 'required|in:superadmin,admin,kasir,sales,gudang',
            'status'    => 'required|in:aktif,nonaktif',
            'hak_akses' => 'nullable|array' // Menangkap data checkbox
        ]);

        DB::table('users')->insert([
            'nama'       => $request->nama,
            'username'   => $request->username,
            'password'   => Hash::make($request->password), // Enkripsi sandi
            'role'       => $request->role,
            'status'     => $request->status,
            'hak_akses'  => json_encode($request->hak_akses ?? []), // Konversi ke JSON
            'created_at' => now(),
            'updated_at' => now(),
        ]);

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

        // Jika password diisi, berarti Super Admin ingin mereset password
        if ($request->filled('password')) {
            $request->validate(['password' => 'string|min:6']);
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