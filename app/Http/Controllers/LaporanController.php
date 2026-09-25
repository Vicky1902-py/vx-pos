<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Services\TenantManager;

class LaporanController extends Controller
{
    public function index(Request $request)
    {
        $startDate = $request->get('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', Carbon::now()->endOfMonth()->format('Y-m-d'));
        $status = $request->get('status', 'semua'); 
        $tokoId = TenantManager::getTokoId();

        $query = DB::table('transaksi')
            ->leftJoin('users as sales', 'transaksi.sales_id', '=', 'sales.id')
            ->select('transaksi.*', 'sales.nama as nama_sales')
            ->where('transaksi.toko_id', $tokoId)
            ->whereBetween(DB::raw('DATE(transaksi.created_at)'), [$startDate, $endDate]);

        if ($status == 'lunas') {
            $query->where('transaksi.piutang', '<=', 0);
        } elseif ($status == 'piutang') {
            $query->where('transaksi.piutang', '>', 0);
        }

        $laporan = $query->orderBy('transaksi.created_at', 'desc')->get();

        $totalOmzet = 0;
        $totalPiutang = 0;
        $totalKas = 0;
        $totalLaba = 0;

        // Optimasi: Tarik semua detail transaksi sekaligus (Mencegah N+1 Query)
        $transaksiIds = $laporan->pluck('id')->toArray();
        $allDetails = DB::table('detail_transaksi')
            ->join('barang', 'detail_transaksi.barang_id', '=', 'barang.id')
            ->leftJoin('harga', 'barang.id', '=', 'harga.barang_id')
            ->whereIn('detail_transaksi.transaksi_id', $transaksiIds)
            ->select(
                'detail_transaksi.transaksi_id', 
                'detail_transaksi.jumlah', 
                'detail_transaksi.harga_modal as modal_historis', 
                'harga.harga_modal as modal_sekarang'
            )
            ->get()
            ->groupBy('transaksi_id');

        foreach ($laporan as $row) {
            $totalOmzet += $row->total_transaksi;
            $totalPiutang += $row->piutang;
            $totalKas += $row->dp;

            $details = $allDetails[$row->id] ?? collect();
            $modalTx = 0;
            foreach ($details as $dt) {
                // Gunakan modal historis jika ada, fallback ke modal sekarang
                $modalItem = ($dt->modal_historis > 0) ? (float)$dt->modal_historis : (float)$dt->modal_sekarang;
                $modalTx += ($dt->jumlah * $modalItem);
            }
            
            $row->laba = $row->total_transaksi - $modalTx;
            $totalLaba += $row->laba;
        }

        return view('superadmin.laporan.index', compact(
            'laporan', 'startDate', 'endDate', 'status', 
            'totalOmzet', 'totalPiutang', 'totalKas', 'totalLaba'
        ));
    }

    public function exportExcel(Request $request)
    {
        $startDate = $request->get('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', Carbon::now()->endOfMonth()->format('Y-m-d'));
        $status = $request->get('status', 'semua');
        $tokoId = TenantManager::getTokoId();

        $query = DB::table('transaksi')
            ->leftJoin('users as sales', 'transaksi.sales_id', '=', 'sales.id')
            ->select('transaksi.*', 'sales.nama as nama_sales')
            ->where('transaksi.toko_id', $tokoId)
            ->whereBetween(DB::raw('DATE(transaksi.created_at)'), [$startDate, $endDate]);

        if ($status == 'lunas') {
            $query->where('transaksi.piutang', '<=', 0);
        } elseif ($status == 'piutang') {
            $query->where('transaksi.piutang', '>', 0);
        }

        $laporan = $query->orderBy('transaksi.created_at', 'asc')->get();
        
        $totalOmzet = 0;
        $totalPiutang = 0;
        $totalKas = 0;
        $totalLaba = 0;

        $transaksiIds = $laporan->pluck('id')->toArray();
        $allDetails = DB::table('detail_transaksi')
            ->join('barang', 'detail_transaksi.barang_id', '=', 'barang.id')
            ->leftJoin('harga', 'barang.id', '=', 'harga.barang_id')
            ->whereIn('detail_transaksi.transaksi_id', $transaksiIds)
            ->select(
                'detail_transaksi.transaksi_id',
                'barang.nama_barang', 
                'barang.satuan', 
                'detail_transaksi.jumlah', 
                'detail_transaksi.harga_modal as modal_historis',
                'harga.harga_modal as modal_sekarang'
            )
            ->get()
            ->groupBy('transaksi_id');

        foreach ($laporan as $row) {
            $totalOmzet += $row->total_transaksi;
            $totalPiutang += $row->piutang;
            $totalKas += $row->dp;

            $details = $allDetails[$row->id] ?? collect();
            $modalTx = 0;
            $arrDetail = [];
            foreach ($details as $dt) {
                $modalItem = ($dt->modal_historis > 0) ? (float)$dt->modal_historis : (float)$dt->modal_sekarang;
                $modalTx += ($dt->jumlah * $modalItem);
                $arrDetail[] = $dt->nama_barang . ' (' . $dt->jumlah . ' ' . strtoupper($dt->satuan) . ')';
            }
            
            $row->laba = $row->total_transaksi - $modalTx;
            $row->detail_barang = implode(', ', $arrDetail);
            $totalLaba += $row->laba;
        }
        
        $toko = TenantManager::getActiveToko();
        $namaToko = $toko->nama_toko ?? 'Chanada AutoParts';

        return view('superadmin.laporan.excel', compact(
            'laporan', 'startDate', 'endDate', 'status', 
            'totalOmzet', 'totalPiutang', 'totalKas', 'totalLaba', 'namaToko'
        ));
    }
}