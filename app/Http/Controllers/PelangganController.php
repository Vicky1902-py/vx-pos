<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\TenantManager;

class PelangganController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->get('search');
        $tokoId = TenantManager::getTokoId();

        $pelanggan = DB::table('transaksi')
            ->select(
                'nama_pelanggan as nama',
                DB::raw('COUNT(id) as total_transaksi'),
                DB::raw('SUM(total_transaksi) as total_belanja'),
                DB::raw('SUM(dp) as total_dibayar'),
                DB::raw('SUM(piutang) as total_piutang')
            )
            ->where('transaksi.toko_id', $tokoId)
            ->when($search, function($query, $search) {
                return $query->where('nama_pelanggan', 'like', "%{$search}%");
            })
            ->groupBy('nama_pelanggan')
            ->orderBy('total_piutang', 'desc')
            ->paginate(15);

        $ringkasan = DB::table('transaksi')
            ->where('transaksi.toko_id', $tokoId)
            ->select(
                DB::raw('COUNT(DISTINCT nama_pelanggan) as total_pelanggan'),
                DB::raw('SUM(piutang) as total_piutang_global')
            )
            ->first();

        return view('superadmin.pelanggan.index', compact('pelanggan', 'ringkasan'));
    }

    // [BARU] Menampilkan detail kartu piutang pelanggan beserta riwayatnya
    public function show($nama)
    {
        $tokoId = TenantManager::getTokoId();
        $namaPelanggan = urldecode($nama);
        $namaAsliDb = ($namaPelanggan == 'UMUM') ? 'Umum' : $namaPelanggan;

        $transaksi = DB::table('transaksi')
            ->where('nama_pelanggan', $namaAsliDb)
            ->where('toko_id', $tokoId)
            ->orderBy('created_at', 'desc')
            ->get();

        // Ambil riwayat cicilan untuk semua transaksi milik pelanggan ini
        $transaksiIds = $transaksi->pluck('id');
        $riwayat = DB::table('riwayat_cicilan')
            ->where('toko_id', $tokoId)
            ->whereIn('transaksi_id', $transaksiIds)
            ->orderBy('tanggal_bayar', 'asc')
            ->get()
            ->groupBy('transaksi_id');

        $ringkasan = [
            'total_belanja' => $transaksi->sum('total_transaksi'),
            'total_dibayar' => $transaksi->sum('dp'),
            'total_piutang' => $transaksi->sum('piutang'),
        ];

        return view('superadmin.pelanggan.show', compact('transaksi', 'riwayat', 'namaPelanggan', 'ringkasan'));
    }

    // [BARU] Mengunduh Rekapitulasi Pelanggan ke Excel
    public function exportExcel()
    {
        $tokoId = TenantManager::getTokoId();

        $pelanggan = DB::table('transaksi')
            ->select(
                'nama_pelanggan as nama',
                DB::raw('COUNT(id) as total_transaksi'),
                DB::raw('SUM(total_transaksi) as total_belanja'),
                DB::raw('SUM(dp) as total_dibayar'),
                DB::raw('SUM(piutang) as total_piutang')
            )
            ->where('transaksi.toko_id', $tokoId)
            ->groupBy('nama_pelanggan')
            ->orderBy('total_piutang', 'desc')
            ->get();

        $totalPiutangGlobal = $pelanggan->sum('total_piutang');
        $pengaturan = DB::table('pengaturan_toko')->where('toko_id', $tokoId)->first() 
            ?? DB::table('pengaturan_toko')->first();
        $namaToko = $pengaturan->nama_toko ?? 'VxPOS';

        return view('superadmin.pelanggan.excel', compact('pelanggan', 'totalPiutangGlobal', 'namaToko'));
    }
}