<?php
    $filename = "Rekap_Buku_Pelanggan_" . date('Y-m-d') . ".xls";
    header("Content-Type: application/vnd-ms-excel");
    header("Content-Disposition: attachment; filename=\"$filename\"");
    header("Cache-Control: max-age=0");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Rekap Buku Pelanggan</title>
    <style>
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #000000; padding: 5px; text-align: left; }
        th { background-color: #d9d9d9; font-weight: bold; text-align: center; }
        .title { font-size: 16px; font-weight: bold; text-align: center; }
        .right { text-align: right; }
    </style>
</head>
<body>

    <table>
        <tr>
            <td colspan="7" class="title">{{ strtoupper($namaToko) }} - REKAP BUKU PELANGGAN & PIUTANG</td>
        </tr>
        <tr>
            <td colspan="7" class="title">Tanggal Unduh: {{ date('d M Y, H:i') }}</td>
        </tr>
        <tr>
            <td colspan="7"></td>
        </tr>
        <tr>
            <th>No</th>
            <th>Nama Pelanggan / Toko</th>
            <th>Jumlah Transaksi</th>
            <th>Total Belanja (Gross) (Rp)</th>
            <th>Total Uang Masuk / Terbayar (Rp)</th>
            <th>Total Sisa Piutang (Rp)</th>
            <th>Status Akun</th>
        </tr>
        
        @php 
            $no = 1; 
            $totalGross = 0;
            $totalMasuk = 0;
        @endphp
        
        @foreach($pelanggan as $row)
        @php
            $totalGross += $row->total_belanja;
            $totalMasuk += $row->total_dibayar;
        @endphp
        <tr>
            <td style="text-align: center;">{{ $no++ }}</td>
            <td style="font-weight: bold;">{{ strtoupper($row->nama ?? 'UMUM') }}</td>
            <td style="text-align: center;">{{ $row->total_transaksi }}</td>
            <td class="right">{{ $row->total_belanja }}</td>
            <td class="right">{{ $row->total_dibayar }}</td>
            <td class="right" style="{{ $row->total_piutang > 0 ? 'color: red; font-weight: bold;' : '' }}">{{ $row->total_piutang }}</td>
            <td style="text-align: center;">{{ $row->total_piutang > 0 ? 'PIUTANG' : 'AMAN' }}</td>
        </tr>
        @endforeach
        
        <tr>
            <td colspan="3" style="text-align: right; font-weight: bold;">TOTAL KESELURUHAN GLOBAL</td>
            <td class="right" style="font-weight: bold;">{{ $totalGross }}</td>
            <td class="right" style="font-weight: bold; color: green;">{{ $totalMasuk }}</td>
            <td class="right" style="font-weight: bold; color: red;">{{ $totalPiutangGlobal }}</td>
            <td></td>
        </tr>
    </table>

</body>
</html>