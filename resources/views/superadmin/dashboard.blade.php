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

<!-- ======================================================== -->
<!-- FINNOVA SUB-HEADER: TITLE & ACTIONS -->
<!-- ======================================================== -->
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
    <div class="flex items-center gap-3">
        <a href="{{ url()->previous() }}" class="w-9 h-9 rounded-full bg-white border border-slate-200/80 shadow-sm flex items-center justify-center text-slate-600 hover:text-indigo-600 hover:border-indigo-200 transition-all">
            <i class="fa-solid fa-arrow-left text-xs"></i>
        </a>
        <div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">Ringkasan Bisnis</h1>
            <p class="text-xs text-slate-400 font-medium">Pantau performa penjualan, arus kas kasir, dan inventori toko realtime.</p>
        </div>
    </div>

    <div class="flex items-center gap-2.5">
        <a href="{{ route('superadmin.transaksi.index') }}" class="h-10 px-4 rounded-full bg-white border border-slate-200/80 text-slate-700 hover:bg-slate-50 text-xs font-bold shadow-sm inline-flex items-center gap-2 transition-all">
            <i class="fa-solid fa-sliders text-slate-400"></i>
            <span>Filter Transaksi</span>
        </a>
        <a href="{{ route('superadmin.transaksi.create') }}" class="h-10 px-5 rounded-full bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white text-xs font-bold shadow-lg shadow-indigo-500/25 inline-flex items-center gap-2 transition-all hover:scale-[1.02]">
            <i class="fa-solid fa-plus text-xs"></i>
            <span>Buat Transaksi Baru</span>
        </a>
    </div>
</div>

<!-- ======================================================== -->
<!-- FINNOVA 4 METRIC CARDS ROW -->
<!-- ======================================================== -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 lg:gap-5 mb-6">
    
    <!-- CARD 1: OVERDUE / TOTAL OMZET -->
    <div class="bg-white rounded-3xl p-5 border border-slate-100 shadow-[0_8px_30px_rgb(0,0,0,0.03)] flex flex-col justify-between hover:shadow-md transition-all duration-300 group">
        <div>
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-bold text-slate-500">Total Omzet (Bulan Ini)</span>
                <span class="w-6 h-6 rounded-full bg-rose-50 text-rose-500 flex items-center justify-center text-[10px]">
                    <i class="fa-solid fa-circle-exclamation"></i>
                </span>
            </div>
            <div class="text-2xl font-black text-slate-900 tracking-tight mb-1">
                Rp {{ number_format($totalOmzet, 0, ',', '.') }}
            </div>
            <div class="flex items-center gap-1.5 text-[11px] font-bold text-emerald-600 mb-4">
                <i class="fa-solid fa-arrow-up text-[10px]"></i>
                <span>+12.5% dari bulan lalu</span>
            </div>
        </div>

        <!-- Desk / Store Aesthetic Visual Banner -->
        <div class="h-24 w-full rounded-2xl bg-gradient-to-tr from-slate-100 via-indigo-50/40 to-purple-50/50 border border-slate-100 p-2.5 flex items-center justify-between overflow-hidden relative">
            <div class="z-10">
                <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Target Bulanan</p>
                <p class="text-xs font-extrabold text-indigo-700">Tercapai 84%</p>
                <span class="inline-block mt-1 text-[9px] bg-indigo-100 text-indigo-600 font-bold px-1.5 py-0.5 rounded">Sehat</span>
            </div>
            <div class="w-16 h-16 rounded-xl bg-white/80 shadow-sm border border-slate-200/60 flex items-center justify-center text-indigo-500 text-2xl group-hover:scale-105 transition-transform">
                <i class="fa-solid fa-wallet"></i>
            </div>
        </div>
    </div>

    <!-- CARD 2: DUE WITHIN NEXT MONTH / PENJUALAN SALES (WITH MICRO BAR CHART) -->
    <div class="bg-white rounded-3xl p-5 border border-slate-100 shadow-[0_8px_30px_rgb(0,0,0,0.03)] flex flex-col justify-between hover:shadow-md transition-all duration-300">
        <div>
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-bold text-slate-500">Penjualan Sales & Nota</span>
                <span class="w-6 h-6 rounded-full bg-indigo-50 text-indigo-600 flex items-center justify-center text-[10px]">
                    <i class="fa-regular fa-calendar"></i>
                </span>
            </div>
            <div class="text-2xl font-black text-slate-900 tracking-tight mb-1">
                {{ number_format($penjualanSales, 0, ',', '.') }} <span class="text-sm font-semibold text-slate-400">Invoice</span>
            </div>
            <div class="flex items-center gap-1.5 text-[11px] font-bold text-indigo-600 mb-4">
                <i class="fa-solid fa-arrow-up text-[10px]"></i>
                <span>+8.2% transaksi aktif</span>
            </div>
        </div>

        <!-- Micro Bar Chart (Like FINNOVA Card 2) -->
        <div class="h-24 w-full rounded-2xl bg-slate-50/70 border border-slate-100 px-3 py-2 flex items-end justify-between gap-1.5">
            <div class="flex flex-col items-center flex-1 gap-1">
                <div class="w-full bg-slate-200 rounded-full h-8"></div>
                <span class="text-[9px] font-bold text-slate-400">Min</span>
            </div>
            <div class="flex flex-col items-center flex-1 gap-1">
                <div class="w-full bg-slate-200 rounded-full h-12"></div>
                <span class="text-[9px] font-bold text-slate-400">Sen</span>
            </div>
            <div class="flex flex-col items-center flex-1 gap-1">
                <div class="w-full bg-slate-200 rounded-full h-14"></div>
                <span class="text-[9px] font-bold text-slate-400">Sel</span>
            </div>
            <div class="flex flex-col items-center flex-1 gap-1">
                <div class="w-full bg-slate-200 rounded-full h-10"></div>
                <span class="text-[9px] font-bold text-slate-400">Rab</span>
            </div>
            <div class="flex flex-col items-center flex-1 gap-1">
                <div class="w-full bg-slate-200 rounded-full h-16"></div>
                <span class="text-[9px] font-bold text-slate-400">Kam</span>
            </div>
            <div class="flex flex-col items-center flex-1 gap-1">
                <div class="w-full bg-indigo-500 rounded-full h-20 shadow-sm shadow-indigo-500/40"></div>
                <span class="text-[9px] font-bold text-indigo-600">Hari ini</span>
            </div>
        </div>
    </div>

    <!-- CARD 3: AVERAGE TIME TO GET PAID / STOK KRITIS (WITH SPARKLINE WITH NODES) -->
    <div class="bg-white rounded-3xl p-5 border border-slate-100 shadow-[0_8px_30px_rgb(0,0,0,0.03)] flex flex-col justify-between hover:shadow-md transition-all duration-300">
        <div>
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-bold text-slate-500">Stok Kritis Gudang</span>
                <span class="w-6 h-6 rounded-full bg-cyan-50 text-cyan-600 flex items-center justify-center text-[10px]">
                    <i class="fa-regular fa-clock"></i>
                </span>
            </div>
            <div class="text-2xl font-black text-slate-900 tracking-tight mb-1">
                {{ $stokRendah }} <span class="text-sm font-semibold text-slate-400">Item Kritis</span>
            </div>
            <div class="flex items-center gap-1.5 text-[11px] font-bold {{ $stokRendah > 0 ? 'text-amber-500' : 'text-cyan-600' }} mb-4">
                <i class="fa-solid fa-shield-halved text-[10px]"></i>
                <span>{{ $stokRendah > 0 ? 'Segera lakukan restock gudang' : 'Semua stok dalam batas aman' }}</span>
            </div>
        </div>

        <!-- Micro Sparkline SVG with Nodes (Like FINNOVA Card 3) -->
        <div class="h-24 w-full rounded-2xl bg-cyan-50/30 border border-slate-100 p-2 flex items-center justify-center relative overflow-hidden">
            <svg class="w-full h-16" viewBox="0 0 200 60" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M 5 45 C 30 45, 40 35, 60 30 C 80 25, 95 38, 120 20 C 145 5, 165 25, 195 10" stroke="#6366f1" stroke-width="2.5" stroke-linecap="round" fill="none"/>
                <!-- Dotted nodes -->
                <circle cx="5" cy="45" r="3.5" fill="#ffffff" stroke="#6366f1" stroke-width="2"/>
                <circle cx="60" cy="30" r="3.5" fill="#ffffff" stroke="#6366f1" stroke-width="2"/>
                <circle cx="95" cy="35" r="3.5" fill="#ffffff" stroke="#6366f1" stroke-width="2"/>
                <circle cx="120" cy="20" r="3.5" fill="#ffffff" stroke="#6366f1" stroke-width="2"/>
                <circle cx="160" cy="20" r="3.5" fill="#ffffff" stroke="#6366f1" stroke-width="2"/>
                <circle cx="195" cy="10" r="4.5" fill="#6366f1" stroke="#ffffff" stroke-width="2"/>
            </svg>
        </div>
    </div>

    <!-- CARD 4: AVAILABLE FOR INSTANT PAYOUT / KAS MASUK & PAYMENT CHANNELS -->
    <div class="bg-white rounded-3xl p-5 border border-slate-100 shadow-[0_8px_30px_rgb(0,0,0,0.03)] flex flex-col justify-between hover:shadow-md transition-all duration-300">
        <div>
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-bold text-slate-500">Kas Masuk & Terbayar</span>
                <div class="flex items-center gap-1.5">
                    <span class="w-6 h-6 rounded-full bg-emerald-50 text-emerald-600 flex items-center justify-center text-[10px]">
                        <i class="fa-solid fa-briefcase"></i>
                    </span>
                    <a href="{{ route('superadmin.kasir.index') }}" title="Buka Kasir" class="w-6 h-6 rounded-full bg-slate-50 text-slate-400 hover:text-indigo-600 flex items-center justify-center text-[10px] transition-colors">
                        <i class="fa-solid fa-arrow-up-right-from-square"></i>
                    </a>
                </div>
            </div>
            <div class="text-2xl font-black text-slate-900 tracking-tight mb-1">
                Rp {{ number_format($kasTerbayar, 0, ',', '.') }}
            </div>
            <div class="flex items-center gap-1.5 text-[11px] font-bold text-slate-400 mb-4">
                <span class="bg-emerald-50 text-emerald-600 px-2 py-0.5 rounded-full text-[10px]">Tervalidasi Kasir</span>
            </div>
        </div>

        <!-- Payment Channels & Quick Action (Like FINNOVA Card 4) -->
        <div class="space-y-2">
            <div class="grid grid-cols-3 gap-1.5 text-center text-[9px] font-bold">
                <div class="py-2 px-1 rounded-xl bg-slate-50 border border-slate-100 text-slate-600">
                    <p class="text-[8px] text-slate-400">•••• Tunai</p>
                    <p class="truncate">Cash</p>
                </div>
                <div class="py-2 px-1 rounded-xl bg-gradient-to-r from-indigo-600 to-purple-600 text-white shadow-sm shadow-indigo-500/30">
                    <p class="text-[8px] text-indigo-200">•••• QRIS</p>
                    <p class="truncate">Digital</p>
                </div>
                <div class="py-2 px-1 rounded-xl bg-slate-50 border border-slate-100 text-slate-600">
                    <p class="text-[8px] text-slate-400">•••• Piutang</p>
                    <p class="truncate">Tempo</p>
                </div>
            </div>
            <a href="{{ route('superadmin.kasir.index') }}" class="w-full py-2 rounded-full bg-slate-900 hover:bg-slate-800 text-white text-[11px] font-bold flex items-center justify-center gap-1.5 transition-colors">
                <span>Validasi Kasir</span>
                <i class="fa-solid fa-chevron-right text-[9px]"></i>
            </a>
        </div>
    </div>

</div>

<!-- ======================================================== -->
<!-- FINNOVA ACTIVE FILTERS TOOLBAR -->
<!-- ======================================================== -->
<div class="flex flex-wrap items-center justify-between gap-3 mb-6 bg-white/60 p-2.5 rounded-2xl border border-slate-200/60 shadow-sm">
    <div class="flex flex-wrap items-center gap-2">
        <span class="inline-flex items-center gap-1.5 bg-slate-900 text-white text-xs font-bold px-3 py-1.5 rounded-full">
            <span>Filter Aktif</span>
            <span class="w-4 h-4 rounded-full bg-indigo-500 text-[10px] flex items-center justify-center">2</span>
        </span>

        <div class="relative">
            <button class="inline-flex items-center gap-2 bg-white border border-slate-200 text-slate-700 text-xs font-bold px-3.5 py-1.5 rounded-full hover:bg-slate-50 transition-colors">
                <span>Semua Pelanggan</span>
                <i class="fa-solid fa-chevron-down text-[10px] text-slate-400"></i>
            </button>
        </div>

        <div class="relative">
            <button class="inline-flex items-center gap-2 bg-white border border-slate-200 text-slate-700 text-xs font-bold px-3.5 py-1.5 rounded-full hover:bg-slate-50 transition-colors">
                <span>Status: Selesai</span>
                <i class="fa-solid fa-chevron-down text-[10px] text-slate-400"></i>
            </button>
        </div>

        <span class="inline-flex items-center gap-2 bg-white border border-slate-200 text-slate-700 text-xs font-bold px-3.5 py-1.5 rounded-full">
            <span>{{ date('F Y') }}</span>
            <i class="fa-regular fa-calendar text-[11px] text-slate-400"></i>
        </span>
    </div>

    <!-- Quick Search Pill -->
    <div class="w-full sm:w-auto relative">
        <input type="text" placeholder="Cari invoice atau pelanggan..." class="w-full sm:w-64 pl-9 pr-4 py-1.5 bg-white border border-slate-200 rounded-full text-xs font-medium focus:outline-none focus:ring-2 focus:ring-indigo-500 text-slate-700">
        <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-2.5 text-slate-400 text-xs"></i>
    </div>
</div>

<!-- ======================================================== -->
<!-- FINNOVA DEEP CONTRAST DARK CONTAINER (THE HERO OF FINNOVA) -->
<!-- ======================================================== -->
<div class="bg-[#121422] rounded-3xl p-5 sm:p-6 lg:p-7 text-white shadow-2xl mb-8 border border-slate-800">
    
    <!-- Top Filter Pill Navigation inside Dark Container -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6 pb-4 border-b border-slate-800/80">
        <div class="flex items-center gap-2">
            <h2 class="text-base font-extrabold text-white tracking-tight">Daftar Tagihan & Transaksi</h2>
        </div>

        <div class="flex items-center gap-2 flex-wrap">
            <button class="px-4 py-1.5 rounded-full text-xs font-bold text-slate-400 hover:text-white transition-colors">
                Semua Tagihan
            </button>
            <button class="px-4 py-1.5 rounded-full text-xs font-bold text-slate-400 hover:text-white transition-colors">
                Draft <span class="ml-1 text-[10px] px-1.5 py-0.2 bg-slate-800 rounded-full text-slate-300">3</span>
            </button>
            <button class="px-4 py-1.5 rounded-full text-xs font-bold bg-[#6366f1] text-white shadow-lg shadow-indigo-500/40">
                Belum Lunas <span class="ml-1 text-[10px] px-1.5 py-0.2 bg-white/20 rounded-full text-white">{{ $kasPiutang > 0 ? 'Aktif' : '0' }}</span>
            </button>
            
            <div class="hidden md:flex items-center gap-1.5 ml-2 pl-3 border-l border-slate-800">
                <button class="w-8 h-8 rounded-full bg-slate-800/80 hover:bg-slate-700 text-slate-300 flex items-center justify-center text-xs transition-colors">
                    <i class="fa-solid fa-list"></i>
                </button>
                <button class="w-8 h-8 rounded-full bg-slate-800/80 hover:bg-slate-700 text-slate-300 flex items-center justify-center text-xs transition-colors">
                    <i class="fa-solid fa-ellipsis-vertical"></i>
                </button>
            </div>
        </div>
    </div>

    <!-- Two-Panel Finnova Layout: Left Invoice List + Right Floating Purple Glass Card -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        
        <!-- Left Panel: Recent Invoices List (5 Columns on Desktop) -->
        <div class="lg:col-span-4 space-y-2.5">
            <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-2">Tagihan Belum Lunas / Terkini</p>

            @forelse($transaksiTerbaru as $index => $trx)
            <div class="p-3.5 rounded-2xl transition-all duration-200 cursor-pointer flex items-center justify-between gap-3 {{ $index === 0 ? 'bg-gradient-to-r from-indigo-900/60 to-purple-900/40 border border-indigo-500/40 shadow-md' : 'bg-slate-900/50 hover:bg-slate-800/60 border border-slate-800/60' }}">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-full bg-gradient-to-tr {{ $index % 2 === 0 ? 'from-indigo-500 to-purple-600' : 'from-emerald-500 to-teal-600' }} flex items-center justify-center text-white font-black text-xs shadow-sm">
                        {{ strtoupper(substr($trx->nama_pelanggan ?? 'P', 0, 1)) }}
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="text-xs font-bold text-white tracking-tight"># {{ $trx->no_invoice }}</span>
                        </div>
                        <p class="text-[10px] text-slate-400">{{ $trx->nama_pelanggan ?? 'Pelanggan Umum' }}</p>
                    </div>
                </div>

                <div class="text-right">
                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-full {{ ($trx->status ?? '') === 'selesai' ? 'bg-emerald-500/20 text-emerald-300' : 'bg-amber-500/20 text-amber-300' }}">
                        {{ strtoupper($trx->status ?? 'SELESAI') }}
                    </span>
                    <p class="text-xs font-bold text-white mt-1">Rp {{ number_format($trx->total_transaksi ?? 0, 0, ',', '.') }}</p>
                </div>
            </div>
            @empty
            <div class="text-center py-8 bg-slate-900/40 rounded-2xl border border-slate-800/60">
                <i class="fa-solid fa-receipt text-slate-600 text-3xl mb-2"></i>
                <p class="text-xs text-slate-400">Belum ada transaksi tercatat hari ini.</p>
                <a href="{{ route('superadmin.transaksi.create') }}" class="inline-block mt-3 text-xs font-bold text-indigo-400 hover:text-indigo-300">
                    + Buat Transaksi Pertama
                </a>
            </div>
            @endforelse
        </div>

        <!-- Right Panel: Floating Purple Glass Card (8 Columns on Desktop) -->
        <div class="lg:col-span-8">
            <div class="rounded-3xl bg-gradient-to-br from-[#5448c8] via-[#4d3ebf] to-[#3f31ad] p-6 lg:p-7 shadow-2xl relative overflow-hidden text-white border border-indigo-400/30">
                
                <!-- Ambient Subtle Glows -->
                <div class="absolute -right-10 -bottom-10 w-64 h-64 bg-indigo-400/20 rounded-full blur-3xl pointer-events-none"></div>

                <!-- Top Row: Details & Customer Meta -->
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6 relative z-10">
                    <div>
                        <div class="flex items-center gap-2 mb-1">
                            <span class="text-xs text-indigo-200 font-semibold">Detail Transaksi</span>
                            <span class="text-[10px] uppercase font-bold bg-white/20 px-2 py-0.5 rounded-full text-white">
                                {{ strtoupper($featuredInvoice->status ?? 'AKTIF') }}
                            </span>
                        </div>
                        <h3 class="text-xl lg:text-2xl font-black tracking-tight text-white">
                            # {{ $featuredInvoice->no_invoice ?? 'INV-1003' }}
                        </h3>
                    </div>

                    <div class="flex items-center gap-3">
                        <div class="text-right">
                            <p class="text-[10px] text-indigo-200 uppercase font-bold">Outlet / Toko</p>
                            <p class="text-sm font-black text-white flex items-center justify-end gap-1.5">
                                <span>{{ $activeToko->nama_toko ?? ($namaToko ?? 'VxPOS Store') }}</span>
                                <i class="fa-solid fa-store text-xs text-indigo-300"></i>
                            </p>
                        </div>
                        
                        <div class="flex items-center gap-2 pl-3 border-l border-white/20">
                            <div class="w-10 h-10 rounded-full bg-white/20 border border-white/30 flex items-center justify-center text-white font-black text-sm shadow-md">
                                {{ strtoupper(substr($featuredInvoice->nama_pelanggan ?? 'Pelanggan', 0, 1)) }}
                            </div>
                            <div class="text-left">
                                <p class="text-xs font-bold text-white">{{ $featuredInvoice->nama_pelanggan ?? 'Pelanggan Walk-In' }}</p>
                                <p class="text-[10px] text-indigo-200 font-medium">Buku Pelanggan Toko</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Middle Row: Item Breakdown Tiles (Like FINNOVA 3 item cards) -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mb-6 relative z-10">
                    @forelse($featuredItems as $item)
                    <div class="bg-white/10 hover:bg-white/15 backdrop-blur-md rounded-2xl p-4 border border-white/10 transition-all flex flex-col justify-between">
                        <div class="flex justify-between items-start mb-2">
                            <span class="text-base font-black text-white">Rp {{ number_format($item->subtotal ?? 0, 0, ',', '.') }}</span>
                            <i class="fa-solid fa-arrow-up-right-from-square text-xs text-indigo-200"></i>
                        </div>
                        <div>
                            <p class="text-xs font-bold text-white line-clamp-1">{{ $item->nama_barang ?? 'Produk' }}</p>
                            <p class="text-[10px] text-indigo-200">{{ $item->jumlah ?? 1 }} Pcs &bull; {{ $item->kategori ?? 'Umum' }}</p>
                        </div>
                    </div>
                    @empty
                    <!-- Sample Finnova Presentation Tiles if no items yet -->
                    <div class="bg-white/10 backdrop-blur-md rounded-2xl p-4 border border-white/10 flex flex-col justify-between">
                        <div class="flex justify-between items-start mb-2">
                            <span class="text-base font-black text-white">Rp {{ number_format(($totalOmzet ?? 0) > 0 ? ($totalOmzet * 0.4) : 150000, 0, ',', '.') }}</span>
                            <i class="fa-solid fa-arrow-up-right-from-square text-xs text-indigo-200"></i>
                        </div>
                        <div>
                            <p class="text-xs font-bold text-white">Produk Unggulan</p>
                            <p class="text-[10px] text-indigo-200">Kategori Retail Utama</p>
                        </div>
                    </div>

                    <div class="bg-white/10 backdrop-blur-md rounded-2xl p-4 border border-white/10 flex flex-col justify-between">
                        <div class="flex justify-between items-start mb-2">
                            <span class="text-base font-black text-white">Rp {{ number_format(($totalOmzet ?? 0) > 0 ? ($totalOmzet * 0.35) : 85000, 0, ',', '.') }}</span>
                            <i class="fa-solid fa-arrow-up-right-from-square text-xs text-indigo-200"></i>
                        </div>
                        <div>
                            <p class="text-xs font-bold text-white">Item Grosir & Paket</p>
                            <p class="text-[10px] text-indigo-200">Katalog Barang Terlaris</p>
                        </div>
                    </div>
                    @endforelse

                    <!-- Add Item / POS Quick Shortcut Tile -->
                    <a href="{{ route('superadmin.transaksi.create') }}" class="border-2 border-dashed border-white/30 hover:border-white/60 hover:bg-white/10 rounded-2xl p-4 transition-all flex flex-col items-center justify-center text-center cursor-pointer group">
                        <div class="w-8 h-8 rounded-full bg-white/10 group-hover:scale-110 flex items-center justify-center mb-1 text-white transition-all">
                            <i class="fa-solid fa-plus text-xs"></i>
                        </div>
                        <span class="text-xs font-bold text-white">Tambah Item</span>
                        <span class="text-[10px] text-indigo-200">Buka Mesin POS</span>
                    </a>
                </div>

                <!-- Bottom Row: Financial Totals & Actions Bar (Like FINNOVA Bottom Row) -->
                <div class="pt-5 border-t border-white/15 flex flex-col md:flex-row md:items-center justify-between gap-4 relative z-10">
                    <div class="grid grid-cols-3 gap-4 lg:gap-8">
                        <div>
                            <p class="text-[10px] font-bold text-indigo-200 uppercase">Sub Total</p>
                            <p class="text-sm font-extrabold text-white">
                                Rp {{ number_format($featuredInvoice->total_transaksi ?? $totalOmzet ?? 0, 0, ',', '.') }}
                            </p>
                        </div>
                        <div>
                            <p class="text-[10px] font-bold text-indigo-200 uppercase">Uang Masuk (DP)</p>
                            <p class="text-sm font-extrabold text-emerald-300">
                                Rp {{ number_format($featuredInvoice->dp ?? $kasTerbayar ?? 0, 0, ',', '.') }}
                            </p>
                        </div>
                        <div>
                            <p class="text-[10px] font-bold text-indigo-200 uppercase">Sisa Tagihan</p>
                            <p class="text-sm font-extrabold text-rose-300">
                                Rp {{ number_format($featuredInvoice->piutang ?? $kasPiutang ?? 0, 0, ',', '.') }}
                            </p>
                        </div>
                    </div>

                    <div class="flex items-center gap-2">
                        <a href="{{ route('superadmin.transaksi.index') }}" title="Lihat Riwayat Lengkap" class="w-10 h-10 rounded-full bg-white/10 hover:bg-white/20 border border-white/20 flex items-center justify-center text-white transition-all">
                            <i class="fa-solid fa-link text-xs"></i>
                        </a>
                        <a href="{{ route('superadmin.kasir.index') }}" title="Cetak Nota Validasi" class="w-10 h-10 rounded-full bg-white/10 hover:bg-white/20 border border-white/20 flex items-center justify-center text-white transition-all">
                            <i class="fa-solid fa-print text-xs"></i>
                        </a>
                        <a href="{{ route('superadmin.kasir.index') }}" class="px-5 py-2.5 rounded-full bg-white hover:bg-slate-100 text-slate-900 font-extrabold text-xs shadow-lg transition-all hover:scale-105 inline-flex items-center gap-1.5">
                            <span>Validasi Kasir</span>
                            <i class="fa-solid fa-arrow-right text-[10px]"></i>
                        </a>
                    </div>
                </div>

            </div>
        </div>

    </div>
</div>

<!-- ======================================================== -->
<!-- DETAILED ANALYTICS & CHARTS SECTION -->
<!-- ======================================================== -->
<div class="grid grid-cols-1 xl:grid-cols-3 gap-6 mb-8">
    
    <!-- Grafik Pendapatan 7 Hari Terakhir (ApexCharts) -->
    <div class="xl:col-span-2 bg-white p-6 rounded-3xl border border-slate-100 shadow-[0_8px_30px_rgb(0,0,0,0.03)]">
        <div class="flex justify-between items-start mb-4">
            <div>
                <h3 class="font-bold text-slate-900 text-base">Tren Pendapatan Harian</h3>
                <p class="text-slate-400 text-xs font-medium">Statistik omzet masuk selama 7 hari operasional terakhir</p>
            </div>
            <a href="{{ route('superadmin.laporan.index') }}" class="text-xs font-bold text-indigo-600 hover:text-indigo-700 bg-indigo-50 px-3 py-1 rounded-full">
                Lihat Laporan Lengkap &rarr;
            </a>
        </div>
        <div id="revenueChart" class="mt-2"></div>
    </div>

    <!-- Grafik Donat Arus Kas & Piutang (ApexCharts) -->
    <div class="xl:col-span-1 bg-white p-6 rounded-3xl border border-slate-100 shadow-[0_8px_30px_rgb(0,0,0,0.03)] flex flex-col justify-between">
        <div>
            <div class="flex justify-between items-start mb-2">
                <div>
                    <h3 class="font-bold text-slate-900 text-base">Arus Kas & Piutang</h3>
                    <p class="text-slate-400 text-xs font-medium">Rasio pelunasan uang kas vs piutang tempo</p>
                </div>
                <a href="{{ route('superadmin.pelanggan.index') }}" class="text-indigo-600 hover:text-indigo-700 text-xs" title="Buku Pelanggan">
                    <i class="fa-solid fa-arrow-up-right-from-square"></i>
                </a>
            </div>
            <div class="flex items-center justify-center my-4">
                <div id="cashFlowChart" class="w-full flex justify-center"></div>
            </div>
        </div>

        <div class="grid grid-cols-2 gap-3 border-t border-slate-100 pt-4">
            <div class="p-3 bg-emerald-50/60 rounded-2xl">
                <p class="text-[10px] text-emerald-700 font-extrabold uppercase mb-0.5">Uang Masuk</p>
                <p class="text-sm font-black text-emerald-600">Rp {{ number_format($kasTerbayar ?? 0, 0, ',', '.') }}</p>
            </div>
            <div class="p-3 bg-rose-50/60 rounded-2xl text-right">
                <p class="text-[10px] text-rose-700 font-extrabold uppercase mb-0.5">Total Piutang</p>
                <p class="text-sm font-black text-rose-600">Rp {{ number_format($kasPiutang ?? 0, 0, ',', '.') }}</p>
            </div>
        </div>
    </div>
</div>

<!-- ======================================================== -->
<!-- PRODUK TERLARIS & PERFORMA SALES -->
<!-- ======================================================== -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
    
    <!-- Produk Terlaris -->
    <div class="bg-white p-6 rounded-3xl border border-slate-100 shadow-[0_8px_30px_rgb(0,0,0,0.03)]">
        <div class="flex justify-between items-center mb-5">
            <div>
                <h3 class="font-bold text-slate-900 text-base">Top 5 Produk Terlaris</h3>
                <p class="text-slate-400 text-xs font-medium">Volume penjualan item terbanyak bulan ini</p>
            </div>
            <a href="{{ route('superadmin.barang.index') }}" class="text-xs font-bold text-indigo-600 hover:text-indigo-700">
                Kelola Stok &rarr;
            </a>
        </div>
        
        <div class="space-y-3.5">
            @forelse($produkTerlaris as $index => $produk)
            <div class="flex items-center justify-between p-3 rounded-2xl hover:bg-slate-50 transition-colors border border-transparent hover:border-slate-100">
                <div class="flex items-center gap-3.5">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-indigo-50 to-purple-50 text-indigo-600 flex items-center justify-center font-bold text-xs border border-indigo-100 shadow-sm">
                        #{{ $index + 1 }}
                    </div>
                    <div>
                        <h6 class="text-xs font-extrabold text-slate-800 leading-tight">{{ $produk->nama_barang ?? 'Barang' }}</h6>
                        <p class="text-[10px] text-slate-400 font-medium">{{ $produk->kategori ?? 'Umum' }}</p>
                    </div>
                </div>
                <div class="text-right">
                    <span class="text-xs font-black text-slate-800">{{ number_format($produk->total_terjual ?? 0, 0, ',', '.') }}</span>
                    <span class="text-[10px] text-slate-400 block font-medium">Terjual</span>
                </div>
            </div>
            @empty
            <div class="text-center py-8">
                <i class="fa-solid fa-box-open text-slate-300 text-4xl mb-3"></i>
                <p class="text-xs text-slate-400 font-medium">Belum ada data penjualan tercatat bulan ini.</p>
            </div>
            @endforelse
        </div>
    </div>

    <!-- Quick Shortcuts & System Overview -->
    <div class="bg-white p-6 rounded-3xl border border-slate-100 shadow-[0_8px_30px_rgb(0,0,0,0.03)] flex flex-col justify-between">
        <div>
            <div class="flex justify-between items-center mb-5">
                <div>
                    <h3 class="font-bold text-slate-900 text-base">Akses Operasional Cepat</h3>
                    <p class="text-slate-400 text-xs font-medium">Pintasan modul kasir, logistik, dan laporan keuangan</p>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <a href="{{ route('superadmin.transaksi.create') }}" class="p-4 rounded-2xl bg-indigo-50/60 hover:bg-indigo-100/60 border border-indigo-100 transition-all group">
                    <div class="w-9 h-9 rounded-xl bg-indigo-600 text-white flex items-center justify-center text-sm shadow-md shadow-indigo-600/30 mb-3 group-hover:scale-105 transition-transform">
                        <i class="fa-solid fa-cash-register"></i>
                    </div>
                    <p class="text-xs font-bold text-slate-800">Kasir POS</p>
                    <p class="text-[10px] text-slate-400">Input penjualan cepat</p>
                </a>

                <a href="{{ route('superadmin.gudang.index') }}" class="p-4 rounded-2xl bg-amber-50/60 hover:bg-amber-100/60 border border-amber-100 transition-all group">
                    <div class="w-9 h-9 rounded-xl bg-amber-500 text-white flex items-center justify-center text-sm shadow-md shadow-amber-500/30 mb-3 group-hover:scale-105 transition-transform">
                        <i class="fa-solid fa-boxes-stacked"></i>
                    </div>
                    <p class="text-xs font-bold text-slate-800">Gudang & Stok</p>
                    <p class="text-[10px] text-slate-400">Peringatan restock barang</p>
                </a>

                <a href="{{ route('superadmin.pelanggan.index') }}" class="p-4 rounded-2xl bg-emerald-50/60 hover:bg-emerald-100/60 border border-emerald-100 transition-all group">
                    <div class="w-9 h-9 rounded-xl bg-emerald-600 text-white flex items-center justify-center text-sm shadow-md shadow-emerald-600/30 mb-3 group-hover:scale-105 transition-transform">
                        <i class="fa-solid fa-address-book"></i>
                    </div>
                    <p class="text-xs font-bold text-slate-800">Buku Pelanggan</p>
                    <p class="text-[10px] text-slate-400">Daftar kontak & piutang</p>
                </a>

                <a href="{{ route('superadmin.laporan.index') }}" class="p-4 rounded-2xl bg-purple-50/60 hover:bg-purple-100/60 border border-purple-100 transition-all group">
                    <div class="w-9 h-9 rounded-xl bg-purple-600 text-white flex items-center justify-center text-sm shadow-md shadow-purple-600/30 mb-3 group-hover:scale-105 transition-transform">
                        <i class="fa-solid fa-chart-pie"></i>
                    </div>
                    <p class="text-xs font-bold text-slate-800">Laporan Keuangan</p>
                    <p class="text-[10px] text-slate-400">Laba rugi & omzet</p>
                </a>
            </div>
        </div>

        <div class="mt-6 pt-4 border-t border-slate-100 flex items-center justify-between text-xs text-slate-400 font-medium">
            <span>Status Toko: <strong class="text-emerald-600 font-bold">Aktif & Terhubung</strong></span>
            <span>Versi Finnova POS 2.0</span>
        </div>
    </div>

</div>

<!-- ======================================================== -->
<!-- APEXCHARTS INITIALIZATION SCRIPT -->
<!-- ======================================================== -->
<script>
    // ----------------------------------------------------
    // 1. Grafik Batang Pendapatan 7 Hari
    // ----------------------------------------------------
    var grafikPendapatan = {!! json_encode($grafikPendapatan) !!};
    var grafikTanggal = {!! json_encode($grafikTanggal) !!};

    var barOptions = {
        series: [{
            name: 'Pendapatan',
            data: grafikPendapatan
        }],
        chart: {
            height: 280,
            type: 'bar',
            toolbar: { show: false },
            fontFamily: 'Plus Jakarta Sans, sans-serif'
        },
        plotOptions: {
            bar: {
                borderRadius: 8,
                columnWidth: '38%',
                distributed: false
            }
        },
        dataLabels: { enabled: false },
        colors: ['#6366f1'],
        xaxis: {
            categories: grafikTanggal,
            axisBorder: { show: false },
            axisTicks: { show: false },
            labels: { style: { colors: '#94a3b8', fontSize: '11px', fontWeight: 600 } }
        },
        yaxis: {
            labels: {
                style: { colors: '#94a3b8', fontSize: '11px', fontWeight: 600 },
                formatter: function (val) { 
                    if(val >= 1000000) return (val / 1000000).toFixed(1) + "M";
                    if(val >= 1000) return (val / 1000).toFixed(0) + "k";
                    return val;
                }
            }
        },
        grid: {
            borderColor: '#f1f5f9',
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
    // 2. Grafik Donat Arus Kas
    // ----------------------------------------------------
    var kasTerbayar = {{ (int) ($kasTerbayar ?? 0) }};
    var kasPiutang = {{ (int) ($kasPiutang ?? 0) }};
    
    if(kasTerbayar === 0 && kasPiutang === 0) {
        kasTerbayar = 1; 
        var donutColors = ['#f1f5f9', '#e2e8f0'];
    } else {
        var donutColors = ['#10b981', '#f43f5e']; 
    }

    var donutOptions = {
        series: [kasTerbayar, kasPiutang],
        labels: ['Uang Masuk', 'Sisa Piutang'],
        chart: {
            type: 'donut',
            height: 250,
            fontFamily: 'Plus Jakarta Sans, sans-serif'
        },
        colors: donutColors,
        plotOptions: {
            pie: {
                donut: {
                    size: '76%',
                    labels: {
                        show: true,
                        name: { fontSize: '11px', color: '#94a3b8', fontWeight: 600 },
                        value: {
                            fontSize: '16px',
                            fontWeight: 800,
                            color: '#1e293b',
                            formatter: function (val) {
                                if (kasTerbayar === 1 && kasPiutang === 0) return "Rp 0";
                                if(val >= 1000000) return (val / 1000000).toFixed(1) + " Jt";
                                return "Rp " + val.toLocaleString('id-ID');
                            }
                        },
                        total: {
                            show: true,
                            label: 'Total Kasir',
                            color: '#94a3b8',
                            fontSize: '10px',
                            fontWeight: 700,
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
        stroke: { width: 4, colors: ['#ffffff'] },
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