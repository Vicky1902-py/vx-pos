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
    <!-- [PERBAIKAN] Mengunci skala layar agar terasa seperti Aplikasi Native (Anti Zoom-in) -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>@yield('title') - {{ $namaToko }}</title>
    
    @if($logoPath)
        <link rel="icon" type="image/png" href="{{ $logoPath }}">
    @else
        <link rel="icon" href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><text y=%22.9em%22 font-size=%2290%22>🚗</text></svg>">
    @endif

    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Public+Sans:wght@300;400;500;600;700&display=swap');
        body { font-family: 'Public Sans', sans-serif; background-color: #f8f7fa; }
        .sidebar-active { background: linear-gradient(72.47deg, #7367f0 22.16%, rgba(115, 103, 240, 0.7) 76.47%); box-shadow: 0px 2px 6px rgba(115, 103, 240, 0.48); color: white !important; }
        
        /* [PERBAIKAN] Custom Scrollbar Premium (Mac/iOS Style) */
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
        ::-webkit-scrollbar-thumb:hover { background: #94a3b8; }

        /* [PERBAIKAN] Global Anti-Terpotong untuk Tabel/Data Panjang Khusus Layar HP */
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
<body class="overflow-x-hidden relative">

    <!-- Overlay Mobile -->
    <div id="sidebar-overlay" class="fixed inset-0 bg-gray-900/50 z-30 hidden lg:hidden backdrop-blur-sm transition-opacity cursor-pointer"></div>

    <!-- Sidebar Menu -->
    <aside id="sidebar" class="fixed top-0 left-0 z-40 w-64 h-screen transition-transform -translate-x-full lg:translate-x-0 bg-white border-r border-gray-100 overflow-y-auto">
        <div class="h-full px-3 py-4 flex flex-col">
            
            <!-- Logo -->
            <div class="flex items-center px-4 mb-8 mt-2">
                @if($logoPath)
                    <img src="{{ $logoPath }}" alt="{{ $namaToko }}" class="max-h-10 w-auto mr-3 object-contain drop-shadow-sm">
                    <span class="text-[15px] font-bold text-gray-800 tracking-tight leading-tight line-clamp-2">{{ $namaToko }}</span>
                @else
                    <div class="w-8 h-8 bg-indigo-600 rounded-lg flex items-center justify-center mr-3 shadow-md font-bold text-white text-xs">
                        VX
                    </div>
                    <span class="text-xl font-bold text-gray-800 tracking-tight">Vx<span class="text-indigo-600">POS</span></span>
                @endif
            </div>
            
            @php
                // AMBIL DATA HAK AKSES (ACL) DARI DATABASE
                $aksesArr = json_decode(Auth::user()->hak_akses, true) ?? [];
                $isGod = Auth::user()->role === 'superadmin';
            @endphp

            <ul class="space-y-1.5 font-medium pb-8 flex-1">
                <!-- Menu Dashboard -->
                <p class="text-[11px] uppercase text-gray-400 font-bold px-4 mb-2 mt-4 tracking-wider">Menu Utama</p>
                <li>
                    <a href="{{ route('superadmin.dashboard') }}" class="flex items-center p-3 rounded-lg group transition-all {{ request()->is('superadmin/dashboard') ? 'sidebar-active' : 'text-gray-600 hover:bg-gray-50' }}">
                        <i class="fa-solid fa-house-chimney w-5 h-5 transition duration-75"></i>
                        <span class="ml-3 text-[15px]">Dashboard</span>
                    </a>
                </li>
                
                <!-- DATA MASTER -->
                @if($isGod || in_array('master_barang', $aksesArr) || in_array('manajemen_harga', $aksesArr))
                <p class="text-[11px] uppercase text-gray-400 font-bold px-4 mb-2 mt-6 tracking-wider">Data Master</p>
                @endif
                
                @if($isGod || in_array('master_barang', $aksesArr))
                <li>
                    <a href="{{ route('superadmin.barang.index') }}" class="flex items-center p-3 rounded-lg group transition-all {{ request()->is('superadmin/barang*') ? 'sidebar-active' : 'text-gray-600 hover:bg-gray-50' }}">
                        <i class="fa-solid fa-box-open w-5 h-5 transition duration-75"></i>
                        <span class="ml-3 text-[15px]">Master Barang</span>
                    </a>
                </li>
                @endif
                
                @if($isGod || in_array('manajemen_harga', $aksesArr))
                <li>
                    <a href="{{ route('superadmin.harga.index') }}" class="flex items-center p-3 rounded-lg group transition-all {{ request()->is('superadmin/harga*') ? 'sidebar-active' : 'text-gray-600 hover:bg-gray-50' }}">
                        <i class="fa-solid fa-tags w-5 h-5 transition duration-75"></i>
                        <span class="ml-3 text-[15px]">Manajemen Harga</span>
                    </a>
                </li>
                @endif

                <!-- OPERASIONAL -->
                @if($isGod || in_array('transaksi_sales', $aksesArr) || in_array('validasi_kasir', $aksesArr) || in_array('stok_gudang', $aksesArr))
                <p class="text-[11px] uppercase text-gray-400 font-bold px-4 mb-2 mt-6 tracking-wider">Operasional</p>
                @endif
                
                @if($isGod || in_array('transaksi_sales', $aksesArr))
                <li>
                    <a href="{{ route('superadmin.transaksi.index') }}" class="flex items-center p-3 rounded-lg group transition-all {{ request()->is('superadmin/transaksi*') ? 'sidebar-active' : 'text-gray-600 hover:bg-gray-50' }}">
                        <i class="fa-solid fa-cart-shopping w-5 h-5 transition duration-75 text-[#00cfe8]"></i>
                        <span class="ml-3 text-[15px]">Transaksi Penjualan</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('superadmin.pelanggan.index') }}" class="flex items-center p-3 rounded-lg group transition-all {{ request()->is('superadmin/pelanggan*') ? 'sidebar-active' : 'text-gray-600 hover:bg-gray-50' }}">
                        <i class="fa-solid fa-address-book w-5 h-5 transition duration-75"></i>
                        <span class="ml-3 text-[15px]">Buku Pelanggan</span>
                    </a>
                </li>
                @endif
                
                @if($isGod || in_array('validasi_kasir', $aksesArr))
                <li>
                    <a href="{{ route('superadmin.kasir.index') }}" class="flex items-center p-3 rounded-lg group transition-all {{ request()->is('superadmin/kasir*') ? 'sidebar-active' : 'text-gray-600 hover:bg-gray-50' }}">
                        <i class="fa-solid fa-file-invoice-dollar w-5 h-5 transition duration-75 text-[#28c76f]"></i>
                        <span class="ml-3 text-[15px]">Validasi Kasir</span>
                    </a>
                </li>
                @endif
                
                @if($isGod || in_array('stok_gudang', $aksesArr))
                <li>
                    <a href="{{ route('superadmin.gudang.index') }}" class="flex items-center p-3 rounded-lg group transition-all {{ request()->is('superadmin/gudang*') ? 'sidebar-active' : 'text-gray-600 hover:bg-gray-50' }}">
                        <i class="fa-solid fa-boxes-stacked w-5 h-5 transition duration-75 text-[#ff9f43]"></i>
                        <span class="ml-3 text-[15px]">Manajemen Gudang</span>
                    </a>
                </li>
                @endif
                
                <!-- KEUANGAN -->
                @if($isGod || in_array('laporan_penjualan', $aksesArr) || in_array('kelola_bonus', $aksesArr))
                <p class="text-[11px] uppercase text-gray-400 font-bold px-4 mb-2 mt-6 tracking-wider">Keuangan</p>
                @endif
                
                @if($isGod || in_array('laporan_penjualan', $aksesArr))
                <li>
                    <a href="{{ route('superadmin.laporan.index') }}" class="flex items-center p-3 rounded-lg group transition-all {{ request()->is('superadmin/laporan*') ? 'sidebar-active' : 'text-gray-600 hover:bg-gray-50' }}">
                        <i class="fa-solid fa-chart-pie w-5 h-5 transition duration-75"></i>
                        <span class="ml-3 text-[15px]">Laporan Penjualan</span>
                    </a>
                </li>
                @endif
                
                @if($isGod || in_array('kelola_bonus', $aksesArr))
                <li>
                    <a href="{{ route('superadmin.bonus.index') }}" class="flex items-center p-3 rounded-lg group transition-all {{ request()->is('superadmin/bonus*') ? 'sidebar-active' : 'text-gray-600 hover:bg-gray-50' }}">
                        <i class="fa-solid fa-hand-holding-dollar w-5 h-5 transition duration-75"></i>
                        <span class="ml-3 text-[15px]">Bonus & Pencairan</span>
                    </a>
                </li>
                @endif

                <!-- SISTEM (GOD MODE) -->
                @if($isGod || in_array('manajemen_user', $aksesArr))
                <p class="text-[11px] uppercase text-gray-400 font-bold px-4 mb-2 mt-6 tracking-wider">Sistem</p>
                
                <li>
                    <a href="{{ route('superadmin.gaji.index') }}" class="flex items-center p-3 rounded-lg group transition-all {{ request()->is('superadmin/gaji*') ? 'sidebar-active' : 'text-gray-600 hover:bg-gray-50' }}">
                        <i class="fa-solid fa-envelope-open-text w-5 h-5 transition duration-75"></i>
                        <span class="ml-3 text-[15px]">Penggajian / Payroll</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('superadmin.user.index') }}" class="flex items-center p-3 rounded-lg group transition-all {{ request()->is('superadmin/user*') ? 'sidebar-active' : 'text-gray-600 hover:bg-gray-50' }}">
                        <i class="fa-solid fa-users-gear w-5 h-5 transition duration-75"></i>
                        <span class="ml-3 text-[15px]">Manajemen User</span>
                    </a>
                </li>
                @if($isPlatformAdmin)
                <li class="bg-amber-50 rounded-xl border border-amber-200/60 my-1">
                    <a href="{{ route('platform.dashboard') }}" class="flex items-center p-3 rounded-lg group transition-all text-amber-900 font-bold hover:bg-amber-100">
                        <i class="fa-solid fa-crown w-5 h-5 transition duration-75 text-amber-600"></i>
                        <span class="ml-3 text-[14px]">Master Platform SaaS</span>
                    </a>
                </li>
                @endif
                <li>
                    <a href="{{ route('superadmin.pengaturan.index') }}" class="flex items-center p-3 rounded-lg group transition-all {{ request()->is('superadmin/pengaturan*') ? 'sidebar-active' : 'text-gray-600 hover:bg-gray-50' }}">
                        <i class="fa-solid fa-gear w-5 h-5 transition duration-75"></i>
                        <span class="ml-3 text-[15px]">Pengaturan Toko</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('superadmin.backup.index') }}" class="flex items-center p-3 rounded-lg group transition-all {{ request()->is('superadmin/backup*') ? 'sidebar-active' : 'text-gray-600 hover:bg-gray-50' }}">
                        <i class="fa-solid fa-database w-5 h-5 transition duration-75"></i>
                        <span class="ml-3 text-[15px]">Backup Database</span>
                    </a>
                </li>
                @endif
                
                <!-- Keluar -->
                <li class="mt-4 pt-4 border-t border-gray-100">
                    <form action="/logout" method="POST">
                        @csrf
                        <button type="submit" class="flex items-center w-full p-3 text-red-500 rounded-lg hover:bg-red-50 group transition-all font-semibold">
                            <i class="fa-solid fa-right-from-bracket w-5 h-5"></i>
                            <span class="ml-3 text-[15px]">Keluar Aplikasi</span>
                        </button>
                    </form>
                </li>
            </ul>
        </div>
    </aside>

    <!-- [PERBAIKAN] Spasi Dinamis (Rapat di HP, Longgar di PC) -->
    <div class="lg:ml-64 p-3 lg:p-6 min-h-screen flex flex-col">
        <!-- [PERBAIKAN] Navbar Atas Responsif -->
        <nav class="bg-white/80 backdrop-blur-md sticky top-2 lg:top-4 z-20 rounded-xl shadow-sm border border-gray-100 mb-4 lg:mb-6 px-4 lg:px-6 py-2.5 lg:py-3 flex justify-between items-center">
            <div class="flex items-center text-gray-500 text-sm">
                <!-- Ikon menu burger untuk mobile -->
                <i id="mobile-menu-btn" class="fa-solid fa-bars mr-3 lg:mr-4 cursor-pointer text-xl lg:hidden hover:text-indigo-600 transition-colors"></i>
                <span class="font-medium hidden sm:inline-block">Selamat Datang, <span class="text-indigo-600 font-bold">{{ Auth::user()->nama }}</span> <span class="text-xs bg-indigo-100 text-indigo-600 px-2 py-0.5 rounded ml-2 uppercase">{{ Auth::user()->role }}</span></span>
            </div>
            <div class="flex items-center space-x-3">
                @if($isPlatformAdmin)
                    <a href="{{ route('superadmin.toko.index') }}" title="Klik untuk ganti cabang/toko aktif" class="flex items-center gap-2 px-3 py-1.5 rounded-lg bg-indigo-50 border border-indigo-100 text-xs font-bold text-indigo-700 hover:bg-indigo-100 transition-all">
                        <i class="fa-solid fa-shop text-indigo-500"></i>
                        <span class="max-w-[160px] truncate">{{ $namaToko }}</span>
                        <i class="fa-solid fa-arrows-rotate text-[10px] text-indigo-400"></i>
                    </a>
                @else
                    <span class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-gray-100 text-xs font-bold text-gray-700">
                        <i class="fa-solid fa-shop text-gray-500"></i>
                        <span class="max-w-[160px] truncate">{{ $namaToko }}</span>
                    </span>
                @endif
                <div class="w-9 h-9 lg:w-10 lg:h-10 rounded-full bg-gradient-to-r from-indigo-500 to-purple-500 flex items-center justify-center text-white font-bold border-2 border-white shadow-md cursor-pointer hover:shadow-lg transition-all">
                    {{ substr(Auth::user()->nama, 0, 1) }}
                </div>
            </div>
        </nav>

        <main class="relative z-10 flex-1">
            @yield('content')
        </main>
        
        <!-- Footer Hak Cipta -->
        <footer class="mt-8 pt-4 pb-2 border-t border-gray-200 text-center text-xs lg:text-sm text-gray-500 font-medium">
            VxPOS Enterprise Multi-Store &bull; Hak Cipta by. Vicky Koroh &bull; &copy; {{ date('Y') }}
        </footer>
    </div>

    <!-- Script JavaScript untuk Buka/Tutup Menu HP -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebar-overlay');
            const toggleBtn = document.getElementById('mobile-menu-btn');

            function toggleMenu() {
                sidebar.classList.toggle('-translate-x-full');
                overlay.classList.toggle('hidden');
            }

            toggleBtn.addEventListener('click', toggleMenu);
            overlay.addEventListener('click', toggleMenu);
        });
    </script>
</body>
</html>