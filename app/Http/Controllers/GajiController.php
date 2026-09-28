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

        $hasGajiToko = Schema::hasTable('penggajian') && Schema::hasColumn('penggajian', 'toko_id');
        $hasBonusToko = Schema::hasTable('pencairan_bonus') && Schema::hasColumn('pencairan_bonus', 'toko_id');

        foreach ($karyawan as $k) {
            // Cek apakah gaji bulan ini sudah diproses
            $gajiQuery = DB::table('penggajian')
                ->where('user_id', $k->id)
                ->where('periode_bulan', $bulan)
                ->where('periode_tahun', $tahun);

            if ($hasGajiToko) {
                $gajiQuery->where('toko_id', $tokoId);
            }

            $gaji = $gajiQuery->first();

            if ($gaji) {
                $k->status_gaji = 'dibayar';
                $k->data_gaji = $gaji;
            } else {
                $k->status_gaji = 'belum';
                
                // [INTEGRASI] Tarik otomatis total bonus bulan ini jika belum dicetak gajinya
                $bonusQuery = DB::table('pencairan_bonus')
                    ->where('sales_id', $k->id)
                    ->whereMonth('created_at', $bulan)
                    ->whereYear('created_at', $tahun);

                if ($hasBonusToko) {
                    $bonusQuery->where('toko_id', $tokoId);
                }
                    
                $k->estimasi_bonus = $bonusQuery->sum('total_bonus');
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
        ]);

        // Verifikasi bahwa karyawan milik toko aktif
        $karyawan = DB::table('users')
            ->where('id', $request->user_id)
            ->where('toko_id', $tokoId)
            ->first();

        if (!$karyawan) {
            return redirect()->back()->withErrors('Akses Ditolak: Karyawan tidak ditemukan di toko ini.');
        }

        $gajiPokok = $request->gaji_pokok ?: 0;
        $tunjangan = $request->tunjangan ?: 0;
        $bonus = $request->bonus ?: 0;
        $potongan = $request->potongan ?: 0;
        
        $totalGaji = ($gajiPokok + $tunjangan + $bonus) - $potongan;

        $hasGajiToko = Schema::hasTable('penggajian') && Schema::hasColumn('penggajian', 'toko_id');

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
            'tanggal_cair'        => now(),
            'updated_at'          => now()
        ];
        if ($hasGajiToko) $updatePayload['toko_id'] = $tokoId;

        DB::beginTransaction();
        try {
            DB::table('penggajian')->updateOrInsert($matchCondition, $updatePayload);

            DB::commit();
            return redirect()->back()->with('success', 'Gaji karyawan berhasil diproses dan disimpan.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withErrors('Gagal menyimpan data gaji: ' . $e->getMessage());
        }
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