@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')

@if(isset($isPlatformAdmin) && $isPlatformAdmin)
<!-- ======================================================== -->
<!-- BANNER MODE ASISTENSI SUPERADMIN UTAMA PLATFORM -->
<!-- ======================================================== -->
<div class="mb-6 rounded-2xl bg-gradient-to-r from-slate-900 via-indigo-950 to-slate-900 p-5 text-white shadow-xl border border-indigo-900/40 flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
    <div class="flex items-center space-x-3.5">
        <div class="w-11 h-11 rounded-xl bg-amber-500/20 border border-amber-500/40 flex items-center justify-center text-amber-400 text-xl font-bold shadow-md">
            <i class="fa-solid fa-crown"></i>
        </div>
        <div>
            <div class="flex items-center space-x-2">
                <span class="text-xs font-bold uppercase tracking-wider text-amber-400">Mode Asistensi Superadmin Utama</span>
                <span class="bg-indigo-500/20 text-indigo-300 text-[10px] font-bold px-2 py-0.5 rounded border border-indigo-500/30">
                    {{ strtoupper($activeToko->paket ?? 'PRO') }} PLAN
                </span>
            </div>
            <p class="text-sm font-bold text-white mt-0.5">
                Mengelola Operasional: <span class="text-amber-300">{{ $activeToko->nama_toko ?? 'VxPOS Pusat' }}</span>
            </p>
        </div>
    </div>

    <div class="flex items-center space-x-3 w-full md:w-auto justify-end">
        <a href="{{ route('platform.dashboard') }}" class="inline-flex items-center gap-2 bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-600 hover:to-amber-700 text-slate-950 font-bold py-2.5 px-4 rounded-xl text-xs shadow-lg shadow-amber-500/20 transition">
            <i class="fa-solid fa-arrow-left"></i>
            <span>Kembali ke Master Portal Platform</span>
        </a>
    </div>
</div>
@endif

<!-- [PERBAIKAN] Mengubah Div menjadi Tag <a> agar berfungsi sebagai Shortcut interaktif -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
    
    <a href="{{ route('superadmin.laporan.index') }}" class="block bg-white p-5 rounded-xl shadow-[0_4px_18px_0_rgba(75,70,92,0.1)] hover:-translate-y-1.5 hover:shadow-[0_10px_25px_0_rgba(115,103,240,0.2)] transition-all duration-300 cursor-pointer group">
        <div class="flex justify-between items-start mb-4">
            <div class="w-10 h-10 rounded-lg bg-[#7367f0]/10 flex items-center justify-center text-[#7367f0] group-hover:bg-[#7367f0] group-hover:text-white transition-colors">
                <i class="fa-solid fa-wallet text-xl"></i>
            </div>
            <div class="text-gray-300 group-hover:text-[#7367f0]"><i class="fa-solid fa-arrow-up-right-from-square"></i></div>
        </div>
        <h4 class="text-[#a8aaae] text-[15px] font-medium mb-1 group-hover:text-gray-600 transition-colors">Total Omzet (Bulan Ini)</h4>
        <div class="flex items-center gap-3">
            <h3 class="text-2xl font-bold text-[#4b465c]">Rp {{ number_format($totalOmzet, 0, ',', '.') }}</h3>
        </div>
    </a>
    
    <a href="{{ route('superadmin.transaksi.index') }}" class="block bg-white p-5 rounded-xl shadow-[0_4px_18px_0_rgba(75,70,92,0.1)] hover:-translate-y-1.5 hover:shadow-[0_10px_25px_0_rgba(16,185,129,0.2)] transition-all duration-300 cursor-pointer group">
        <div class="flex justify-between items-start mb-4">
            <div class="w-10 h-10 rounded-lg bg-emerald-50 flex items-center justify-center text-emerald-500 group-hover:bg-emerald-500 group-hover:text-white transition-colors">
                <i class="fa-solid fa-cart-shopping text-xl"></i>
            </div>
            <div class="text-gray-300 group-hover:text-emerald-500"><i class="fa-solid fa-arrow-up-right-from-square"></i></div>
        </div>
        <h4 class="text-[#a8aaae] text-[15px] font-medium mb-1 group-hover:text-gray-600 transition-colors">Penjualan Sales</h4>
        <div class="flex items-center gap-3">
            <h3 class="text-2xl font-bold text-[#4b465c]">{{ number_format($penjualanSales, 0, ',', '.') }} <span class="text-sm font-normal text-gray-400">Invoice</span></h3>
        </div>
    </a>

    <a href="{{ route('superadmin.gudang.index') }}" class="block bg-white p-5 rounded-xl shadow-[0_4px_18px_0_rgba(75,70,92,0.1)] hover:-translate-y-1.5 hover:shadow-[0_10px_25px_0_rgba(239,68,68,0.2)] transition-all duration-300 cursor-pointer group">
        <div class="flex justify-between items-start mb-4">
            <div class="w-10 h-10 rounded-lg bg-red-50 flex items-center justify-center text-red-500 group-hover:bg-red-500 group-hover:text-white transition-colors">
                <i class="fa-solid fa-boxes-stacked text-xl"></i>
            </div>
            <div class="text-gray-300 group-hover:text-red-500"><i class="fa-solid fa-arrow-up-right-from-square"></i></div>
        </div>
        <h4 class="text-[#a8aaae] text-[15px] font-medium mb-1 group-hover:text-gray-600 transition-colors">Stok Rendah</h4>
        <div class="flex items-center gap-3">
            <h3 class="text-2xl font-bold text-[#4b465c]">{{ $stokRendah }} <span class="text-sm font-normal text-gray-400">Item</span></h3>
            @if($stokRendah > 0)
                <span class="bg-red-100 text-red-600 px-2 py-0.5 rounded text-xs font-semibold">Warning</span>
            @else
                <span class="bg-emerald-100 text-emerald-600 px-2 py-0.5 rounded text-xs font-semibold">Aman</span>
            @endif
        </div>
    </a>

    <a href="#" class="block bg-white p-5 rounded-xl shadow-[0_4px_18px_0_rgba(75,70,92,0.1)] hover:-translate-y-1.5 hover:shadow-[0_10px_25px_0_rgba(6,182,212,0.2)] transition-all duration-300 cursor-pointer group">
        <div class="flex justify-between items-start mb-4">
            <div class="w-10 h-10 rounded-lg bg-cyan-50 flex items-center justify-center text-cyan-500 group-hover:bg-cyan-500 group-hover:text-white transition-colors">
                <i class="fa-solid fa-user-tag text-xl"></i>
            </div>
            <div class="text-gray-300 group-hover:text-cyan-500"><i class="fa-solid fa-arrow-up-right-from-square"></i></div>
        </div>
        <h4 class="text-[#a8aaae] text-[15px] font-medium mb-1 group-hover:text-gray-600 transition-colors">Bonus Bulan Ini</h4>
        <div class="flex items-center gap-3">
            <h3 class="text-2xl font-bold text-[#4b465c]">Rp {{ number_format($bonusBulanIni, 0, ',', '.') }}</h3>
        </div>
    </a>
</div>

<!-- [BARU] Layout Grid Diperbarui Menjadi 3 Kolom untuk menampung Donut Chart -->
<div class="grid grid-cols-1 xl:grid-cols-3 gap-6 mb-6">
    
    <!-- Grafik Bar 7 Hari Terakhir -->
    <div class="xl:col-span-2 bg-white p-6 rounded-xl shadow-[0_4px_18px_0_rgba(75,70,92,0.1)]">
        <div class="flex justify-between items-start mb-4">
            <div>
                <h3 class="font-bold text-[#4b465c] text-lg">Grafik Pendapatan</h3>
                <p class="text-[#a8aaae] text-sm">Ringkasan pendapatan 7 hari terakhir</p>
            </div>
            <button class="text-gray-400 hover:text-gray-600"><i class="fa-solid fa-ellipsis-vertical"></i></button>
        </div>
        <div id="revenueChart" class="mt-2"></div>
    </div>

    <!-- [BARU] Grafik Donat Piutang vs Uang Masuk -->
    <div class="xl:col-span-1 bg-white p-6 rounded-xl shadow-[0_4px_18px_0_rgba(75,70,92,0.1)] flex flex-col">
        <div class="flex justify-between items-start mb-2">
            <div>
                <h3 class="font-bold text-[#4b465c] text-lg">Arus Kas & Piutang</h3>
                <p class="text-[#a8aaae] text-sm">Rasio uang masuk vs sisa tagihan</p>
            </div>
            <a href="{{ route('superadmin.pelanggan.index') }}" class="text-[#7367f0] hover:text-[#5e50ee]" title="Buku Pelanggan"><i class="fa-solid fa-arrow-up-right-from-square"></i></a>
        </div>
        <div class="flex-grow flex items-center justify-center">
            <div id="cashFlowChart" class="w-full flex justify-center"></div>
        </div>
        <div class="mt-4 grid grid-cols-2 gap-4 border-t border-gray-100 pt-4">
            <div>
                <p class="text-xs text-gray-400 font-bold uppercase mb-1">Uang Masuk</p>
                <p class="text-sm font-bold text-emerald-500">Rp {{ number_format($kasTerbayar, 0, ',', '.') }}</p>
            </div>
            <div class="text-right">
                <p class="text-xs text-gray-400 font-bold uppercase mb-1">Total Piutang</p>
                <p class="text-sm font-bold text-red-500">Rp {{ number_format($kasPiutang, 0, ',', '.') }}</p>
            </div>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <div class="bg-white p-6 rounded-xl shadow-[0_4px_18px_0_rgba(75,70,92,0.1)]">
        <div class="flex justify-between items-start mb-6">
            <div>
                <h3 class="font-bold text-[#4b465c] text-lg">Produk Terlaris</h3>
                <p class="text-[#a8aaae] text-sm">Penjualan Bulan Ini</p>
            </div>
            <button class="text-gray-400 hover:text-gray-600"><i class="fa-solid fa-ellipsis-vertical"></i></button>
        </div>
        
        <div class="space-y-6">
            @forelse($produkTerlaris as $produk)
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <div class="w-10 h-10 rounded bg-[#f8f7fa] flex items-center justify-center text-[#7367f0]">
                        <i class="fa-solid fa-box-open"></i>
                    </div>
                    <div>
                        <h6 class="text-[15px] font-bold text-[#4b465c] mb-0.5">{{ $produk->nama_barang }}</h6>
                        <p class="text-[13px] text-[#a8aaae]">{{ $produk->kategori }}</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <span class="text-[15px] font-bold text-[#4b465c]">{{ number_format($produk->total_terjual, 0, ',', '.') }}</span>
                </div>
            </div>
            @empty
            <div class="text-center py-8">
                <i class="fa-solid fa-box-open text-gray-200 text-4xl mb-3"></i>
                <p class="text-sm text-gray-400 font-medium">Belum ada data penjualan bulan ini.</p>
            </div>
            @endforelse
        </div>
    </div>
</div>

<script>
    // ----------------------------------------------------
    // 1. Inisialisasi Grafik Batang (Pendapatan 7 Hari)
    // ----------------------------------------------------
    var grafikPendapatan = {!! json_encode($grafikPendapatan) !!};
    var grafikTanggal = {!! json_encode($grafikTanggal) !!};

    var barOptions = {
        series: [{
            name: 'Pendapatan',
            data: grafikPendapatan
        }],
        chart: {
            height: 310,
            type: 'bar',
            toolbar: { show: false },
            fontFamily: 'Public Sans, sans-serif'
        },
        plotOptions: {
            bar: {
                borderRadius: 5,
                columnWidth: '35%',
                distributed: true
            }
        },
        dataLabels: { enabled: false },
        colors: ['#f2f2f3', '#f2f2f3', '#f2f2f3', '#f2f2f3', '#f2f2f3', '#f2f2f3', '#7367f0'],
        xaxis: {
            categories: grafikTanggal,
            axisBorder: { show: false },
            axisTicks: { show: false },
            labels: { style: { colors: '#a8aaae', fontSize: '13px' } }
        },
        yaxis: {
            labels: {
                style: { colors: '#a8aaae', fontSize: '13px' },
                formatter: function (val) { 
                    if(val >= 1000000) return (val / 1000000).toFixed(1) + "M";
                    if(val >= 1000) return (val / 1000).toFixed(0) + "k";
                    return val;
                }
            }
        },
        grid: {
            borderColor: '#f1f1f2',
            strokeDashArray: 4,
            yaxis: { lines: { show: true } },
            xaxis: { lines: { show: false } },
            padding: { top: 0, right: 0, bottom: 0, left: 10 }
        },
        legend: { show: false },
        tooltip: {
            theme: 'light',
            y: { 
                formatter: function (val) { 
                    return "Rp " + val.toLocaleString('id-ID');
                } 
            }
        }
    };

    var barChart = new ApexCharts(document.querySelector("#revenueChart"), barOptions);
    barChart.render();

    // ----------------------------------------------------
    // [BARU] 2. Inisialisasi Grafik Donat (Arus Kas)
    // ----------------------------------------------------
    var kasTerbayar = {{ (int) $kasTerbayar }};
    var kasPiutang = {{ (int) $kasPiutang }};
    
    // Cegah error tampilan jika database masih benar-benar kosong
    if(kasTerbayar === 0 && kasPiutang === 0) {
        kasTerbayar = 1; // Dummy data agar donat tergambar abu-abu
        var donutColors = ['#f8f7fa', '#f8f7fa'];
    } else {
        var donutColors = ['#10b981', '#ef4444']; // Hijau (Masuk) & Merah (Piutang)
    }

    var donutOptions = {
        series: [kasTerbayar, kasPiutang],
        labels: ['Uang Masuk', 'Sisa Piutang'],
        chart: {
            type: 'donut',
            height: 280,
            fontFamily: 'Public Sans, sans-serif'
        },
        colors: donutColors,
        plotOptions: {
            pie: {
                donut: {
                    size: '75%',
                    labels: {
                        show: true,
                        name: { fontSize: '12px', color: '#a8aaae' },
                        value: {
                            fontSize: '18px',
                            fontWeight: 700,
                            color: '#4b465c',
                            formatter: function (val) {
                                // Sembunyikan value dummy jika kosong
                                if (kasTerbayar === 1 && kasPiutang === 0) return "Rp 0";
                                if(val >= 1000000) return (val / 1000000).toFixed(1) + " Jt";
                                return "Rp " + val.toLocaleString('id-ID');
                            }
                        },
                        total: {
                            show: true,
                            label: 'Total Transaksi',
                            color: '#a8aaae',
                            formatter: function (w) {
                                let total = kasTerbayar + kasPiutang;
                                if (kasTerbayar === 1 && kasPiutang === 0) return "Rp 0";
                                if(total >= 1000000) return (total / 1000000).toFixed(1) + " Jt";
                                return "Rp " + total.toLocaleString('id-ID');
                            }
                        }
                    }
                }
            }
        },
        dataLabels: { enabled: false },
        stroke: { width: 5, colors: ['#ffffff'] },
        legend: { show: false },
        tooltip: {
            theme: 'light',
            y: {
                formatter: function(val) {
                    if (kasTerbayar === 1 && kasPiutang === 0) return "Rp 0";
                    return "Rp " + val.toLocaleString('id-ID');
                }
            }
        }
    };

    var donutChart = new ApexCharts(document.querySelector("#cashFlowChart"), donutOptions);
    donutChart.render();
</script>
@endsection