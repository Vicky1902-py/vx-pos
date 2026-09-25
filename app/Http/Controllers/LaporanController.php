<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class LaporanController extends Controller
{
    public function index(Request $request)
    {
        $startDate = $request->get('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', Carbon::now()->endOfMonth()->format('Y-m-d'));
        $status = $request->get('status', 'semua'); 

        $query = DB::table('transaksi')
            ->leftJoin('users as sales', 'transaksi.sales_id', '=', 'sales.id')
            ->select('transaksi.*', 'sales.nama as nama_sales')
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

        // [BARU] Looping untuk kalkulasi Kas Riil dan Profit Margin per transaksi
        foreach ($laporan as $row) {
            $totalOmzet += $row->total_transaksi;
            $totalPiutang += $row->piutang;
            $totalKas += $row->dp;

            // Tarik harga modal dari setiap barang di transaksi ini
            $details = DB::table('detail_transaksi')
                ->join('barang', 'detail_transaksi.barang_id', '=', 'barang.id')
                ->leftJoin('harga', 'barang.id', '=', 'harga.barang_id')
                ->where('detail_transaksi.transaksi_id', $row->id)
                ->select('detail_transaksi.jumlah', 'harga.harga_modal')
                ->get();
            
            $modalTx = 0;
            foreach($details as $dt) {
                $modalTx += ($dt->jumlah * (float)$dt->harga_modal);
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

        $query = DB::table('transaksi')
            ->leftJoin('users as sales', 'transaksi.sales_id', '=', 'sales.id')
            ->select('transaksi.*', 'sales.nama as nama_sales')
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

        foreach ($laporan as $row) {
            $totalOmzet += $row->total_transaksi;
            $totalPiutang += $row->piutang;
            $totalKas += $row->dp;

            // [BARU] Tarik detail nama barang dan jumlahnya khusus untuk format Excel
            $details = DB::table('detail_transaksi')
                ->join('barang', 'detail_transaksi.barang_id', '=', 'barang.id')
                ->leftJoin('harga', 'barang.id', '=', 'harga.barang_id')
                ->where('detail_transaksi.transaksi_id', $row->id)
                ->select('barang.nama_barang', 'barang.satuan', 'detail_transaksi.jumlah', 'harga.harga_modal')
                ->get();
            
            $modalTx = 0;
            $arrDetail = [];
            foreach($details as $dt) {
                $modalTx += ($dt->jumlah * (float)$dt->harga_modal);
                $arrDetail[] = $dt->nama_barang . ' (' . $dt->jumlah . ' ' . strtoupper($dt->satuan) . ')';
            }
            
            $row->laba = $row->total_transaksi - $modalTx;
            $row->detail_barang = implode(', ', $arrDetail); // Rangkai teks rincian
            $totalLaba += $row->laba;
        }
        
        $pengaturan = DB::table('pengaturan_toko')->first();
        $namaToko = $pengaturan->nama_toko ?? 'Chanada AutoParts';

        return view('superadmin.laporan.excel', compact(
            'laporan', 'startDate', 'endDate', 'status', 
            'totalOmzet', 'totalPiutang', 'totalKas', 'totalLaba', 'namaToko'
        ));
    }
}