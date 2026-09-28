<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Services\TenantManager;

class BarangController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->get('search');
        $tokoId = TenantManager::getTokoId();

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
            ->where('barang.toko_id', $tokoId)
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
        $tokoId = TenantManager::getTokoId();

        $request->validate([
            'kode_barang'   => 'required',
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
                'toko_id'     => $tokoId,
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
        $tokoId = TenantManager::getTokoId();
        $query = DB::table('barang')->where('id', $id);
        if (!TenantManager::isPlatformAdmin()) {
            $query->where('toko_id', $tokoId);
        }
        $barang = $query->first();
        if (!$barang) {
            return redirect()->back()->withErrors('Barang tidak ditemukan atau bukan milik toko Anda.');
        }

        $request->validate([
            'kode_barang'   => [
                'required',
                \Illuminate\Validation\Rule::unique('barang', 'kode_barang')->ignore($id)->where(function ($q) use ($tokoId, $barang) {
                    return $q->where('toko_id', $barang->toko_id ?? $tokoId);
                })
            ],
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
        $tokoId = TenantManager::getTokoId();
        $query = DB::table('barang')->where('id', $id);
        if (!TenantManager::isPlatformAdmin()) {
            $query->where('toko_id', $tokoId);
        }
        $barang = $query->first();
        if (!$barang) {
            return redirect()->back()->withErrors('Barang tidak ditemukan atau bukan milik toko Anda.');
        }

        DB::beginTransaction();
        try {
            DB::table('stok')->where('barang_id', $id)->delete();
            DB::table('harga')->where('barang_id', $id)->delete();
            DB::table('barang')->where('id', $id)->delete();
            DB::commit();
            return redirect()->back()->with('success', 'Barang beserta seluruh riwayat stok dan harganya berhasil dihapus permanen.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withErrors('Gagal menghapus barang: ' . $e->getMessage());
        }
    }

    private function cleanNumber($val): float
    {
        if (is_numeric($val)) return (float)$val;
        if (empty($val)) return 0.0;
        
        $cleaned = preg_replace('/[^\d.,]/', '', (string)$val);
        if (empty($cleaned)) return 0.0;

        if (strpos($cleaned, '.') !== false && strpos($cleaned, ',') !== false) {
            $cleaned = str_replace('.', '', $cleaned);
            $cleaned = str_replace(',', '.', $cleaned);
        } elseif (strpos($cleaned, '.') !== false) {
            if (preg_match('/\.\d{3}$/', $cleaned)) {
                $cleaned = str_replace('.', '', $cleaned);
            }
        } elseif (strpos($cleaned, ',') !== false) {
            if (preg_match('/,\d{3}$/', $cleaned)) {
                $cleaned = str_replace(',', '', $cleaned);
            } else {
                $cleaned = str_replace(',', '.', $cleaned);
            }
        }
        return (float)$cleaned;
    }

    public function importMassal(Request $request)
    {
        $tokoId = TenantManager::getTokoId();
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
                if (!is_array($row)) continue;

                // Normalisasi kunci kolom: huruf kecil & hilangkan spasi / simbol pemisah
                $c = [];
                foreach ($row as $k => $v) {
                    $cleanKey = strtolower(trim(str_replace([' ', '_', '-', '.', '/', '\\'], '', (string)$k)));
                    $c[$cleanKey] = is_string($v) ? trim($v) : $v;
                }

                // Ekstraksi nilai dengan fallback cerdas untuk segala variasi nama kolom
                $kode     = $c['kodebarang'] ?? $c['kode'] ?? $c['barcode'] ?? $c['sku'] ?? null;
                $nama     = $c['namabarang'] ?? $c['nama'] ?? $c['item'] ?? $c['produk'] ?? null;
                $kategori = !empty($c['kategori']) ? $c['kategori'] : 'Umum';
                $satuan   = !empty($c['satuan']) ? $c['satuan'] : 'Pcs';
                
                $statusInput = strtolower($c['status'] ?? 'aktif');
                $status   = in_array($statusInput, ['aktif', 'nonaktif']) ? $statusInput : 'aktif';

                // Ekstraksi Stok Aman
                $stokTersedia = (int) max(0, $this->cleanNumber($c['stoktersedia'] ?? $c['stok'] ?? $c['qty'] ?? 0));
                $stokMinimum  = (int) max(0, $this->cleanNumber($c['stokminimum'] ?? $c['minstok'] ?? $c['min'] ?? 0));

                // Ekstraksi 3 Tingkat Harga
                $hargaModal   = max(0, $this->cleanNumber($c['hargamodal'] ?? $c['modal'] ?? $c['hpp'] ?? 0));
                $hargaMinimum = max(0, $this->cleanNumber($c['hargaminimum'] ?? $c['hargamin'] ?? $c['minjual'] ?? 0));
                $hargaJual    = max(0, $this->cleanNumber($c['hargajual'] ?? $c['harga'] ?? $c['jual'] ?? $c['price'] ?? 0));
                $diskonRupiah = max(0, $this->cleanNumber($c['diskonrupiah'] ?? $c['diskon'] ?? $c['discount'] ?? 0));

                // Sinkronisasi otomatis aturan Hierarki Harga jika data dari Excel keliru
                if ($hargaMinimum < $hargaModal) {
                    $hargaMinimum = $hargaModal;
                }
                if ($hargaJual < $hargaMinimum) {
                    $hargaJual = $hargaMinimum;
                }
                if (($hargaJual - $diskonRupiah) < $hargaMinimum) {
                    $diskonRupiah = max(0, $hargaJual - $hargaMinimum);
                }

                // Jika nama kosong, lewati baris ini
                if (empty($nama)) {
                    $skipped++;
                    continue;
                }

                // Jika kode barang kosong, buat kode SKU otomatis
                if (empty($kode)) {
                    $kode = 'BRG-' . strtoupper(Str::random(6));
                }

                $existingBarang = DB::table('barang')
                    ->where('kode_barang', $kode)
                    ->where('toko_id', $tokoId)
                    ->first();

                if ($existingBarang) {
                    $barangId = $existingBarang->id;
                    
                    DB::table('barang')->where('id', $barangId)->update([
                        'nama_barang' => $nama,
                        'kategori'    => $kategori,
                        'satuan'      => $satuan,
                        'status'      => $status,
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
                            'diskon_rupiah' => $diskonRupiah,
                            'updated_at'    => now(),
                        ]
                    );
                    $updated++;
                    
                } else {
                    $barangId = DB::table('barang')->insertGetId([
                        'toko_id'     => $tokoId,
                        'kode_barang' => $kode,
                        'nama_barang' => $nama,
                        'kategori'    => $kategori,
                        'satuan'      => $satuan,
                        'status'      => $status,
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
                        'diskon_rupiah' => $diskonRupiah,
                        'created_at'    => now(),
                        'updated_at'    => now(),
                    ]);
                    $inserted++;
                }
            }
            
            DB::commit();
            return response()->json([
                'success' => true, 
                'message' => "Proses impor selesai! ($inserted) Barang Baru ditambah, ($updated) Barang Diperbarui, dan ($skipped) baris dilewati."
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Gagal memproses data impor: ' . $e->getMessage()], 500);
        }
    }
}