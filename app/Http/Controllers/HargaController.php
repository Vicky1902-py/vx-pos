<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HargaController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->get('search');
        
        // Mengambil data barang dan digabungkan (join) dengan data harganya
        $barangHarga = DB::table('barang')
            ->leftJoin('harga', 'barang.id', '=', 'harga.barang_id')
            ->select(
                'barang.id as barang_id', 
                'barang.kode_barang', 
                'barang.nama_barang', 
                'barang.kategori',
                'harga.id as harga_id',
                'harga.harga_modal',
                'harga.harga_minimum',
                'harga.harga_jual',
                'harga.diskon_rupiah' // [BARU] Tambahan untuk menampilkan data diskon
            )
            ->when($search, function ($query, $search) {
                return $query->where('barang.kode_barang', 'like', "%{$search}%")
                             ->orWhere('barang.nama_barang', 'like', "%{$search}%");
            })
            ->orderBy('barang.id', 'desc')
            ->paginate(10);

        return view('superadmin.harga.index', compact('barangHarga'));
    }

    public function storeOrUpdate(Request $request)
    {
        // Validasi input angka dengan Hierarki Harga (Anti-Rugi)
        $request->validate([
            'barang_id'     => 'required|numeric',
            'harga_modal'   => 'required|numeric|min:0',
            'harga_minimum' => 'required|numeric|gte:harga_modal', // Wajib >= harga_modal
            'harga_jual'    => 'required|numeric|gte:harga_minimum', // Wajib >= harga_minimum
            'diskon_rupiah' => 'nullable|numeric|min:0', // [BARU] Validasi nilai diskon
        ], [
            'harga_minimum.gte' => 'Harga Minimum tidak boleh lebih kecil dari Harga Modal.',
            'harga_jual.gte'    => 'Harga Jual tidak boleh lebih kecil dari Harga Minimum.'
        ]);

        $diskon = $request->diskon_rupiah ?? 0;

        // [BARU] Logika Sistem Anti-Rugi
        // Menghitung Harga Final setelah diskon, lalu mengeceknya terhadap Harga Minimum
        if (($request->harga_jual - $diskon) < $request->harga_minimum) {
            return redirect()->back()->withErrors('Gagal menyimpan! Harga Final setelah diskon tidak boleh lebih rendah dari Harga Minimum.');
        }

        // Cek apakah barang ini sudah pernah diset harganya
        $exists = DB::table('harga')->where('barang_id', $request->barang_id)->first();

        if ($exists) {
            // Jika sudah ada, kita Update
            DB::table('harga')->where('barang_id', $request->barang_id)->update([
                'harga_modal'   => $request->harga_modal,
                'harga_minimum' => $request->harga_minimum,
                'harga_jual'    => $request->harga_jual,
                'diskon_rupiah' => $diskon, // [BARU] Simpan ke kolom baru
                'updated_at'    => now()
            ]);
            $pesan = 'Harga dan Diskon berhasil diperbarui!';
        } else {
            // Jika belum ada, kita Insert baru
            DB::table('harga')->insert([
                'barang_id'     => $request->barang_id,
                'harga_modal'   => $request->harga_modal,
                'harga_minimum' => $request->harga_minimum,
                'harga_jual'    => $request->harga_jual,
                'diskon_rupiah' => $diskon, // [BARU] Simpan ke kolom baru
                'created_at'    => now(),
                'updated_at'    => now()
            ]);
            $pesan = 'Harga dan Diskon baru berhasil ditetapkan!';
        }

        return redirect()->back()->with('success', $pesan);
    }
}