<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VxPOS | Sistem Kasir & ERP Terpadu Multi-Toko</title>
    
    <link rel="icon" href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><text y=%22.9em%22 font-size=%2290%22>⚡</text></svg>">
    
    <!-- Tailwind CSS CDN & FontAwesome -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'sans-serif'],
                    },
                    colors: {
                        brand: {
                            50: '#eef2ff',
                            100: '#e0e7ff',
                            200: '#c7d2fe',
                            500: '#6366f1',
                            600: '#4f46e5',
                            700: '#4338ca',
                            900: '#312e81',
                        }
                    }
                }
            }
        }
    </script>
    <style>
        .gradient-text {
            background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 50%, #ec4899 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .hero-glow {
            background: radial-gradient(circle at 50% 20%, rgba(99, 102, 241, 0.15) 0%, rgba(255, 255, 255, 0) 70%);
        }
    </style>
</head>
<body class="bg-[#fafafa] text-slate-800 font-sans antialiased overflow-x-hidden">

    <!-- Header / Navbar -->
    <header class="fixed top-0 left-0 right-0 z-50 bg-white/80 backdrop-blur-md border-b border-slate-100 transition-all">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-20 flex items-center justify-between">
            <!-- Brand Logo -->
            <a href="/" class="flex items-center gap-3 group">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-brand-600 to-indigo-500 flex items-center justify-center text-white shadow-lg shadow-brand-500/30 group-hover:scale-105 transition-transform">
                    <i class="fa-solid fa-bolt-lightning text-lg"></i>
                </div>
                <div>
                    <span class="text-xl font-extrabold tracking-tight text-slate-900">Vx<span class="text-brand-600">POS</span></span>
                    <span class="hidden sm:inline-block text-[10px] font-semibold tracking-wider uppercase px-2 py-0.5 ml-2 bg-indigo-50 text-brand-600 rounded-full border border-indigo-100">Multi-Store</span>
                </div>
            </a>

            <!-- Navigation Links (Desktop) -->
            <nav class="hidden md:flex items-center gap-8 text-sm font-semibold text-slate-600">
                <a href="#fitur" class="hover:text-brand-600 transition-colors">Fitur Utama</a>
                <a href="#multi-toko" class="hover:text-brand-600 transition-colors">Multi-Toko</a>
                <a href="#keunggulan" class="hover:text-brand-600 transition-colors">Sistem Anti-Rugi</a>
                <a href="#pricing" class="hover:text-brand-600 transition-colors">Paket Harga</a>
                <a href="#faq" class="hover:text-brand-600 transition-colors">FAQ</a>
            </nav>

            <!-- Action Button -->
            <div class="flex items-center gap-3">
                <a href="{{ route('login') }}" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm font-bold text-white bg-brand-600 hover:bg-brand-700 shadow-lg shadow-brand-600/25 hover:shadow-brand-600/40 hover:-translate-y-0.5 transition-all">
                    <span>Masuk ke Toko</span>
                    <i class="fa-solid fa-arrow-right text-xs"></i>
                </a>
            </div>
        </div>
    </header>

    <!-- Hero Section -->
    <section class="relative pt-36 pb-20 md:pt-44 md:pb-32 hero-glow overflow-hidden">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center relative z-10">
            <!-- Badge -->
            <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-indigo-50/80 border border-indigo-100/80 text-brand-700 text-xs sm:text-sm font-semibold mb-6 shadow-sm">
                <span class="flex h-2 w-2 rounded-full bg-brand-600 animate-ping"></span>
                <span>Platform Kasir & ERP Terpadu untuk Banyak Toko</span>
            </div>

            <!-- Headline -->
            <h1 class="text-4xl sm:text-5xl lg:text-6xl font-extrabold text-slate-900 tracking-tight leading-[1.15] max-w-4xl mx-auto mb-6">
                Satu Platform Cerdas untuk Mengelola <span class="gradient-text">Banyak Cabang & Toko</span>
            </h1>

            <!-- Subtitle -->
            <p class="text-base sm:text-lg lg:text-xl text-slate-600 max-w-2xl mx-auto mb-10 leading-relaxed">
                Kelola kasir cepat, alur gudang fisik terverifikasi, proteksi harga modal anti-rugi, serta buku piutang dan slip gaji di semua cabang toko Anda dari satu dashboard terpusat.
            </p>

            <!-- CTA Buttons -->
            <div class="flex flex-col sm:flex-row items-center justify-center gap-4 mb-16">
                <a href="{{ route('login') }}" class="w-full sm:w-auto px-8 py-3.5 rounded-xl text-base font-bold text-white bg-slate-900 hover:bg-brand-600 shadow-xl shadow-slate-900/10 hover:shadow-brand-600/30 transition-all flex items-center justify-center gap-3">
                    <i class="fa-solid fa-store"></i>
                    <span>Buka Sistem Toko</span>
                </a>
                <a href="#pricing" class="w-full sm:w-auto px-8 py-3.5 rounded-xl text-base font-bold text-slate-700 bg-white hover:bg-slate-50 border border-slate-200 shadow-sm transition-all flex items-center justify-center gap-2">
                    <i class="fa-solid fa-tag text-slate-400"></i>
                    <span>Lihat Paket Langganan</span>
                </a>
            </div>

            <!-- Dashboard Preview Mockup -->
            <div class="relative max-w-5xl mx-auto mt-6">
                <div class="rounded-2xl p-2 bg-gradient-to-b from-slate-200 via-slate-100 to-white shadow-2xl shadow-slate-300/60 border border-slate-200">
                    <div class="bg-slate-900 rounded-xl p-4 sm:p-6 text-left text-white shadow-inner">
                        <!-- Window Header -->
                        <div class="flex items-center justify-between border-b border-slate-800 pb-4 mb-6">
                            <div class="flex items-center gap-2">
                                <span class="w-3 h-3 rounded-full bg-rose-500 inline-block"></span>
                                <span class="w-3 h-3 rounded-full bg-amber-500 inline-block"></span>
                                <span class="w-3 h-3 rounded-full bg-emerald-500 inline-block"></span>
                                <span class="ml-3 text-xs font-mono text-slate-400 hidden sm:inline">https://app.vxpos.id/superadmin/dashboard</span>
                            </div>
                            <div class="flex items-center gap-3">
                                <span class="text-xs bg-slate-800 px-3 py-1 rounded-lg text-emerald-400 font-medium flex items-center gap-1.5">
                                    <i class="fa-solid fa-circle text-[7px] text-emerald-400"></i> Multi-Tenant Ready
                                </span>
                            </div>
                        </div>

                        <!-- Stats Cards in Mockup -->
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                            <div class="bg-slate-800/80 p-4 rounded-xl border border-slate-700/50">
                                <p class="text-xs text-slate-400">Total Omzet Bulan Ini</p>
                                <p class="text-xl sm:text-2xl font-bold text-white mt-1">Rp 148.520.000</p>
                                <p class="text-[11px] text-emerald-400 mt-1"><i class="fa-solid fa-arrow-trend-up"></i> +18.4% bulan ini</p>
                            </div>
                            <div class="bg-slate-800/80 p-4 rounded-xl border border-slate-700/50">
                                <p class="text-xs text-slate-400">Toko / Cabang Aktif</p>
                                <p class="text-xl sm:text-2xl font-bold text-indigo-400 mt-1">5 Toko</p>
                                <p class="text-[11px] text-slate-400 mt-1">Tersinkronisasi Realtime</p>
                            </div>
                            <div class="bg-slate-800/80 p-4 rounded-xl border border-slate-700/50">
                                <p class="text-xs text-slate-400">Validasi Fisik Gudang</p>
                                <p class="text-xl sm:text-2xl font-bold text-amber-400 mt-1">12 Order</p>
                                <p class="text-[11px] text-slate-400 mt-1">Menunggu Penyiapan</p>
                            </div>
                            <div class="bg-slate-800/80 p-4 rounded-xl border border-slate-700/50">
                                <p class="text-xs text-slate-400">Margin Laba Terproteksi</p>
                                <p class="text-xl sm:text-2xl font-bold text-emerald-400 mt-1">100% Aman</p>
                                <p class="text-[11px] text-emerald-400 mt-1">Anti-Rugi Aktif</p>
                            </div>
                        </div>

                        <!-- Mini Mockup Rows -->
                        <div class="bg-slate-800/40 rounded-xl p-4 border border-slate-700/40 hidden sm:block">
                            <div class="flex items-center justify-between text-xs text-slate-400 border-b border-slate-700/60 pb-2 mb-3">
                                <span>CABANG / TOKO</span>
                                <span>STATUS OPERASIONAL</span>
                                <span>OMZET HARI INI</span>
                                <span>AKSI</span>
                            </div>
                            <div class="space-y-2 text-xs">
                                <div class="flex items-center justify-between py-1.5 text-slate-300">
                                    <span class="font-semibold text-white flex items-center gap-2"><i class="fa-solid fa-shop text-brand-400"></i> Toko Pusat - VxPOS Flagship</span>
                                    <span class="px-2 py-0.5 rounded bg-emerald-500/20 text-emerald-300 text-[10px]">Aktif Melayani</span>
                                    <span>Rp 14.850.000</span>
                                    <span class="text-indigo-400 hover:underline cursor-pointer">Buka Kasir &rarr;</span>
                                </div>
                                <div class="flex items-center justify-between py-1.5 text-slate-300">
                                    <span class="font-semibold text-white flex items-center gap-2"><i class="fa-solid fa-shop text-indigo-400"></i> Cabang 2 - Sentosa Motor</span>
                                    <span class="px-2 py-0.5 rounded bg-emerald-500/20 text-emerald-300 text-[10px]">Aktif Melayani</span>
                                    <span>Rp 9.240.000</span>
                                    <span class="text-indigo-400 hover:underline cursor-pointer">Buka Kasir &rarr;</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Section: Kenapa Multi-Toko? -->
    <section id="multi-toko" class="py-20 bg-white border-t border-slate-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-3xl mx-auto mb-16">
                <span class="text-brand-600 text-xs sm:text-sm font-bold uppercase tracking-wider">Arsitektur Multi-Tenant</span>
                <h2 class="text-3xl sm:text-4xl font-extrabold text-slate-900 mt-2 mb-4">
                    Satu Akun Sistem, Bebas Buka Banyak Toko & Mitra
                </h2>
                <p class="text-slate-600 text-base sm:text-lg">
                    Setiap toko memiliki data mandiri yang terisolasi secara aman. Anda dapat mengontrol semuanya dari dashboard utama tanpa pusing instalasi server berulang kali.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="p-8 rounded-2xl bg-slate-50 border border-slate-100 hover:border-brand-200 hover:shadow-xl hover:shadow-brand-500/5 transition-all">
                    <div class="w-12 h-12 rounded-xl bg-brand-500/10 text-brand-600 flex items-center justify-center text-xl mb-6">
                        <i class="fa-solid fa-shield-halved"></i>
                    </div>
                    <h3 class="text-xl font-bold text-slate-900 mb-3">Isolasi Data Aman</h3>
                    <p class="text-slate-600 text-sm leading-relaxed">
                        Data produk, stok, transaksi kasir, dan laporan keuangan toko A tidak akan pernah bercampur dengan toko B berkat pembatasan level database otomatis.
                    </p>
                </div>

                <div class="p-8 rounded-2xl bg-slate-50 border border-slate-100 hover:border-brand-200 hover:shadow-xl hover:shadow-brand-500/5 transition-all">
                    <div class="w-12 h-12 rounded-xl bg-indigo-500/10 text-indigo-600 flex items-center justify-center text-xl mb-6">
                        <i class="fa-solid fa-users-gear"></i>
                    </div>
                    <h3 class="text-xl font-bold text-slate-900 mb-3">Hak Akses Fleksibel</h3>
                    <p class="text-slate-600 text-sm leading-relaxed">
                        Tentukan peran pengguna dengan mudah: Superadmin Platform, Owner Toko, Kasir POS, Petugas Gudang, hingga Sales lapangan per cabang.
                    </p>
                </div>

                <div class="p-8 rounded-2xl bg-slate-50 border border-slate-100 hover:border-brand-200 hover:shadow-xl hover:shadow-brand-500/5 transition-all">
                    <div class="w-12 h-12 rounded-xl bg-purple-500/10 text-purple-600 flex items-center justify-center text-xl mb-6">
                        <i class="fa-solid fa-chart-pie"></i>
                    </div>
                    <h3 class="text-xl font-bold text-slate-900 mb-3">Laporan Konsolidasi</h3>
                    <p class="text-slate-600 text-sm leading-relaxed">
                        Pantau performa omzet masing-masing toko secara terpisah, atau gabungkan dalam satu laporan eksekutif untuk melihat total pertumbuhan bisnis.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Section: Fitur Unggulan -->
    <section id="fitur" class="py-20 bg-slate-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-3xl mx-auto mb-16">
                <span class="text-brand-600 text-xs sm:text-sm font-bold uppercase tracking-wider">Fitur Operasional Lengkap</span>
                <h2 class="text-3xl sm:text-4xl font-extrabold text-slate-900 mt-2 mb-4">
                    Dirancang Khusus untuk Bisnis Suku Cadang & Retail
                </h2>
                <p class="text-slate-600 text-base sm:text-lg">
                    Semua kebutuhan operasional toko dari input kasir hingga slip gaji karyawan sudah terintegrasi tanpa perlu aplikasi tambahan.
                </p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 sm:gap-8">
                <!-- Feature 1 -->
                <div class="bg-white p-7 rounded-2xl border border-slate-100 shadow-sm hover:shadow-md transition-shadow">
                    <div class="w-10 h-10 rounded-lg bg-indigo-50 text-brand-600 flex items-center justify-center text-lg mb-4">
                        <i class="fa-solid fa-cash-register"></i>
                    </div>
                    <h4 class="text-lg font-bold text-slate-900 mb-2">Kasir POS Cepat & Responsif</h4>
                    <p class="text-slate-600 text-sm leading-relaxed">
                        Pencarian barang instan, perhitungan diskon per item, uang muka (DP), dan cetak nota kasir rapi yang kompatibel di PC, laptop, maupun tablet.
                    </p>
                </div>

                <!-- Feature 2 -->
                <div id="keunggulan" class="bg-white p-7 rounded-2xl border border-slate-100 shadow-sm hover:shadow-md transition-shadow">
                    <div class="w-10 h-10 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center text-lg mb-4">
                        <i class="fa-solid fa-lock"></i>
                    </div>
                    <h4 class="text-lg font-bold text-slate-900 mb-2">Proteksi Margin (Sistem Anti-Rugi)</h4>
                    <p class="text-slate-600 text-sm leading-relaxed">
                        Validasi backend otomatis menolak pemberian diskon yang menembus harga modal. Lindungi keuntungan toko Anda dari kesalahan kasir.
                    </p>
                </div>

                <!-- Feature 3 -->
                <div class="bg-white p-7 rounded-2xl border border-slate-100 shadow-sm hover:shadow-md transition-shadow">
                    <div class="w-10 h-10 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center text-lg mb-4">
                        <i class="fa-solid fa-boxes-stacked"></i>
                    </div>
                    <h4 class="text-lg font-bold text-slate-900 mb-2">Alur Verifikasi Fisik Gudang</h4>
                    <p class="text-slate-600 text-sm leading-relaxed">
                        Stok tidak langsung berkurang sebelum tim gudang memverifikasi barang fisik secara nyata. Dilengkapi kunci baris (*lockForUpdate*) anti-selisih.
                    </p>
                </div>

                <!-- Feature 4 -->
                <div class="bg-white p-7 rounded-2xl border border-slate-100 shadow-sm hover:shadow-md transition-shadow">
                    <div class="w-10 h-10 rounded-lg bg-sky-50 text-sky-600 flex items-center justify-center text-lg mb-4">
                        <i class="fa-solid fa-book-bookmark"></i>
                    </div>
                    <h4 class="text-lg font-bold text-slate-900 mb-2">Buku Piutang & Kartu Cicilan</h4>
                    <p class="text-slate-600 text-sm leading-relaxed">
                        Lacak riwayat pelanggan yang berhutang, catat cicilan berjalan secara real-time, dan unduh rekap piutang ke Excel dengan mudah.
                    </p>
                </div>

                <!-- Feature 5 -->
                <div class="bg-white p-7 rounded-2xl border border-slate-100 shadow-sm hover:shadow-md transition-shadow">
                    <div class="w-10 h-10 rounded-lg bg-rose-50 text-rose-600 flex items-center justify-center text-lg mb-4">
                        <i class="fa-solid fa-hand-holding-dollar"></i>
                    </div>
                    <h4 class="text-lg font-bold text-slate-900 mb-2">Payroll & Komisi Sales</h4>
                    <p class="text-slate-600 text-sm leading-relaxed">
                        Hitung bonus sales otomatis dari transaksi lunas, kelola tunjangan dan potongan karyawan, serta cetak slip gaji resmi satu klik.
                    </p>
                </div>

                <!-- Feature 6 -->
                <div class="bg-white p-7 rounded-2xl border border-slate-100 shadow-sm hover:shadow-md transition-shadow">
                    <div class="w-10 h-10 rounded-lg bg-violet-50 text-violet-600 flex items-center justify-center text-lg mb-4">
                        <i class="fa-solid fa-cloud-arrow-down"></i>
                    </div>
                    <h4 class="text-lg font-bold text-slate-900 mb-2">Backup Database Mandiri</h4>
                    <p class="text-slate-600 text-sm leading-relaxed">
                        Unduh salinan cadangan SQL seluruh struktur dan isi data toko kapan saja untuk jaminan keamanan data tingkat tinggi.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Pricing Section -->
    <section id="pricing" class="py-20 bg-white border-t border-slate-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-3xl mx-auto mb-16">
                <span class="text-brand-600 text-xs sm:text-sm font-bold uppercase tracking-wider">Pilihan Paket Langganan</span>
                <h2 class="text-3xl sm:text-4xl font-extrabold text-slate-900 mt-2 mb-4">
                    Paket Investasi yang Sesuai dengan Skala Toko Anda
                </h2>
                <p class="text-slate-600 text-base sm:text-lg">
                    Tingkatkan efisiensi dan hentikan kebocoran uang toko Anda hari ini.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8 max-w-6xl mx-auto">
                <!-- Starter Plan -->
                <div class="rounded-2xl border border-slate-200 p-8 flex flex-col justify-between hover:border-slate-300 transition-all">
                    <div>
                        <h3 class="text-lg font-bold text-slate-900">Starter</h3>
                        <p class="text-slate-500 text-xs mt-1">Cocok untuk 1 toko tunggal yang baru memulai.</p>
                        <div class="my-6">
                            <span class="text-4xl font-extrabold text-slate-900">Rp 149rb</span>
                            <span class="text-slate-500 text-sm">/bulan</span>
                        </div>
                        <ul class="space-y-3 text-sm text-slate-600">
                            <li class="flex items-center gap-2.5"><i class="fa-solid fa-check text-emerald-500 text-xs"></i> 1 Toko / Cabang</li>
                            <li class="flex items-center gap-2.5"><i class="fa-solid fa-check text-emerald-500 text-xs"></i> Maksimal 3 User (Kasir/Admin)</li>
                            <li class="flex items-center gap-2.5"><i class="fa-solid fa-check text-emerald-500 text-xs"></i> POS Kasir & Cetak Nota</li>
                            <li class="flex items-center gap-2.5"><i class="fa-solid fa-check text-emerald-500 text-xs"></i> Manajemen Stok & Gudang</li>
                            <li class="flex items-center gap-2.5 text-slate-400"><i class="fa-solid fa-xmark text-slate-300 text-xs"></i> Multi Cabang Konsolidasi</li>
                        </ul>
                    </div>
                    <a href="{{ route('login') }}" class="mt-8 block text-center py-3 px-4 rounded-xl text-sm font-bold text-slate-800 bg-slate-100 hover:bg-slate-200 transition-colors">
                        Pilih Starter
                    </a>
                </div>

                <!-- Pro Plan (Featured) -->
                <div class="rounded-2xl border-2 border-brand-600 p-8 flex flex-col justify-between relative shadow-xl shadow-brand-500/10 bg-white">
                    <div class="absolute -top-3.5 left-1/2 -translate-x-1/2 bg-brand-600 text-white text-[11px] font-bold uppercase tracking-wider px-3.5 py-1 rounded-full shadow-sm">
                        Paling Populer
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-slate-900">Professional</h3>
                        <p class="text-slate-500 text-xs mt-1">Untuk toko yang ingin berkembang dan memiliki cabang.</p>
                        <div class="my-6">
                            <span class="text-4xl font-extrabold text-brand-600">Rp 299rb</span>
                            <span class="text-slate-500 text-sm">/bulan</span>
                        </div>
                        <ul class="space-y-3 text-sm text-slate-600">
                            <li class="flex items-center gap-2.5"><i class="fa-solid fa-check text-emerald-500 text-xs"></i> <strong>Hingga 5 Cabang Toko</strong></li>
                            <li class="flex items-center gap-2.5"><i class="fa-solid fa-check text-emerald-500 text-xs"></i> <strong>Unlimited User Kasir & Sales</strong></li>
                            <li class="flex items-center gap-2.5"><i class="fa-solid fa-check text-emerald-500 text-xs"></i> Proteksi Margin Anti-Rugi</li>
                            <li class="flex items-center gap-2.5"><i class="fa-solid fa-check text-emerald-500 text-xs"></i> Buku Piutang & Kartu Cicilan</li>
                            <li class="flex items-center gap-2.5"><i class="fa-solid fa-check text-emerald-500 text-xs"></i> Payroll Gaji & Komisi Sales</li>
                            <li class="flex items-center gap-2.5"><i class="fa-solid fa-check text-emerald-500 text-xs"></i> Ekspor Laporan Excel</li>
                        </ul>
                    </div>
                    <a href="{{ route('login') }}" class="mt-8 block text-center py-3 px-4 rounded-xl text-sm font-bold text-white bg-brand-600 hover:bg-brand-700 shadow-md shadow-brand-600/30 transition-all">
                        Coba Paket Pro
                    </a>
                </div>

                <!-- Enterprise Plan -->
                <div class="rounded-2xl border border-slate-200 p-8 flex flex-col justify-between hover:border-slate-300 transition-all">
                    <div>
                        <h3 class="text-lg font-bold text-slate-900">Enterprise</h3>
                        <p class="text-slate-500 text-xs mt-1">Solusi custom untuk jaringan distributor skala besar.</p>
                        <div class="my-6">
                            <span class="text-4xl font-extrabold text-slate-900">Custom</span>
                            <span class="text-slate-500 text-sm">/tahunan</span>
                        </div>
                        <ul class="space-y-3 text-sm text-slate-600">
                            <li class="flex items-center gap-2.5"><i class="fa-solid fa-check text-emerald-500 text-xs"></i> Unlimited Cabang Toko</li>
                            <li class="flex items-center gap-2.5"><i class="fa-solid fa-check text-emerald-500 text-xs"></i> Dedicated Server / Private Cloud</li>
                            <li class="flex items-center gap-2.5"><i class="fa-solid fa-check text-emerald-500 text-xs"></i> Custom Domain (nama.tokoanda.com)</li>
                            <li class="flex items-center gap-2.5"><i class="fa-solid fa-check text-emerald-500 text-xs"></i> Bantuan Migrasi Data Suku Cadang</li>
                            <li class="flex items-center gap-2.5"><i class="fa-solid fa-check text-emerald-500 text-xs"></i> Priority Support 24/7</li>
                        </ul>
                    </div>
                    <a href="https://wa.me/6281234567890?text=Halo%20saya%20tertarik%20paket%20Enterprise%20VX-POS" target="_blank" class="mt-8 block text-center py-3 px-4 rounded-xl text-sm font-bold text-slate-800 bg-slate-100 hover:bg-slate-200 transition-colors">
                        Hubungi Sales
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- FAQ Section -->
    <section id="faq" class="py-20 bg-slate-50 border-t border-slate-100">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <span class="text-brand-600 text-xs sm:text-sm font-bold uppercase tracking-wider">Tanya Jawab</span>
                <h2 class="text-3xl font-extrabold text-slate-900 mt-2">Pertanyaan yang Sering Diajukan</h2>
            </div>

            <div class="space-y-4">
                <div class="p-6 bg-white rounded-xl border border-slate-100 shadow-sm">
                    <h4 class="text-base font-bold text-slate-900 flex items-center justify-between">
                        <span>Apakah data antar toko saya benar-benar terpisah?</span>
                        <i class="fa-solid fa-angle-down text-slate-400 text-sm"></i>
                    </h4>
                    <p class="text-slate-600 text-sm mt-2 leading-relaxed">
                        Ya. Sistem menggunakan arsitektur multi-tenant dengan isolasi `toko_id`. Kasir dan admin di Toko A tidak akan pernah bisa melihat transaksi, stok, atau laporan keuangan milik Toko B.
                    </p>
                </div>

                <div class="p-6 bg-white rounded-xl border border-slate-100 shadow-sm">
                    <h4 class="text-base font-bold text-slate-900 flex items-center justify-between">
                        <span>Apakah aplikasi ini bisa diakses dari HP atau tablet kasir?</span>
                        <i class="fa-solid fa-angle-down text-slate-400 text-sm"></i>
                    </h4>
                    <p class="text-slate-600 text-sm mt-2 leading-relaxed">
                        Tentu saja. Tampilan kasir POS dan dashboard admin sudah didesain responsif, fleksibel digunakan pada layar monitor PC, laptop, iPad/tablet, hingga smartphone Android kasir.
                    </p>
                </div>

                <div class="p-6 bg-white rounded-xl border border-slate-100 shadow-sm">
                    <h4 class="text-base font-bold text-slate-900 flex items-center justify-between">
                        <span>Bagaimana sistem mencegah kerugian akibat diskon berlebihan?</span>
                        <i class="fa-solid fa-angle-down text-slate-400 text-sm"></i>
                    </h4>
                    <p class="text-slate-600 text-sm mt-2 leading-relaxed">
                        Setiap barang memiliki Harga Modal, Harga Minimum, dan Harga Jual. Jika kasir mencoba menginput diskon yang membuat harga akhir lebih rendah dari Harga Minimum, backend sistem otomatis menolak transaksi atau mengembalikannya ke batas bawah yang aman.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-slate-900 text-slate-400 py-12 border-t border-slate-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col md:flex-row items-center justify-between gap-6 pb-8 border-b border-slate-800">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-brand-600 flex items-center justify-center text-white font-bold">
                        <i class="fa-solid fa-bolt-lightning text-sm"></i>
                    </div>
                    <span class="text-xl font-extrabold text-white">Vx<span class="text-brand-500">POS</span></span>
                </div>
                <p class="text-xs text-slate-500 text-center md:text-right">
                    Solusi POS & Manajemen Multi-Toko Modern &bull; Powered by Laravel 11
                </p>
            </div>
            <div class="pt-8 flex flex-col sm:flex-row items-center justify-between gap-4 text-xs text-slate-500">
                <p>&copy; 2026 VxPOS Multi-Store Platform. Hak Cipta by. Vicky Koroh.</p>
                <div class="flex gap-6">
                    <a href="{{ route('login') }}" class="hover:text-white transition-colors">Login Toko</a>
                    <a href="#fitur" class="hover:text-white transition-colors">Fitur</a>
                    <a href="#pricing" class="hover:text-white transition-colors">Harga</a>
                </div>
            </div>
        </div>
    </footer>

</body>
</html>
