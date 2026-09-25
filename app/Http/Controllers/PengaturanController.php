<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class PengaturanController extends Controller
{
    public function index()
    {
        $pengaturan = DB::table('pengaturan_toko')->first();
        return view('superadmin.pengaturan.index', compact('pengaturan'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'nama_toko' => 'required|string|max:255',
            'logo'      => 'nullable|image|mimes:jpeg,png,jpg|max:2048', // Maksimal 2MB
        ]);

        $data = $request->except(['_token', 'logo']);
        $data['updated_at'] = now();

        if ($request->hasFile('logo')) {
            $pengaturanLama = DB::table('pengaturan_toko')->first();
            
            // Hapus logo lama jika ada
            if ($pengaturanLama && $pengaturanLama->logo && File::exists(public_path('uploads/logo/' . $pengaturanLama->logo))) {
                File::delete(public_path('uploads/logo/' . $pengaturanLama->logo));
            }

            // Simpan logo baru
            $fileName = time() . '_logo.' . $request->logo->extension();
            $request->logo->move(public_path('uploads/logo'), $fileName);
            $data['logo'] = $fileName;
        }

        DB::table('pengaturan_toko')->where('id', 1)->update($data);

        return redirect()->back()->with('success', 'Profil toko dan logo berhasil diperbarui!');
    }
}