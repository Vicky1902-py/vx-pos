@php
    $pengaturan = null;
    $tokoAktif = null;
    $tokoId = null;
    $isPlatformAdmin = false;
    try {
        $tokoAktif = \App\Services\TenantManager::getActiveToko();
        $tokoId = \App\Services\TenantManager::getTokoId();
        if (\Illuminate\Support\Facades\Schema::hasTable('pengaturan_toko')) {
            $pengaturan = \Illuminate\Support\Facades\DB::table('pengaturan_toko')->where('toko_id', $tokoId)->first() 
                        ?? \Illuminate\Support\Facades\DB::table('pengaturan_toko')->first();
        }
        $isPlatformAdmin = \App\Services\TenantManager::isPlatformAdmin();
    } catch (\Throwable $e) {}

    $logoPath = ($tokoAktif && !empty($tokoAktif->logo)) ? asset('uploads/logo/' . $tokoAktif->logo) 
                : (($pengaturan && !empty($pengaturan->logo)) ? asset('uploads/logo/' . $pengaturan->logo) : null);
    $namaToko = $tokoAktif->nama_toko ?? ($pengaturan->nama_toko ?? 'VxPOS');
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>@yield('title') - {{ $namaToko }}</title>
    
    @if($logoPath)
        <link rel="icon" type="image/png" href="{{ $logoPath }}">
    @else
        <link rel="icon" href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><text y=%22.9em%22 font-size=%2290%22>⚡</text></svg>">
    @endif

    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f4f6fc; color: #1e293b; }
        
        .sidebar-active {
            background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%) !important;
            color: #ffffff !important;
            box-shadow: 0 8px 20px -4px rgba(99, 102, 241, 0.45);
        }
        .sidebar-active i {
            color: #ffffff !important;
        }

        /* Finnova Capsule Pills */
        .pill-capsule {
            background: #121422;
            border-radius: 9999px;
            padding: 4px;
        }
        .pill-item {
            padding: 6px 16px;
            border-radius: 9999px;
            font-size: 12px;
            font-weight: 700;
            transition: all 0.2s ease;
            color: #94a3b8;
        }
        .pill-item:hover {
            color: #ffffff;
        }
        .pill-item-active {
            background: #6366f1;
            color: #ffffff !important;
            box-shadow: 0 4px 12px rgba(99, 102, 241, 0.4);
        }

        /* Scrollbar Premium */
        ::-webkit-scrollbar { width: 5px; height: 5px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 999px; }
        ::-webkit-scrollbar-thumb:hover { background: #94a3b8; }

        /* Sidebar Collapsed State (Auxiliary Pane) */
        .sidebar-collapsed {
            width: 5rem !important; /* w-20 */
        }
        .sidebar-collapsed .sidebar-label,
        .sidebar-collapsed .sidebar-heading,
        .sidebar-collapsed .sidebar-full-logo,
        .sidebar-collapsed .sidebar-badge {
            display: none !important;
        }
        .sidebar-collapsed .sidebar-link {
            justify-content: center !important;
            padding-left: 0 !important;
            padding-right: 0 !important;
        }
        .sidebar-collapsed .sidebar-link i {
            margin-right: 0 !important;
            font-size: 18px !important;
        }
        .sidebar-collapsed .sidebar-mini-logo {
            display: flex !important;
        }
        .content-collapsed {
            margin-left: 5rem !important; /* lg:ml-20 */
        }

        @media (max-width: 1023px) {
            main table, main .table-responsive { 
                display: block; 
                width: 100%; 
                overflow-x: auto; 
                -webkit-overflow-scrolling: touch; 
            }
        }
    </style>
</head>
<body class="overflow-x-hidden relative min-h-screen">

    <!-- Overlay Mobile -->
    <div id="sidebar-overlay" class="fixed inset-0 bg-slate-900/60 z-30 hidden lg:hidden backdrop-blur-sm transition-opacity cursor-pointer"></div>

    <!-- Sidebar Menu (Collapsible Auxiliary Pane) -->
    <aside id="sidebar" class="fixed top-0 left-0 z-40 w-64 h-screen transition-all duration-300 -translate-x-full lg:translate-x-0 bg-white border-r border-slate-100 overflow-y-auto shadow-sm flex flex-col">
        <div class="h-full px-3 py-4 flex flex-col justify-between">
            
            <div>
                <!-- Brand Header & Collapse Toggle Button -->
                <div class="flex items-center justify-between px-2 mb-6 mt-1">
                    <!-- Expanded Logo -->
                    <div class="sidebar-full-logo flex items-center gap-2.5 overflow-hidden">
                        @if($logoPath)
                            <img src="{{ $logoPath }}" alt="{{ $namaToko }}" class="max-h-9 w-auto object-contain drop-shadow-sm">
                            <span class="text-sm font-extrabold text-slate-900 tracking-tight leading-tight line-clamp-1">{{ $namaToko }}</span>
                        @else
                            <div class="w-8 h-8 rounded-xl bg-gradient-to-tr from-indigo-500 to-indigo-600 flex items-center justify-center shadow-md shadow-indigo-500/30 text-white font-black text-xs">
                                VX
                            </div>
                            <span class="text-lg font-black text-slate-900 tracking-tight">Vx<span class="text-indigo-600">POS</span></span>
                        @endif
                    </div>

                    <!-- Mini Collapsed Logo (Only shown when collapsed) -->
                    <div class="sidebar-mini-logo hidden w-full justify-center">
                        <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-indigo-500 to-indigo-600 flex items-center justify-center shadow-md shadow-indigo-500/30 text-white font-black text-sm">
                            <i class="fa-solid fa-bolt"></i>
                        </div>
                    </div>

                    <!-- Desktop Sidebar Collapse Toggle Button (Auxiliary Pane Toggle) -->
                    <button id="toggle-sidebar-collapse" title="Ciutkan / Lebarkan Menu (Icon Mode)" class="hidden lg:flex w-7 h-7 rounded-lg bg-slate-100 hover:bg-indigo-50 hover:text-indigo-600 text-slate-400 items-center justify-center transition-all cursor-pointer">
                        <i id="collapse-icon" class="fa-solid fa-angles-left text-xs transition-transform duration-300"></i>
                    </button>
                </div>
                
                @php
                    $currentUser = Auth::user();
                    $aksesArr = json_decode($currentUser->hak_akses, true) ?? [];
                    $isSuperAdmin = $currentUser->role === 'superadmin';
                    $isAdminToko = in_array($currentUser->role, ['admin', 'admin_toko']);
                    $isGod = $isSuperAdmin || $isAdminToko;

                    $roleDefaults = \App\Http\Middleware\AksesModul::getDefaultPermissionsByRole($currentUser->role ?? '');
                    $effectiveAkses = array_unique(array_merge($aksesArr, $roleDefaults));

                    $canAccess = function($modul) use ($isGod, $effectiveAkses) {
                        if ($isGod) return true;
                        return in_array($modul, $effectiveAkses);
                    };
                @endphp

                <!-- Nav Menu Items -->
                <ul class="space-y-1 font-semibold text-xs">
                    <!-- Menu Utama -->
                    <p class="sidebar-heading text-[10px] uppercase text-slate-400 font-extrabold px-3 mb-1.5 mt-2 tracking-wider">Utama</p>
                    
                    <li>
                        <a href="{{ route('superadmin.dashboard') }}" title="Dashboard" class="sidebar-link flex items-center p-2.5 rounded-xl group transition-all {{ request()->is('superadmin/dashboard') ? 'sidebar-active' : 'text-slate-600 hover:bg-slate-50' }}">
                            <i class="fa-solid fa-house-chimney w-5 text-center text-slate-400 group-hover:text-indigo-600 transition-colors"></i>
                            <span class="sidebar-label ml-2.5 text-xs">Dashboard</span>
                        </a>
                    </li>
                    
                    <!-- DATA MASTER -->
                    @if($canAccess('master_barang') || $canAccess('manajemen_harga'))
                    <p class="sidebar-heading text-[10px] uppercase text-slate-400 font-extrabold px-3 mb-1.5 mt-5 tracking-wider">Katalog</p>
                    @endif
                    
                    @if($canAccess('master_barang'))
                    <li>
                        <a href="{{ route('superadmin.barang.index') }}" title="Master Barang" class="sidebar-link flex items-center p-2.5 rounded-xl group transition-all {{ request()->is('superadmin/barang*') ? 'sidebar-active' : 'text-slate-600 hover:bg-slate-50' }}">
                            <i class="fa-solid fa-box-open w-5 text-center text-slate-400 group-hover:text-indigo-600 transition-colors"></i>
                            <span class="sidebar-label ml-2.5 text-xs">Master Barang</span>
                        </a>
                    </li>
                    @endif
                    
                    @if($canAccess('manajemen_harga'))
                    <li>
                        <a href="{{ route('superadmin.harga.index') }}" title="Manajemen Harga" class="sidebar-link flex items-center p-2.5 rounded-xl group transition-all {{ request()->is('superadmin/harga*') ? 'sidebar-active' : 'text-slate-600 hover:bg-slate-50' }}">
                            <i class="fa-solid fa-tags w-5 text-center text-slate-400 group-hover:text-indigo-600 transition-colors"></i>
                            <span class="sidebar-label ml-2.5 text-xs">Manajemen Harga</span>
                        </a>
                    </li>
                    @endif

                    <!-- OPERASIONAL -->
                    @if($canAccess('transaksi_sales') || $canAccess('validasi_kasir') || $canAccess('stok_gudang'))
                    <p class="sidebar-heading text-[10px] uppercase text-slate-400 font-extrabold px-3 mb-1.5 mt-5 tracking-wider">Operasional</p>
                    @endif
                    
                    @if($canAccess('transaksi_sales'))
                    <li>
                        <a href="{{ route('superadmin.transaksi.create') }}" title="Kasir / Input POS" class="sidebar-link flex items-center p-2.5 rounded-xl group transition-all {{ request()->is('superadmin/transaksi/create') ? 'sidebar-active' : 'text-slate-600 hover:bg-slate-50' }}">
                            <i class="fa-solid fa-cash-register w-5 text-center text-emerald-500 transition-colors"></i>
                            <span class="sidebar-label ml-2.5 text-xs font-bold text-emerald-600">Kasir / POS</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('superadmin.transaksi.index') }}" title="Riwayat Transaksi" class="sidebar-link flex items-center p-2.5 rounded-xl group transition-all {{ request()->is('superadmin/transaksi') ? 'sidebar-active' : 'text-slate-600 hover:bg-slate-50' }}">
                            <i class="fa-solid fa-receipt w-5 text-center text-slate-400 group-hover:text-indigo-600 transition-colors"></i>
                            <span class="sidebar-label ml-2.5 text-xs">Riwayat Transaksi</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('superadmin.pelanggan.index') }}" title="Buku Pelanggan" class="sidebar-link flex items-center p-2.5 rounded-xl group transition-all {{ request()->is('superadmin/pelanggan*') ? 'sidebar-active' : 'text-slate-600 hover:bg-slate-50' }}">
                            <i class="fa-solid fa-address-book w-5 text-center text-slate-400 group-hover:text-indigo-600 transition-colors"></i>
                            <span class="sidebar-label ml-2.5 text-xs">Buku Pelanggan</span>
                        </a>
                    </li>
                    @endif
                    
                    @if($canAccess('validasi_kasir'))
                    <li>
                        <a href="{{ route('superadmin.kasir.index') }}" title="Validasi Kasir" class="sidebar-link flex items-center p-2.5 rounded-xl group transition-all {{ request()->is('superadmin/kasir*') ? 'sidebar-active' : 'text-slate-600 hover:bg-slate-50' }}">
                            <i class="fa-solid fa-file-invoice-dollar w-5 text-center text-slate-400 group-hover:text-indigo-600 transition-colors"></i>
                            <span class="sidebar-label ml-2.5 text-xs">Validasi Kasir</span>
                        </a>
                    </li>
                    @endif
                    
                    @if($canAccess('stok_gudang'))
                    <li>
                        <a href="{{ route('superadmin.gudang.index') }}" title="Manajemen Gudang" class="sidebar-link flex items-center p-2.5 rounded-xl group transition-all {{ request()->is('superadmin/gudang*') ? 'sidebar-active' : 'text-slate-600 hover:bg-slate-50' }}">
                            <i class="fa-solid fa-boxes-stacked w-5 text-center text-amber-500 transition-colors"></i>
                            <span class="sidebar-label ml-2.5 text-xs">Gudang & Stok</span>
                        </a>
                    </li>
                    @endif
                    
                    <!-- KEUANGAN -->
                    @if($canAccess('laporan_penjualan') || $canAccess('kelola_bonus'))
                    <p class="sidebar-heading text-[10px] uppercase text-slate-400 font-extrabold px-3 mb-1.5 mt-5 tracking-wider">Keuangan</p>
                    @endif
                    
                    @if($canAccess('laporan_penjualan'))
                    <li>
                        <a href="{{ route('superadmin.laporan.index') }}" title="Pusat Laporan" class="sidebar-link flex items-center p-2.5 rounded-xl group transition-all {{ request()->is('superadmin/laporan*') ? 'sidebar-active' : 'text-slate-600 hover:bg-slate-50' }}">
                            <i class="fa-solid fa-chart-pie w-5 text-center text-slate-400 group-hover:text-indigo-600 transition-colors"></i>
                            <span class="sidebar-label ml-2.5 text-xs">Pusat Laporan</span>
                        </a>
                    </li>
                    @endif
                    
                    @if($canAccess('kelola_bonus'))
                    <li>
                        <a href="{{ route('superadmin.bonus.index') }}" title="Bonus & Komisi" class="sidebar-link flex items-center p-2.5 rounded-xl group transition-all {{ request()->is('superadmin/bonus*') ? 'sidebar-active' : 'text-slate-600 hover:bg-slate-50' }}">
                            <i class="fa-solid fa-hand-holding-dollar w-5 text-center text-slate-400 group-hover:text-indigo-600 transition-colors"></i>
                            <span class="sidebar-label ml-2.5 text-xs">Bonus & Komisi</span>
                        </a>
                    </li>
                    @endif

                    <!-- SISTEM & PENGATURAN -->
                    @if($canAccess('manajemen_user'))
                    <p class="sidebar-heading text-[10px] uppercase text-slate-400 font-extrabold px-3 mb-1.5 mt-5 tracking-wider">Pengaturan</p>
                    
                    <li>
                        <a href="{{ route('superadmin.gaji.index') }}" title="Penggajian" class="sidebar-link flex items-center p-2.5 rounded-xl group transition-all {{ request()->is('superadmin/gaji*') ? 'sidebar-active' : 'text-slate-600 hover:bg-slate-50' }}">
                            <i class="fa-solid fa-envelope-open-text w-5 text-center text-slate-400 group-hover:text-indigo-600 transition-colors"></i>
                            <span class="sidebar-label ml-2.5 text-xs">Penggajian</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('superadmin.user.index') }}" title="Manajemen Pengguna" class="sidebar-link flex items-center p-2.5 rounded-xl group transition-all {{ request()->is('superadmin/user*') ? 'sidebar-active' : 'text-slate-600 hover:bg-slate-50' }}">
                            <i class="fa-solid fa-users-gear w-5 text-center text-slate-400 group-hover:text-indigo-600 transition-colors"></i>
                            <span class="sidebar-label ml-2.5 text-xs">Pengguna</span>
                        </a>
                    </li>
                    @if($isPlatformAdmin)
                    <li class="bg-amber-50 rounded-xl border border-amber-200/50 my-1">
                        <a href="{{ route('platform.dashboard') }}" title="Master SaaS Control" class="sidebar-link flex items-center p-2.5 rounded-xl group transition-all text-amber-900 font-bold hover:bg-amber-100">
                            <i class="fa-solid fa-crown w-5 text-center text-amber-600"></i>
                            <span class="sidebar-label ml-2.5 text-xs">Platform SaaS</span>
                        </a>
                    </li>
                    @endif
                    <li>
                        <a href="{{ route('superadmin.pengaturan.index') }}" title="Profil Toko" class="sidebar-link flex items-center p-2.5 rounded-xl group transition-all {{ request()->is('superadmin/pengaturan*') ? 'sidebar-active' : 'text-slate-600 hover:bg-slate-50' }}">
                            <i class="fa-solid fa-gear w-5 text-center text-slate-400 group-hover:text-indigo-600 transition-colors"></i>
                            <span class="sidebar-label ml-2.5 text-xs">Profil Toko</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('superadmin.backup.index') }}" title="Cadangan & Restore" class="sidebar-link flex items-center p-2.5 rounded-xl group transition-all {{ request()->is('superadmin/backup*') ? 'sidebar-active' : 'text-slate-600 hover:bg-slate-50' }}">
                            <i class="fa-solid fa-shield-halved w-5 text-center text-slate-400 group-hover:text-indigo-600 transition-colors"></i>
                            <span class="sidebar-label ml-2.5 text-xs">Cadangan & Restore</span>
                        </a>
                    </li>
                    @endif
                </ul>
            </div>

            <!-- Finnova Cloud Core Status Widget (Bottom Sidebar Graphic) -->
            <div class="sidebar-full-logo my-4 px-1">
                <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-slate-900 via-indigo-950 to-slate-900 p-3.5 text-white shadow-xl shadow-indigo-950/20 border border-indigo-500/20 group hover:border-indigo-400/40 transition-all duration-300">
                    <!-- Ambient Glow Orb -->
                    <div class="absolute -right-6 -bottom-6 w-24 h-24 bg-indigo-500/25 rounded-full blur-2xl pointer-events-none group-hover:scale-125 transition-transform duration-500"></div>
                    <div class="absolute -left-6 -top-6 w-20 h-20 bg-purple-500/15 rounded-full blur-xl pointer-events-none"></div>

                    <!-- Header with Icon & Active Pill -->
                    <div class="relative flex items-center justify-between gap-2 mb-2">
                        <div class="w-8 h-8 rounded-xl bg-gradient-to-tr from-indigo-500 via-indigo-600 to-purple-600 flex items-center justify-center text-white text-xs shadow-md shadow-indigo-500/30 group-hover:rotate-6 transition-transform">
                            <i class="fa-solid fa-cloud-bolt text-xs"></i>
                        </div>
                        <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full bg-emerald-500/20 border border-emerald-400/30 text-[9px] font-extrabold text-emerald-300 tracking-wide">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                            ONLINE
                        </span>
                    </div>

                    <!-- Title & Description -->
                    <div class="relative">
                        <h4 class="text-xs font-black tracking-tight text-white flex items-center gap-1.5">
                            VxPOS Cloud Core
                        </h4>
                        <p class="text-[10px] text-slate-400 mt-0.5 font-medium leading-tight">
                            Multi-Store Realtime Sync & Proteksi Anti-Rugi
                        </p>
                    </div>

                    <!-- Health / Security Info -->
                    <div class="relative mt-3 pt-2.5 border-t border-white/10 flex items-center justify-between text-[10px]">
                        <span class="text-slate-400 font-semibold flex items-center gap-1">
                            <i class="fa-solid fa-shield-halved text-indigo-400 text-[10px]"></i> Server Secure
                        </span>
                        <span class="font-extrabold text-indigo-300 text-[9px] bg-white/5 px-1.5 py-0.5 rounded border border-white/10">v2.5 PRO</span>
                    </div>
                </div>
            </div>

            <!-- Mini Icon Mode for Collapsed Sidebar -->
            <div class="sidebar-mini-logo hidden justify-center my-3">
                <div title="VxPOS Cloud Core - Status Terhubung & Aman" class="w-9 h-9 rounded-xl bg-gradient-to-br from-indigo-600 to-purple-600 flex items-center justify-center text-white text-xs shadow-md shadow-indigo-600/30 cursor-pointer hover:scale-110 transition-transform">
                    <i class="fa-solid fa-cloud-bolt"></i>
                </div>
            </div>

            <!-- Logout Button at Sidebar Bottom -->
            <div class="pt-3 border-t border-slate-100">
                <form action="/logout" method="POST">
                    @csrf
                    <button type="submit" title="Keluar Aplikasi" class="sidebar-link flex items-center w-full p-2.5 text-rose-500 rounded-xl hover:bg-rose-50 group transition-all font-bold text-xs">
                        <i class="fa-solid fa-right-from-bracket w-5 text-center"></i>
                        <span class="sidebar-label ml-2.5">Keluar</span>
                    </button>
                </form>
            </div>
        </div>
    </aside>

    <!-- Main Content Container -->
    <div id="main-content-container" class="lg:ml-64 transition-all duration-300 min-h-screen flex flex-col">
        
        <!-- Finnova-Inspired Top Header Navbar -->
        <header class="sticky top-2 lg:top-3 z-30 px-3 lg:px-6">
            <nav class="bg-white/90 backdrop-blur-xl border border-slate-200/70 shadow-[0_8px_30px_rgb(0,0,0,0.04)] rounded-2xl px-3.5 lg:px-5 py-2.5 flex items-center justify-between gap-3">
                
                <!-- Left: Mobile Menu & Current Context -->
                <div class="flex items-center gap-3">
                    <button id="mobile-menu-btn" class="lg:hidden w-9 h-9 rounded-xl bg-slate-100 hover:bg-indigo-50 hover:text-indigo-600 text-slate-600 flex items-center justify-center transition-colors">
                        <i class="fa-solid fa-bars text-sm"></i>
                    </button>
                    
                    <div class="hidden sm:flex items-center gap-2">
                        <span class="text-xs font-bold text-slate-800">
                            {{ $namaToko }}
                        </span>
                        <span class="text-[10px] font-bold bg-indigo-50 text-indigo-600 px-2 py-0.5 rounded-full border border-indigo-100">
                            {{ strtoupper(Auth::user()->role ?? 'ADMIN') }}
                        </span>
                    </div>
                </div>

                <!-- Center: Finnova Capsule Pill Nav (Desktop) -->
                <div class="hidden md:flex items-center pill-capsule shadow-sm">
                    <a href="{{ route('superadmin.dashboard') }}" class="pill-item {{ request()->is('superadmin/dashboard') ? 'pill-item-active' : '' }}">
                        <i class="fa-solid fa-chart-line mr-1.5 text-[11px]"></i> Overview
                    </a>
                    @if($canAccess('transaksi_sales'))
                    <a href="{{ route('superadmin.transaksi.create') }}" class="pill-item {{ request()->is('superadmin/transaksi/create') ? 'pill-item-active' : '' }}">
                        <i class="fa-solid fa-cash-register mr-1.5 text-[11px]"></i> Kasir POS
                    </a>
                    <a href="{{ route('superadmin.transaksi.index') }}" class="pill-item {{ request()->is('superadmin/transaksi') ? 'pill-item-active' : '' }}">
                        <i class="fa-solid fa-receipt mr-1.5 text-[11px]"></i> Transaksi
                    </a>
                    @endif
                    @if($canAccess('stok_gudang'))
                    <a href="{{ route('superadmin.gudang.index') }}" class="pill-item {{ request()->is('superadmin/gudang*') ? 'pill-item-active' : '' }}">
                        <i class="fa-solid fa-boxes-stacked mr-1.5 text-[11px]"></i> Gudang
                    </a>
                    @endif
                    @if($canAccess('laporan_penjualan'))
                    <a href="{{ route('superadmin.laporan.index') }}" class="pill-item {{ request()->is('superadmin/laporan*') ? 'pill-item-active' : '' }}">
                        <i class="fa-solid fa-pie-chart mr-1.5 text-[11px]"></i> Laporan
                    </a>
                    @endif
                </div>

                <!-- Right: Quick Action Buttons & Profile Avatar -->
                <div class="flex items-center gap-2.5">
                    @if($isPlatformAdmin)
                        <a href="{{ route('superadmin.toko.index') }}" title="Ganti Toko Aktif" class="hidden sm:flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-indigo-50 hover:bg-indigo-100 border border-indigo-100 text-xs font-bold text-indigo-700 transition-all">
                            <i class="fa-solid fa-store text-[11px] text-indigo-500"></i>
                            <span class="max-w-[120px] truncate text-[11px]">{{ $namaToko }}</span>
                            <i class="fa-solid fa-arrows-rotate text-[10px] text-indigo-400"></i>
                        </a>
                    @endif

                    <!-- User Profile Circle Avatar -->
                    <div class="flex items-center gap-2 pl-1">
                        <div class="w-9 h-9 rounded-full bg-gradient-to-tr from-indigo-600 to-purple-600 flex items-center justify-center text-white font-extrabold text-xs shadow-md shadow-indigo-500/20">
                            {{ strtoupper(substr(Auth::user()->nama ?? Auth::user()->name ?? 'U', 0, 1)) }}
                        </div>
                        <div class="hidden xl:block text-left text-xs">
                            <p class="font-bold text-slate-800 leading-tight">{{ Auth::user()->nama ?? Auth::user()->name ?? 'User' }}</p>
                            <p class="text-[10px] text-slate-400 leading-tight font-medium">{{ Auth::user()->username ?? 'user' }}</p>
                        </div>
                    </div>
                </div>
            </nav>
        </header>

        <!-- Main Page Content -->
        <main class="relative z-10 flex-1 px-3 lg:px-6 py-4">
            @yield('content')
        </main>
        
        <!-- Modern Clean Footer -->
        <footer class="mt-auto px-6 py-4 border-t border-slate-200/60 text-center text-xs text-slate-400 font-medium">
            {{ $namaToko }} &bull; Enterprise Multi-Store POS Architecture &bull; &copy; {{ date('Y') }}
        </footer>
    </div>

    <!-- ======================================================== -->
    <!-- GLOBAL FINNOVA CONFIRMATION MODAL (PENGGANTI POPUP BROWSER) -->
    <!-- ======================================================== -->
    <div id="globalFinnovaModal" class="fixed inset-0 z-[9999] bg-slate-950/60 backdrop-blur-sm hidden items-center justify-center p-4 transition-all duration-300">
        <div id="globalFinnovaCard" class="bg-white rounded-3xl p-6 sm:p-7 max-w-md w-full shadow-2xl border border-slate-100 transform scale-95 opacity-0 transition-all duration-200">
            <div class="text-center">
                <div id="globalModalIconBg" class="w-16 h-16 rounded-2xl bg-amber-50 text-amber-500 mx-auto flex items-center justify-center mb-4 text-2xl shadow-inner border border-amber-100">
                    <i id="globalModalIcon" class="fa-solid fa-triangle-exclamation"></i>
                </div>
                <h3 id="globalModalTitle" class="text-lg font-extrabold text-slate-900 tracking-tight">Konfirmasi Tindakan</h3>
                <p id="globalModalMessage" class="text-xs text-slate-500 mt-2 leading-relaxed px-2">Apakah Anda yakin ingin melanjutkan tindakan ini?</p>
                
                <div class="mt-6 flex flex-col-reverse sm:flex-row gap-2.5">
                    <button type="button" id="globalModalCancelBtn" class="w-full py-2.5 px-4 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs transition-colors cursor-pointer">
                        Batal
                    </button>
                    <button type="button" id="globalModalConfirmBtn" class="w-full py-2.5 px-4 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs shadow-md shadow-indigo-600/30 transition-all cursor-pointer">
                        Ya, Lanjutkan
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- GLOBAL FINNOVA TOAST NOTIFICATION CONTAINER -->
    <div id="globalToastContainer" class="fixed top-5 right-5 z-[10000] flex flex-col gap-2.5 pointer-events-none max-w-sm w-full"></div>

    <!-- Scripts: Mobile Drawer, Collapsible Auxiliary Pane, Finnova Custom Popups & Toast -->
    <script>
        // 1. Universal Finnova Confirm Modal API
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
                msgEl.textContent = options.message || 'Apakah Anda yakin ingin melanjutkan tindakan ini?';
                confirmBtn.textContent = options.confirmText || 'Ya, Lanjutkan';
                cancelBtn.textContent = options.cancelText || 'Batal';

                const type = options.type || 'warning';
                if (type === 'danger') {
                    iconBg.className = 'w-16 h-16 rounded-2xl bg-rose-50 text-rose-500 mx-auto flex items-center justify-center mb-4 text-2xl shadow-inner border border-rose-100';
                    iconEl.className = 'fa-solid fa-triangle-exclamation';
                    confirmBtn.className = 'w-full py-2.5 px-4 rounded-xl bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs shadow-md shadow-rose-600/30 transition-all cursor-pointer';
                } else if (type === 'success') {
                    iconBg.className = 'w-16 h-16 rounded-2xl bg-emerald-50 text-emerald-500 mx-auto flex items-center justify-center mb-4 text-2xl shadow-inner border border-emerald-100';
                    iconEl.className = 'fa-solid fa-circle-check';
                    confirmBtn.className = 'w-full py-2.5 px-4 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-md shadow-emerald-600/30 transition-all cursor-pointer';
                } else {
                    iconBg.className = 'w-16 h-16 rounded-2xl bg-indigo-50 text-indigo-500 mx-auto flex items-center justify-center mb-4 text-2xl shadow-inner border border-indigo-100';
                    iconEl.className = 'fa-solid fa-circle-question';
                    confirmBtn.className = 'w-full py-2.5 px-4 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs shadow-md shadow-indigo-600/30 transition-all cursor-pointer';
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
                    document.removeEventListener('keydown', keyHandler);
                    resolve(confirmed);
                }

                function keyHandler(e) {
                    if (e.key === 'Escape') cleanup(false);
                }

                confirmBtn.onclick = () => cleanup(true);
                cancelBtn.onclick = () => cleanup(false);
                modal.onclick = (e) => { if (e.target === modal) cleanup(false); };
                document.addEventListener('keydown', keyHandler);
            });
        };

        // 2. Universal Finnova Toast Notification API
        window.finnovaToast = function(message, type = 'info', duration = 3500) {
            const container = document.getElementById('globalToastContainer');
            if (!container) return;

            const toast = document.createElement('div');
            toast.className = 'pointer-events-auto transform -translate-y-2 opacity-0 transition-all duration-300 flex items-center gap-3 p-3.5 rounded-2xl shadow-xl border bg-white text-slate-800 text-xs font-semibold';
            
            let iconClass = 'fa-solid fa-circle-info text-indigo-500';
            let borderColor = 'border-slate-100';
            if (type === 'danger' || type === 'error') {
                iconClass = 'fa-solid fa-circle-xmark text-rose-500';
                borderColor = 'border-rose-100 bg-rose-50/50';
            } else if (type === 'warning') {
                iconClass = 'fa-solid fa-triangle-exclamation text-amber-500';
                borderColor = 'border-amber-100 bg-amber-50/50';
            } else if (type === 'success') {
                iconClass = 'fa-solid fa-circle-check text-emerald-500';
                borderColor = 'border-emerald-100 bg-emerald-50/50';
            }
            toast.classList.add(...borderColor.split(' '));

            toast.innerHTML = `
                <div class="text-base"><i class="${iconClass}"></i></div>
                <div class="flex-1 leading-snug">${message}</div>
                <button type="button" class="text-slate-400 hover:text-slate-600 transition-colors ml-1 cursor-pointer">
                    <i class="fa-solid fa-xmark text-xs"></i>
                </button>
            `;

            const closeBtn = toast.querySelector('button');
            closeBtn.onclick = () => removeToast();

            container.appendChild(toast);
            requestAnimationFrame(() => {
                toast.classList.remove('-translate-y-2', 'opacity-0');
                toast.classList.add('translate-y-0', 'opacity-100');
            });

            const timer = setTimeout(() => removeToast(), duration);

            function removeToast() {
                clearTimeout(timer);
                toast.classList.add('opacity-0', '-translate-y-2');
                setTimeout(() => toast.remove(), 250);
            }
        };

        // Override default window.alert with modern Finnova Toast
        window.alert = function(msg) {
            window.finnovaToast(msg, 'warning', 4000);
        };

        // Global delegation for data-confirm forms
        document.addEventListener('submit', function(e) {
            const form = e.target;
            if (!form) return;

            const confirmMsg = form.getAttribute('data-confirm');
            if (confirmMsg && !form.dataset.confirmed) {
                e.preventDefault();
                const type = form.getAttribute('data-confirm-type') || 'warning';
                const title = form.getAttribute('data-confirm-title') || 'Konfirmasi';
                window.finnovaConfirm({
                    title: title,
                    message: confirmMsg,
                    type: type,
                    confirmText: form.getAttribute('data-confirm-btn') || 'Ya, Lanjutkan'
                }).then(confirmed => {
                    if (confirmed) {
                        form.dataset.confirmed = 'true';
                        form.submit();
                    }
                });
            }
        });

        // Global delegation for data-confirm links and buttons
        document.addEventListener('click', function(e) {
            const target = e.target.closest('a[data-confirm], button[data-confirm]');
            if (target && !target.dataset.confirmed && target.tagName === 'A') {
                e.preventDefault();
                const confirmMsg = target.getAttribute('data-confirm');
                const type = target.getAttribute('data-confirm-type') || 'warning';
                const title = target.getAttribute('data-confirm-title') || 'Konfirmasi';
                window.finnovaConfirm({
                    title: title,
                    message: confirmMsg,
                    type: type,
                    confirmText: target.getAttribute('data-confirm-btn') || 'Ya, Lanjutkan'
                }).then(confirmed => {
                    if (confirmed) {
                        target.dataset.confirmed = 'true';
                        window.location.href = target.href;
                    }
                });
            }
        });

        // Auto-convert any lingering legacy confirm() inside onsubmit and onclick attributes
        function convertLegacyConfirms() {
            document.querySelectorAll('form[onsubmit*="confirm("]').forEach(form => {
                const onsubmitStr = form.getAttribute('onsubmit');
                const match = onsubmitStr.match(/confirm\(['"](.*?)['"]\)/);
                if (match && match[1]) {
                    form.removeAttribute('onsubmit');
                    form.setAttribute('data-confirm', match[1]);
                    if (match[1].toLowerCase().includes('hapus') || match[1].toLowerCase().includes('batalkan')) {
                        form.setAttribute('data-confirm-type', 'danger');
                    } else if (match[1].toLowerCase().includes('packing') || match[1].toLowerCase().includes('lunas') || match[1].toLowerCase().includes('setujui')) {
                        form.setAttribute('data-confirm-type', 'success');
                    }
                }
            });

            document.querySelectorAll('a[onclick*="confirm("], button[onclick*="confirm("]').forEach(el => {
                const onclickStr = el.getAttribute('onclick');
                const match = onclickStr.match(/confirm\(['"](.*?)['"]\)/);
                if (match && match[1]) {
                    el.removeAttribute('onclick');
                    el.setAttribute('data-confirm', match[1]);
                    if (match[1].toLowerCase().includes('hapus') || match[1].toLowerCase().includes('batalkan')) {
                        el.setAttribute('data-confirm-type', 'danger');
                    }
                }
            });
        }

        document.addEventListener('DOMContentLoaded', function() {
            convertLegacyConfirms();

            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebar-overlay');
            const mobileBtn = document.getElementById('mobile-menu-btn');
            const collapseBtn = document.getElementById('toggle-sidebar-collapse');
            const collapseIcon = document.getElementById('collapse-icon');
            const mainContent = document.getElementById('main-content-container');

            // 1. Mobile Menu Drawer Toggle
            function toggleMobileMenu() {
                sidebar.classList.toggle('-translate-x-full');
                overlay.classList.toggle('hidden');
            }
            if (mobileBtn) mobileBtn.addEventListener('click', toggleMobileMenu);
            if (overlay) overlay.addEventListener('click', toggleMobileMenu);

            // 2. Desktop Collapsible Auxiliary Pane (Icon Mode)
            function setSidebarCollapsed(collapsed) {
                if (collapsed) {
                    sidebar.classList.add('sidebar-collapsed');
                    mainContent.classList.add('content-collapsed');
                    mainContent.classList.remove('lg:ml-64');
                    if (collapseIcon) {
                        collapseIcon.classList.remove('fa-angles-left');
                        collapseIcon.classList.add('fa-angles-right');
                    }
                    localStorage.setItem('vxpos_sidebar_collapsed', 'true');
                } else {
                    sidebar.classList.remove('sidebar-collapsed');
                    mainContent.classList.remove('content-collapsed');
                    mainContent.classList.add('lg:ml-64');
                    if (collapseIcon) {
                        collapseIcon.classList.remove('fa-angles-right');
                        collapseIcon.classList.add('fa-angles-left');
                    }
                    localStorage.setItem('vxpos_sidebar_collapsed', 'false');
                }
            }

            if (collapseBtn) {
                collapseBtn.addEventListener('click', function() {
                    const isCurrentlyCollapsed = sidebar.classList.contains('sidebar-collapsed');
                    setSidebarCollapsed(!isCurrentlyCollapsed);
                });
            }

            // Restore user's previous collapse preference
            const savedState = localStorage.getItem('vxpos_sidebar_collapsed');
            if (savedState === 'true') {
                setSidebarCollapsed(true);
            }
        });
    </script>
</body>
</html>