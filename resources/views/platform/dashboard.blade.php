<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
                <a href="{{ route('platform.repair_db') }}" class="hidden md:inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-semibold bg-slate-800 hover:bg-slate-700 text-slate-300 border border-slate-700 transition" onclick="return confirm('Jalankan sinkronisasi dan perbaikan skema database?')">
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
                    <a href="{{ route('platform.demo.generate') }}" onclick="return confirm('Buat atau segarkan data simulasi Toko Retail Demo?')" class="px-5 py-2.5 rounded-xl bg-gradient-to-r from-emerald-500 to-teal-600 hover:from-emerald-600 hover:to-teal-700 text-slate-950 font-bold text-sm shadow-lg shadow-emerald-500/20 transition flex items-center space-x-2">
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
                <a href="{{ route('platform.demo.generate') }}" onclick="return confirm('Segarkan data simulasi Toko Demo?')" class="px-3.5 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-semibold border border-slate-700 transition flex items-center gap-1.5">
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
                                            <form action="{{ route('platform.toko.destroy', $toko->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus toko mitra ini?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="px-2.5 py-1.5 rounded-lg bg-rose-500/10 hover:bg-rose-500/20 text-rose-400 border border-rose-500/30 text-xs font-semibold transition" title="Hapus Toko">
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
    </script>
</body>
</html>
