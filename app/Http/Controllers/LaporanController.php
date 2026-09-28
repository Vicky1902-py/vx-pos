<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Services\TenantManager;

class LaporanController extends Controller
{
    /**
     * Halaman Utama Pusat Laporan (3 Kartu Utama: Keuangan, Stok, Penggajian & Bonus)
     */
    public function index(Request $request)
    {
        $tab = $request->get('tab', 'keuangan');
        $tokoId = TenantManager::getTokoId();

        // =========================================================
        // 1. DATA KARTU PERTAMA: LAPORAN KEUANGAN & PENJUALAN
        // =========================================================
        $startDate = $request->get('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', Carbon::now()->endOfMonth()->format('Y-m-d'));
        $status = $request->get('status', 'semua'); 

        $queryKeuangan = DB::table('transaksi')
            ->leftJoin('users as sales', 'transaksi.sales_id', '=', 'sales.id')
            ->select('transaksi.*', 'sales.nama as nama_sales')
            ->where('transaksi.toko_id', $tokoId)
            ->whereBetween(DB::raw('DATE(transaksi.created_at)'), [$startDate, $endDate]);

        if ($status == 'lunas') {
            $queryKeuangan->where('transaksi.piutang', '<=', 0);
        } elseif ($status == 'piutang') {
            $queryKeuangan->where('transaksi.piutang', '>', 0);
        }

        $laporanKeuangan = $queryKeuangan->orderBy('transaksi.created_at', 'desc')->get();

        $totalOmzet = 0;
        $totalPiutang = 0;
        $totalKas = 0;
        $totalLaba = 0;

        // Optimasi: Tarik semua detail transaksi sekaligus (Mencegah N+1 Query)
        $transaksiIds = $laporanKeuangan->pluck('id')->toArray();
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

        foreach ($laporanKeuangan as $row) {
            $totalOmzet += (float)$row->total_transaksi;
            $totalPiutang += (float)$row->piutang;
            $totalKas += (float)$row->dp;

            $details = $allDetails[$row->id] ?? collect();
            $modalTx = 0;
            foreach ($details as $dt) {
                $modalItem = ($dt->modal_historis > 0) ? (float)$dt->modal_historis : (float)$dt->modal_sekarang;
                $modalTx += ($dt->jumlah * $modalItem);
            }
            
            $row->laba = (float)$row->total_transaksi - $modalTx;
            $totalLaba += $row->laba;
        }

        // =========================================================
        // 2. DATA KARTU KEDUA: LAPORAN STOK & NILAI ASET
        // =========================================================
        $filterStok = $request->get('filter_stok', 'semua');
        $searchStok = $request->get('search_stok', '');

        $stokRaw = DB::table('barang')
            ->leftJoin('stok', 'barang.id', '=', 'stok.barang_id')
            ->leftJoin('harga', 'barang.id', '=', 'harga.barang_id')
            ->where('barang.toko_id', $tokoId)
            ->select(
                'barang.id',
                'barang.nama_barang',
                'barang.kode_barang',
                'barang.kategori',
                'barang.satuan',
                DB::raw('COALESCE(stok.stok_tersedia, 0) as stok_tersedia'),
                DB::raw('COALESCE(stok.stok_minimum, 0) as stok_minimum'),
                DB::raw('COALESCE(harga.harga_modal, 0) as harga_modal'),
                DB::raw('COALESCE(harga.harga_jual, 0) as harga_jual')
            )
            ->orderBy('barang.nama_barang', 'asc')
            ->get();

        $totalJenisBarang = $stokRaw->count();
        $totalStokFisik = 0;
        $totalNilaiModalStok = 0;
        $totalNilaiJualStok = 0;
        $stokMenipisCount = 0;
        $stokHabisCount = 0;

        foreach ($stokRaw as $s) {
            $totalStokFisik += (int)$s->stok_tersedia;
            $modalItem = (float)$s->harga_modal;
            $jualItem = (float)$s->harga_jual;
            $qty = (int)$s->stok_tersedia;

            $s->nilai_modal_total = $qty * $modalItem;
            $s->nilai_jual_total = $qty * $jualItem;
            $s->potensi_laba = $s->nilai_jual_total - $s->nilai_modal_total;

            $totalNilaiModalStok += $s->nilai_modal_total;
            $totalNilaiJualStok += $s->nilai_jual_total;

            if ($qty <= 0) {
                $stokHabisCount++;
                $s->status_stok = 'habis';
            } elseif ($qty <= (int)$s->stok_minimum) {
                $stokMenipisCount++;
                $s->status_stok = 'menipis';
            } else {
                $s->status_stok = 'aman';
            }
        }

        $totalPotensiLabaStok = $totalNilaiJualStok - $totalNilaiModalStok;

        // Filter daftar barang berdasarkan status & pencarian
        $laporanStok = $stokRaw->filter(function($item) use ($filterStok, $searchStok) {
            if ($filterStok !== 'semua' && $item->status_stok !== $filterStok) {
                return false;
            }
            if (!empty($searchStok)) {
                $needle = strtolower($searchStok);
                $matchNama = str_contains(strtolower($item->nama_barang), $needle);
                $matchKode = str_contains(strtolower($item->kode_barang ?? ''), $needle);
                return $matchNama || $matchKode;
            }
            return true;
        });

        // =========================================================
        // 3. DATA KARTU KETIGA: LAPORAN PENGGAJIAN DAN BONUS
        // =========================================================
        $periodeBulan = $request->get('periode_bulan', Carbon::now()->format('m'));
        $periodeTahun = $request->get('periode_tahun', Carbon::now()->format('Y'));

        $karyawanList = DB::table('users')
            ->where('toko_id', $tokoId)
            ->where('role', '!=', 'superadmin')
            ->select('id', 'nama', 'username', 'role')
            ->orderBy('nama', 'asc')
            ->get();

        $gajiSaved = DB::table('penggajian')
            ->where('toko_id', $tokoId)
            ->where('periode_bulan', $periodeBulan)
            ->where('periode_tahun', $periodeTahun)
            ->get()
            ->keyBy('user_id');

        $bulanInt = (int)$periodeBulan;
        $periodeFormats = [
            "$periodeBulan/$periodeTahun",
            "$bulanInt/$periodeTahun",
            "$periodeTahun-$periodeBulan",
            date('F Y', mktime(0, 0, 0, $bulanInt, 10, (int)$periodeTahun)),
        ];

        $bonusList = DB::table('pencairan_bonus')
            ->where(function($q) use ($tokoId) {
                $q->where('toko_id', $tokoId)->orWhereNull('toko_id');
            })
            ->where(function($q) use ($bulanInt, $periodeTahun, $periodeFormats) {
                $q->whereIn('periode', $periodeFormats)
                  ->orWhere(function($sub) use ($bulanInt, $periodeTahun) {
                      $sub->whereMonth('created_at', $bulanInt)
                          ->whereYear('created_at', (int)$periodeTahun);
                  });
            })
            ->select('sales_id', DB::raw('SUM(total_bonus) as total_bonus'))
            ->groupBy('sales_id')
            ->pluck('total_bonus', 'sales_id');

        $laporanGaji = [];
        $totalGapok = 0;
        $totalTunjangan = 0;
        $totalBonusPayroll = 0;
        $totalPotongan = 0;
        $totalBebanPayroll = 0;
        $sudahDibayarCount = 0;

        foreach ($karyawanList as $k) {
            $gaji = $gajiSaved[$k->id] ?? null;
            $bonusCair = (float)($bonusList[$k->id] ?? 0);

            $rowGapok = $gaji ? (float)$gaji->gaji_pokok : 0;
            $rowTunjangan = $gaji ? (float)$gaji->tunjangan : 0;
            $rowBonus = $gaji ? (float)$gaji->bonus : $bonusCair;
            $rowPotongan = $gaji ? (float)$gaji->potongan : 0;
            $rowNetto = $gaji ? (float)$gaji->total_gaji : max(0, ($rowGapok + $rowTunjangan + $rowBonus) - $rowPotongan);
            $statusBayar = $gaji ? 'dibayar' : 'menunggu';

            if ($gaji) {
                $sudahDibayarCount++;
            }

            $totalGapok += $rowGapok;
            $totalTunjangan += $rowTunjangan;
            $totalBonusPayroll += $rowBonus;
            $totalPotongan += $rowPotongan;
            $totalBebanPayroll += $rowNetto;

            $laporanGaji[] = (object) [
                'user_id'             => $k->id,
                'nama'                => $k->nama,
                'username'            => $k->username,
                'role'                => $k->role,
                'gaji_pokok'          => $rowGapok,
                'tunjangan'           => $rowTunjangan,
                'bonus'               => $rowBonus,
                'bonus_dicairkan'     => $bonusCair,
                'potongan'            => $rowPotongan,
                'keterangan_potongan' => $gaji->keterangan_potongan ?? '-',
                'total_gaji'          => $rowNetto,
                'status'              => $statusBayar,
                'gaji_id'             => $gaji->id ?? null,
                'tanggal_cair'        => $gaji->tanggal_cair ?? null,
            ];
        }

        return view('superadmin.laporan.index', compact(
            'tab',
            // Laporan Keuangan
            'laporanKeuangan', 'startDate', 'endDate', 'status', 
            'totalOmzet', 'totalPiutang', 'totalKas', 'totalLaba',
            // Laporan Stok
            'laporanStok', 'filterStok', 'searchStok',
            'totalJenisBarang', 'totalStokFisik', 'totalNilaiModalStok', 
            'totalNilaiJualStok', 'totalPotensiLabaStok', 'stokMenipisCount', 'stokHabisCount',
            // Laporan Gaji & Bonus
            'laporanGaji', 'periodeBulan', 'periodeTahun',
            'totalGapok', 'totalTunjangan', 'totalBonusPayroll', 'totalPotongan', 
            'totalBebanPayroll', 'sudahDibayarCount'
        ));
    }

    /**
     * Ekspor Laporan ke Format Excel (.xls) Sesuai Tab Aktif
     */
    public function exportExcel(Request $request)
    {
        $tab = $request->get('tab', 'keuangan');
        $tokoId = TenantManager::getTokoId();
        $toko = TenantManager::getActiveToko();
        $namaToko = $toko->nama_toko ?? 'VxPOS';

        if ($tab === 'stok') {
            $filterStok = $request->get('filter_stok', 'semua');
            $searchStok = $request->get('search_stok', '');

            $stokRaw = DB::table('barang')
                ->leftJoin('stok', 'barang.id', '=', 'stok.barang_id')
                ->leftJoin('harga', 'barang.id', '=', 'harga.barang_id')
                ->where('barang.toko_id', $tokoId)
                ->select(
                    'barang.id',
                    'barang.nama_barang',
                    'barang.kode_barang',
                    'barang.kategori',
                    'barang.satuan',
                    DB::raw('COALESCE(stok.stok_tersedia, 0) as stok_tersedia'),
                    DB::raw('COALESCE(stok.stok_minimum, 0) as stok_minimum'),
                    DB::raw('COALESCE(harga.harga_modal, 0) as harga_modal'),
                    DB::raw('COALESCE(harga.harga_jual, 0) as harga_jual')
                )
                ->orderBy('barang.nama_barang', 'asc')
                ->get();

            $totalNilaiModalStok = 0;
            $totalNilaiJualStok = 0;

            foreach ($stokRaw as $s) {
                $qty = (int)$s->stok_tersedia;
                $s->nilai_modal_total = $qty * (float)$s->harga_modal;
                $s->nilai_jual_total = $qty * (float)$s->harga_jual;
                $s->potensi_laba = $s->nilai_jual_total - $s->nilai_modal_total;

                $totalNilaiModalStok += $s->nilai_modal_total;
                $totalNilaiJualStok += $s->nilai_jual_total;

                if ($qty <= 0) {
                    $s->status_stok = 'habis';
                } elseif ($qty <= (int)$s->stok_minimum) {
                    $s->status_stok = 'menipis';
                } else {
                    $s->status_stok = 'aman';
                }
            }

            $laporanStok = $stokRaw->filter(function($item) use ($filterStok, $searchStok) {
                if ($filterStok !== 'semua' && $item->status_stok !== $filterStok) return false;
                if (!empty($searchStok)) {
                    $needle = strtolower($searchStok);
                    return str_contains(strtolower($item->nama_barang), $needle) || str_contains(strtolower($item->kode_barang ?? ''), $needle);
                }
                return true;
            });

            return view('superadmin.laporan.excel', [
                'tab'                 => 'stok',
                'namaToko'            => $namaToko,
                'laporanStok'         => $laporanStok,
                'totalNilaiModalStok' => $totalNilaiModalStok,
                'totalNilaiJualStok'  => $totalNilaiJualStok,
                'filterStok'          => $filterStok,
            ]);
        }

        if ($tab === 'gaji') {
            $periodeBulan = $request->get('periode_bulan', Carbon::now()->format('m'));
            $periodeTahun = $request->get('periode_tahun', Carbon::now()->format('Y'));

            $karyawanList = DB::table('users')
                ->where('toko_id', $tokoId)
                ->where('role', '!=', 'superadmin')
                ->select('id', 'nama', 'username', 'role')
                ->orderBy('nama', 'asc')
                ->get();

            $gajiSaved = DB::table('penggajian')
                ->where('toko_id', $tokoId)
                ->where('periode_bulan', $periodeBulan)
                ->where('periode_tahun', $periodeTahun)
                ->get()
                ->keyBy('user_id');

            $bulanInt = (int)$periodeBulan;
            $periodeFormats = [
                "$periodeBulan/$periodeTahun",
                "$bulanInt/$periodeTahun",
                "$periodeTahun-$periodeBulan",
            ];

            $bonusList = DB::table('pencairan_bonus')
                ->where(function($q) use ($tokoId) {
                    $q->where('toko_id', $tokoId)->orWhereNull('toko_id');
                })
                ->where(function($q) use ($bulanInt, $periodeTahun, $periodeFormats) {
                    $q->whereIn('periode', $periodeFormats)
                      ->orWhere(function($sub) use ($bulanInt, $periodeTahun) {
                          $sub->whereMonth('created_at', $bulanInt)
                              ->whereYear('created_at', (int)$periodeTahun);
                      });
                })
                ->select('sales_id', DB::raw('SUM(total_bonus) as total_bonus'))
                ->groupBy('sales_id')
                ->pluck('total_bonus', 'sales_id');

            $laporanGaji = [];
            $totalBebanPayroll = 0;

            foreach ($karyawanList as $k) {
                $gaji = $gajiSaved[$k->id] ?? null;
                $bonusCair = (float)($bonusList[$k->id] ?? 0);

                $rowGapok = $gaji ? (float)$gaji->gaji_pokok : 0;
                $rowTunjangan = $gaji ? (float)$gaji->tunjangan : 0;
                $rowBonus = $gaji ? (float)$gaji->bonus : $bonusCair;
                $rowPotongan = $gaji ? (float)$gaji->potongan : 0;
                $rowNetto = $gaji ? (float)$gaji->total_gaji : max(0, ($rowGapok + $rowTunjangan + $rowBonus) - $rowPotongan);

                $totalBebanPayroll += $rowNetto;

                $laporanGaji[] = (object) [
                    'nama'                => $k->nama,
                    'role'                => $k->role,
                    'gaji_pokok'          => $rowGapok,
                    'tunjangan'           => $rowTunjangan,
                    'bonus'               => $rowBonus,
                    'potongan'            => $rowPotongan,
                    'keterangan_potongan' => $gaji->keterangan_potongan ?? '-',
                    'total_gaji'          => $rowNetto,
                    'status'              => $gaji ? 'Sudah Dibayar' : 'Menunggu',
                ];
            }

            return view('superadmin.laporan.excel', [
                'tab'               => 'gaji',
                'namaToko'          => $namaToko,
                'laporanGaji'       => $laporanGaji,
                'periodeBulan'      => $periodeBulan,
                'periodeTahun'      => $periodeTahun,
                'totalBebanPayroll' => $totalBebanPayroll,
            ]);
        }

        // Default: Laporan Keuangan
        $startDate = $request->get('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', Carbon::now()->endOfMonth()->format('Y-m-d'));
        $status = $request->get('status', 'semua');

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
            $totalOmzet += (float)$row->total_transaksi;
            $totalPiutang += (float)$row->piutang;
            $totalKas += (float)$row->dp;

            $details = $allDetails[$row->id] ?? collect();
            $modalTx = 0;
            $arrDetail = [];
            foreach ($details as $dt) {
                $modalItem = ($dt->modal_historis > 0) ? (float)$dt->modal_historis : (float)$dt->modal_sekarang;
                $modalTx += ($dt->jumlah * $modalItem);
                $arrDetail[] = $dt->nama_barang . ' (' . $dt->jumlah . ' ' . strtoupper($dt->satuan) . ')';
            }
            
            $row->laba = (float)$row->total_transaksi - $modalTx;
            $row->detail_barang = implode(', ', $arrDetail);
            $totalLaba += $row->laba;
        }

        return view('superadmin.laporan.excel', [
            'tab'          => 'keuangan',
            'laporan'      => $laporan,
            'startDate'    => $startDate,
            'endDate'      => $endDate,
            'status'       => $status,
            'totalOmzet'   => $totalOmzet,
            'totalPiutang' => $totalPiutang,
            'totalKas'     => $totalKas,
            'totalLaba'    => $totalLaba,
            'namaToko'     => $namaToko,
        ]);
    }
}