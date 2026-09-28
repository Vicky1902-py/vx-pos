<?php
    $tab = $tab ?? 'keuangan';
    if ($tab === 'stok') {
        $filename = "Laporan_Stok_Aset_" . date('Y-m-d') . ".xls";
    } elseif ($tab === 'gaji') {
        $filename = "Laporan_Gaji_Bonus_" . ($periodeBulan ?? date('m')) . "_" . ($periodeTahun ?? date('Y')) . ".xls";
    } else {
        $filename = "Laporan_Keuangan_" . ($startDate ?? date('Y-m-d')) . "_sd_" . ($endDate ?? date('Y-m-d')) . ".xls";
    }
    header("Content-Type: application/vnd-ms-excel");
    header("Content-Disposition: attachment; filename=\"$filename\"");
    header("Cache-Control: max-age=0");
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Excel - {{ strtoupper($tab) }}</title>
    <style>
        table { border-collapse: collapse; width: 100%; font-family: sans-serif; font-size: 12px; }
        th, td { border: 1px solid #000000; padding: 6px; text-align: left; vertical-align: top; }
        th { background-color: #e2e8f0; font-weight: bold; text-align: center; }
        .title { font-size: 15px; font-weight: bold; text-align: center; border: none; padding: 3px; }
        .subtitle { font-size: 11px; text-align: center; border: none; padding: 2px; color: #475569; }
        .right { text-align: right; }
        .center { text-align: center; }
        .bold { font-weight: bold; }
        .bg-total { background-color: #f1f5f9; font-weight: bold; }
    </style>
</head>
<body>

@if($tab === 'stok')
    <table>
        <tr>
            <td colspan="9" class="title">{{ strtoupper($namaToko ?? 'VxPOS') }} - LAPORAN STOK & NILAI ASET INVENTARIS</td>
        </tr>
        <tr>
            <td colspan="9" class="subtitle">Tanggal Cetak: {{ date('d F Y H:i') }} &bull; Filter: {{ strtoupper($filterStok ?? 'SEMUA') }}</td>
        </tr>
        <tr><td colspan="9" style="border:none; height:10px;"></td></tr>
        <tr>
            <th>No</th>
            <th>Kode SKU</th>
            <th>Nama Barang</th>
            <th>Kategori</th>
            <th>Stok Fisik</th>
            <th>Satuan</th>
            <th>Harga Modal (HPP)</th>
            <th>Total Nilai Aset Modal</th>
            <th>Harga Jual Satuan</th>
        </tr>
        @php $no = 1; @endphp
        @foreach($laporanStok as $row)
        <tr>
            <td class="center">{{ $no++ }}</td>
            <td class="center">{{ $row->kode_barang ?: '-' }}</td>
            <td>{{ $row->nama_barang }}</td>
            <td class="center">{{ strtoupper($row->kategori ?: 'UMUM') }}</td>
            <td class="center bold">{{ $row->stok_tersedia }}</td>
            <td class="center">{{ strtoupper($row->satuan) }}</td>
            <td class="right">{{ $row->harga_modal }}</td>
            <td class="right bold">{{ $row->nilai_modal_total }}</td>
            <td class="right">{{ $row->harga_jual }}</td>
        </tr>
        @endforeach
        <tr class="bg-total">
            <td colspan="7" class="right bold">TOTAL KESELURUHAN NILAI ASET MODAL (HPP)</td>
            <td class="right bold">{{ $totalNilaiModalStok }}</td>
            <td></td>
        </tr>
    </table>

@elseif($tab === 'gaji')
    <table>
        <tr>
            <td colspan="8" class="title">{{ strtoupper($namaToko ?? 'VxPOS') }} - REKAPITULASI PENGGAJIAN & BONUS KARYAWAN</td>
        </tr>
        <tr>
            <td colspan="8" class="subtitle">Periode: Bulan {{ $periodeBulan }} / {{ $periodeTahun }} &bull; Dicetak: {{ date('d F Y H:i') }}</td>
        </tr>
        <tr><td colspan="8" style="border:none; height:10px;"></td></tr>
        <tr>
            <th>No</th>
            <th>Nama Karyawan</th>
            <th>Jabatan / Role</th>
            <th>Gaji Pokok (Rp)</th>
            <th>Tunjangan (Rp)</th>
            <th>Bonus Pencairan (Rp)</th>
            <th>Potongan (Rp)</th>
            <th>Gaji Bersih / Take Home Pay (Rp)</th>
        </tr>
        @php $no = 1; @endphp
        @foreach($laporanGaji as $row)
        <tr>
            <td class="center">{{ $no++ }}</td>
            <td class="bold">{{ strtoupper($row->nama) }}</td>
            <td class="center">{{ strtoupper($row->role) }}</td>
            <td class="right">{{ $row->gaji_pokok }}</td>
            <td class="right">{{ $row->tunjangan }}</td>
            <td class="right bold" style="color: #059669;">{{ $row->bonus }}</td>
            <td class="right" style="color: #dc2626;">{{ $row->potongan }}</td>
            <td class="right bold" style="color: #4338ca;">{{ $row->total_gaji }}</td>
        </tr>
        @endforeach
        <tr class="bg-total">
            <td colspan="7" class="right bold">TOTAL PENGELUARAN BEBAN PAYROLL PERIODE INI</td>
            <td class="right bold" style="color: #4338ca;">{{ $totalBebanPayroll }}</td>
        </tr>
    </table>

@else
    <table>
        <tr>
            <td colspan="11" class="title">{{ strtoupper($namaToko ?? 'VxPOS') }} - LAPORAN KEUANGAN & PENJUALAN</td>
        </tr>
        <tr>
            <td colspan="11" class="subtitle">Periode: {{ date('d M Y', strtotime($startDate)) }} s/d {{ date('d M Y', strtotime($endDate)) }} &bull; Filter Status: {{ strtoupper($status) }}</td>
        </tr>
        <tr><td colspan="11" style="border:none; height:10px;"></td></tr>
        <tr>
            <th>No</th>
            <th>No. Invoice</th>
            <th>Tanggal Transaksi</th>
            <th>Nama Pelanggan</th>
            <th>Nama Sales</th>
            <th>Rincian Produk Terjual</th>
            <th>Omzet Transaksi (Rp)</th>
            <th>Est. Laba Profit (Rp)</th>
            <th>Uang Masuk / Kas (Rp)</th>
            <th>Sisa Piutang (Rp)</th>
            <th>Status</th>
        </tr>
        @php $no = 1; @endphp
        @foreach($laporan as $row)
        <tr>
            <td class="center">{{ $no++ }}</td>
            <td class="bold">{{ $row->no_invoice }}</td>
            <td class="center">{{ \Carbon\Carbon::parse($row->created_at)->format('Y-m-d H:i') }}</td>
            <td>{{ strtoupper($row->nama_pelanggan ?? 'UMUM') }}</td>
            <td class="center">{{ explode(' ', $row->nama_sales ?? '-')[0] }}</td>
            <td>{{ $row->detail_barang }}</td> 
            <td class="right bold">{{ $row->total_transaksi }}</td>
            <td class="right bold" style="color: #0284c7;">{{ $row->laba }}</td>
            <td class="right bold" style="color: #059669;">{{ $row->dp }}</td>
            <td class="right bold" style="{{ $row->piutang > 0 ? 'color: #dc2626;' : '' }}">{{ $row->piutang }}</td>
            <td class="center">{{ $row->piutang > 0 ? 'PIUTANG' : 'LUNAS' }}</td>
        </tr>
        @endforeach
        <tr class="bg-total">
            <td colspan="6" class="right bold">TOTAL KESELURUHAN GLOBAL</td>
            <td class="right bold">{{ $totalOmzet }}</td>
            <td class="right bold" style="color: #0284c7;">{{ $totalLaba }}</td>
            <td class="right bold" style="color: #059669;">{{ $totalKas }}</td>
            <td class="right bold" style="color: #dc2626;">{{ $totalPiutang }}</td>
            <td></td>
        </tr>
    </table>
@endif

</body>
</html>