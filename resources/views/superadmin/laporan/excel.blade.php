<?php
    $filename = "Laporan_Penjualan_Komprehensif_" . $startDate . "_sd_" . $endDate . ".xls";
    header("Content-Type: application/vnd-ms-excel");
    header("Content-Disposition: attachment; filename=\"$filename\"");
    header("Cache-Control: max-age=0");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Laporan Penjualan Excel</title>
    <style>
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #000000; padding: 5px; text-align: left; vertical-align: top; }
        th { background-color: #d9d9d9; font-weight: bold; text-align: center; }
        .title { font-size: 16px; font-weight: bold; text-align: center; }
        .right { text-align: right; }
        .center { text-align: center; }
    </style>
</head>
<body>

    <table>
        <tr>
            <td colspan="10" class="title">{{ strtoupper($namaToko) }} - LAPORAN PENJUALAN & LABA</td>
        </tr>
        <tr>
            <td colspan="10" class="title">Periode: {{ date('d M Y', strtotime($startDate)) }} s/d {{ date('d M Y', strtotime($endDate)) }}</td>
        </tr>
        <tr>
            <td colspan="10" class="title">Filter Status: {{ strtoupper($status) }}</td>
        </tr>
        <tr>
            <td colspan="10"></td>
        </tr>
        <tr>
            <th>No</th>
            <th>No. Invoice</th>
            <th>Tanggal Transaksi</th>
            <th>Nama Pelanggan</th>
            <th>Nama Sales</th>
            <th>Rincian Produk Terjual</th>
            <th>Omzet / Transaksi (Rp)</th>
            <th>Est. Laba Profit (Rp)</th>
            <th>Uang Masuk / Kas (Rp)</th>
            <th>Sisa Piutang (Rp)</th>
            <th>Status</th>
        </tr>
        
        @php $no = 1; @endphp
        @foreach($laporan as $row)
        <tr>
            <td class="center">{{ $no++ }}</td>
            <td>{{ $row->no_invoice }}</td>
            <td class="center">{{ \Carbon\Carbon::parse($row->created_at)->format('Y-m-d H:i') }}</td>
            <td>{{ strtoupper($row->nama_pelanggan ?? 'UMUM') }}</td>
            <td class="center">{{ explode(' ', $row->nama_sales)[0] }}</td>
            <!-- [BARU] Menampilkan detail rangkaian string item -->
            <td>{{ $row->detail_barang }}</td> 
            <td class="right">{{ $row->total_transaksi }}</td>
            <td class="right">{{ $row->laba }}</td>
            <td class="right">{{ $row->dp }}</td>
            <td class="right" style="{{ $row->piutang > 0 ? 'color: red; font-weight: bold;' : '' }}">{{ $row->piutang }}</td>
            <td class="center">{{ $row->piutang > 0 ? 'PIUTANG' : 'LUNAS' }}</td>
        </tr>
        @endforeach
        
        <tr>
            <td colspan="6" style="text-align: right; font-weight: bold;">TOTAL KESELURUHAN GLOBAL</td>
            <td class="right" style="font-weight: bold;">{{ $totalOmzet }}</td>
            <td class="right" style="font-weight: bold; color: #0284c7;">{{ $totalLaba }}</td>
            <td class="right" style="font-weight: bold; color: green;">{{ $totalKas }}</td>
            <td class="right" style="font-weight: bold; color: red;">{{ $totalPiutang }}</td>
            <td></td>
        </tr>
    </table>

</body>
</html>