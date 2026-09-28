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
                    $aksesArr = json_decode(Auth::user()->hak_akses, true) ?? [];
                    $isGod = Auth::user()->role === 'superadmin';
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
                    @if($isGod || in_array('master_barang', $aksesArr) || in_array('manajemen_harga', $aksesArr))
                    <p class="sidebar-heading text-[10px] uppercase text-slate-400 font-extrabold px-3 mb-1.5 mt-5 tracking-wider">Katalog</p>
                    @endif
                    
                    @if($isGod || in_array('master_barang', $aksesArr))
                    <li>
                        <a href="{{ route('superadmin.barang.index') }}" title="Master Barang" class="sidebar-link flex items-center p-2.5 rounded-xl group transition-all {{ request()->is('superadmin/barang*') ? 'sidebar-active' : 'text-slate-600 hover:bg-slate-50' }}">
                            <i class="fa-solid fa-box-open w-5 text-center text-slate-400 group-hover:text-indigo-600 transition-colors"></i>
                            <span class="sidebar-label ml-2.5 text-xs">Master Barang</span>
                        </a>
                    </li>
                    @endif
                    
                    @if($isGod || in_array('manajemen_harga', $aksesArr))
                    <li>
                        <a href="{{ route('superadmin.harga.index') }}" title="Manajemen Harga" class="sidebar-link flex items-center p-2.5 rounded-xl group transition-all {{ request()->is('superadmin/harga*') ? 'sidebar-active' : 'text-slate-600 hover:bg-slate-50' }}">
                            <i class="fa-solid fa-tags w-5 text-center text-slate-400 group-hover:text-indigo-600 transition-colors"></i>
                            <span class="sidebar-label ml-2.5 text-xs">Manajemen Harga</span>
                        </a>
                    </li>
                    @endif

                    <!-- OPERASIONAL -->
                    @if($isGod || in_array('transaksi_sales', $aksesArr) || in_array('validasi_kasir', $aksesArr) || in_array('stok_gudang', $aksesArr))
                    <p class="sidebar-heading text-[10px] uppercase text-slate-400 font-extrabold px-3 mb-1.5 mt-5 tracking-wider">Operasional</p>
                    @endif
                    
                    @if($isGod || in_array('transaksi_sales', $aksesArr))
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
                    
                    @if($isGod || in_array('validasi_kasir', $aksesArr))
                    <li>
                        <a href="{{ route('superadmin.kasir.index') }}" title="Validasi Kasir" class="sidebar-link flex items-center p-2.5 rounded-xl group transition-all {{ request()->is('superadmin/kasir*') ? 'sidebar-active' : 'text-slate-600 hover:bg-slate-50' }}">
                            <i class="fa-solid fa-file-invoice-dollar w-5 text-center text-slate-400 group-hover:text-indigo-600 transition-colors"></i>
                            <span class="sidebar-label ml-2.5 text-xs">Validasi Kasir</span>
                        </a>
                    </li>
                    @endif
                    
                    @if($isGod || in_array('stok_gudang', $aksesArr))
                    <li>
                        <a href="{{ route('superadmin.gudang.index') }}" title="Manajemen Gudang" class="sidebar-link flex items-center p-2.5 rounded-xl group transition-all {{ request()->is('superadmin/gudang*') ? 'sidebar-active' : 'text-slate-600 hover:bg-slate-50' }}">
                            <i class="fa-solid fa-boxes-stacked w-5 text-center text-amber-500 transition-colors"></i>
                            <span class="sidebar-label ml-2.5 text-xs">Gudang & Stok</span>
                        </a>
                    </li>
                    @endif
                    
                    <!-- KEUANGAN -->
                    @if($isGod || in_array('laporan_penjualan', $aksesArr) || in_array('kelola_bonus', $aksesArr))
                    <p class="sidebar-heading text-[10px] uppercase text-slate-400 font-extrabold px-3 mb-1.5 mt-5 tracking-wider">Keuangan</p>
                    @endif
                    
                    @if($isGod || in_array('laporan_penjualan', $aksesArr))
                    <li>
                        <a href="{{ route('superadmin.laporan.index') }}" title="Laporan Penjualan" class="sidebar-link flex items-center p-2.5 rounded-xl group transition-all {{ request()->is('superadmin/laporan*') ? 'sidebar-active' : 'text-slate-600 hover:bg-slate-50' }}">
                            <i class="fa-solid fa-chart-pie w-5 text-center text-slate-400 group-hover:text-indigo-600 transition-colors"></i>
                            <span class="sidebar-label ml-2.5 text-xs">Laporan Penjualan</span>
                        </a>
                    </li>
                    @endif
                    
                    @if($isGod || in_array('kelola_bonus', $aksesArr))
                    <li>
                        <a href="{{ route('superadmin.bonus.index') }}" title="Bonus & Komisi" class="sidebar-link flex items-center p-2.5 rounded-xl group transition-all {{ request()->is('superadmin/bonus*') ? 'sidebar-active' : 'text-slate-600 hover:bg-slate-50' }}">
                            <i class="fa-solid fa-hand-holding-dollar w-5 text-center text-slate-400 group-hover:text-indigo-600 transition-colors"></i>
                            <span class="sidebar-label ml-2.5 text-xs">Bonus & Komisi</span>
                        </a>
                    </li>
                    @endif

                    <!-- SISTEM -->
                    @if($isGod || in_array('manajemen_user', $aksesArr))
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
                    @endif
                </ul>
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
                            {{ strtoupper(Auth::user()->role) }}
                        </span>
                    </div>
                </div>

                <!-- Center: Finnova Capsule Pill Nav (Desktop) -->
                <div class="hidden md:flex items-center pill-capsule shadow-sm">
                    <a href="{{ route('superadmin.dashboard') }}" class="pill-item {{ request()->is('superadmin/dashboard') ? 'pill-item-active' : '' }}">
                        <i class="fa-solid fa-chart-line mr-1.5 text-[11px]"></i> Overview
                    </a>
                    <a href="{{ route('superadmin.transaksi.create') }}" class="pill-item {{ request()->is('superadmin/transaksi/create') ? 'pill-item-active' : '' }}">
                        <i class="fa-solid fa-cash-register mr-1.5 text-[11px]"></i> Kasir POS
                    </a>
                    <a href="{{ route('superadmin.transaksi.index') }}" class="pill-item {{ request()->is('superadmin/transaksi') ? 'pill-item-active' : '' }}">
                        <i class="fa-solid fa-receipt mr-1.5 text-[11px]"></i> Transaksi
                    </a>
                    <a href="{{ route('superadmin.gudang.index') }}" class="pill-item {{ request()->is('superadmin/gudang*') ? 'pill-item-active' : '' }}">
                        <i class="fa-solid fa-boxes-stacked mr-1.5 text-[11px]"></i> Gudang
                    </a>
                    <a href="{{ route('superadmin.laporan.index') }}" class="pill-item {{ request()->is('superadmin/laporan*') ? 'pill-item-active' : '' }}">
                        <i class="fa-solid fa-pie-chart mr-1.5 text-[11px]"></i> Laporan
                    </a>
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
                            {{ strtoupper(substr(Auth::user()->nama, 0, 1)) }}
                        </div>
                        <div class="hidden xl:block text-left text-xs">
                            <p class="font-bold text-slate-800 leading-tight">{{ Auth::user()->nama }}</p>
                            <p class="text-[10px] text-slate-400 leading-tight font-medium">{{ Auth::user()->username }}</p>
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

    <!-- Scripts: Mobile Drawer & Collapsible Auxiliary Pane Toggle -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
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