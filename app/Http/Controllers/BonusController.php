<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Services\TenantManager;

class BonusController extends Controller
{
    public function index(Request $request)
    {
        $tokoId = TenantManager::getTokoId();

        // 1. Ambil daftar sales toko aktif
        $salesList = DB::table('users')
            ->where('toko_id', $tokoId)
            ->where(function($q) use ($tokoId) {
                $q->where('role', 'sales')
                  ->orWhereIn('id', function($sub) use ($tokoId) {
                      $sub->select('sales_id')->from('transaksi')->where('toko_id', $tokoId)->whereNotNull('sales_id');
                  });
            })
            ->get();

        // Tarik semua transaksi lunas toko ini sekaligus
        $semuaTxLunas = DB::table('transaksi')
            ->where('toko_id', $tokoId)
            ->where('piutang', '<=', 0)
            ->get()
            ->groupBy('sales_id');

        // Tarik semua detail transaksi lunas toko ini
        $allTxIds = DB::table('transaksi')
            ->where('toko_id', $tokoId)
            ->where('piutang', '<=', 0)
            ->pluck('id')
            ->toArray();

        $allDetails = DB::table('detail_transaksi')
            ->join('barang', 'detail_transaksi.barang_id', '=', 'barang.id')
            ->leftJoin('harga', 'barang.id', '=', 'harga.barang_id')
            ->whereIn('detail_transaksi.transaksi_id', $allTxIds)
            ->select('detail_transaksi.transaksi_id', 'detail_transaksi.jumlah', 'detail_transaksi.harga_modal as modal_historis', 'harga.harga_modal as modal_sekarang')
            ->get()
            ->groupBy('transaksi_id');

        $semuaBonusCair = DB::table('pencairan_bonus')
            ->where('toko_id', $tokoId)
            ->select('sales_id', DB::raw('SUM(total_bonus) as total_cair'))
            ->groupBy('sales_id')
            ->pluck('total_cair', 'sales_id')
            ->toArray();

        foreach ($salesList as $sales) {
            $txSales = $semuaTxLunas[$sales->id] ?? collect();
            $omzetLunas = $txSales->sum('total_transaksi');

            $modalTotal = 0;
            foreach ($txSales as $tx) {
                $details = $allDetails[$tx->id] ?? collect();
                foreach ($details as $dt) {
                    $modalItem = ($dt->modal_historis > 0) ? (float)$dt->modal_historis : (float)$dt->modal_sekarang;
                    $modalTotal += ($dt->jumlah * $modalItem);
                }
            }
            $labaLunas = $omzetLunas - $modalTotal;
            $bonusCair = $semuaBonusCair[$sales->id] ?? 0;

            $sales->omzet_lunas = $omzetLunas;
            $sales->laba_lunas = $labaLunas;
            $sales->bonus_cair = $bonusCair;
        }

        // 5. Ambil riwayat pencairan bonus toko aktif
        $riwayatBonus = DB::table('pencairan_bonus')
            ->leftJoin('users', 'pencairan_bonus.sales_id', '=', 'users.id')
            ->select('pencairan_bonus.*', 'users.nama as nama_sales')
            ->where('pencairan_bonus.toko_id', $tokoId)
            ->orderBy('pencairan_bonus.created_at', 'desc')
            ->paginate(10);

        return view('superadmin.bonus.index', compact('salesList', 'riwayatBonus'));
    }

    public function store(Request $request)
    {
        $tokoId = TenantManager::getTokoId();

        $request->validate([
            'sales_id'    => 'required|integer',
            'total_bonus' => 'required|numeric|min:1',
            'keterangan'  => 'required|string|max:255'
        ]);

        DB::beginTransaction();
        try {
            DB::table('pencairan_bonus')->insert([
                'toko_id'     => $tokoId,
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