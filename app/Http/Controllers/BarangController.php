<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BarangController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->get('search');
        $barang = DB::table('barang')
            ->leftJoin('stok', 'barang.id', '=', 'stok.barang_id')
            ->leftJoin('harga', 'barang.id', '=', 'harga.barang_id') 
            ->select(
                'barang.*',
                'stok.stok_tersedia',
                'stok.stok_minimum',
                'stok.status_warning',
                'harga.harga_modal',
                'harga.harga_minimum',
                'harga.harga_jual',
                'harga.diskon_rupiah' // [BARU]
            )
            ->when($search, function ($query, $search) {
                return $query->where('barang.kode_barang', 'like', "%{$search}%")
                             ->orWhere('barang.nama_barang', 'like', "%{$search}%")
                             ->orWhere('barang.kategori', 'like', "%{$search}%");
            })
            ->orderBy('barang.id', 'desc')
            ->paginate(10);

        return view('superadmin.barang.index', compact('barang'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'kode_barang'   => 'required|unique:barang,kode_barang',
            'nama_barang'   => 'required',
            'kategori'      => 'required',
            'satuan'        => 'required',
            'status'        => 'required|in:aktif,nonaktif',
            'stok_tersedia' => 'required|integer|min:0',
            'stok_minimum'  => 'required|integer|min:0',
            'harga_modal'   => 'nullable|numeric|min:0',
            'harga_minimum' => 'nullable|numeric|min:0',
            'harga_jual'    => 'nullable|numeric|min:0',
            'diskon_rupiah' => 'nullable|numeric|min:0', // [BARU]
        ]);

        DB::beginTransaction();
        try {
            $barangId = DB::table('barang')->insertGetId([
                'kode_barang' => $request->kode_barang,
                'nama_barang' => $request->nama_barang,
                'kategori'    => $request->kategori,
                'satuan'      => $request->satuan,
                'status'      => $request->status,
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);

            DB::table('stok')->insert([
                'barang_id'      => $barangId,
                'stok_tersedia'  => $request->stok_tersedia,
                'stok_minimum'   => $request->stok_minimum,
                'status_warning' => ($request->stok_tersedia <= $request->stok_minimum) ? 'warning' : 'aman',
                'created_at'     => now(),
                'updated_at'     => now(),
            ]);

            DB::table('harga')->insert([
                'barang_id'     => $barangId,
                'harga_modal'   => $request->harga_modal ?? 0,
                'harga_minimum' => $request->harga_minimum ?? 0,
                'harga_jual'    => $request->harga_jual ?? 0,
                'diskon_rupiah' => $request->diskon_rupiah ?? 0, // [BARU]
                'created_at'    => now(),
                'updated_at'    => now(),
            ]);

            DB::commit();
            return redirect()->back()->with('success', 'Data Barang, Stok, dan 3 Tingkat Harga berhasil ditambahkan secara manual.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withErrors('Gagal menyimpan data: ' . $e->getMessage());
        }
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'kode_barang'   => 'required|unique:barang,kode_barang,'.$id,
            'nama_barang'   => 'required',
            'kategori'      => 'required',
            'satuan'        => 'required',
            'status'        => 'required|in:aktif,nonaktif',
            'stok_tersedia' => 'required|integer|min:0',
            'stok_minimum'  => 'required|integer|min:0',
            'harga_modal'   => 'nullable|numeric|min:0',
            'harga_minimum' => 'nullable|numeric|min:0',
            'harga_jual'    => 'nullable|numeric|min:0',
            'diskon_rupiah' => 'nullable|numeric|min:0', // [BARU]
        ]);

        DB::beginTransaction();
        try {
            DB::table('barang')->where('id', $id)->update([
                'kode_barang' => $request->kode_barang,
                'nama_barang' => $request->nama_barang,
                'kategori'    => $request->kategori,
                'satuan'      => $request->satuan,
                'status'      => $request->status,
                'updated_at'  => now(),
            ]);

            $statusWarning = ($request->stok_tersedia <= $request->stok_minimum) ? 'warning' : 'aman';
            DB::table('stok')->updateOrInsert(
                ['barang_id' => $id],
                [
                    'stok_tersedia'  => $request->stok_tersedia,
                    'stok_minimum'   => $request->stok_minimum,
                    'status_warning' => $statusWarning,
                    'updated_at'     => now()
                ]
            );

            DB::table('harga')->updateOrInsert(
                ['barang_id' => $id],
                [
                    'harga_modal'   => $request->harga_modal ?? 0,
                    'harga_minimum' => $request->harga_minimum ?? 0,
                    'harga_jual'    => $request->harga_jual ?? 0,
                    'diskon_rupiah' => $request->diskon_rupiah ?? 0, // [BARU]
                    'updated_at'    => now()
                ]
            );

            DB::commit();
            return redirect()->back()->with('success', 'Data barang, stok, dan harga berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withErrors('Gagal memperbarui data: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        DB::table('barang')->where('id', $id)->delete();
        return redirect()->back()->with('success', 'Barang beserta seluruh riwayat stok dan harganya berhasil dihapus permanen.');
    }

    public function importMassal(Request $request)
    {
        $dataBarang = $request->input('data');

        if (empty($dataBarang) || !is_array($dataBarang)) {
            return response()->json(['success' => false, 'message' => 'Struktur data tidak valid atau kosong.'], 400);
        }

        $inserted = 0;
        $updated = 0;
        $skipped = 0;

        DB::beginTransaction();
        try {
            foreach ($dataBarang as $row) {
                $kode     = $row['Kode Barang'] ?? $row['kode_barang'] ?? null;
                $nama     = $row['Nama Barang'] ?? $row['nama_barang'] ?? null;
                $kategori = $row['Kategori'] ?? $row['kategori'] ?? null;
                $satuan   = $row['Satuan'] ?? $row['satuan'] ?? null;
                $status   = strtolower($row['Status'] ?? $row['status'] ?? 'aktif');
                
                $stokTersedia = isset($row['Stok Tersedia']) ? (int)$row['Stok Tersedia'] : 0;
                $stokMinimum  = isset($row['Stok Minimum']) ? (int)$row['Stok Minimum'] : 0;
                
                // Ekstraksi 3 Tingkat Harga
                $hargaModal   = isset($row['Harga Modal']) ? (float)$row['Harga Modal'] : 0;
                $hargaMinimum = isset($row['Harga Minimum']) ? (float)$row['Harga Minimum'] : 0;
                $hargaJual    = isset($row['Harga Jual']) ? (float)$row['Harga Jual'] : 0;
                
                // [BARU] Logika Penyelamat (Fail-Safe) untuk Excel lama tanpa kolom Diskon
                $diskonRupiah = isset($row['Diskon']) ? (float)$row['Diskon'] : (isset($row['diskon_rupiah']) ? (float)$row['diskon_rupiah'] : 0);

                if (!$kode || !$nama) {
                    $skipped++;
                    continue;
                }

                $existingBarang = DB::table('barang')->where('kode_barang', $kode)->first();

                if ($existingBarang) {
                    $barangId = $existingBarang->id;
                    
                    DB::table('barang')->where('id', $barangId)->update([
                        'nama_barang' => $nama,
                        'kategori'    => $kategori ?? $existingBarang->kategori,
                        'satuan'      => $satuan ?? $existingBarang->satuan,
                        'status'      => in_array($status, ['aktif', 'nonaktif']) ? $status : $existingBarang->status,
                        'updated_at'  => now(),
                    ]);

                    DB::table('stok')->updateOrInsert(
                        ['barang_id' => $barangId],
                        [
                            'stok_tersedia'  => $stokTersedia,
                            'stok_minimum'   => $stokMinimum,
                            'status_warning' => ($stokTersedia <= $stokMinimum) ? 'warning' : 'aman',
                            'updated_at'     => now(),
                        ]
                    );

                    DB::table('harga')->updateOrInsert(
                        ['barang_id' => $barangId],
                        [
                            'harga_modal'   => $hargaModal,
                            'harga_minimum' => $hargaMinimum,
                            'harga_jual'    => $hargaJual,
                            'diskon_rupiah' => $diskonRupiah, // [BARU]
                            'updated_at'    => now(),
                        ]
                    );
                    $updated++;
                    
                } else {
                    $barangId = DB::table('barang')->insertGetId([
                        'kode_barang' => $kode,
                        'nama_barang' => $nama,
                        'kategori'    => $kategori ?? 'Umum',
                        'satuan'      => $satuan ?? 'Pcs',
                        'status'      => in_array($status, ['aktif', 'nonaktif']) ? $status : 'aktif',
                        'created_at'  => now(),
                        'updated_at'  => now(),
                    ]);

                    DB::table('stok')->insert([
                        'barang_id'      => $barangId,
                        'stok_tersedia'  => $stokTersedia,
                        'stok_minimum'   => $stokMinimum,
                        'status_warning' => ($stokTersedia <= $stokMinimum) ? 'warning' : 'aman',
                        'created_at'     => now(),
                        'updated_at'     => now(),
                    ]);

                    DB::table('harga')->insert([
                        'barang_id'     => $barangId,
                        'harga_modal'   => $hargaModal,
                        'harga_minimum' => $hargaMinimum,
                        'harga_jual'    => $hargaJual,
                        'diskon_rupiah' => $diskonRupiah, // [BARU]
                        'created_at'    => now(),
                        'updated_at'    => now(),
                    ]);
                    $inserted++;
                }
            }
            
            DB::commit();
            return response()->json([
                'success' => true, 
                'message' => "Proses selesai! ($inserted) Barang Baru ditambah, ($updated) Barang Diperbarui, dan ($skipped) dilewati."
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Gagal menyimpan ke database: ' . $e->getMessage()], 500);
        }
    }
}