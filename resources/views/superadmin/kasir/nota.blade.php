<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>INVOICE - {{ $transaksi->no_invoice }}</title>
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; background: #e2e8f0; margin: 0; padding: 20px; color: #333; font-size: 11px; }
        .page-half-f4 { background: #fff; width: 215mm; min-height: 165mm; margin: 0 auto; padding: 25px 30px; box-sizing: border-box; box-shadow: 0 4px 10px rgba(0,0,0,0.1); }
        
        .header { display: flex; justify-content: space-between; border-bottom: 2px solid #000; padding-bottom: 10px; margin-bottom: 15px; }
        .header-logo { width: 40%; display: flex; align-items: center; }
        .header-logo img { max-width: 120px; max-height: 60px; }
        .header-logo h1 { margin: 0; color: #1e3a8a; font-size: 18px; line-height: 1; } 
        .header-text { width: 60%; text-align: right; line-height: 1.4; }
        .header-text strong { font-size: 14px; color: #000; display: block; margin-bottom: 2px; }
        
        .info-grid { display: flex; justify-content: space-between; margin-bottom: 15px; }
        .kepada-box { width: 50%; border: 1px solid #ccc; padding: 10px; border-radius: 4px; line-height: 1.4; }
        .kepada-box strong { font-size: 12px; display: block; margin-bottom: 5px; border-bottom: 1px solid #eee; padding-bottom: 3px;}
        
        .invoice-box { width: 45%; }
        .invoice-title { font-size: 18px; font-weight: bold; color: #000; letter-spacing: 1px; text-align: right; margin: 0 0 5px 0; }
        .invoice-meta { width: 100%; border-collapse: collapse; }
        .invoice-meta td { padding: 2px 0; }
        .invoice-meta td:first-child { font-weight: bold; width: 80px; }
        
        .table-items { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
        .table-items th, .table-items td { border: 1px solid #000; padding: 6px 8px; }
        .table-items th { background: #f1f5f9; font-weight: bold; text-align: center; }
        .table-items td.center { text-align: center; }
        .table-items td.right { text-align: right; }
        
        .history-table { width: 100%; border-collapse: collapse; margin-bottom: 10px; font-size: 11px; }
        .history-table th, .history-table td { border: 1px dashed #ccc; padding: 6px 8px; }
        .history-table th { background: #f8fafc; font-weight: bold; text-align: left; }
        
        .summary-box { display: flex; justify-content: space-between; align-items: flex-start; }
        .terbilang-box { width: 55%; background: #f8fafc; border: 1px solid #cbd5e1; padding: 8px; font-style: italic; font-weight: bold; }
        .totals-table { width: 40%; border-collapse: collapse; }
        .totals-table th, .totals-table td { padding: 4px; text-align: right; border: 1px solid #000; }
        .totals-table th { background: #f1f5f9; width: 50%; }
        
        .footer-grid { display: flex; justify-content: space-between; margin-top: 15px; }
        .payment-info { width: 50%; line-height: 1.4; }
        .payment-info strong { display: block; margin-bottom: 3px; text-decoration: underline; }
        
        .signatures { width: 45%; display: flex; justify-content: space-between; text-align: center; }
        .sign-box { width: 45%; }
        .sign-space { height: 40px; }
        .sign-name { border-top: 1px solid #000; padding-top: 3px; font-weight: bold; }

        .btn-print { display: block; width: 200px; margin: 20px auto; padding: 10px; background: #000; color: #fff; text-align: center; text-decoration: none; border-radius: 5px; font-weight: bold; cursor: pointer; border: none; }

        /* [BARU] Class pemecah halaman saat dicetak */
        .page-break { page-break-before: always; break-before: page; margin-top: 20px; }

        @media print {
            @page { size: portrait; margin: 0; }
            body { background: #fff; padding: 0; margin: 0; }
            .page-half-f4 { box-shadow: none; width: 100%; padding: 15mm; }
            .page-break { margin-top: 0; } /* Reset margin di tampilan cetak */
            .btn-print { display: none; }
            .terbilang-box, .history-table th { -webkit-print-color-adjust: exact; background-color: #f8fafc !important; }
            .table-items th, .totals-table th { -webkit-print-color-adjust: exact; background-color: #f1f5f9 !important; }
        }
    </style>
</head>
<body>

<!-- HALAMAN 1: INVOICE UTAMA -->
<div class="page-half-f4">
    <div class="header">
        <div class="header-logo">
            @if(isset($pengaturan->logo) && $pengaturan->logo)
                <img src="{{ asset('uploads/logo/' . $pengaturan->logo) }}" alt="Logo">
            @else
                <h1>VXPOS<br>POINT OF SALE</h1>
            @endif
        </div>
        <div class="header-text">
            <strong>{{ $pengaturan->nama_toko ?? 'VxPOS' }}</strong>
            Alamat: {{ $pengaturan->alamat ?? '-' }}<br>
            Telp: {{ $pengaturan->telepon ?? '-' }} | Email: {{ $pengaturan->email ?? '-' }}
        </div>
    </div>

    <div class="info-grid">
        <div class="kepada-box">
            <strong>Kepada Yth.</strong>
            {{ strtoupper($transaksi->nama_pelanggan ?? 'PELANGGAN UMUM') }}<br>
            Kode Sales: {{ strtoupper(explode(' ', $transaksi->nama_sales)[0]) }}
        </div>
        <div class="invoice-box">
            <h2 class="invoice-title">INVOICE</h2>
            <table class="invoice-meta">
                <tr><td>No. Invoice</td><td>: <strong>{{ $transaksi->no_invoice }}</strong></td></tr>
                <tr><td>Tanggal</td><td>: {{ \Carbon\Carbon::parse($transaksi->created_at)->translatedFormat('d/m/Y') }}</td></tr>
                <tr><td>Status</td><td>: {{ $transaksi->piutang > 0 ? 'BON / PIUTANG' : 'LUNAS' }}</td></tr>
            </table>
        </div>
    </div>

    <table class="table-items">
        <thead>
            <tr>
                <th>Nama Produk</th>
                <th>Qty</th>
                <th>Sat</th>
                <th class="right">Harga</th>
                <th class="right">Jumlah</th>
            </tr>
        </thead>
        <tbody>
            @foreach($detail as $dt)
            @php
                // Kalkulasi untuk tampilan harga final dan persentase jika ada diskon
                $hargaFinal = $dt->harga_jual - ($dt->diskon_item ?? 0);
                $persenDiskon = ($dt->diskon_item > 0 && $dt->harga_jual > 0) ? round(($dt->diskon_item / $dt->harga_jual) * 100) : 0;
            @endphp
            <tr>
                <td>
                    {{ $dt->nama_barang }}
                    @if(isset($dt->diskon_item) && $dt->diskon_item > 0)
                        <br><span style="font-size: 9px; color: #ef4444;">(Disc: {{ $persenDiskon }}%)</span>
                    @endif
                </td>
                <td class="center">{{ $dt->jumlah }}</td>
                <td class="center">{{ strtoupper($dt->satuan ?? 'PCS') }}</td>
                <td class="right">
                    @if(isset($dt->diskon_item) && $dt->diskon_item > 0)
                        <del style="font-size: 9px; color: #94a3b8;">{{ number_format($dt->harga_jual, 0, ',', '.') }}</del><br>
                    @endif
                    {{ number_format($hargaFinal, 0, ',', '.') }}
                </td>
                <td class="right">{{ number_format($dt->subtotal, 0, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="summary-box">
        <div class="terbilang-box">
            Terbilang: <br><span>{{ $teksTerbilang }}</span>
        </div>
        <table class="totals-table">
            @if(isset($transaksi->diskon) && $transaksi->diskon > 0)
            <tr>
                <th>TOTAL DISKON</th>
                <td style="color: red; font-weight:bold;">- Rp {{ number_format($transaksi->diskon, 0, ',', '.') }}</td>
            </tr>
            @endif
            <tr>
                <th>TOTAL TAGIHAN</th>
                <td style="font-weight:bold; font-size: 13px;">Rp {{ number_format($transaksi->total_transaksi, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <th>TOTAL DIBAYAR</th>
                <td>Rp {{ number_format($transaksi->dp, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <th>SISA PIUTANG</th>
                <td style="color: {{ $transaksi->piutang > 0 ? 'red' : 'black' }}; font-weight:bold;">
                    Rp {{ number_format($transaksi->piutang, 0, ',', '.') }}
                </td>
            </tr>
        </table>
    </div>

    <div class="footer-grid">
        <div class="payment-info">
            <strong>Metode Pembayaran:</strong>
            Bank: {{ $pengaturan->bank ?? '-' }} | No. Rek: {{ $pengaturan->no_rekening ?? '-' }}<br>
            A/N: {{ $pengaturan->atas_nama ?? '-' }}<br>
            <em>* {!! nl2br(e($pengaturan->keterangan ?? 'Terima kasih')) !!}</em>
        </div>
        
        <div class="signatures">
            <div class="sign-box">
                <div>Hormat Kami,</div>
                <div class="sign-space"></div>
                <div class="sign-name">({{ $pengaturan->nama_toko ?? 'Toko' }})</div>
            </div>
            <div class="sign-box">
                <div>Diterima Oleh,</div>
                <div class="sign-space"></div>
                <div class="sign-name">( ........................ )</div>
            </div>
        </div>
    </div>
</div>

@php
    // Logika Pintar: Halaman Lampiran hanya muncul jika ada Piutang ATAU jumlah riwayat bayar lebih dari 1 kali
    $tampilLampiran = isset($riwayatCicilan) && count($riwayatCicilan) > 0 && ($transaksi->piutang > 0 || count($riwayatCicilan) > 1);
@endphp

@if($tampilLampiran)
<!-- HALAMAN 2: LAMPIRAN RIWAYAT CICILAN -->
<div class="page-half-f4 page-break">
    <div class="header" style="border-bottom: 2px solid #ccc; padding-bottom: 5px; margin-bottom: 15px;">
        <div class="header-text" style="width: 100%; text-align: left;">
            <strong style="font-size: 16px;">LAMPIRAN INVOICE: {{ $transaksi->no_invoice }}</strong>
            Pelanggan: {{ strtoupper($transaksi->nama_pelanggan ?? 'PELANGGAN UMUM') }}
        </div>
    </div>
    
    <div>
        <strong style="font-size: 12px; display: block; margin-bottom: 8px;">Detail Riwayat Pembayaran Masuk:</strong>
        <table class="history-table">
            <thead>
                <tr>
                    <th style="width: 25%;">Tgl Bayar</th>
                    <th style="width: 50%;">Keterangan</th>
                    <th style="width: 25%; text-align: right;">Nominal (Rp)</th>
                </tr>
            </thead>
            <tbody>
                @foreach($riwayatCicilan as $rc)
                <tr>
                    <td>{{ \Carbon\Carbon::parse($rc->tanggal_bayar)->translatedFormat('d M Y - H:i') }}</td>
                    <td>{{ $rc->keterangan }}</td>
                    <td style="text-align: right; font-weight: bold;">{{ number_format($rc->nominal_bayar, 0, ',', '.') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

<div style="margin: 15px auto; text-align: center; font-size: 10px; color: #888;">
    VxPOS Point of Sale &bull; Hak Cipta by. Vicky Koroh
</div>

<button class="btn-print" onclick="window.print()">CETAK NOTA</button>

<script>
    window.onload = function() { window.print(); }
</script>
</body>
</html>