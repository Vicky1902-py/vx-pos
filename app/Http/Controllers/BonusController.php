<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class BonusController extends Controller
{
    public function index(Request $request)
    {
        // 1. Ambil daftar user yang memiliki peran 'sales' atau pernah mencatat transaksi
        $salesList = DB::table('users')
            ->where('role', 'sales')
            ->orWhereIn('id', function($query) {
                $query->select('sales_id')->from('transaksi')->whereNotNull('sales_id');
            })
            ->get();

        foreach ($salesList as $sales) {
            // 2. Hitung Total Omzet khusus yang LUNAS
            $transaksiLunas = DB::table('transaksi')
                ->where('sales_id', $sales->id)
                ->where('piutang', '<=', 0) // Hanya transaksi lunas
                ->get();

            $omzetLunas = $transaksiLunas->sum('total_transaksi');

            // 3. Hitung Total Laba (Profit) khusus yang LUNAS
            $modalTotal = 0;
            foreach ($transaksiLunas as $tx) {
                $details = DB::table('detail_transaksi')
                    ->join('barang', 'detail_transaksi.barang_id', '=', 'barang.id')
                    ->leftJoin('harga', 'barang.id', '=', 'harga.barang_id')
                    ->where('detail_transaksi.transaksi_id', $tx->id)
                    ->select('detail_transaksi.jumlah', 'harga.harga_modal')
                    ->get();
                
                foreach($details as $dt) {
                    $modalTotal += ($dt->jumlah * (float)$dt->harga_modal);
                }
            }
            $labaLunas = $omzetLunas - $modalTotal;

            // 4. Hitung Total Bonus yang sudah pernah diberikan/dicairkan ke Sales ini
            $bonusCair = DB::table('pencairan_bonus')
                ->where('sales_id', $sales->id)
                ->sum('total_bonus');

            // Sisipkan hasil kalkulasi ke dalam objek untuk dikirim ke view
            $sales->omzet_lunas = $omzetLunas;
            $sales->laba_lunas = $labaLunas;
            $sales->bonus_cair = $bonusCair;
        }

        // 5. Ambil riwayat tabel pencairan bonus untuk ditampilkan di bawah
        $riwayatBonus = DB::table('pencairan_bonus')
            ->leftJoin('users', 'pencairan_bonus.sales_id', '=', 'users.id')
            ->select('pencairan_bonus.*', 'users.nama as nama_sales')
            ->orderBy('pencairan_bonus.created_at', 'desc')
            ->paginate(10);

        return view('superadmin.bonus.index', compact('salesList', 'riwayatBonus'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'sales_id'    => 'required|integer',
            'total_bonus' => 'required|numeric|min:1',
            'keterangan'  => 'required|string|max:255'
        ]);

        DB::beginTransaction();
        try {
            DB::table('pencairan_bonus')->insert([
                'sales_id'    => $request->sales_id,
                'total_bonus' => $request->total_bonus,
                'keterangan'  => $request->keterangan,
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);

            DB::commit();
            return redirect()->back()->with('success', 'Bonus berhasil dicairkan dan dicatat ke dalam sistem!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withErrors('Gagal mencatat bonus: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        DB::table('pencairan_bonus')->where('id', $id)->delete();
        return redirect()->back()->with('success', 'Riwayat pencairan bonus berhasil dibatalkan/dihapus.');
    }
}