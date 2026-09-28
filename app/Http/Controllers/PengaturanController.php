<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use App\Services\TenantManager;

class PengaturanController extends Controller
{
    public function index()
    {
        $tokoId = TenantManager::getTokoId();
        
        $pengaturan = DB::table('pengaturan_toko')->where('toko_id', $tokoId)->first();
        if (!$pengaturan) {
            $toko = DB::table('toko')->where('id', $tokoId)->first();
            $defaultName = $toko ? $toko->nama_toko : 'VxPOS';
            
            $ptCols = Schema::getColumnListing('pengaturan_toko');
            $ptData = [
                'toko_id'   => $tokoId,
                'nama_toko' => $defaultName,
            ];
            if (in_array('alamat', $ptCols)) $ptData['alamat'] = $toko->alamat ?? 'Alamat Toko';
            if (in_array('telepon', $ptCols)) $ptData['telepon'] = $toko->no_telp ?? '08123456789';
            if (in_array('created_at', $ptCols)) $ptData['created_at'] = now()->toDateTimeString();
            if (in_array('updated_at', $ptCols)) $ptData['updated_at'] = now()->toDateTimeString();

            DB::table('pengaturan_toko')->insert($ptData);
            $pengaturan = DB::table('pengaturan_toko')->where('toko_id', $tokoId)->first();
        }

        return view('superadmin.pengaturan.index', compact('pengaturan'));
    }

    public function update(Request $request)
    {
        $tokoId = TenantManager::getTokoId();

        $request->validate([
            'nama_toko' => 'required|string|max:255',
            'logo'      => 'nullable|image|mimes:jpeg,png,jpg|max:2048', // Maksimal 2MB
        ]);

        $data = $request->except(['_token', 'logo']);
        $ptCols = Schema::getColumnListing('pengaturan_toko');

        if (in_array('updated_at', $ptCols)) {
            $data['updated_at'] = now()->toDateTimeString();
        }

        if ($request->hasFile('logo')) {
            $pengaturanLama = DB::table('pengaturan_toko')->where('toko_id', $tokoId)->first();
            
            // Hapus logo lama jika ada
            if ($pengaturanLama && !empty($pengaturanLama->logo) && File::exists(public_path('uploads/logo/' . $pengaturanLama->logo))) {
                File::delete(public_path('uploads/logo/' . $pengaturanLama->logo));
            }

            // Simpan logo baru
            $fileName = time() . '_toko_' . $tokoId . '_logo.' . $request->logo->extension();
            $request->logo->move(public_path('uploads/logo'), $fileName);
            $data['logo'] = $fileName;
        }

        $exists = DB::table('pengaturan_toko')->where('toko_id', $tokoId)->exists();
        if ($exists) {
            DB::table('pengaturan_toko')->where('toko_id', $tokoId)->update($data);
        } else {
            $data['toko_id'] = $tokoId;
            if (in_array('created_at', $ptCols)) $data['created_at'] = now()->toDateTimeString();
            DB::table('pengaturan_toko')->insert($data);
        }

        // Sinkronisasi nama ke tabel induk toko jika ada
        if (Schema::hasTable('toko')) {
            $tokoCols = Schema::getColumnListing('toko');
            $tUpdate = ['nama_toko' => $request->nama_toko];
            if (in_array('updated_at', $tokoCols)) $tUpdate['updated_at'] = now()->toDateTimeString();
            DB::table('toko')->where('id', $tokoId)->update($tUpdate);
        }

        return redirect()->back()->with('success', 'Profil toko dan logo berhasil diperbarui!');
    }
}