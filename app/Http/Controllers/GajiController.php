<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class GajiController extends Controller
{
    public function index(Request $request)
    {
        $bulan = $request->get('bulan', date('m'));
        $tahun = $request->get('tahun', date('Y'));

        // Tarik semua user kecuali superadmin
        $karyawan = DB::table('users')->where('role', '!=', 'superadmin')->get();

        foreach ($karyawan as $k) {
            // Cek apakah gaji bulan ini sudah diproses
            $gaji = DB::table('penggajian')
                ->where('user_id', $k->id)
                ->where('periode_bulan', $bulan)
                ->where('periode_tahun', $tahun)
                ->first();

            if ($gaji) {
                $k->status_gaji = 'dibayar';
                $k->data_gaji = $gaji;
            } else {
                $k->status_gaji = 'belum';
                
                // [INTEGRASI] Tarik otomatis total bonus bulan ini jika belum dicetak gajinya
                $totalBonusBulanIni = DB::table('pencairan_bonus')
                    ->where('sales_id', $k->id)
                    ->whereMonth('created_at', $bulan)
                    ->whereYear('created_at', $tahun)
                    ->sum('total_bonus');
                    
                $k->estimasi_bonus = $totalBonusBulanIni;
            }
        }

        return view('superadmin.gaji.index', compact('karyawan', 'bulan', 'tahun'));
    }

    public function store(Request $request)
    {
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

        $gajiPokok = $request->gaji_pokok ?: 0;
        $tunjangan = $request->tunjangan ?: 0;
        $bonus = $request->bonus ?: 0;
        $potongan = $request->potongan ?: 0;
        
        $totalGaji = ($gajiPokok + $tunjangan + $bonus) - $potongan;

        DB::beginTransaction();
        try {
            DB::table('penggajian')->updateOrInsert(
                [
                    'user_id'       => $request->user_id,
                    'periode_bulan' => $request->periode_bulan,
                    'periode_tahun' => $request->periode_tahun,
                ],
                [
                    'gaji_pokok'          => $gajiPokok,
                    'tunjangan'           => $tunjangan,
                    'bonus'               => $bonus,
                    'potongan'            => $potongan,
                    'keterangan_potongan' => $request->keterangan_potongan,
                    'total_gaji'          => $totalGaji,
                    'tanggal_cair'        => now(),
                    'updated_at'          => now()
                ]
            );

            DB::commit();
            return redirect()->back()->with('success', 'Gaji karyawan berhasil diproses dan disimpan.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withErrors('Gagal menyimpan data gaji: ' . $e->getMessage());
        }
    }

    public function cetakSlip($id)
    {
        $gaji = DB::table('penggajian')
            ->join('users', 'penggajian.user_id', '=', 'users.id')
            ->select('penggajian.*', 'users.nama', 'users.role')
            ->where('penggajian.id', $id)
            ->first();

        if (!$gaji) {
            return redirect()->back()->withErrors('Data slip gaji tidak ditemukan.');
        }

        $pengaturan = DB::table('pengaturan_toko')->first();

        return view('superadmin.gaji.slip', compact('gaji', 'pengaturan'));
    }
}