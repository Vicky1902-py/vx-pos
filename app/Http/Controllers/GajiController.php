<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;
use App\Services\TenantManager;

class GajiController extends Controller
{
    public function index(Request $request)
    {
        $bulan = $request->get('bulan', date('m'));
        $tahun = $request->get('tahun', date('Y'));
        $tokoId = TenantManager::getTokoId();

        // Tarik semua user khusus toko aktif (kecuali superadmin platform)
        $karyawan = DB::table('users')
            ->where('role', '!=', 'superadmin')
            ->where('toko_id', $tokoId)
            ->get();

        $bulanPad = str_pad($bulan, 2, '0', STR_PAD_LEFT);
        $bulanInt = (int)$bulan;
        $tahunStr = (string)$tahun;

        $hasGajiToko = Schema::hasTable('penggajian') && Schema::hasColumn('penggajian', 'toko_id');
        $hasBonusToko = Schema::hasTable('pencairan_bonus') && Schema::hasColumn('pencairan_bonus', 'toko_id');
        $hasPeriode = Schema::hasTable('pencairan_bonus') && Schema::hasColumn('pencairan_bonus', 'periode');
        $hasCreatedAt = Schema::hasTable('pencairan_bonus') && Schema::hasColumn('pencairan_bonus', 'created_at');

        foreach ($karyawan as $k) {
            // Cek apakah gaji bulan ini sudah diproses
            $gajiQuery = DB::table('penggajian')
                ->where('user_id', $k->id)
                ->where('periode_bulan', $bulanPad)
                ->where('periode_tahun', $tahunStr);

            if ($hasGajiToko) {
                $gajiQuery->where(function($q) use ($tokoId) {
                    $q->where('toko_id', $tokoId)->orWhereNull('toko_id');
                });
            }

            $gaji = $gajiQuery->first();

            // [INTEGRASI REVOLUSIONER] Selalu tarik total bonus yang dicairkan untuk karyawan ini
            $bonusQuery = DB::table('pencairan_bonus')
                ->where(function($q) use ($k) {
                    $q->where('sales_id', $k->id);
                    if (Schema::hasColumn('pencairan_bonus', 'user_id')) {
                        $q->orWhere('user_id', $k->id);
                    }
                });

            if ($hasBonusToko) {
                $bonusQuery->where(function($q) use ($tokoId) {
                    $q->where('toko_id', $tokoId)->orWhereNull('toko_id');
                });
            }

            $periodeFormats = [
                "$bulanPad/$tahunStr",
                "$bulanInt/$tahunStr",
                "$tahunStr-$bulanPad",
                date('F Y', mktime(0, 0, 0, $bulanInt, 10, (int)$tahunStr)),
            ];

            $bonusQuery->where(function($q) use ($bulanInt, $tahunStr, $periodeFormats, $hasPeriode, $hasCreatedAt) {
                if ($hasPeriode && $hasCreatedAt) {
                    $q->whereIn('periode', $periodeFormats)
                      ->orWhere(function($sub) use ($bulanInt, $tahunStr) {
                          $sub->whereMonth('created_at', $bulanInt)->whereYear('created_at', $tahunStr);
                      });
                } elseif ($hasPeriode) {
                    $q->whereIn('periode', $periodeFormats);
                } elseif ($hasCreatedAt) {
                    $q->whereMonth('created_at', $bulanInt)->whereYear('created_at', $tahunStr);
                }
            });

            $totalBonusDicairkan = (float) $bonusQuery->sum('total_bonus');
            $k->estimasi_bonus = $totalBonusDicairkan;
            $k->gaji_pokok_standar = Schema::hasColumn('users', 'gaji_pokok') ? (float)($k->gaji_pokok ?? 0) : 0;

            if ($gaji) {
                $k->status_gaji = 'dibayar';
                $k->data_gaji = $gaji;
            } else {
                $k->status_gaji = 'belum';
                $k->data_gaji = null;
            }
        }

        return view('superadmin.gaji.index', compact('karyawan', 'bulan', 'tahun'));
    }

    public function store(Request $request)
    {
        $tokoId = TenantManager::getTokoId();

        $request->validate([
            'user_id'       => 'required|integer',
            'periode_bulan' => 'required|string|size:2',
            'periode_tahun' => 'required|string|size:4',
            'gaji_pokok'    => 'required|numeric|min:0',
            'tunjangan'     => 'nullable|numeric|min:0',
            'bonus'         => 'nullable|numeric|min:0',
            'potongan'      => 'nullable|numeric|min:0',
            'keterangan_potongan' => 'nullable|string|max:255',
            'simpan_sebagai_standar' => 'nullable',
        ]);

        // Verifikasi bahwa karyawan milik toko aktif
        $karyawan = DB::table('users')
            ->where('id', $request->user_id)
            ->where('toko_id', $tokoId)
            ->first();

        if (!$karyawan) {
            return redirect()->back()->withErrors('Akses Ditolak: Karyawan tidak ditemukan di toko ini.');
        }

        $gajiPokok = (float)($request->gaji_pokok ?: 0);
        $tunjangan = (float)($request->tunjangan ?: 0);
        $bonus = (float)($request->bonus ?: 0);
        $potongan = (float)($request->potongan ?: 0);
        
        $totalGaji = ($gajiPokok + $tunjangan + $bonus) - $potongan;
        if ($totalGaji < 0) $totalGaji = 0;

        $cols = Schema::hasTable('penggajian') ? Schema::getColumnListing('penggajian') : [];
        $hasGajiToko = in_array('toko_id', $cols);

        $matchCondition = [
            'user_id'       => $request->user_id,
            'periode_bulan' => $request->periode_bulan,
            'periode_tahun' => $request->periode_tahun,
        ];
        if ($hasGajiToko) $matchCondition['toko_id'] = $tokoId;

        $updatePayload = [
            'gaji_pokok'          => $gajiPokok,
            'tunjangan'           => $tunjangan,
            'bonus'               => $bonus,
            'potongan'            => $potongan,
            'keterangan_potongan' => $request->keterangan_potongan,
            'total_gaji'          => $totalGaji,
        ];
        if (in_array('toko_id', $cols)) $updatePayload['toko_id'] = $tokoId;
        if (in_array('tanggal_cair', $cols)) $updatePayload['tanggal_cair'] = now();
        if (in_array('updated_at', $cols)) $updatePayload['updated_at'] = now();
        if (in_array('created_at', $cols)) {
            $exists = DB::table('penggajian')->where($matchCondition)->exists();
            if (!$exists) {
                $updatePayload['created_at'] = now();
            }
        }

        DB::beginTransaction();
        try {
            DB::table('penggajian')->updateOrInsert($matchCondition, $updatePayload);

            // Simpan sebagai gaji pokok standar pegawai jika dicentang atau belum pernah diatur
            if ($request->has('simpan_sebagai_standar') && Schema::hasColumn('users', 'gaji_pokok')) {
                DB::table('users')->where('id', $request->user_id)->update([
                    'gaji_pokok' => $gajiPokok,
                    'updated_at' => now(),
                ]);
            }

            DB::commit();
            return redirect()->back()->with('success', 'Gaji karyawan berhasil diproses dan disimpan.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withErrors('Gagal menyimpan data gaji: ' . $e->getMessage());
        }
    }

    /**
     * Update Cepat Gaji Pokok Standar Pegawai via AJAX / Modal
     */
    public function updateGajiStandar(Request $request)
    {
        $request->validate([
            'user_id'    => 'required|integer',
            'gaji_pokok' => 'required|numeric|min:0',
        ]);

        $tokoId = TenantManager::getTokoId();
        $karyawan = DB::table('users')
            ->where('id', $request->user_id)
            ->where('toko_id', $tokoId)
            ->first();

        if (!$karyawan) {
            return response()->json(['success' => false, 'message' => 'Karyawan tidak ditemukan.'], 404);
        }

        if (Schema::hasColumn('users', 'gaji_pokok')) {
            DB::table('users')->where('id', $request->user_id)->update([
                'gaji_pokok' => (float)$request->gaji_pokok,
                'updated_at' => now(),
            ]);
        }

        return response()->json([
            'success'   => true,
            'message'   => 'Gaji pokok standar berhasil diperbarui.',
            'nominal'   => (float)$request->gaji_pokok,
            'formatted' => 'Rp ' . number_format($request->gaji_pokok, 0, ',', '.'),
        ]);
    }

    public function cetakSlip($id)
    {
        $tokoId = TenantManager::getTokoId();

        $gaji = DB::table('penggajian')
            ->join('users', 'penggajian.user_id', '=', 'users.id')
            ->select('penggajian.*', 'users.nama', 'users.role', 'users.toko_id')
            ->where('penggajian.id', $id)
            ->where('users.toko_id', $tokoId)
            ->first();

        if (!$gaji) {
            return redirect()->back()->withErrors('Data slip gaji tidak ditemukan atau bukan milik toko aktif.');
        }

        $pengaturan = DB::table('pengaturan_toko')->where('toko_id', $tokoId)->first() 
            ?? DB::table('pengaturan_toko')->first();

        return view('superadmin.gaji.slip', compact('gaji', 'pengaturan'));
    }
}