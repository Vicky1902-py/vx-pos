@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')

@if(isset($isPlatformAdmin) && $isPlatformAdmin)
<!-- ======================================================== -->
<!-- PUSAT KENDALI SAAS: PLATFORM SUPERADMIN MASTER CONTROL -->
<!-- ======================================================== -->
<div class="mb-8 rounded-2xl bg-gradient-to-r from-slate-900 via-indigo-950 to-slate-900 p-6 text-white shadow-xl border border-indigo-900/40 relative overflow-hidden">
    <div class="absolute -right-10 -bottom-10 w-64 h-64 bg-indigo-500/10 rounded-full blur-3xl pointer-events-none"></div>

    <div class="relative z-10 flex flex-col lg:flex-row justify-between items-start lg:items-center gap-6 pb-6 border-b border-white/10">
        <div>
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-indigo-500/20 text-indigo-300 text-xs font-bold uppercase tracking-wider mb-2 border border-indigo-500/30">
                <i class="fa-solid fa-crown text-amber-400"></i> Platform Super Admin Master Control
            </div>
            <h2 class="text-2xl lg:text-3xl font-black text-white tracking-tight">Pusat Kendali Eksekutif Multi-Store VxPOS</h2>
            <p class="text-slate-300 text-sm mt-1">
                Anda sedang memantau: <strong class="text-amber-400">{{ $activeToko->nama_toko ?? 'VxPOS' }}</strong> 
                <span class="text-xs text-slate-400">({{ $activeToko->paket ?? 'pro' }} plan)</span>
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <!-- Dropdown Switch Toko Cepat -->
            <div class="relative inline-block text-left">
                <select onchange="if(this.value) window.location.href=this.value" class="bg-white/10 border border-white/20 text-white rounded-xl px-4 py-2.5 text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-indigo-400 cursor-pointer">
                    <option value="" class="text-gray-900 font-bold">--- Beralih Pantau Toko ---</option>
                    @if(isset($daftarSemuaToko))
                        @foreach($daftarSemuaToko as $dt)
                            <option value="{{ route('superadmin.toko.switch', $dt->id) }}" class="text-gray-900 font-medium" {{ ($activeToko && $activeToko->id == $dt->id) ? 'selected' : '' }}>
                                {{ $dt->nama_toko }} ({{ strtoupper($dt->paket) }})
                            </option>
                        @endforeach
                    @endif
                </select>
            </div>

            <!-- Tombol Kelola Semua Toko -->
            <a href="{{ route('superadmin.toko.index') }}" class="inline-flex items-center gap-2 bg-gradient-to-r from-indigo-500 to-purple-600 hover:from-indigo-600 hover:to-purple-700 text-white font-bold py-2.5 px-5 rounded-xl text-sm shadow-lg shadow-indigo-500/30 hover:scale-[1.02] transition-all">
                <i class="fa-solid fa-store"></i>
                <span>Kelola Semua Toko & Langganan</span>
                <i class="fa-solid fa-arrow-right text-xs"></i>
            </a>
        </div>
    </div>

    <!-- Ringkasan Eksekutif Global SaaS -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mt-6 pt-2">
        <div class="bg-white/5 border border-white/10 rounded-xl p-4">
            <span class="text-xs text-slate-400 block mb-1">Total Toko Terdaftar</span>
            <div class="text-2xl font-black text-white flex items-center gap-2">
                <i class="fa-solid fa-shop text-indigo-400 text-lg"></i>
                {{ $totalSemuaToko ?? 0 }}
            </div>
            <span class="text-[11px] text-slate-400 mt-1 block">Seluruh Tenant Sistem</span>
        </div>

        <div class="bg-white/5 border border-white/10 rounded-xl p-4">
            <span class="text-xs text-slate-400 block mb-1">Toko Aktif Berlangganan</span>
            <div class="text-2xl font-black text-emerald-400 flex items-center gap-2">
                <i class="fa-solid fa-circle-check text-lg"></i>
                {{ $totalTokoAktif ?? 0 }}
            </div>
            <span class="text-[11px] text-emerald-400/80 mt-1 block">Status Normal / Beroperasi</span>
        </div>

        <div class="bg-white/5 border border-white/10 rounded-xl p-4">
            <span class="text-xs text-slate-400 block mb-1">Omzet Global (Semua Toko)</span>
            <div class="text-2xl font-black text-amber-400 flex items-center gap-2">
                <i class="fa-solid fa-sack-dollar text-lg"></i>
                Rp {{ number_format($omzetGlobalSaaS ?? 0, 0, ',', '.') }}
            </div>
            <span class="text-[11px] text-amber-300/80 mt-1 block">Total Transaksi Konsolidasi</span>
        </div>

        <div class="bg-white/5 border border-white/10 rounded-xl p-4">
            <span class="text-xs text-slate-400 block mb-1">Hak Akses Anda</span>
            <div class="text-xl font-black text-purple-300 flex items-center gap-2">
                <i class="fa-solid fa-shield-halved text-lg"></i>
                Super Admin
            </div>
            <span class="text-[11px] text-purple-200/80 mt-1 block">Full Control Multi-Tenant</span>
        </div>
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