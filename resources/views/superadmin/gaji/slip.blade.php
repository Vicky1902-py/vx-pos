@php
    $logoPath = ($pengaturan && $pengaturan->logo) ? asset('uploads/logo/' . $pengaturan->logo) : null;
    $namaToko = $pengaturan->nama_toko ?? 'VxPOS';
    $alamatToko = $pengaturan->alamat ?? 'Alamat Belum Diatur';
    $telpToko = $pengaturan->telepon ?? '-';
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Slip Gaji - {{ $gaji->nama }} - {{ $gaji->periode_bulan }}/{{ $gaji->periode_tahun }}</title>
    <style>
        body { font-family: 'Courier New', Courier, monospace; color: #000; padding: 20px; font-size: 14px; line-height: 1.5; }
        .container { max-width: 600px; margin: 0 auto; border: 1px dashed #000; padding: 20px; }
        .header { text-align: center; border-bottom: 2px dashed #000; padding-bottom: 15px; margin-bottom: 15px; }
        .header h2 { margin: 0; font-size: 22px; text-transform: uppercase; }
        .header p { margin: 3px 0; font-size: 12px; }
        .title { text-align: center; font-weight: bold; margin: 15px 0; font-size: 16px; text-decoration: underline; }
        .info-table { width: 100%; margin-bottom: 20px; }
        .info-table td { padding: 3px 0; font-weight: bold; }
        .calc-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .calc-table th, .calc-table td { padding: 6px 0; }
        .calc-table th { text-align: left; border-bottom: 1px solid #000; padding-bottom: 5px; }
        .val { text-align: right; }
        .section-title { font-weight: bold; text-decoration: underline; margin-top: 10px; display: block; }
        .total-row td { border-top: 2px solid #000; font-weight: bold; padding-top: 10px; font-size: 16px; }
        .footer { display: flex; justify-content: space-between; margin-top: 40px; text-align: center; }
        .ttd-box { width: 45%; }
        .ttd-space { height: 70px; }
        @media print {
            body { padding: 0; }
            .container { border: none; padding: 0; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>{{ $namaToko }}</h2>
            <p>{{ $alamatToko }}</p>
            <p>Telp: {{ $telpToko }}</p>
        </div>

        <div class="title">SLIP GAJI KARYAWAN</div>

        <table class="info-table">
            <tr>
                <td width="30%">NAMA KARYAWAN</td>
                <td width="5%">:</td>
                <td>{{ strtoupper($gaji->nama) }}</td>
            </tr>
            <tr>
                <td>POSISI / BAGIAN</td>
                <td>:</td>
                <td>{{ strtoupper($gaji->role) }}</td>
            </tr>
            <tr>
                <td>PERIODE BULAN</td>
                <td>:</td>
                <td>{{ $gaji->periode_bulan }} - {{ $gaji->periode_tahun }}</td>
            </tr>
        </table>

        <table class="calc-table">
            <tr><td colspan="2"><span class="section-title">A. PENDAPATAN</span></td></tr>
            <tr>
                <td>Gaji Pokok</td>
                <td class="val">Rp {{ number_format($gaji->gaji_pokok, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td>Tunjangan / Lain-lain</td>
                <td class="val">Rp {{ number_format($gaji->tunjangan, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td>Insentif / Bonus Pencairan</td>
                <td class="val">Rp {{ number_format($gaji->bonus, 0, ',', '.') }}</td>
            </tr>
            @php $totalPendapatan = $gaji->gaji_pokok + $gaji->tunjangan + $gaji->bonus; @endphp
            <tr>
                <td style="padding-left: 20px; font-style: italic;">Subtotal Pendapatan</td>
                <td class="val" style="font-weight:bold;">Rp {{ number_format($totalPendapatan, 0, ',', '.') }}</td>
            </tr>

            <tr><td colspan="2"><br><span class="section-title">B. POTONGAN</span></td></tr>
            <tr>
                <td>Total Potongan <br><small><i>({{ $gaji->keterangan_potongan ?: '-' }})</i></small></td>
                <td class="val">Rp {{ number_format($gaji->potongan, 0, ',', '.') }}</td>
            </tr>

            <tr class="total-row">
                <td>GAJI BERSIH (TAKE HOME PAY)</td>
                <td class="val">Rp {{ number_format($gaji->total_gaji, 0, ',', '.') }}</td>
            </tr>
        </table>

        <p style="font-size: 11px; font-style: italic;">Dicetak otomatis oleh Sistem VxPOS &bull; Hak Cipta by. Vicky Koroh pada: {{ \Carbon\Carbon::now()->format('d-m-Y H:i:s') }}</p>

        <div class="footer">
            <div class="ttd-box">
                <p>Penerima,</p>
                <div class="ttd-space"></div>
                <p>( {{ strtoupper($gaji->nama) }} )</p>
            </div>
            <div class="ttd-box">
                <p>Manajemen/Keuangan,</p>
                <div class="ttd-space"></div>
                <p>( ____________________ )</p>
            </div>
        </div>
        
        <div class="no-print" style="margin-top: 30px; text-align: center;">
            <button onclick="window.print()" style="padding: 10px 20px; font-size: 16px; background-color: #7367f0; color: white; border: none; cursor: pointer; border-radius: 5px;">Cetak Dokumen Ini</button>
            <p style="font-family: sans-serif; font-size: 12px; margin-top: 10px;">Tekan Ctrl + P (Atau CMD + P di Mac) untuk mencetak langsung.</p>
        </div>
    </div>
</body>
</html>