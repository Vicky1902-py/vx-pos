<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Pusat Kendali Superadmin Utama - VxPOS Master Platform</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Public+Sans:wght@300;400;500;600;700;800&display=swap');
        body { font-family: 'Public Sans', sans-serif; background-color: #0f172a; color: #f8fafc; }
        .glow-gold { box-shadow: 0 0 25px rgba(245, 158, 11, 0.25); }
    </style>
</head>
<body class="min-h-screen bg-slate-950 text-slate-100 flex flex-col">

    <!-- Top Navigation Bar -->
    <header class="bg-slate-900 border-b border-slate-800 sticky top-0 z-40 backdrop-blur-md bg-opacity-95">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 bg-gradient-to-tr from-amber-500 to-indigo-600 rounded-xl flex items-center justify-center font-black text-white text-lg shadow-lg">
                    VX
                </div>
                <div>
                    <div class="flex items-center space-x-2">
                        <span class="text-xl font-extrabold tracking-tight text-white">Vx<span class="text-amber-400">POS</span></span>
                        <span class="bg-amber-400/20 text-amber-300 text-[10px] font-bold px-2 py-0.5 rounded-full border border-amber-400/30 uppercase tracking-widest">
                            Master Platform
                        </span>
                    </div>
                    <p class="text-[11px] text-slate-400 font-medium">Hak Cipta by. Vicky Koroh</p>
                </div>
            </div>

            <div class="flex items-center space-x-4">
                <a href="{{ route('platform.repair_db') }}" class="hidden md:inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-semibold bg-slate-800 hover:bg-slate-700 text-slate-300 border border-slate-700 transition" data-confirm="Jalankan sinkronisasi dan perbaikan skema database?" data-confirm-type="success" data-confirm-title="Perbaikan Database">
                    <i class="fa-solid fa-wrench mr-1.5 text-emerald-400"></i> Periksa Database
                </a>
                
                <div class="h-6 w-px bg-slate-800 hidden md:block"></div>

                <div class="flex items-center space-x-3">
                    <div class="w-8 h-8 rounded-full bg-amber-500/20 border border-amber-500/40 flex items-center justify-center text-amber-400 text-xs font-bold">
                        <i class="fa-solid fa-crown"></i>
                    </div>
                    <div class="hidden sm:block text-left">
                        <p class="text-xs font-bold text-white">{{ Auth::user()->nama ?? 'Vicky Koroh' }}</p>
                        <p class="text-[10px] text-amber-400 font-semibold uppercase">Superadmin Utama</p>
                    </div>
                </div>

                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="p-2 text-slate-400 hover:text-rose-400 hover:bg-slate-800 rounded-lg transition" title="Logout">
                        <i class="fa-solid fa-right-from-bracket"></i>
                    </button>
                </form>
            </div>
        </div>
    </header>

    <!-- Main Content Area -->
    <main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">
        
        <!-- Flash Alert Messages -->
        @if(session('success'))
            <div class="p-4 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-300 text-sm flex items-center space-x-3">
                <i class="fa-solid fa-circle-check text-emerald-400 text-lg"></i>
                <span>{{ session('success') }}</span>
            </div>
        @endif
        @if($errors->any())
            <div class="p-4 rounded-xl bg-rose-500/10 border border-rose-500/30 text-rose-300 text-sm flex items-center space-x-3">
                <i class="fa-solid fa-triangle-exclamation text-rose-400 text-lg"></i>
                <span>{{ $errors->first() }}</span>
            </div>
        @endif

        <!-- Executive Hero Banner -->
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-slate-900 via-indigo-950 to-slate-900 border border-indigo-900/50 p-6 md:p-8 glow-gold">
            <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
                <div>
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-amber-500/10 border border-amber-500/20 text-amber-400 text-xs font-semibold mb-3">
                        <i class="fa-solid fa-shield-halved"></i> Panel Superadmin Utama Multi-Tenant
                    </div>
                    <h1 class="text-2xl md:text-3xl font-black text-white tracking-tight">
                        Pusat Kendali Eksekutif Platform VxPOS
                    </h1>
                    <p class="text-slate-400 text-sm mt-1 max-w-2xl">
                        Sistem terpisah untuk mengontrol seluruh toko mitra, mengelola paket langganan SaaS, memantau omzet lintas toko se-Indonesia, dan melakukan remote support/asistensi 1-klik.
                    </p>
                </div>
                <div class="flex flex-wrap items-center gap-3">
                    <a href="{{ route('platform.demo.generate') }}" data-confirm="Buat atau segarkan data simulasi Toko Retail Demo?" data-confirm-type="success" data-confirm-title="Segarkan Toko Demo" class="px-5 py-2.5 rounded-xl bg-gradient-to-r from-emerald-500 to-teal-600 hover:from-emerald-600 hover:to-teal-700 text-slate-950 font-bold text-sm shadow-lg shadow-emerald-500/20 transition flex items-center space-x-2">
                        <i class="fa-solid fa-bolt"></i>
                        <span>Buat / Reset Akun Demo</span>
                    </a>
                    <button onclick="document.getElementById('modalTambahToko').classList.remove('hidden')" class="px-5 py-2.5 rounded-xl bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-600 hover:to-amber-700 text-slate-950 font-bold text-sm shadow-lg shadow-amber-500/20 transition flex items-center space-x-2">
                        <i class="fa-solid fa-plus-circle"></i>
                        <span>Daftarkan Toko Baru</span>
                    </button>
                    <a href="{{ route('superadmin.dashboard') }}" class="px-4 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 font-semibold text-sm border border-slate-700 transition flex items-center space-x-2">
                        <i class="fa-solid fa-cash-register text-indigo-400"></i>
                        <span>Buka POS Toko Aktif</span>
                    </a>
                </div>
            </div>
        </div>

        <!-- Card Informasi & Akses Cepat Akun Demo -->
        @php
            $demoToko = \Illuminate\Support\Facades\DB::table('toko')->where('slug', 'toko-demo')->first();
        @endphp
        <div class="p-5 rounded-2xl bg-gradient-to-r from-slate-900 via-emerald-950/20 to-slate-900 border border-emerald-500/30 flex flex-col md:flex-row items-start md:items-center justify-between gap-4 shadow-xl">
            <div class="flex items-center space-x-3.5">
                <div class="w-12 h-12 rounded-xl bg-emerald-500/20 border border-emerald-500/40 flex items-center justify-center text-emerald-400 text-xl font-bold shadow-md">
                    <i class="fa-solid fa-store"></i>
                </div>
                <div>
                    <div class="flex items-center space-x-2">
                        <span class="text-xs font-bold uppercase tracking-wider text-emerald-400">Akun Demo Siap Digunakan</span>
                        <span class="bg-emerald-500/20 text-emerald-300 text-[10px] font-bold px-2 py-0.5 rounded border border-emerald-500/30">PRO PLAN</span>
                    </div>
                    <p class="text-xs text-slate-300 mt-1">
                        Admin Demo: <strong class="text-white bg-slate-800 px-2 py-0.5 rounded border border-slate-700">demo</strong> &nbsp;&bull;&nbsp; 
                        Kasir Demo: <strong class="text-white bg-slate-800 px-2 py-0.5 rounded border border-slate-700">kasir_demo</strong> &nbsp;&bull;&nbsp; 
                        Password: <strong class="text-amber-400 bg-slate-800 px-2 py-0.5 rounded border border-slate-700">demo123</strong>
                    </p>
                </div>
            </div>
            <div class="flex flex-wrap items-center gap-2.5 w-full md:w-auto justify-end">
                <a href="{{ route('platform.demo.generate') }}" data-confirm="Segarkan data simulasi Toko Demo?" data-confirm-type="success" data-confirm-title="Segarkan Data Demo" class="px-3.5 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-semibold border border-slate-700 transition flex items-center gap-1.5">
                    <i class="fa-solid fa-rotate text-emerald-400"></i>
                    <span>Segarkan Data Demo</span>
                </a>
                @if($demoToko)
                    <a href="{{ route('platform.toko.impersonate', $demoToko->id) }}" class="px-4 py-2 rounded-xl bg-emerald-500 hover:bg-emerald-600 text-slate-950 text-xs font-bold shadow-md transition flex items-center gap-1.5">
                        <i class="fa-solid fa-arrow-right-to-bracket"></i>
                        <span>Masuk ke Toko Demo</span>
                    </a>
                @endif
            </div>
        </div>

        <!-- 4 Global SaaS KPI Metric Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
            <!-- Card 1 -->
            <div class="p-5 rounded-2xl bg-slate-900 border border-slate-800 flex items-center space-x-4">
                <div class="w-12 h-12 rounded-xl bg-blue-500/10 border border-blue-500/20 flex items-center justify-center text-blue-400 text-xl font-bold">
                    <i class="fa-solid fa-shop"></i>
                </div>
                <div>
                    <p class="text-xs uppercase text-slate-400 font-bold tracking-wider">Total Toko Mitra</p>
                    <h3 class="text-2xl font-black text-white mt-0.5">{{ $totalSemuaToko }} <span class="text-xs font-normal text-slate-400">toko</span></h3>
                    <p class="text-[11px] text-emerald-400 mt-0.5"><i class="fa-solid fa-circle-check"></i> {{ $totalTokoAktif }} Aktif</p>
                </div>
            </div>

            <!-- Card 2 -->
            <div class="p-5 rounded-2xl bg-slate-900 border border-slate-800 flex items-center space-x-4">
                <div class="w-12 h-12 rounded-xl bg-emerald-500/10 border border-emerald-500/20 flex items-center justify-center text-emerald-400 text-xl font-bold">
                    <i class="fa-solid fa-wallet"></i>
                </div>
                <div>
                    <p class="text-xs uppercase text-slate-400 font-bold tracking-wider">Estimasi MRR Langganan</p>
                    <h3 class="text-2xl font-black text-emerald-400 mt-0.5">Rp {{ number_format($estimasiSaaSMrr, 0, ',', '.') }}</h3>
                    <p class="text-[11px] text-slate-400 mt-0.5">Pendapatan SaaS / bulan</p>
                </div>
            </div>

            <!-- Card 3 -->
            <div class="p-5 rounded-2xl bg-slate-900 border border-slate-800 flex items-center space-x-4">
                <div class="w-12 h-12 rounded-xl bg-amber-500/10 border border-amber-500/20 flex items-center justify-center text-amber-400 text-xl font-bold">
                    <i class="fa-solid fa-chart-line"></i>
                </div>
                <div>
                    <p class="text-xs uppercase text-slate-400 font-bold tracking-wider">Total Transaksi Kasir</p>
                    <h3 class="text-2xl font-black text-white mt-0.5">{{ number_format($totalTransaksiGlobal, 0, ',', '.') }}</h3>
                    <p class="text-[11px] text-slate-400 mt-0.5">Semua toko se-Indonesia</p>
                </div>
            </div>

            <!-- Card 4 -->
            <div class="p-5 rounded-2xl bg-slate-900 border border-slate-800 flex items-center space-x-4">
                <div class="w-12 h-12 rounded-xl bg-purple-500/10 border border-purple-500/20 flex items-center justify-center text-purple-400 text-xl font-bold">
                    <i class="fa-solid fa-coins"></i>
                </div>
                <div>
                    <p class="text-xs uppercase text-slate-400 font-bold tracking-wider">Volume Omzet Global</p>
                    <h3 class="text-xl font-black text-white mt-0.5">Rp {{ number_format($omzetGlobalTransaksi, 0, ',', '.') }}</h3>
                    <p class="text-[11px] text-purple-400 mt-0.5">{{ $totalPenggunaGlobal }} staf terdaftar</p>
                </div>
            </div>
        </div>

        <!-- Tab Navigation Pusat Kendali Platform -->
        <div class="flex items-center justify-between border-b border-slate-800 pb-3 flex-wrap gap-3">
            <div class="flex items-center space-x-2 bg-slate-900/90 p-1.5 rounded-2xl border border-slate-800">
                <button type="button" onclick="switchPlatformTab('toko')" id="btnTabToko" 
                    class="platform-tab-btn px-4 py-2.5 rounded-xl text-xs font-bold transition-all flex items-center gap-2 bg-amber-500 text-slate-950 shadow-md">
                    <i class="fa-solid fa-store"></i>
                    <span>Daftar Toko & SaaS</span>
                    <span class="px-1.5 py-0.5 rounded-full text-[10px] bg-slate-950/20 text-slate-950 font-black">{{ $totalSemuaToko }}</span>
                </button>

                <button type="button" onclick="switchPlatformTab('traffic')" id="btnTabTraffic" 
                    class="platform-tab-btn px-4 py-2.5 rounded-xl text-xs font-bold text-slate-400 hover:text-white transition-all flex items-center gap-2">
                    <span class="relative flex h-2 w-2">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                    </span>
                    <i class="fa-solid fa-satellite-dish"></i>
                    <span>Live Traffic & Sesi</span>
                    <span id="tabTrafficBadge" class="px-2 py-0.5 rounded-full text-[10px] bg-emerald-500/20 text-emerald-300 font-bold border border-emerald-500/30">{{ $totalOnlineNow }} Online</span>
                </button>

                <button type="button" onclick="switchPlatformTab('cms')" id="btnTabCms" 
                    class="platform-tab-btn px-4 py-2.5 rounded-xl text-xs font-bold text-slate-400 hover:text-white transition-all flex items-center gap-2">
                    <i class="fa-solid fa-palette text-indigo-400"></i>
                    <span>CMS Landing Page</span>
                    <span class="px-2 py-0.5 rounded-full text-[10px] bg-indigo-500/20 text-indigo-300 font-semibold border border-indigo-500/30">Edit Beranda</span>
                </button>
            </div>

            <div class="flex items-center gap-2 text-xs text-slate-400">
                <a href="/" target="_blank" class="px-3.5 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 font-semibold border border-slate-700 transition flex items-center gap-2">
                    <i class="fa-solid fa-arrow-up-right-from-square text-amber-400"></i>
                    <span>Pratinjau Beranda (Landing)</span>
                </a>
            </div>
        </div>

        <!-- Tab 1: Daftar Toko Mitra & SaaS -->
        <div id="tabContentToko" class="tab-content space-y-6">
            <!-- Tabel Monitoring & Manajemen Seluruh Toko Mitra -->
            <div class="bg-slate-900 border border-slate-800 rounded-2xl overflow-hidden shadow-xl">
                <div class="p-5 md:p-6 border-b border-slate-800 flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div>
                        <h2 class="text-lg font-bold text-white flex items-center gap-2">
                            <i class="fa-solid fa-network-wired text-indigo-400"></i>
                            Daftar Seluruh Toko Mitra
                        </h2>
                        <p class="text-xs text-slate-400 mt-0.5">Kelola paket, perpanjang langganan, dan masuk ke sistem toko untuk remote assist.</p>
                    </div>
                
                <!-- Search Form -->
                <form method="GET" action="{{ route('platform.dashboard') }}" class="flex items-center gap-2">
                    <div class="relative">
                        <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-500 text-xs"></i>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama toko/telepon..." 
                            class="pl-9 pr-4 py-2 bg-slate-950 border border-slate-700 rounded-xl text-xs text-white placeholder-slate-500 focus:outline-none focus:border-amber-500 w-64 transition">
                    </div>
                    <button type="submit" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-xs font-semibold text-white rounded-xl border border-slate-700 transition">
                        Cari
                    </button>
                    @if(request('search'))
                        <a href="{{ route('platform.dashboard') }}" class="p-2 text-slate-400 hover:text-white text-xs">Reset</a>
                    @endif
                </form>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-950/60 border-b border-slate-800 text-[11px] uppercase tracking-wider text-slate-400 font-bold">
                            <th class="py-3.5 px-4">ID & Toko</th>
                            <th class="py-3.5 px-4">Paket Langganan</th>
                            <th class="py-3.5 px-4">Status & Kadaluarsa</th>
                            <th class="py-3.5 px-4">Barang</th>
                            <th class="py-3.5 px-4">Transaksi</th>
                            <th class="py-3.5 px-4">Omzet Toko</th>
                            <th class="py-3.5 px-4 text-center">Aksi Superadmin</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800/60 text-xs text-slate-200">
                        @forelse($daftarToko as $toko)
                            <tr class="hover:bg-slate-800/40 transition {{ $toko->id == $activeTokoId ? 'bg-indigo-950/20 border-l-4 border-amber-400' : '' }}">
                                <td class="py-4 px-4">
                                    <div class="flex items-center space-x-3">
                                        <div class="w-9 h-9 rounded-lg bg-slate-800 border border-slate-700 flex items-center justify-center font-bold text-amber-400">
                                            {{ substr($toko->nama_toko, 0, 2) }}
                                        </div>
                                        <div>
                                            <div class="font-bold text-white flex items-center gap-1.5">
                                                {{ $toko->nama_toko }}
                                                @if($toko->id == 1)
                                                    <span class="bg-blue-500/20 text-blue-400 text-[9px] font-bold px-1.5 py-0.5 rounded border border-blue-500/30">Pusat</span>
                                                @endif
                                                @if($toko->id == $activeTokoId)
                                                    <span class="bg-amber-500/20 text-amber-400 text-[9px] font-bold px-1.5 py-0.5 rounded border border-amber-500/30">Sedang Aktif</span>
                                                @endif
                                            </div>
                                            <p class="text-[11px] text-slate-400 mt-0.5"><i class="fa-solid fa-phone text-[9px] mr-1"></i> {{ $toko->no_telp ?? '-' }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-4 px-4">
                                    @php
                                        $badgeColor = match($toko->paket ?? 'starter') {
                                            'enterprise' => 'bg-purple-500/20 text-purple-300 border-purple-500/30',
                                            'pro'        => 'bg-indigo-500/20 text-indigo-300 border-indigo-500/30',
                                            default      => 'bg-slate-700 text-slate-300 border-slate-600',
                                        };
                                    @endphp
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider border {{ $badgeColor }}">
                                        {{ $toko->paket ?? 'Starter' }}
                                    </span>
                                </td>
                                <td class="py-4 px-4">
                                    <div>
                                        @if($toko->status === 'aktif')
                                            <span class="inline-flex items-center gap-1 text-emerald-400 font-bold text-[11px]">
                                                <i class="fa-solid fa-circle text-[7px]"></i> Aktif
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1 text-rose-400 font-bold text-[11px]">
                                                <i class="fa-solid fa-circle text-[7px]"></i> {{ ucfirst($toko->status) }}
                                            </span>
                                        @endif
                                        <p class="text-[10px] text-slate-400 mt-0.5">
                                            Exp: {{ $toko->expired_at ? date('d M Y', strtotime($toko->expired_at)) : 'Selamanya' }}
                                        </p>
                                    </div>
                                </td>
                                <td class="py-4 px-4 font-semibold text-slate-300">
                                    {{ number_format($toko->total_barang) }} item
                                </td>
                                <td class="py-4 px-4 font-semibold text-slate-300">
                                    {{ number_format($toko->total_transaksi) }} tx
                                </td>
                                <td class="py-4 px-4 font-bold text-white">
                                    Rp {{ number_format($toko->total_omzet, 0, ',', '.') }}
                                </td>
                                <td class="py-4 px-4 text-center">
                                    <div class="flex items-center justify-center space-x-2">
                                        <!-- Tombol 1: Masuk ke POS Toko Ini -->
                                        <a href="{{ route('platform.toko.impersonate', $toko->id) }}" 
                                           class="px-2.5 py-1.5 rounded-lg bg-indigo-600 hover:bg-indigo-500 text-white font-semibold text-xs transition shadow flex items-center space-x-1" 
                                           title="Masuk ke Sistem Toko Ini (Asistensi)">
                                            <i class="fa-solid fa-arrow-right-to-bracket"></i>
                                            <span class="hidden md:inline">Masuk Toko</span>
                                        </a>

                                        <!-- Tombol 2: Edit Toko & Paket -->
                                        <button onclick="bukaModalEdit({{ json_encode($toko) }})" 
                                                class="px-2.5 py-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 text-xs font-semibold transition" 
                                                title="Edit Informasi & Paket">
                                            <i class="fa-solid fa-pen-to-square"></i>
                                        </button>

                                        <!-- Tombol 3: Hapus Toko (Khusus bukan Toko ID 1) -->
                                        @if($toko->id != 1)
                                            <form action="{{ route('platform.toko.destroy', $toko->id) }}" method="POST" data-confirm="Apakah Anda yakin ingin menghapus toko mitra ini secara permanen?" data-confirm-type="danger" data-confirm-title="Hapus Toko Mitra" data-confirm-btn="Ya, Hapus Toko">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="px-2.5 py-1.5 rounded-lg bg-rose-500/10 hover:bg-rose-500/20 text-rose-400 border border-rose-500/30 text-xs font-semibold transition cursor-pointer" title="Hapus Toko">
                                                    <i class="fa-solid fa-trash"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-8 text-center text-slate-400">
                                    Belum ada data toko mitra. Silakan klik <strong>"Daftarkan Toko Baru"</strong>.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($daftarToko->hasPages())
                <div class="p-4 border-t border-slate-800 bg-slate-950/40">
                    {{ $daftarToko->links() }}
                </div>
            @endif
            </div>
        </div>
        <!-- End tabContentToko -->

        <!-- Tab 2: Live Traffic Monitoring (Radar Pengguna Online) -->
        <div id="tabContentTraffic" class="tab-content hidden space-y-6">
            <!-- Live Traffic Control Panel & Summary Bar -->
            <div class="p-6 rounded-2xl bg-gradient-to-r from-slate-900 via-indigo-950/40 to-slate-900 border border-slate-800 shadow-xl flex flex-col md:flex-row md:items-center justify-between gap-5">
                <div class="flex items-center space-x-4">
                    <div class="relative w-12 h-12 rounded-2xl bg-emerald-500/10 border border-emerald-500/30 flex items-center justify-center text-emerald-400 text-xl font-bold">
                        <i class="fa-solid fa-satellite-dish"></i>
                        <span class="absolute -top-1 -right-1 flex h-3.5 w-3.5">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-3.5 w-3.5 bg-emerald-500"></span>
                        </span>
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h2 class="text-lg font-black text-white">Live Traffic & Radar Sesi Pengguna</h2>
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-emerald-500/20 text-emerald-300 border border-emerald-500/40 uppercase tracking-widest animate-pulse">
                                LIVE ACTIVE
                            </span>
                        </div>
                        <p class="text-xs text-slate-400 mt-1 max-w-xl">
                            Memantau kasir, admin toko, sales, dan gudang yang sedang membuka menu atau memproses transaksi secara real-time. Superadmin otomatis dikecualikan.
                        </p>
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-3">
                    <!-- Server Clock -->
                    <div class="px-3.5 py-2 rounded-xl bg-slate-950 border border-slate-800 flex items-center gap-2">
                        <i class="fa-regular fa-clock text-amber-400 text-xs"></i>
                        <div class="text-left">
                            <span class="text-[10px] text-slate-500 uppercase block font-bold leading-none">Waktu Server</span>
                            <span id="liveClock" class="text-xs font-mono font-bold text-slate-200 leading-none">{{ now()->format('H:i:s') }} WIB</span>
                        </div>
                    </div>

                    <!-- Auto-Refresh Toggle -->
                    <button type="button" id="btnToggleAutoRefresh" onclick="toggleAutoRefresh()" 
                        class="px-3.5 py-2 rounded-xl bg-slate-950 hover:bg-slate-800 border border-slate-800 text-xs font-semibold text-emerald-400 flex items-center gap-2 transition">
                        <i id="autoRefreshIcon" class="fa-solid fa-arrows-rotate animate-spin text-xs"></i>
                        <span id="autoRefreshText">Auto-Refresh: ON (5s)</span>
                    </button>

                    <!-- Manual Refresh Button -->
                    <button type="button" onclick="fetchLiveTrafficData()" 
                        class="px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold shadow-md shadow-indigo-600/30 transition flex items-center gap-2">
                        <i class="fa-solid fa-rotate-right"></i>
                        <span>Segarkan Sekarang</span>
                    </button>
                </div>
            </div>

            <!-- Active Online Sessions Table Card -->
            <div class="bg-slate-900 border border-slate-800 rounded-2xl overflow-hidden shadow-xl">
                <div class="p-5 md:p-6 border-b border-slate-800 flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div>
                        <h3 class="text-base font-bold text-white flex items-center gap-2">
                            <i class="fa-solid fa-users-viewfinder text-emerald-400"></i>
                            Daftar Pengguna Online Saat Ini
                            <span id="liveOnlineCountBadge" class="ml-2 px-2.5 py-0.5 rounded-full text-xs font-black bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">
                                {{ $totalOnlineNow }} Sesi Aktif
                            </span>
                        </h3>
                        <p class="text-xs text-slate-400 mt-0.5">Sesi login non-superadmin yang aktif beraktivitas dalam 10 menit terakhir.</p>
                    </div>
                    
                    <div class="text-xs text-slate-400 flex items-center gap-1.5">
                        <span class="inline-block w-2 h-2 rounded-full bg-emerald-400 animate-ping"></span>
                        <span>Diperbarui otomatis tiap 5 detik</span>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-950/60 border-b border-slate-800 text-[11px] uppercase tracking-wider text-slate-400 font-bold">
                                <th class="py-3.5 px-4">Pengguna & Peran</th>
                                <th class="py-3.5 px-4">Toko / Cabang</th>
                                <th class="py-3.5 px-4">Menu / Fitur yang Diakses</th>
                                <th class="py-3.5 px-4">IP & Perangkat</th>
                                <th class="py-3.5 px-4">Terakhir Aktif</th>
                                <th class="py-3.5 px-4 text-center">Tindakan</th>
                            </tr>
                        </thead>
                        <tbody id="liveTrafficTableBody" class="divide-y divide-slate-800/60 text-xs text-slate-200">
                            @forelse($onlineUsers as $user)
                                <tr class="hover:bg-slate-800/40 transition">
                                    <td class="py-4 px-4">
                                        <div class="flex items-center space-x-3">
                                            <div class="w-9 h-9 rounded-xl bg-slate-800 border border-slate-700 flex items-center justify-center font-bold text-white text-xs">
                                                {{ strtoupper(substr($user->nama ?: $user->username, 0, 2)) }}
                                            </div>
                                            <div>
                                                <p class="font-bold text-white">{{ $user->nama ?: $user->username }}</p>
                                                <div class="flex items-center gap-1.5 mt-0.5">
                                                    <span class="text-[10px] text-slate-400 font-mono">&#64;{{ $user->username }}</span>
                                                    <span class="px-1.5 py-0.2 rounded text-[9px] font-bold uppercase
                                                        {{ $user->role == 'admin' ? 'bg-indigo-500/20 text-indigo-300 border border-indigo-500/30' : '' }}
                                                        {{ $user->role == 'kasir' ? 'bg-emerald-500/20 text-emerald-300 border border-emerald-500/30' : '' }}
                                                        {{ $user->role == 'sales' ? 'bg-amber-500/20 text-amber-300 border border-amber-500/30' : '' }}
                                                        {{ $user->role == 'gudang' ? 'bg-purple-500/20 text-purple-300 border border-purple-500/30' : '' }}
                                                    ">
                                                        {{ $user->role }}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-4 px-4">
                                        <div class="font-semibold text-white">{{ $user->nama_toko ?: 'Toko #' . $user->toko_id }}</div>
                                        <span class="text-[10px] text-slate-500 font-mono">ID Toko: {{ $user->toko_id }}</span>
                                    </td>
                                    <td class="py-4 px-4">
                                        <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-bold bg-indigo-500/10 text-indigo-300 border border-indigo-500/20">
                                            <i class="fa-solid fa-compass text-[11px] text-indigo-400"></i>
                                            <span>{{ $user->feature_name }}</span>
                                        </div>
                                        <p class="text-[10px] text-slate-500 font-mono mt-1">{{ $user->url }} ({{ $user->method }})</p>
                                    </td>
                                    <td class="py-4 px-4">
                                        <p class="font-mono text-slate-300 text-xs">{{ $user->ip_address }}</p>
                                        <p class="text-[10px] text-slate-500 truncate max-w-[180px]" title="{{ $user->user_agent }}">{{ $user->user_agent }}</p>
                                    </td>
                                    <td class="py-4 px-4">
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                                            <i class="fa-solid fa-clock text-[9px]"></i>
                                            {{ \Carbon\Carbon::parse($user->last_active_at)->diffForHumans() }}
                                        </span>
                                    </td>
                                    <td class="py-4 px-4 text-center">
                                        <form action="{{ route('platform.traffic.kick', $user->user_id) }}" method="POST" 
                                            data-confirm="Putuskan sesi login pengguna [{{ $user->nama ?: $user->username }}] secara paksa?" 
                                            data-confirm-type="danger" 
                                            data-confirm-title="Putuskan Sesi Pengguna" 
                                            data-confirm-btn="Ya, Putuskan Sesi">
                                            @csrf
                                            <button type="submit" class="px-3 py-1.5 rounded-lg bg-rose-500/10 hover:bg-rose-500/25 text-rose-400 border border-rose-500/30 text-xs font-bold transition flex items-center gap-1.5 mx-auto" title="Kick Out">
                                                <i class="fa-solid fa-user-slash"></i>
                                                <span>Putuskan Sesi</span>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr id="emptyTrafficRow">
                                    <td colspan="6" class="py-12 text-center">
                                        <div class="w-14 h-14 rounded-2xl bg-slate-800/80 text-slate-500 mx-auto flex items-center justify-center text-2xl mb-3 border border-slate-700/50">
                                            <i class="fa-solid fa-radar text-emerald-400 animate-pulse"></i>
                                        </div>
                                        <h4 class="text-sm font-bold text-white">Radar Aktif: Belum Ada Staf/Kasir Online</h4>
                                        <p class="text-xs text-slate-400 mt-1 max-w-md mx-auto">
                                            Saat kasir, admin toko, sales, atau gudang login dan berinteraksi di toko mereka, aktivitas dan sesi mereka akan langsung terdeteksi seketika di sini.
                                        </p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Live Request Stream Feed (Audit Trail 35 Terakhir) -->
            <div class="bg-slate-900 border border-slate-800 rounded-2xl overflow-hidden shadow-xl">
                <div class="p-5 md:p-6 border-b border-slate-800 flex items-center justify-between">
                    <div>
                        <h3 class="text-base font-bold text-white flex items-center gap-2">
                            <i class="fa-solid fa-list-check text-indigo-400"></i>
                            Audit Feed Alur Permintaan (Live Activity Log)
                        </h3>
                        <p class="text-xs text-slate-400 mt-0.5">Riwayat 35 akses menu dan transaksi terbaru lintas toko se-Indonesia.</p>
                    </div>
                    <span class="text-[10px] font-mono text-slate-500 uppercase">Stream Logs Real-Time</span>
                </div>

                <div class="p-4 md:p-6 max-h-96 overflow-y-auto space-y-2.5" id="liveTrafficLogsContainer">
                    @forelse($recentTrafficLogs as $log)
                        <div class="p-3 rounded-xl bg-slate-950/60 border border-slate-800/80 flex flex-col sm:flex-row sm:items-center justify-between gap-2 text-xs hover:border-slate-700 transition">
                            <div class="flex items-center gap-3">
                                <span class="font-mono text-[10px] text-amber-400 font-bold bg-slate-900 px-2 py-0.5 rounded border border-slate-800">
                                    {{ \Carbon\Carbon::parse($log->created_at)->format('H:i:s') }}
                                </span>
                                <div>
                                    <span class="font-bold text-white">{{ $log->nama ?: $log->username }}</span>
                                    <span class="text-slate-400">&#64;{{ $log->nama_toko ?: 'Toko #' . $log->toko_id }}</span>
                                    <span class="text-[10px] px-1.5 py-0.2 rounded font-bold uppercase ml-1
                                        {{ $log->role == 'admin' ? 'bg-indigo-500/20 text-indigo-300' : 'bg-emerald-500/20 text-emerald-300' }}">
                                        {{ $log->role }}
                                    </span>
                                </div>
                            </div>

                            <div class="flex items-center gap-3 sm:justify-end">
                                <div class="text-left sm:text-right">
                                    <span class="font-semibold text-slate-200">{{ $log->feature_name }}</span>
                                    <span class="font-mono text-[10px] text-slate-500 block">{{ $log->method }} {{ $log->url }}</span>
                                </div>
                                <span class="font-mono text-[10px] text-slate-400 bg-slate-900 px-2 py-1 rounded border border-slate-800 hidden md:inline-block">
                                    {{ $log->ip_address }}
                                </span>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-8 text-xs text-slate-500" id="emptyLogsPlaceholder">
                            Belum ada riwayat aktivitas yang tercatat.
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Tab 3: CMS Landing Page -->
        <div id="tabContentCms" class="tab-content hidden space-y-6">
            <!-- CMS Header Banner -->
            <div class="p-6 rounded-2xl bg-gradient-to-r from-slate-900 via-indigo-950/40 to-slate-900 border border-slate-800 shadow-xl flex flex-col md:flex-row md:items-center justify-between gap-5">
                <div class="flex items-center space-x-4">
                    <div class="w-12 h-12 rounded-2xl bg-amber-500/10 border border-amber-500/30 flex items-center justify-center text-amber-400 text-xl font-bold">
                        <i class="fa-solid fa-palette"></i>
                    </div>
                    <div>
                        <h2 class="text-lg font-black text-white">Full CMS Kelola Landing Page Beranda</h2>
                        <p class="text-xs text-slate-400 mt-1 max-w-xl">
                            Kelola logo, favicon, headline hero, teks penawaran, nomor WhatsApp konsultasi, daftar harga paket, dan informasi legal langsung dari dashboard Godmode.
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <a href="/" target="_blank" class="px-4 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-white text-xs font-bold border border-slate-700 transition flex items-center gap-2">
                        <i class="fa-solid fa-arrow-up-right-from-square text-amber-400"></i>
                        <span>Lihat Tampilan Landing Page</span>
                    </a>
                </div>
            </div>

            <!-- CMS Form -->
            <form action="{{ route('platform.landing.update') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                @csrf

                <!-- Section 1: Identitas Brand, Logo & Favicon -->
                <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 shadow-xl space-y-5">
                    <div class="pb-3 border-b border-slate-800 flex items-center justify-between">
                        <h3 class="text-base font-bold text-white flex items-center gap-2">
                            <i class="fa-solid fa-copyright text-amber-400"></i>
                            1. Identitas Brand, Logo & Favicon
                        </h3>
                        <span class="text-[10px] text-slate-500 font-mono uppercase">Header & Brand Assets</span>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div class="space-y-1.5">
                            <label class="text-xs font-semibold text-slate-300">Nama Brand / Software</label>
                            <input type="text" name="brand_name" value="{{ old('brand_name', $landingSettings->brand_name ?? 'VxPOS') }}" required 
                                placeholder="Contoh: VxPOS" 
                                class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-700 rounded-xl text-xs text-white focus:outline-none focus:border-amber-500">
                        </div>

                        <div class="space-y-1.5">
                            <label class="text-xs font-semibold text-slate-300">Tagline / Slogan Brand</label>
                            <input type="text" name="tagline" value="{{ old('tagline', $landingSettings->tagline ?? 'Sistem Kasir & ERP Terpadu Multi-Toko') }}" 
                                placeholder="Contoh: Sistem Kasir & ERP Terpadu Multi-Toko" 
                                class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-700 rounded-xl text-xs text-white focus:outline-none focus:border-amber-500">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5 pt-2">
                        <!-- Upload Logo -->
                        <div class="p-4 rounded-xl bg-slate-950/60 border border-slate-800 space-y-3">
                            <label class="text-xs font-bold text-white flex items-center justify-between">
                                <span>Unggah Logo Brand Baru</span>
                                <span class="text-[10px] text-slate-500">Maks. 3MB (PNG, JPG, SVG, WebP)</span>
                            </label>
                            <div class="flex items-center gap-4">
                                <div class="w-16 h-12 rounded-lg bg-slate-900 border border-slate-700 flex items-center justify-center p-1 overflow-hidden shrink-0">
                                    @if(!empty($landingSettings->logo))
                                        <img src="{{ asset($landingSettings->logo) }}" alt="Logo" class="max-h-full max-w-full object-contain">
                                    @else
                                        <span class="text-xs font-black text-amber-400">LOGO</span>
                                    @endif
                                </div>
                                <input type="file" name="logo" accept="image/*" 
                                    class="text-xs text-slate-400 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-slate-800 file:text-slate-200 hover:file:bg-slate-700 file:cursor-pointer cursor-pointer">
                            </div>
                            @if(!empty($landingSettings->logo))
                                <p class="text-[10px] text-emerald-400 font-mono">Logo aktif: {{ $landingSettings->logo }}</p>
                            @endif
                        </div>

                        <!-- Upload Favicon -->
                        <div class="p-4 rounded-xl bg-slate-950/60 border border-slate-800 space-y-3">
                            <label class="text-xs font-bold text-white flex items-center justify-between">
                                <span>Unggah Favicon Web Baru</span>
                                <span class="text-[10px] text-slate-500">Maks. 1MB (.ico, PNG, SVG)</span>
                            </label>
                            <div class="flex items-center gap-4">
                                <div class="w-12 h-12 rounded-lg bg-slate-900 border border-slate-700 flex items-center justify-center p-1 overflow-hidden shrink-0">
                                    @if(!empty($landingSettings->favicon))
                                        <img src="{{ asset($landingSettings->favicon) }}" alt="Favicon" class="max-h-full max-w-full object-contain">
                                    @else
                                        <span class="text-sm">⚡</span>
                                    @endif
                                </div>
                                <input type="file" name="favicon" accept=".ico,image/*" 
                                    class="text-xs text-slate-400 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-slate-800 file:text-slate-200 hover:file:bg-slate-700 file:cursor-pointer cursor-pointer">
                            </div>
                            @if(!empty($landingSettings->favicon))
                                <p class="text-[10px] text-emerald-400 font-mono">Favicon aktif: {{ $landingSettings->favicon }}</p>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Section 2: Hero Section (Teks Utama Beranda) -->
                <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 shadow-xl space-y-5">
                    <div class="pb-3 border-b border-slate-800 flex items-center justify-between">
                        <h3 class="text-base font-bold text-white flex items-center gap-2">
                            <i class="fa-solid fa-bullhorn text-indigo-400"></i>
                            2. Hero Banner & Tombol Call to Action (CTA)
                        </h3>
                        <span class="text-[10px] text-slate-500 font-mono uppercase">Bagian Teratas Beranda</span>
                    </div>

                    <div class="space-y-1.5">
                        <label class="text-xs font-semibold text-slate-300">Badge Promosi Atas (Kecil)</label>
                        <input type="text" name="hero_badge" value="{{ old('hero_badge', $landingSettings->hero_badge ?? '⚡ SOFTWARE ERP RETAIL & KASIR POINT OF SALE #1 DI INDONESIA') }}" 
                            class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-700 rounded-xl text-xs text-white focus:outline-none focus:border-amber-500">
                    </div>

                    <div class="space-y-1.5">
                        <label class="text-xs font-semibold text-slate-300">Headline / Judul Utama</label>
                        <textarea name="hero_title" rows="2" required 
                            class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-700 rounded-xl text-xs text-white focus:outline-none focus:border-amber-500 leading-relaxed">{{ old('hero_title', $landingSettings->hero_title ?? 'Satu Platform Cerdas untuk Mengelola Banyak Cabang & Toko') }}</textarea>
                    </div>

                    <div class="space-y-1.5">
                        <label class="text-xs font-semibold text-slate-300">Subjudul / Deskripsi Penjelas</label>
                        <textarea name="hero_subtitle" rows="3" 
                            class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-700 rounded-xl text-xs text-white focus:outline-none focus:border-amber-500 leading-relaxed">{{ old('hero_subtitle', $landingSettings->hero_subtitle ?? 'Kelola kasir cepat, alur gudang fisik terverifikasi, proteksi harga modal anti-rugi, serta buku piutang dan slip gaji di semua cabang toko Anda dari satu dashboard terpusat.') }}</textarea>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5 pt-2">
                        <div class="p-4 rounded-xl bg-slate-950/60 border border-slate-800 space-y-3">
                            <p class="text-xs font-bold text-amber-400">Tombol Aksi Utama (Kuning)</p>
                            <div class="space-y-2">
                                <div>
                                    <label class="text-[11px] text-slate-400 block mb-1">Teks Tombol</label>
                                    <input type="text" name="cta_btn_primary_text" value="{{ old('cta_btn_primary_text', $landingSettings->cta_btn_primary_text ?? 'Coba Demo 1-Klik') }}" 
                                        class="w-full px-3 py-2 bg-slate-900 border border-slate-700 rounded-lg text-xs text-white focus:outline-none focus:border-amber-500">
                                </div>
                                <div>
                                    <label class="text-[11px] text-slate-400 block mb-1">Link Tujuan</label>
                                    <input type="text" name="cta_btn_primary_link" value="{{ old('cta_btn_primary_link', $landingSettings->cta_btn_primary_link ?? '/demo-login/admin') }}" 
                                        class="w-full px-3 py-2 bg-slate-900 border border-slate-700 rounded-lg text-xs text-white focus:outline-none focus:border-amber-500">
                                </div>
                            </div>
                        </div>

                        <div class="p-4 rounded-xl bg-slate-950/60 border border-slate-800 space-y-3">
                            <p class="text-xs font-bold text-emerald-400">Tombol Aksi Kedua (WhatsApp / Konsultasi)</p>
                            <div class="space-y-2">
                                <div>
                                    <label class="text-[11px] text-slate-400 block mb-1">Teks Tombol</label>
                                    <input type="text" name="cta_btn_secondary_text" value="{{ old('cta_btn_secondary_text', $landingSettings->cta_btn_secondary_text ?? 'Konsultasi WhatsApp') }}" 
                                        class="w-full px-3 py-2 bg-slate-900 border border-slate-700 rounded-lg text-xs text-white focus:outline-none focus:border-amber-500">
                                </div>
                                <div>
                                    <label class="text-[11px] text-slate-400 block mb-1">Link Tujuan (Kosongkan untuk otomatis link WhatsApp)</label>
                                    <input type="text" name="cta_btn_secondary_link" value="{{ old('cta_btn_secondary_link', $landingSettings->cta_btn_secondary_link ?? '#konsultasi') }}" 
                                        class="w-full px-3 py-2 bg-slate-900 border border-slate-700 rounded-lg text-xs text-white focus:outline-none focus:border-amber-500">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Section 3: WhatsApp & Marketing Konsultasi -->
                <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 shadow-xl space-y-5">
                    <div class="pb-3 border-b border-slate-800 flex items-center justify-between">
                        <h3 class="text-base font-bold text-white flex items-center gap-2">
                            <i class="fa-brands fa-whatsapp text-emerald-400"></i>
                            3. Integrasi Chat WhatsApp & Konsultasi Langsung
                        </h3>
                        <span class="text-[10px] text-slate-500 font-mono uppercase">Lead Generation</span>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                        <div class="space-y-1.5 md:col-span-1">
                            <label class="text-xs font-semibold text-slate-300">Nomor WhatsApp Admin</label>
                            <input type="text" name="wa_number" value="{{ old('wa_number', $landingSettings->wa_number ?? '6281234567890') }}" 
                                placeholder="Contoh: 6281234567890" 
                                class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-700 rounded-xl text-xs text-white focus:outline-none focus:border-amber-500 font-mono">
                            <p class="text-[11px] text-slate-500">Format internasional tanpa spasi atau strip (awalan 62).</p>
                        </div>

                        <div class="space-y-1.5 md:col-span-2">
                            <label class="text-xs font-semibold text-slate-300">Template Pesan WhatsApp Otomatis</label>
                            <textarea name="wa_message" rows="3" 
                                class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-700 rounded-xl text-xs text-white focus:outline-none focus:border-amber-500 leading-relaxed">{{ old('wa_message', $landingSettings->wa_message ?? 'Halo Admin VxPOS, saya tertarik untuk menggunakan software kasir & ERP toko ini.') }}</textarea>
                        </div>
                    </div>
                </div>

                <!-- Section 4: Statistik Social Proof & Harga Paket -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Card Metrik Angka -->
                    <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 shadow-xl space-y-4">
                        <div class="pb-3 border-b border-slate-800">
                            <h3 class="text-base font-bold text-white flex items-center gap-2">
                                <i class="fa-solid fa-chart-simple text-purple-400"></i>
                                4. Tampilan 3 Metrik Utama (Social Proof)
                            </h3>
                        </div>

                        <div class="space-y-3">
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="text-[11px] text-slate-400 block mb-1">Nilai Stat 1</label>
                                    <input type="text" name="stat_1_val" value="{{ old('stat_1_val', $landingSettings->stat_1_val ?? '10.000+') }}" 
                                        class="w-full px-3 py-2 bg-slate-950 border border-slate-700 rounded-lg text-xs text-white">
                                </div>
                                <div>
                                    <label class="text-[11px] text-slate-400 block mb-1">Label Stat 1</label>
                                    <input type="text" name="stat_1_label" value="{{ old('stat_1_label', $landingSettings->stat_1_label ?? 'Transaksi Diproses') }}" 
                                        class="w-full px-3 py-2 bg-slate-950 border border-slate-700 rounded-lg text-xs text-white">
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="text-[11px] text-slate-400 block mb-1">Nilai Stat 2</label>
                                    <input type="text" name="stat_2_val" value="{{ old('stat_2_val', $landingSettings->stat_2_val ?? '99.9%') }}" 
                                        class="w-full px-3 py-2 bg-slate-950 border border-slate-700 rounded-lg text-xs text-white">
                                </div>
                                <div>
                                    <label class="text-[11px] text-slate-400 block mb-1">Label Stat 2</label>
                                    <input type="text" name="stat_2_label" value="{{ old('stat_2_label', $landingSettings->stat_2_label ?? 'Akurasi Stok Gudang') }}" 
                                        class="w-full px-3 py-2 bg-slate-950 border border-slate-700 rounded-lg text-xs text-white">
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="text-[11px] text-slate-400 block mb-1">Nilai Stat 3</label>
                                    <input type="text" name="stat_3_val" value="{{ old('stat_3_val', $landingSettings->stat_3_val ?? '0%') }}" 
                                        class="w-full px-3 py-2 bg-slate-950 border border-slate-700 rounded-lg text-xs text-white">
                                </div>
                                <div>
                                    <label class="text-[11px] text-slate-400 block mb-1">Label Stat 3</label>
                                    <input type="text" name="stat_3_label" value="{{ old('stat_3_label', $landingSettings->stat_3_label ?? 'Kebocoran Diskon') }}" 
                                        class="w-full px-3 py-2 bg-slate-950 border border-slate-700 rounded-lg text-xs text-white">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Card Harga Paket -->
                    <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 shadow-xl space-y-4">
                        <div class="pb-3 border-b border-slate-800">
                            <h3 class="text-base font-bold text-white flex items-center gap-2">
                                <i class="fa-solid fa-tags text-teal-400"></i>
                                5. Label Harga Paket Langganan SaaS
                            </h3>
                        </div>

                        <div class="space-y-3">
                            <div>
                                <label class="text-[11px] text-slate-400 block mb-1">Harga Paket Starter (per bulan)</label>
                                <div class="relative">
                                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-xs text-slate-500 font-bold">Rp</span>
                                    <input type="text" name="pricing_starter" value="{{ old('pricing_starter', $landingSettings->pricing_starter ?? '99.000') }}" 
                                        class="w-full pl-9 pr-3 py-2 bg-slate-950 border border-slate-700 rounded-lg text-xs text-white">
                                </div>
                            </div>

                            <div>
                                <label class="text-[11px] text-slate-400 block mb-1">Harga Paket Pro (Paling Populer - per bulan)</label>
                                <div class="relative">
                                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-xs text-slate-500 font-bold">Rp</span>
                                    <input type="text" name="pricing_pro" value="{{ old('pricing_pro', $landingSettings->pricing_pro ?? '299.000') }}" 
                                        class="w-full pl-9 pr-3 py-2 bg-slate-950 border border-slate-700 rounded-lg text-xs text-white">
                                </div>
                            </div>

                            <div>
                                <label class="text-[11px] text-slate-400 block mb-1">Harga Paket Enterprise (per bulan)</label>
                                <div class="relative">
                                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-xs text-slate-500 font-bold">Rp</span>
                                    <input type="text" name="pricing_enterprise" value="{{ old('pricing_enterprise', $landingSettings->pricing_enterprise ?? '799.000') }}" 
                                        class="w-full pl-9 pr-3 py-2 bg-slate-950 border border-slate-700 rounded-lg text-xs text-white">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Section 5: Footer & Kontak Legal Perusahaan -->
                <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 shadow-xl space-y-5">
                    <div class="pb-3 border-b border-slate-800 flex items-center justify-between">
                        <h3 class="text-base font-bold text-white flex items-center gap-2">
                            <i class="fa-solid fa-building text-slate-400"></i>
                            6. Footer, Alamat & Legalitas Hak Cipta
                        </h3>
                        <span class="text-[10px] text-slate-500 font-mono uppercase">Footer Information</span>
                    </div>

                    <div class="space-y-1.5">
                        <label class="text-xs font-semibold text-slate-300">Deskripsi Singkat Footer</label>
                        <textarea name="footer_desc" rows="2" 
                            class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-700 rounded-xl text-xs text-white focus:outline-none focus:border-amber-500">{{ old('footer_desc', $landingSettings->footer_desc ?? 'VxPOS adalah ekosistem Cloud Point of Sale dan Enterprise Resource Planning modern yang didesain untuk mendigitalkan toko grosir, ritel, minimarket, dan cabang usaha di seluruh Indonesia.') }}</textarea>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="space-y-1.5">
                            <label class="text-xs font-semibold text-slate-300">Alamat Kantor / Domisili</label>
                            <input type="text" name="footer_address" value="{{ old('footer_address', $landingSettings->footer_address ?? 'Jakarta & Surabaya, Indonesia') }}" 
                                class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-700 rounded-xl text-xs text-white focus:outline-none focus:border-amber-500">
                        </div>

                        <div class="space-y-1.5">
                            <label class="text-xs font-semibold text-slate-300">Telepon Kontak Dukungan</label>
                            <input type="text" name="footer_phone" value="{{ old('footer_phone', $landingSettings->footer_phone ?? '+62 812-3456-7890') }}" 
                                class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-700 rounded-xl text-xs text-white focus:outline-none focus:border-amber-500">
                        </div>

                        <div class="space-y-1.5">
                            <label class="text-xs font-semibold text-slate-300">Email Dukungan</label>
                            <input type="email" name="footer_email" value="{{ old('footer_email', $landingSettings->footer_email ?? 'support@vxpos.id') }}" 
                                class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-700 rounded-xl text-xs text-white focus:outline-none focus:border-amber-500">
                        </div>
                    </div>

                    <div class="space-y-1.5 pt-1">
                        <label class="text-xs font-semibold text-slate-300">Teks Hak Cipta (Copyright Footer)</label>
                        <input type="text" name="copyright_text" value="{{ old('copyright_text', $landingSettings->copyright_text ?? 'VxPOS Point of Sale &bull; Hak Cipta by. Vicky Koroh') }}" 
                            class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-700 rounded-xl text-xs text-white focus:outline-none focus:border-amber-500">
                    </div>
                </div>

                <!-- Sticky Submit Save Bar -->
                <div class="sticky bottom-6 z-30 p-4 rounded-2xl bg-slate-900/95 border border-slate-700 backdrop-blur-md shadow-2xl flex flex-col sm:flex-row items-center justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl bg-amber-500/20 text-amber-400 flex items-center justify-center font-bold">
                            <i class="fa-solid fa-cloud-arrow-up"></i>
                        </div>
                        <div>
                            <h4 class="text-xs font-bold text-white">Publikasi CMS Landing Page</h4>
                            <p class="text-[11px] text-slate-400">Perubahan akan langsung tayang pada halaman beranda utama.</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-3 w-full sm:w-auto justify-end">
                        <a href="/" target="_blank" class="px-4 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-semibold border border-slate-700 transition">
                            Pratinjau Beranda
                        </a>
                        <button type="submit" class="px-6 py-2.5 rounded-xl bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-600 hover:to-amber-700 text-slate-950 font-black text-xs shadow-lg shadow-amber-500/20 transition flex items-center gap-2">
                            <i class="fa-solid fa-check-circle"></i>
                            <span>Simpan & Publikasikan Perubahan</span>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </main>

    <!-- Modal Tambah Toko Baru -->
    <div id="modalTambahToko" class="fixed inset-0 z-50 bg-slate-950/80 backdrop-blur-sm flex items-center justify-center p-4 hidden">
        <div class="bg-slate-900 border border-slate-800 rounded-2xl max-w-lg w-full p-6 shadow-2xl space-y-5 overflow-y-auto max-h-[90vh]">
            <div class="flex items-center justify-between pb-3 border-b border-slate-800">
                <h3 class="text-base font-bold text-white flex items-center gap-2">
                    <i class="fa-solid fa-store text-amber-400"></i>
                    Daftarkan Toko Mitra Baru
                </h3>
                <button onclick="document.getElementById('modalTambahToko').classList.add('hidden')" class="text-slate-400 hover:text-white">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <form action="{{ route('platform.toko.store') }}" method="POST" class="space-y-4">
                @csrf
                <div class="space-y-1">
                    <label class="text-xs font-semibold text-slate-300">Nama Toko Mitra</label>
                    <input type="text" name="nama_toko" required placeholder="Contoh: Toko Berkah Cabang 2" 
                        class="w-full px-3 py-2 bg-slate-950 border border-slate-700 rounded-xl text-xs text-white focus:outline-none focus:border-amber-500">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div class="space-y-1">
                        <label class="text-xs font-semibold text-slate-300">No. WhatsApp / HP</label>
                        <input type="text" name="no_telp" placeholder="08123456789" 
                            class="w-full px-3 py-2 bg-slate-950 border border-slate-700 rounded-xl text-xs text-white focus:outline-none focus:border-amber-500">
                    </div>
                    <div class="space-y-1">
                        <label class="text-xs font-semibold text-slate-300">Paket Langganan</label>
                        <select name="paket" required class="w-full px-3 py-2 bg-slate-950 border border-slate-700 rounded-xl text-xs text-white focus:outline-none focus:border-amber-500">
                            <option value="starter">Starter (Maks 3 User)</option>
                            <option value="pro" selected>Pro (Maks 10 User)</option>
                            <option value="enterprise">Enterprise (Unlimited User)</option>
                        </select>
                    </div>
                </div>

                <div class="space-y-1">
                    <label class="text-xs font-semibold text-slate-300">Alamat Toko</label>
                    <textarea name="alamat" rows="2" placeholder="Alamat lengkap toko..." 
                        class="w-full px-3 py-2 bg-slate-950 border border-slate-700 rounded-xl text-xs text-white focus:outline-none focus:border-amber-500"></textarea>
                </div>

                <div class="pt-3 border-t border-slate-800">
                    <p class="text-xs font-bold text-amber-400 mb-2">Akun Admin Pemilik Toko Pertama:</p>
                    <div class="space-y-3">
                        <div>
                            <input type="text" name="admin_nama" required placeholder="Nama Lengkap Admin Toko" 
                                class="w-full px-3 py-2 bg-slate-950 border border-slate-700 rounded-xl text-xs text-white focus:outline-none focus:border-amber-500">
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <input type="text" name="admin_username" required placeholder="Username Login" 
                                class="w-full px-3 py-2 bg-slate-950 border border-slate-700 rounded-xl text-xs text-white focus:outline-none focus:border-amber-500">
                            <input type="password" name="admin_password" required placeholder="Password Login" 
                                class="w-full px-3 py-2 bg-slate-950 border border-slate-700 rounded-xl text-xs text-white focus:outline-none focus:border-amber-500">
                        </div>
                    </div>
                </div>

                <div class="pt-3 flex items-center justify-end space-x-3">
                    <button type="button" onclick="document.getElementById('modalTambahToko').classList.add('hidden')" 
                        class="px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-xs font-semibold text-slate-300">
                        Batal
                    </button>
                    <button type="submit" 
                        class="px-5 py-2 rounded-xl bg-amber-500 hover:bg-amber-600 text-slate-950 text-xs font-bold shadow-lg shadow-amber-500/20">
                        Simpan & Daftarkan Toko
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Edit Toko -->
    <div id="modalEditToko" class="fixed inset-0 z-50 bg-slate-950/80 backdrop-blur-sm flex items-center justify-center p-4 hidden">
        <div class="bg-slate-900 border border-slate-800 rounded-2xl max-w-lg w-full p-6 shadow-2xl space-y-5">
            <div class="flex items-center justify-between pb-3 border-b border-slate-800">
                <h3 class="text-base font-bold text-white flex items-center gap-2">
                    <i class="fa-solid fa-sliders text-indigo-400"></i>
                    Edit Data & Paket Toko
                </h3>
                <button onclick="document.getElementById('modalEditToko').classList.add('hidden')" class="text-slate-400 hover:text-white">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <form id="formEditToko" method="POST" class="space-y-4">
                @csrf
                @method('PUT')
                <div class="space-y-1">
                    <label class="text-xs font-semibold text-slate-300">Nama Toko</label>
                    <input type="text" id="editNamaToko" name="nama_toko" required 
                        class="w-full px-3 py-2 bg-slate-950 border border-slate-700 rounded-xl text-xs text-white focus:outline-none focus:border-amber-500">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div class="space-y-1">
                        <label class="text-xs font-semibold text-slate-300">No. WhatsApp / HP</label>
                        <input type="text" id="editNoTelp" name="no_telp" 
                            class="w-full px-3 py-2 bg-slate-950 border border-slate-700 rounded-xl text-xs text-white focus:outline-none focus:border-amber-500">
                    </div>
                    <div class="space-y-1">
                        <label class="text-xs font-semibold text-slate-300">Paket Langganan</label>
                        <select id="editPaket" name="paket" required class="w-full px-3 py-2 bg-slate-950 border border-slate-700 rounded-xl text-xs text-white focus:outline-none focus:border-amber-500">
                            <option value="starter">Starter (Maks 3 User)</option>
                            <option value="pro">Pro (Maks 10 User)</option>
                            <option value="enterprise">Enterprise (Unlimited User)</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div class="space-y-1">
                        <label class="text-xs font-semibold text-slate-300">Status Toko</label>
                        <select id="editStatus" name="status" required class="w-full px-3 py-2 bg-slate-950 border border-slate-700 rounded-xl text-xs text-white focus:outline-none focus:border-amber-500">
                            <option value="aktif">Aktif</option>
                            <option value="nonaktif">Nonaktif</option>
                            <option value="suspended">Suspended</option>
                        </select>
                    </div>
                    <div class="space-y-1">
                        <label class="text-xs font-semibold text-slate-300">Tanggal Kadaluarsa</label>
                        <input type="date" id="editExpiredAt" name="expired_at" 
                            class="w-full px-3 py-2 bg-slate-950 border border-slate-700 rounded-xl text-xs text-white focus:outline-none focus:border-amber-500">
                    </div>
                </div>

                <div class="space-y-1">
                    <label class="text-xs font-semibold text-slate-300">Alamat Toko</label>
                    <textarea id="editAlamat" name="alamat" rows="2" 
                        class="w-full px-3 py-2 bg-slate-950 border border-slate-700 rounded-xl text-xs text-white focus:outline-none focus:border-amber-500"></textarea>
                </div>

                <div class="pt-3 flex items-center justify-end space-x-3">
                    <button type="button" onclick="document.getElementById('modalEditToko').classList.add('hidden')" 
                        class="px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-xs font-semibold text-slate-300">
                        Batal
                    </button>
                    <button type="submit" 
                        class="px-5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold shadow-lg">
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Footer Copyright -->
    <footer class="mt-auto py-6 border-t border-slate-900 bg-slate-950 text-center text-xs text-slate-500">
        <p>VxPOS Enterprise Multi-Tenant &copy; 2026. Hak Cipta by. <strong>Vicky Koroh</strong>. Seluruh hak cipta dilindungi.</p>
    </footer>

    <!-- Global Finnova Confirm Modal -->
    <div id="globalFinnovaModal" class="fixed inset-0 z-[9999] bg-slate-950/80 backdrop-blur-sm hidden items-center justify-center p-4 transition-all duration-300">
        <div id="globalFinnovaCard" class="bg-slate-900 rounded-3xl p-6 sm:p-7 max-w-md w-full shadow-2xl border border-slate-800 transform scale-95 opacity-0 transition-all duration-200 text-white">
            <div class="text-center">
                <div id="globalModalIconBg" class="w-16 h-16 rounded-2xl bg-amber-500/10 text-amber-400 mx-auto flex items-center justify-center mb-4 text-2xl shadow-inner border border-amber-500/20">
                    <i id="globalModalIcon" class="fa-solid fa-triangle-exclamation"></i>
                </div>
                <h3 id="globalModalTitle" class="text-lg font-extrabold text-white tracking-tight">Konfirmasi Tindakan</h3>
                <p id="globalModalMessage" class="text-xs text-slate-400 mt-2 leading-relaxed px-2">Apakah Anda yakin ingin melanjutkan tindakan ini?</p>
                
                <div class="mt-6 flex flex-col-reverse sm:flex-row gap-2.5">
                    <button type="button" id="globalModalCancelBtn" class="w-full py-2.5 px-4 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 font-bold text-xs transition-colors cursor-pointer">
                        Batal
                    </button>
                    <button type="button" id="globalModalConfirmBtn" class="w-full py-2.5 px-4 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs shadow-md shadow-indigo-600/30 transition-all cursor-pointer">
                        Ya, Lanjutkan
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        function bukaModalEdit(toko) {
            document.getElementById('formEditToko').action = '/platform/toko/' + toko.id;
            document.getElementById('editNamaToko').value = toko.nama_toko || '';
            document.getElementById('editNoTelp').value = toko.no_telp || '';
            document.getElementById('editAlamat').value = toko.alamat || '';
            document.getElementById('editPaket').value = toko.paket || 'starter';
            document.getElementById('editStatus').value = toko.status || 'aktif';
            document.getElementById('editExpiredAt').value = toko.expired_at ? toko.expired_at.substring(0, 10) : '';
            document.getElementById('modalEditToko').classList.remove('hidden');
        }

        // Tab Switching System for Platform
        function switchPlatformTab(tabName) {
            const tabs = ['toko', 'traffic', 'cms'];
            tabs.forEach(t => {
                const content = document.getElementById('tabContent' + t.charAt(0).toUpperCase() + t.slice(1));
                const btn = document.getElementById('btnTab' + t.charAt(0).toUpperCase() + t.slice(1));
                if (content && btn) {
                    if (t === tabName) {
                        content.classList.remove('hidden');
                        btn.className = 'platform-tab-btn px-4 py-2.5 rounded-xl text-xs font-bold transition-all flex items-center gap-2 bg-amber-500 text-slate-950 shadow-md';
                    } else {
                        content.classList.add('hidden');
                        btn.className = 'platform-tab-btn px-4 py-2.5 rounded-xl text-xs font-bold text-slate-400 hover:text-white transition-all flex items-center gap-2';
                    }
                }
            });
            try {
                history.replaceState(null, null, '#' + tabName);
            } catch (e) {}

            if (tabName === 'traffic') {
                fetchLiveTrafficData();
            }
        }

        // Live Traffic Polling System
        let autoRefreshInterval = null;
        let isAutoRefreshActive = true;

        function toggleAutoRefresh() {
            isAutoRefreshActive = !isAutoRefreshActive;
            const btn = document.getElementById('btnToggleAutoRefresh');
            const icon = document.getElementById('autoRefreshIcon');
            const text = document.getElementById('autoRefreshText');
            if (isAutoRefreshActive) {
                icon.className = 'fa-solid fa-arrows-rotate animate-spin text-xs';
                text.textContent = 'Auto-Refresh: ON (5s)';
                btn.className = 'px-3.5 py-2 rounded-xl bg-slate-950 hover:bg-slate-800 border border-slate-800 text-xs font-semibold text-emerald-400 flex items-center gap-2 transition';
                startAutoRefresh();
            } else {
                icon.className = 'fa-solid fa-pause text-xs';
                text.textContent = 'Auto-Refresh: OFF';
                btn.className = 'px-3.5 py-2 rounded-xl bg-slate-950 hover:bg-slate-800 border border-slate-800 text-xs font-semibold text-slate-400 flex items-center gap-2 transition';
                if (autoRefreshInterval) clearInterval(autoRefreshInterval);
            }
        }

        function startAutoRefresh() {
            if (autoRefreshInterval) clearInterval(autoRefreshInterval);
            autoRefreshInterval = setInterval(() => {
                if (isAutoRefreshActive) {
                    fetchLiveTrafficData();
                }
            }, 5000);
        }

        async function fetchLiveTrafficData() {
            try {
                const res = await fetch("{{ route('platform.traffic.data') }}", {
                    headers: { 'Accept': 'application/json' }
                });
                if (!res.ok) return;
                const data = await res.json();
                
                // Update badge & counts
                const countBadge = document.getElementById('tabTrafficBadge');
                const onlineCountHeader = document.getElementById('liveOnlineCountBadge');
                const clock = document.getElementById('liveClock');
                if (countBadge) countBadge.textContent = `${data.total_online} Online`;
                if (onlineCountHeader) onlineCountHeader.textContent = `${data.total_online} Sesi Aktif`;
                if (clock && data.server_time) clock.textContent = `${data.server_time} WIB`;

                // Update Table Rows
                const tbody = document.getElementById('liveTrafficTableBody');
                if (tbody) {
                    if (data.online_users && data.online_users.length > 0) {
                        tbody.innerHTML = data.online_users.map(u => {
                            const initial = (u.nama || u.username).substring(0, 2).toUpperCase();
                            const roleColor = u.role === 'admin' ? 'bg-indigo-500/20 text-indigo-300 border border-indigo-500/30' :
                                              u.role === 'kasir' ? 'bg-emerald-500/20 text-emerald-300 border border-emerald-500/30' :
                                              u.role === 'sales' ? 'bg-amber-500/20 text-amber-300 border border-amber-500/30' :
                                              'bg-purple-500/20 text-purple-300 border border-purple-500/30';
                            const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
                            return `
                                <tr class="hover:bg-slate-800/40 transition">
                                    <td class="py-4 px-4">
                                        <div class="flex items-center space-x-3">
                                            <div class="w-9 h-9 rounded-xl bg-slate-800 border border-slate-700 flex items-center justify-center font-bold text-white text-xs">
                                                ${initial}
                                            </div>
                                            <div>
                                                <p class="font-bold text-white">${u.nama || u.username}</p>
                                                <div class="flex items-center gap-1.5 mt-0.5">
                                                    <span class="text-[10px] text-slate-400 font-mono">&#64;${u.username}</span>
                                                    <span class="px-1.5 py-0.2 rounded text-[9px] font-bold uppercase ${roleColor}">${u.role}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-4 px-4">
                                        <div class="font-semibold text-white">${u.nama_toko || 'Toko #' + u.toko_id}</div>
                                        <span class="text-[10px] text-slate-500 font-mono">ID Toko: ${u.toko_id}</span>
                                    </td>
                                    <td class="py-4 px-4">
                                        <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-bold bg-indigo-500/10 text-indigo-300 border border-indigo-500/20">
                                            <i class="fa-solid fa-compass text-[11px] text-indigo-400"></i>
                                            <span>${u.feature_name || 'Menjelajah Halaman'}</span>
                                        </div>
                                        <p class="text-[10px] text-slate-500 font-mono mt-1">${u.url} (${u.method || 'GET'})</p>
                                    </td>
                                    <td class="py-4 px-4">
                                        <p class="font-mono text-slate-300 text-xs">${u.ip_address || '-'}</p>
                                        <p class="text-[10px] text-slate-500 truncate max-w-[180px]" title="${u.user_agent || ''}">${u.user_agent || '-'}</p>
                                    </td>
                                    <td class="py-4 px-4">
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                                            <i class="fa-solid fa-clock text-[9px]"></i>
                                            ${u.time_ago || 'Baru saja'}
                                        </span>
                                    </td>
                                    <td class="py-4 px-4 text-center">
                                        <form action="/platform/traffic/kick/${u.user_id}" method="POST" 
                                            data-confirm="Putuskan sesi login pengguna [${u.nama || u.username}] secara paksa?" 
                                            data-confirm-type="danger" 
                                            data-confirm-title="Putuskan Sesi Pengguna" 
                                            data-confirm-btn="Ya, Putuskan Sesi">
                                            <input type="hidden" name="_token" value="${csrf}">
                                            <button type="submit" class="px-3 py-1.5 rounded-lg bg-rose-500/10 hover:bg-rose-500/25 text-rose-400 border border-rose-500/30 text-xs font-bold transition flex items-center gap-1.5 mx-auto" title="Kick Out">
                                                <i class="fa-solid fa-user-slash"></i>
                                                <span>Putuskan Sesi</span>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            `;
                        }).join('');
                    } else {
                        tbody.innerHTML = `
                            <tr>
                                <td colspan="6" class="py-12 text-center">
                                    <div class="w-14 h-14 rounded-2xl bg-slate-800/80 text-slate-500 mx-auto flex items-center justify-center text-2xl mb-3 border border-slate-700/50">
                                        <i class="fa-solid fa-radar text-emerald-400 animate-pulse"></i>
                                    </div>
                                    <h4 class="text-sm font-bold text-white">Radar Aktif: Belum Ada Staf/Kasir Online</h4>
                                    <p class="text-xs text-slate-400 mt-1 max-w-md mx-auto">
                                        Saat kasir, admin toko, sales, atau gudang login dan berinteraksi di toko mereka, aktivitas dan sesi mereka akan langsung terdeteksi seketika di sini.
                                    </p>
                                </td>
                            </tr>
                        `;
                    }
                }

                // Update Logs Container
                const logsContainer = document.getElementById('liveTrafficLogsContainer');
                if (logsContainer && data.recent_logs && data.recent_logs.length > 0) {
                    logsContainer.innerHTML = data.recent_logs.map(l => {
                        const roleColor = l.role === 'admin' ? 'bg-indigo-500/20 text-indigo-300' : 'bg-emerald-500/20 text-emerald-300';
                        return `
                            <div class="p-3 rounded-xl bg-slate-950/60 border border-slate-800/80 flex flex-col sm:flex-row sm:items-center justify-between gap-2 text-xs hover:border-slate-700 transition">
                                <div class="flex items-center gap-3">
                                    <span class="font-mono text-[10px] text-amber-400 font-bold bg-slate-900 px-2 py-0.5 rounded border border-slate-800">
                                        ${l.time_formatted || ''}
                                    </span>
                                    <div>
                                        <span class="font-bold text-white">${l.nama || l.username}</span>
                                        <span class="text-slate-400">&#64;${l.nama_toko || 'Toko #' + l.toko_id}</span>
                                        <span class="text-[10px] px-1.5 py-0.2 rounded font-bold uppercase ml-1 ${roleColor}">
                                            ${l.role}
                                        </span>
                                    </div>
                                </div>

                                <div class="flex items-center gap-3 sm:justify-end">
                                    <div class="text-left sm:text-right">
                                        <span class="font-semibold text-slate-200">${l.feature_name}</span>
                                        <span class="font-mono text-[10px] text-slate-500 block">${l.method} ${l.url}</span>
                                    </div>
                                    <span class="font-mono text-[10px] text-slate-400 bg-slate-900 px-2 py-1 rounded border border-slate-800 hidden md:inline-block">
                                        ${l.ip_address || '-'}
                                    </span>
                                </div>
                            </div>
                        `;
                    }).join('');
                }
            } catch (err) {
                console.error("Traffic polling error:", err);
            }
        }

        // Initialize Tab based on URL Hash
        document.addEventListener('DOMContentLoaded', () => {
            const hash = window.location.hash.replace('#', '');
            if (hash === 'traffic' || hash === 'cms' || hash === 'toko') {
                switchPlatformTab(hash);
            }
            startAutoRefresh();
        });

        window.finnovaConfirm = function(options) {
            return new Promise((resolve) => {
                const modal = document.getElementById('globalFinnovaModal');
                const card = document.getElementById('globalFinnovaCard');
                const titleEl = document.getElementById('globalModalTitle');
                const msgEl = document.getElementById('globalModalMessage');
                const confirmBtn = document.getElementById('globalModalConfirmBtn');
                const cancelBtn = document.getElementById('globalModalCancelBtn');
                const iconBg = document.getElementById('globalModalIconBg');
                const iconEl = document.getElementById('globalModalIcon');

                titleEl.textContent = options.title || 'Konfirmasi Tindakan';
                msgEl.textContent = options.message || 'Apakah Anda yakin?';
                confirmBtn.textContent = options.confirmText || 'Ya, Lanjutkan';
                cancelBtn.textContent = options.cancelText || 'Batal';

                const type = options.type || 'warning';
                if (type === 'danger') {
                    iconBg.className = 'w-16 h-16 rounded-2xl bg-rose-500/10 text-rose-400 mx-auto flex items-center justify-center mb-4 text-2xl border border-rose-500/20';
                    iconEl.className = 'fa-solid fa-triangle-exclamation';
                    confirmBtn.className = 'w-full py-2.5 px-4 rounded-xl bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs shadow-md transition-all cursor-pointer';
                } else if (type === 'success') {
                    iconBg.className = 'w-16 h-16 rounded-2xl bg-emerald-500/10 text-emerald-400 mx-auto flex items-center justify-center mb-4 text-2xl border border-emerald-500/20';
                    iconEl.className = 'fa-solid fa-circle-check';
                    confirmBtn.className = 'w-full py-2.5 px-4 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-md transition-all cursor-pointer';
                } else {
                    iconBg.className = 'w-16 h-16 rounded-2xl bg-indigo-500/10 text-indigo-400 mx-auto flex items-center justify-center mb-4 text-2xl border border-indigo-500/20';
                    iconEl.className = 'fa-solid fa-circle-question';
                    confirmBtn.className = 'w-full py-2.5 px-4 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs shadow-md transition-all cursor-pointer';
                }

                modal.classList.remove('hidden');
                modal.classList.add('flex');
                requestAnimationFrame(() => {
                    card.classList.remove('scale-95', 'opacity-0');
                    card.classList.add('scale-100', 'opacity-100');
                });

                function cleanup(confirmed) {
                    card.classList.remove('scale-100', 'opacity-100');
                    card.classList.add('scale-95', 'opacity-0');
                    setTimeout(() => {
                        modal.classList.remove('flex');
                        modal.classList.add('hidden');
                    }, 180);
                    confirmBtn.onclick = null;
                    cancelBtn.onclick = null;
                    modal.onclick = null;
                    resolve(confirmed);
                }

                confirmBtn.onclick = () => cleanup(true);
                cancelBtn.onclick = () => cleanup(false);
                modal.onclick = (e) => { if (e.target === modal) cleanup(false); };
            });
        };

        document.addEventListener('submit', function(e) {
            const form = e.target;
            const confirmMsg = form.getAttribute('data-confirm');
            if (confirmMsg && !form.dataset.confirmed) {
                e.preventDefault();
                window.finnovaConfirm({
                    title: form.getAttribute('data-confirm-title') || 'Konfirmasi',
                    message: confirmMsg,
                    type: form.getAttribute('data-confirm-type') || 'warning',
                    confirmText: form.getAttribute('data-confirm-btn') || 'Ya, Lanjutkan'
                }).then(confirmed => {
                    if (confirmed) {
                        form.dataset.confirmed = 'true';
                        form.submit();
                    }
                });
            }
        });

        document.addEventListener('click', function(e) {
            const target = e.target.closest('a[data-confirm]');
            if (target && !target.dataset.confirmed) {
                e.preventDefault();
                window.finnovaConfirm({
                    title: target.getAttribute('data-confirm-title') || 'Konfirmasi',
                    message: target.getAttribute('data-confirm'),
                    type: target.getAttribute('data-confirm-type') || 'warning',
                    confirmText: target.getAttribute('data-confirm-btn') || 'Ya, Lanjutkan'
                }).then(confirmed => {
                    if (confirmed) {
                        target.dataset.confirmed = 'true';
                        window.location.href = target.href;
                    }
                });
            }
        });
    </script>
</body>
</html>
