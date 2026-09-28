@php
    $cms = \App\Services\LandingPageService::get();
    $cleanWa = !empty($cms->wa_number) ? preg_replace('/[^0-9]/', '', $cms->wa_number) : '6281234567890';
    $waText = urlencode($cms->wa_message ?? 'Halo Admin VxPOS, saya ingin konsultasi sistem POS dan implementasi toko');
    $waUrl = "https://wa.me/{$cleanWa}?text={$waText}";
@endphp
<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $cms->brand_name ?? 'VxPOS' }} | {{ $cms->tagline ?? 'Sistem Kasir & ERP Terpadu Multi-Toko' }}</title>
    
    @if(!empty($cms->favicon))
        <link rel="icon" href="{{ asset($cms->favicon) }}">
    @else
        <link rel="icon" href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><text y=%22.9em%22 font-size=%2290%22>⚡</text></svg>">
    @endif
    
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
        @keyframes floatSlow {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-12px) rotate(0.8deg); }
        }
        @keyframes floatReverse {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(12px) rotate(-0.8deg); }
        }
        .animate-float-slow {
            animation: floatSlow 5s ease-in-out infinite;
        }
        .animate-float-reverse {
            animation: floatReverse 6s ease-in-out infinite;
        }

        /* Modern Shimmer Glow Motion */
        @keyframes shimmerSweep {
            0% { transform: translateX(-150%) skewX(-20deg); }
            100% { transform: translateX(250%) skewX(-20deg); }
        }
        .btn-shimmer {
            position: relative;
            overflow: hidden;
        }
        .btn-shimmer::after {
            content: '';
            position: absolute;
            top: 0; left: 0; width: 60%; height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.45), transparent);
            transform: translateX(-150%) skewX(-20deg);
            animation: shimmerSweep 4s cubic-bezier(0.4, 0, 0.2, 1) infinite;
        }
        .badge-shimmer {
            position: relative;
            overflow: hidden;
        }
        .badge-shimmer::after {
            content: '';
            position: absolute;
            top: 0; left: 0; width: 50%; height: 100%;
            background: linear-gradient(90deg, transparent, rgba(99, 102, 241, 0.25), transparent);
            animation: shimmerSweep 4.5s ease-in-out infinite;
        }

        /* Cursor Ambient Glow */
        .cursor-ambient-glow {
            position: absolute;
            width: 550px;
            height: 550px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(99, 102, 241, 0.15) 0%, rgba(168, 85, 247, 0.08) 40%, transparent 70%);
            pointer-events: none;
            transform: translate(-50%, -50%);
            transition: transform 0.15s ease-out, opacity 0.4s ease;
            z-index: 1;
        }

        /* Scroll Reveal Animation */
        .reveal-on-scroll {
            opacity: 0;
            transform: translateY(28px);
            transition: opacity 0.75s cubic-bezier(0.16, 1, 0.3, 1), transform 0.75s cubic-bezier(0.16, 1, 0.3, 1);
            will-change: opacity, transform;
        }
        .reveal-on-scroll.is-revealed {
            opacity: 1;
            transform: translateY(0);
        }

        /* 3D Depth Card Hover */
        .interactive-card {
            transition: transform 0.3s cubic-bezier(0.16, 1, 0.3, 1), box-shadow 0.3s cubic-bezier(0.16, 1, 0.3, 1);
        }
        .interactive-card:hover {
            transform: translateY(-5px);
        }
        .hero-mockup-perspective {
            perspective: 1000px;
            transition: transform 0.25s ease-out;
        }
    </style>
</head>
<body class="bg-[#fafafa] text-slate-800 font-sans antialiased overflow-x-hidden">

    <!-- Header / Navbar -->
    <header class="fixed top-0 left-0 right-0 z-50 bg-white/80 backdrop-blur-md border-b border-slate-100 transition-all">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-20 flex items-center justify-between">
            <!-- Brand Logo -->
            <a href="/" class="flex items-center gap-3 group">
                @if(!empty($cms->logo))
                    <img src="{{ asset($cms->logo) }}" alt="{{ $cms->brand_name ?? 'VxPOS' }}" class="h-10 w-auto max-w-[160px] object-contain">
                @else
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-brand-600 to-indigo-500 flex items-center justify-center text-white shadow-lg shadow-brand-500/30 group-hover:scale-105 transition-transform">
                        <i class="fa-solid fa-bolt-lightning text-lg"></i>
                    </div>
                @endif
                <div>
                    <span class="text-xl font-extrabold tracking-tight text-slate-900">{{ $cms->brand_name ?? 'VxPOS' }}</span>
                    <span class="hidden sm:inline-block text-[10px] font-semibold tracking-wider uppercase px-2 py-0.5 ml-2 bg-indigo-50 text-brand-600 rounded-full border border-indigo-100">Multi-Store</span>
                </div>
            </a>

            <!-- Navigation Links (Desktop) -->
            <nav class="hidden md:flex items-center gap-8 text-sm font-semibold text-slate-600">
                <a href="#fitur" class="hover:text-brand-600 transition-colors">Fitur Utama</a>
                <a href="#multi-toko" class="hover:text-brand-600 transition-colors">Multi-Toko</a>
                <a href="#keunggulan" class="hover:text-brand-600 transition-colors">Sistem Anti-Rugi</a>
                <a href="#pricing" class="hover:text-brand-600 transition-colors">Paket Harga</a>
                <a href="#konsultasi" class="hover:text-brand-600 transition-colors">Konsultasi</a>
                <a href="#testimoni" class="hover:text-brand-600 transition-colors">Testimoni</a>
                <a href="#faq" class="hover:text-brand-600 transition-colors">FAQ</a>
            </nav>

            <!-- Action Button -->
            <div class="flex items-center gap-3">
                <a href="{{ route('login.demo.quick', 'admin') }}" class="hidden sm:inline-flex items-center gap-1.5 px-4 py-2.5 rounded-xl text-sm font-bold text-amber-700 bg-amber-100 hover:bg-amber-200 transition-all border border-amber-200">
                    <i class="fa-solid fa-flask-vial"></i>
                    <span>Coba Demo</span>
                </a>
                <a href="{{ route('login') }}" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm font-bold text-white bg-brand-600 hover:bg-brand-700 shadow-lg shadow-brand-600/25 hover:shadow-brand-600/40 hover:-translate-y-0.5 transition-all">
                    <span>Masuk ke Toko</span>
                    <i class="fa-solid fa-arrow-right text-xs"></i>
                </a>
            </div>
        </div>
    </header>

    <!-- Hero Section -->
    <section class="relative pt-36 pb-20 md:pt-44 md:pb-32 hero-glow overflow-hidden">
        <!-- Interactive Motion Graphic Canvas -->
        <canvas id="motionCanvas" class="absolute inset-0 w-full h-full pointer-events-none z-0 opacity-50"></canvas>
        <div id="cursorGlow" class="cursor-ambient-glow hidden md:block opacity-0"></div>
        <!-- Floating Gradient Orbs -->
        <div class="absolute top-20 -left-32 w-96 h-96 bg-gradient-to-br from-indigo-400/20 to-purple-500/10 rounded-full blur-3xl animate-float-slow pointer-events-none"></div>
        <div class="absolute bottom-10 -right-24 w-80 h-80 bg-gradient-to-tr from-pink-400/15 to-amber-400/10 rounded-full blur-3xl animate-float-reverse pointer-events-none"></div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center relative z-10">
            <!-- Badge -->
            <div class="badge-shimmer inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-indigo-50/80 border border-indigo-100/80 text-brand-700 text-xs sm:text-sm font-semibold mb-6 shadow-sm">
                <span class="flex h-2 w-2 rounded-full bg-brand-600 animate-ping"></span>
                <span>{{ $cms->hero_badge ?? 'Platform Kasir & ERP Terpadu untuk Banyak Toko' }}</span>
            </div>

            <!-- Headline -->
            <h1 class="text-4xl sm:text-5xl lg:text-6xl font-extrabold text-slate-900 tracking-tight leading-[1.15] max-w-4xl mx-auto mb-6">
                {!! nl2br(e($cms->hero_title ?? 'Satu Platform Cerdas untuk Mengelola Banyak Cabang & Toko')) !!}
            </h1>

            <!-- Subtitle -->
            <p class="text-base sm:text-lg lg:text-xl text-slate-600 max-w-2xl mx-auto mb-10 leading-relaxed">
                {{ $cms->hero_subtitle ?? 'Kelola kasir cepat, alur gudang fisik terverifikasi, proteksi harga modal anti-rugi, serta buku piutang dan slip gaji di semua cabang toko Anda dari satu dashboard terpusat.' }}
            </p>

            <!-- CTA Buttons -->
            <div class="flex flex-col sm:flex-row items-center justify-center gap-4 mb-16">
                <a href="{{ $cms->cta_btn_primary_link ?: route('login.demo.quick', 'admin') }}" class="btn-shimmer w-full sm:w-auto px-8 py-3.5 rounded-xl text-base font-bold text-slate-900 bg-amber-400 hover:bg-amber-300 shadow-xl shadow-amber-400/20 hover:-translate-y-0.5 transition-all flex items-center justify-center gap-3">
                    <i class="fa-solid fa-flask-vial"></i>
                    <span>{{ $cms->cta_btn_primary_text ?: 'Coba Demo 1-Klik' }}</span>
                </a>
                <a href="{{ route('login') }}" class="w-full sm:w-auto px-8 py-3.5 rounded-xl text-base font-bold text-white bg-slate-900 hover:bg-brand-600 shadow-xl shadow-slate-900/10 hover:shadow-brand-600/30 hover:-translate-y-0.5 transition-all flex items-center justify-center gap-3">
                    <i class="fa-solid fa-store"></i>
                    <span>Buka Sistem Toko</span>
                </a>
                <a href="{{ $cms->cta_btn_secondary_link ?: $waUrl }}" target="{{ str_starts_with($cms->cta_btn_secondary_link ?? '', 'http') || $cms->cta_btn_secondary_link == null ? '_blank' : '_self' }}" class="w-full sm:w-auto px-8 py-3.5 rounded-xl text-base font-bold text-emerald-700 bg-emerald-50 hover:bg-emerald-100 border border-emerald-200 shadow-sm transition-all flex items-center justify-center gap-2">
                    <i class="fa-brands fa-whatsapp text-emerald-600 text-lg"></i>
                    <span>{{ $cms->cta_btn_secondary_text ?: 'Konsultasi WhatsApp' }}</span>
                </a>
            </div>

            <!-- Dashboard Preview Mockup with Floating Interactive Cards -->
            <div class="relative max-w-5xl mx-auto mt-6 hero-mockup-perspective" id="heroMockupWrapper">
                
                <!-- Floating Motion Card 1 (Top Left) -->
                <div class="hidden lg:flex items-center gap-3.5 absolute -top-8 -left-8 bg-white/95 backdrop-blur-md p-3.5 rounded-2xl shadow-xl shadow-slate-200/50 border border-slate-100 z-20 animate-float-slow text-left">
                    <div class="w-10 h-10 rounded-xl bg-emerald-500/10 text-emerald-600 flex items-center justify-center font-bold text-base shadow-inner">
                        <i class="fa-solid fa-receipt"></i>
                    </div>
                    <div>
                        <p class="text-[10px] text-slate-400 font-bold uppercase tracking-wider">Kasir Real-Time</p>
                        <p class="text-xs font-bold text-slate-900">Rp 2.450.000 <span class="text-[10px] text-emerald-500 bg-emerald-50 px-1.5 py-0.5 rounded font-bold ml-1">Lunas</span></p>
                    </div>
                </div>

                <!-- Floating Motion Card 2 (Bottom Right) -->
                <div class="hidden lg:flex items-center gap-3.5 absolute -bottom-6 -right-8 bg-white/95 backdrop-blur-md p-3.5 rounded-2xl shadow-xl shadow-slate-200/50 border border-slate-100 z-20 animate-float-reverse text-left">
                    <div class="w-10 h-10 rounded-xl bg-indigo-500/10 text-indigo-600 flex items-center justify-center font-bold text-base shadow-inner">
                        <i class="fa-solid fa-shield-halved"></i>
                    </div>
                    <div>
                        <p class="text-[10px] text-slate-400 font-bold uppercase tracking-wider">Sistem Anti-Rugi Aktif</p>
                        <p class="text-xs font-bold text-slate-900">Margin HPP Terkunci Aman</p>
                    </div>
                </div>

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
                                <span id="mockupLiveToast" class="text-xs bg-slate-800/90 px-3 py-1 rounded-lg text-emerald-400 font-medium flex items-center gap-1.5 border border-slate-700/60 transition-all duration-500">
                                    <i class="fa-solid fa-circle text-[7px] text-emerald-400 animate-pulse"></i>
                                    <span id="liveToastText">Multi-Tenant Ready</span>
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

            <!-- Social Proof Stats Showcase from CMS -->
            <div class="max-w-4xl mx-auto mt-12 grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="p-4 rounded-2xl bg-white border border-slate-200/80 shadow-sm text-center interactive-card reveal-on-scroll">
                    <p class="text-2xl sm:text-3xl font-black text-brand-600 count-up" data-target="{{ preg_replace('/[^0-9]/', '', $cms->stat_1_val ?? '10000') }}" data-suffix="{{ preg_replace('/[0-9.]/', '', $cms->stat_1_val ?? '+') }}">{{ $cms->stat_1_val ?? '10.000+' }}</p>
                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider mt-1">{{ $cms->stat_1_label ?? 'Transaksi Diproses' }}</p>
                </div>
                <div class="p-4 rounded-2xl bg-white border border-slate-200/80 shadow-sm text-center interactive-card reveal-on-scroll">
                    <p class="text-2xl sm:text-3xl font-black text-emerald-600 count-up" data-target="{{ preg_replace('/[^0-9.]/', '', $cms->stat_2_val ?? '99.9') }}" data-suffix="{{ preg_replace('/[0-9.]/', '', $cms->stat_2_val ?? '%') }}">{{ $cms->stat_2_val ?? '99.9%' }}</p>
                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider mt-1">{{ $cms->stat_2_label ?? 'Akurasi Stok Gudang' }}</p>
                </div>
                <div class="p-4 rounded-2xl bg-white border border-slate-200/80 shadow-sm text-center interactive-card reveal-on-scroll">
                    <p class="text-2xl sm:text-3xl font-black text-indigo-600 count-up" data-target="{{ preg_replace('/[^0-9]/', '', $cms->stat_3_val ?? '0') }}" data-suffix="{{ preg_replace('/[0-9.]/', '', $cms->stat_3_val ?? '%') }}">{{ $cms->stat_3_val ?? '0%' }}</p>
                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider mt-1">{{ $cms->stat_3_label ?? 'Kebocoran Diskon' }}</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Section: Kenapa Multi-Toko? -->
    <section id="multi-toko" class="py-20 bg-white border-t border-slate-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-3xl mx-auto mb-16 reveal-on-scroll">
                <span class="text-brand-600 text-xs sm:text-sm font-bold uppercase tracking-wider">Arsitektur Multi-Tenant</span>
                <h2 class="text-3xl sm:text-4xl font-extrabold text-slate-900 mt-2 mb-4">
                    Satu Akun Sistem, Bebas Buka Banyak Toko & Mitra
                </h2>
                <p class="text-slate-600 text-base sm:text-lg">
                    Setiap toko memiliki data mandiri yang terisolasi secara aman. Anda dapat mengontrol semuanya dari dashboard utama tanpa pusing instalasi server berulang kali.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="group p-8 rounded-2xl bg-white border border-slate-100 hover:border-brand-200 hover:shadow-2xl hover:shadow-brand-500/10 transition-all interactive-card reveal-on-scroll relative overflow-hidden">
                    <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-brand-500 to-indigo-500 opacity-0 group-hover:opacity-100 transition-opacity"></div>
                    <div class="absolute -right-6 -top-6 w-24 h-24 bg-brand-500/5 rounded-full group-hover:bg-brand-500/10 transition-colors"></div>
                    <div class="relative z-10">
                        <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-brand-500 to-indigo-600 text-white flex items-center justify-center text-2xl mb-6 shadow-lg shadow-brand-500/25 group-hover:scale-110 transition-transform">
                            <i class="fa-solid fa-shield-halved"></i>
                        </div>
                        <h3 class="text-xl font-bold text-slate-900 mb-3">Isolasi Data Aman</h3>
                        <p class="text-slate-600 text-sm leading-relaxed">
                            Data produk, stok, transaksi kasir, dan laporan keuangan toko A tidak akan pernah bercampur dengan toko B berkat pembatasan level database otomatis.
                        </p>
                    </div>
                </div>

                <div class="group p-8 rounded-2xl bg-white border border-slate-100 hover:border-indigo-200 hover:shadow-2xl hover:shadow-indigo-500/10 transition-all interactive-card reveal-on-scroll relative overflow-hidden">
                    <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-indigo-500 to-violet-500 opacity-0 group-hover:opacity-100 transition-opacity"></div>
                    <div class="absolute -right-6 -top-6 w-24 h-24 bg-indigo-500/5 rounded-full group-hover:bg-indigo-500/10 transition-colors"></div>
                    <div class="relative z-10">
                        <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-indigo-500 to-violet-600 text-white flex items-center justify-center text-2xl mb-6 shadow-lg shadow-indigo-500/25 group-hover:scale-110 transition-transform">
                            <i class="fa-solid fa-users-gear"></i>
                        </div>
                        <h3 class="text-xl font-bold text-slate-900 mb-3">Hak Akses Fleksibel</h3>
                        <p class="text-slate-600 text-sm leading-relaxed">
                            Tentukan peran pengguna dengan mudah: Superadmin Platform, Owner Toko, Kasir POS, Petugas Gudang, hingga Sales lapangan per cabang.
                        </p>
                    </div>
                </div>

                <div class="group p-8 rounded-2xl bg-white border border-slate-100 hover:border-purple-200 hover:shadow-2xl hover:shadow-purple-500/10 transition-all interactive-card reveal-on-scroll relative overflow-hidden">
                    <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-purple-500 to-pink-500 opacity-0 group-hover:opacity-100 transition-opacity"></div>
                    <div class="absolute -right-6 -top-6 w-24 h-24 bg-purple-500/5 rounded-full group-hover:bg-purple-500/10 transition-colors"></div>
                    <div class="relative z-10">
                        <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-purple-500 to-pink-600 text-white flex items-center justify-center text-2xl mb-6 shadow-lg shadow-purple-500/25 group-hover:scale-110 transition-transform">
                            <i class="fa-solid fa-chart-pie"></i>
                        </div>
                        <h3 class="text-xl font-bold text-slate-900 mb-3">Laporan Konsolidasi</h3>
                        <p class="text-slate-600 text-sm leading-relaxed">
                            Pantau performa omzet masing-masing toko secara terpisah, atau gabungkan dalam satu laporan eksekutif untuk melihat total pertumbuhan bisnis.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Section: Fitur Unggulan -->
    <section id="fitur" class="py-20 bg-slate-50 relative overflow-hidden">
        <!-- Section Background Accent -->
        <div class="absolute top-0 right-0 w-72 h-72 bg-brand-500/5 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute bottom-0 left-0 w-64 h-64 bg-purple-500/5 rounded-full blur-3xl pointer-events-none"></div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
            <div class="text-center max-w-3xl mx-auto mb-16 reveal-on-scroll">
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
                <div class="group bg-white p-7 rounded-2xl border border-slate-100 shadow-sm hover:shadow-xl hover:shadow-indigo-500/10 transition-all interactive-card reveal-on-scroll relative overflow-hidden">
                    <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-indigo-500 to-brand-600 opacity-0 group-hover:opacity-100 transition-opacity"></div>
                    <div class="absolute -right-4 -bottom-4 w-20 h-20 bg-indigo-500/5 rounded-full group-hover:bg-indigo-500/10 transition-colors"></div>
                    <div class="relative z-10">
                        <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-indigo-500 to-brand-600 text-white flex items-center justify-center text-xl mb-5 shadow-lg shadow-indigo-500/20 group-hover:scale-110 transition-transform">
                            <i class="fa-solid fa-cash-register"></i>
                        </div>
                        <h4 class="text-lg font-bold text-slate-900 mb-2">Kasir POS Cepat & Responsif</h4>
                        <p class="text-slate-600 text-sm leading-relaxed">
                            Pencarian barang instan, perhitungan diskon per item, uang muka (DP), dan cetak nota kasir rapi yang kompatibel di PC, laptop, maupun tablet.
                        </p>
                    </div>
                </div>

                <!-- Feature 2 -->
                <div id="keunggulan" class="group bg-white p-7 rounded-2xl border border-slate-100 shadow-sm hover:shadow-xl hover:shadow-emerald-500/10 transition-all interactive-card reveal-on-scroll relative overflow-hidden">
                    <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-emerald-500 to-teal-500 opacity-0 group-hover:opacity-100 transition-opacity"></div>
                    <div class="absolute -right-4 -bottom-4 w-20 h-20 bg-emerald-500/5 rounded-full group-hover:bg-emerald-500/10 transition-colors"></div>
                    <div class="relative z-10">
                        <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-emerald-500 to-teal-600 text-white flex items-center justify-center text-xl mb-5 shadow-lg shadow-emerald-500/20 group-hover:scale-110 transition-transform">
                            <i class="fa-solid fa-lock"></i>
                        </div>
                        <h4 class="text-lg font-bold text-slate-900 mb-2">Proteksi Margin (Sistem Anti-Rugi)</h4>
                        <p class="text-slate-600 text-sm leading-relaxed">
                            Validasi backend otomatis menolak pemberian diskon yang menembus harga modal. Lindungi keuntungan toko Anda dari kesalahan kasir.
                        </p>
                    </div>
                </div>

                <!-- Feature 3 -->
                <div class="group bg-white p-7 rounded-2xl border border-slate-100 shadow-sm hover:shadow-xl hover:shadow-amber-500/10 transition-all interactive-card reveal-on-scroll relative overflow-hidden">
                    <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-amber-500 to-orange-500 opacity-0 group-hover:opacity-100 transition-opacity"></div>
                    <div class="absolute -right-4 -bottom-4 w-20 h-20 bg-amber-500/5 rounded-full group-hover:bg-amber-500/10 transition-colors"></div>
                    <div class="relative z-10">
                        <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-amber-500 to-orange-600 text-white flex items-center justify-center text-xl mb-5 shadow-lg shadow-amber-500/20 group-hover:scale-110 transition-transform">
                            <i class="fa-solid fa-boxes-stacked"></i>
                        </div>
                        <h4 class="text-lg font-bold text-slate-900 mb-2">Alur Verifikasi Fisik Gudang</h4>
                        <p class="text-slate-600 text-sm leading-relaxed">
                            Stok tidak langsung berkurang sebelum tim gudang memverifikasi barang fisik secara nyata. Dilengkapi kunci baris (*lockForUpdate*) anti-selisih.
                        </p>
                    </div>
                </div>

                <!-- Feature 4 -->
                <div class="group bg-white p-7 rounded-2xl border border-slate-100 shadow-sm hover:shadow-xl hover:shadow-sky-500/10 transition-all interactive-card reveal-on-scroll relative overflow-hidden">
                    <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-sky-500 to-cyan-500 opacity-0 group-hover:opacity-100 transition-opacity"></div>
                    <div class="absolute -right-4 -bottom-4 w-20 h-20 bg-sky-500/5 rounded-full group-hover:bg-sky-500/10 transition-colors"></div>
                    <div class="relative z-10">
                        <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-sky-500 to-cyan-600 text-white flex items-center justify-center text-xl mb-5 shadow-lg shadow-sky-500/20 group-hover:scale-110 transition-transform">
                            <i class="fa-solid fa-book-bookmark"></i>
                        </div>
                        <h4 class="text-lg font-bold text-slate-900 mb-2">Buku Piutang & Kartu Cicilan</h4>
                        <p class="text-slate-600 text-sm leading-relaxed">
                            Lacak riwayat pelanggan yang berhutang, catat cicilan berjalan secara real-time, dan unduh rekap piutang ke Excel dengan mudah.
                        </p>
                    </div>
                </div>

                <!-- Feature 5 -->
                <div class="group bg-white p-7 rounded-2xl border border-slate-100 shadow-sm hover:shadow-xl hover:shadow-rose-500/10 transition-all interactive-card reveal-on-scroll relative overflow-hidden">
                    <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-rose-500 to-pink-500 opacity-0 group-hover:opacity-100 transition-opacity"></div>
                    <div class="absolute -right-4 -bottom-4 w-20 h-20 bg-rose-500/5 rounded-full group-hover:bg-rose-500/10 transition-colors"></div>
                    <div class="relative z-10">
                        <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-rose-500 to-pink-600 text-white flex items-center justify-center text-xl mb-5 shadow-lg shadow-rose-500/20 group-hover:scale-110 transition-transform">
                            <i class="fa-solid fa-hand-holding-dollar"></i>
                        </div>
                        <h4 class="text-lg font-bold text-slate-900 mb-2">Payroll & Komisi Sales</h4>
                        <p class="text-slate-600 text-sm leading-relaxed">
                            Hitung bonus sales otomatis dari transaksi lunas, kelola tunjangan dan potongan karyawan, serta cetak slip gaji resmi satu klik.
                        </p>
                    </div>
                </div>

                <!-- Feature 6 -->
                <div class="group bg-white p-7 rounded-2xl border border-slate-100 shadow-sm hover:shadow-xl hover:shadow-violet-500/10 transition-all interactive-card reveal-on-scroll relative overflow-hidden">
                    <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-violet-500 to-purple-500 opacity-0 group-hover:opacity-100 transition-opacity"></div>
                    <div class="absolute -right-4 -bottom-4 w-20 h-20 bg-violet-500/5 rounded-full group-hover:bg-violet-500/10 transition-colors"></div>
                    <div class="relative z-10">
                        <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-violet-500 to-purple-600 text-white flex items-center justify-center text-xl mb-5 shadow-lg shadow-violet-500/20 group-hover:scale-110 transition-transform">
                            <i class="fa-solid fa-cloud-arrow-down"></i>
                        </div>
                        <h4 class="text-lg font-bold text-slate-900 mb-2">Backup Database Mandiri</h4>
                        <p class="text-slate-600 text-sm leading-relaxed">
                            Unduh salinan cadangan SQL seluruh struktur dan isi data toko kapan saja untuk jaminan keamanan data tingkat tinggi.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Section: Pilihan Paket Langganan SaaS -->
    <section id="pricing" class="py-20 bg-slate-50 border-t border-slate-200/60">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-3xl mx-auto mb-16 reveal-on-scroll">
                <span class="text-brand-600 text-xs sm:text-sm font-bold uppercase tracking-wider bg-brand-50 border border-brand-100 px-3.5 py-1.5 rounded-full inline-block mb-3">
                    Transparan & Tanpa Biaya Tersembunyi
                </span>
                <h2 class="text-3xl sm:text-4xl font-extrabold text-slate-900 tracking-tight">
                    Pilihan Paket Fleksibel Sesuai Skala Toko
                </h2>
                <p class="text-slate-600 text-base sm:text-lg mt-2">
                    Mulai dari toko tunggal hingga jaringan retail waralaba dengan banyak cabang.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8 max-w-6xl mx-auto">
                <!-- Starter -->
                <div class="bg-white rounded-3xl p-8 border border-slate-200 shadow-sm hover:shadow-xl transition-all flex flex-col justify-between interactive-card reveal-on-scroll">
                    <div>
                        <div class="flex items-center justify-between mb-4">
                            <span class="text-xs font-bold uppercase px-3 py-1 bg-slate-100 text-slate-700 rounded-full">Starter</span>
                        </div>
                        <h3 class="text-xl font-bold text-slate-900">Retail Pemula</h3>
                        <p class="text-xs text-slate-500 mt-1">Cocok untuk toko mandiri atau rintisan.</p>
                        <div class="mt-6 mb-6">
                            <span class="text-3xl sm:text-4xl font-extrabold text-slate-900">Rp {{ $cms->pricing_starter ?? '99.000' }}</span>
                            <span class="text-xs text-slate-500 font-semibold">/ bulan</span>
                        </div>
                        <ul class="space-y-3 text-xs text-slate-600">
                            <li class="flex items-center gap-2"><i class="fa-solid fa-check text-emerald-500"></i> 1 Cabang Toko</li>
                            <li class="flex items-center gap-2"><i class="fa-solid fa-check text-emerald-500"></i> Hingga 3 Akun Staf/Kasir</li>
                            <li class="flex items-center gap-2"><i class="fa-solid fa-check text-emerald-500"></i> Kasir POS & Cetak Struk</li>
                            <li class="flex items-center gap-2"><i class="fa-solid fa-check text-emerald-500"></i> Laporan Keuangan Standar</li>
                        </ul>
                    </div>
                    <a href="{{ $waUrl }}" target="_blank" class="mt-8 w-full py-3 text-center rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-800 font-bold text-xs transition">
                        Pilih Paket Starter
                    </a>
                </div>

                <!-- Pro (Featured) -->
                <div class="bg-gradient-to-b from-indigo-900 to-slate-900 text-white rounded-3xl p-8 border-2 border-brand-500 shadow-2xl relative flex flex-col justify-between transform md:-translate-y-2 interactive-card reveal-on-scroll">
                    <div class="absolute -top-3 left-1/2 -translate-x-1/2 bg-gradient-to-r from-amber-400 to-amber-500 text-slate-950 font-black text-[10px] uppercase px-3 py-1 rounded-full shadow-md tracking-wider">
                        PALING POPULER
                    </div>
                    <div>
                        <div class="flex items-center justify-between mb-4">
                            <span class="text-xs font-bold uppercase px-3 py-1 bg-brand-500/20 text-brand-300 rounded-full border border-brand-500/30">Pro Multi-User</span>
                        </div>
                        <h3 class="text-xl font-bold text-white">Toko Ritel & Grosir</h3>
                        <p class="text-xs text-slate-300 mt-1">Solusi komplit anti-rugi dan validasi gudang.</p>
                        <div class="mt-6 mb-6">
                            <span class="text-3xl sm:text-4xl font-extrabold text-white">Rp {{ $cms->pricing_pro ?? '299.000' }}</span>
                            <span class="text-xs text-slate-400 font-semibold">/ bulan</span>
                        </div>
                        <ul class="space-y-3 text-xs text-slate-200">
                            <li class="flex items-center gap-2"><i class="fa-solid fa-check text-emerald-400"></i> Multi-Toko & Multi-Cabang</li>
                            <li class="flex items-center gap-2"><i class="fa-solid fa-check text-emerald-400"></i> Hingga 10 Akun Staf (Kasir, Admin, Sales, Gudang)</li>
                            <li class="flex items-center gap-2"><i class="fa-solid fa-check text-emerald-400"></i> Proteksi Margin & Anti-Rugi Modal</li>
                            <li class="flex items-center gap-2"><i class="fa-solid fa-check text-emerald-400"></i> Verifikasi Fisik Gudang & Mutasi</li>
                            <li class="flex items-center gap-2"><i class="fa-solid fa-check text-emerald-400"></i> Buku Piutang & Kartu Cicilan</li>
                            <li class="flex items-center gap-2"><i class="fa-solid fa-check text-emerald-400"></i> Payroll & Komisi Sales Terpadu</li>
                        </ul>
                    </div>
                    <a href="{{ $waUrl }}" target="_blank" class="mt-8 w-full py-3.5 text-center rounded-xl bg-gradient-to-r from-amber-400 to-amber-500 hover:from-amber-300 hover:to-amber-400 text-slate-950 font-black text-xs shadow-lg transition">
                        Dapatkan Paket Pro Sekarang
                    </a>
                </div>

                <!-- Enterprise -->
                <div class="bg-white rounded-3xl p-8 border border-slate-200 shadow-sm hover:shadow-xl transition-all flex flex-col justify-between interactive-card reveal-on-scroll">
                    <div>
                        <div class="flex items-center justify-between mb-4">
                            <span class="text-xs font-bold uppercase px-3 py-1 bg-purple-50 text-purple-700 rounded-full">Enterprise</span>
                        </div>
                        <h3 class="text-xl font-bold text-slate-900">Jaringan Cabang Besar</h3>
                        <p class="text-xs text-slate-500 mt-1">Untuk distributor atau pemilik rantai franchise.</p>
                        <div class="mt-6 mb-6">
                            <span class="text-3xl sm:text-4xl font-extrabold text-slate-900">Rp {{ $cms->pricing_enterprise ?? '799.000' }}</span>
                            <span class="text-xs text-slate-500 font-semibold">/ bulan</span>
                        </div>
                        <ul class="space-y-3 text-xs text-slate-600">
                            <li class="flex items-center gap-2"><i class="fa-solid fa-check text-emerald-500"></i> Unlimited Cabang & Toko Mitra</li>
                            <li class="flex items-center gap-2"><i class="fa-solid fa-check text-emerald-500"></i> Unlimited Pengguna & Staf</li>
                            <li class="flex items-center gap-2"><i class="fa-solid fa-check text-emerald-500"></i> Dedicated Database Server Support</li>
                            <li class="flex items-center gap-2"><i class="fa-solid fa-check text-emerald-500"></i> Prioritas Bantuan 24/7 & Training Onboarding</li>
                            <li class="flex items-center gap-2"><i class="fa-solid fa-check text-emerald-500"></i> Custom Fitur & Laporan ERP</li>
                        </ul>
                    </div>
                    <a href="{{ $waUrl }}" target="_blank" class="mt-8 w-full py-3 text-center rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs transition">
                        Konsultasi Paket Enterprise
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Section: Konsultasi Admin -->
    <section id="konsultasi" class="py-24 bg-gradient-to-b from-white via-slate-50 to-white border-t border-slate-100 relative overflow-hidden">
        <!-- Background Accent Orbs -->
        <div class="absolute top-1/2 left-0 w-72 h-72 bg-brand-500/10 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute bottom-0 right-0 w-80 h-80 bg-purple-500/10 rounded-full blur-3xl pointer-events-none"></div>

        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
            <!-- Header -->
            <div class="text-center max-w-3xl mx-auto mb-12 reveal-on-scroll">
                <span class="text-brand-600 text-xs sm:text-sm font-bold uppercase tracking-wider bg-brand-50 border border-brand-100 px-3.5 py-1.5 rounded-full inline-block mb-3">
                    Konsultasi & Implementasi Khusus
                </span>
                <h2 class="text-3xl sm:text-4xl font-extrabold text-slate-900 tracking-tight">
                    Solusi Terpadu yang Disesuaikan dengan Bisnis Anda
                </h2>
                <p class="text-slate-600 text-base sm:text-lg mt-3 leading-relaxed">
                    Setiap toko memiliki alur kerja unik. Konsultasikan kebutuhan operasional, jumlah cabang, sistem kasir, dan penyesuaian data Anda langsung dengan tim kami.
                </p>
            </div>

            <!-- Main Consultation Card (Finnova Style) -->
            <div class="bg-gradient-to-br from-[#121422] to-[#1c1f36] rounded-3xl p-8 sm:p-12 text-white shadow-2xl border border-white/10 relative overflow-hidden reveal-on-scroll">
                <div class="absolute -right-16 -top-16 w-64 h-64 bg-indigo-500/20 rounded-full blur-3xl pointer-events-none"></div>
                
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-center relative z-10">
                    <div class="lg:col-span-7 space-y-4">
                        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-500/20 text-emerald-400 text-xs font-bold border border-emerald-500/30">
                            <span class="w-2 h-2 rounded-full bg-emerald-400 animate-ping"></span>
                            <span>Konsultasi & Demo Sistem 100% Gratis</span>
                        </div>
                        <h3 class="text-2xl sm:text-3xl font-black text-white leading-tight">
                            Siap Mengembangkan Jaringan Toko Anda?
                        </h3>
                        <p class="text-slate-300 text-sm leading-relaxed">
                            Dapatkan rekomendasi arsitektur terbaik untuk toko tunggal maupun ratusan cabang toko ritel & grosir Anda. Kami membantu setup awal, import data produk, hingga pelatihan tim kasir dan gudang.
                        </p>
                        
                        <div class="grid grid-cols-2 gap-3 pt-2 text-xs text-slate-300 font-medium">
                            <div class="flex items-center gap-2">
                                <i class="fa-solid fa-circle-check text-emerald-400"></i>
                                <span>Multi-Tenant & Multi-Cabang</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <i class="fa-solid fa-circle-check text-emerald-400"></i>
                                <span>Anti-Rugi & Margin Proteksi</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <i class="fa-solid fa-circle-check text-emerald-400"></i>
                                <span>Migrasi Data Cepat & Aman</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <i class="fa-solid fa-circle-check text-emerald-400"></i>
                                <span>Dukungan Teknis Langsung</span>
                            </div>
                        </div>
                    </div>

                    <!-- Consultation Action Buttons -->
                    <div class="lg:col-span-5 flex flex-col gap-3.5 bg-white/5 p-6 rounded-2xl border border-white/10 backdrop-blur-sm">
                        <p class="text-xs text-slate-300 text-center font-semibold mb-1">
                            Pilih Saluran Komunikasi Pilihan Anda:
                        </p>

                        <!-- WhatsApp Button -->
                        <a href="{{ $waUrl }}" target="_blank" 
                           class="w-full py-3.5 px-5 rounded-xl bg-gradient-to-r from-emerald-500 to-green-600 hover:from-emerald-600 hover:to-green-700 text-white font-bold text-sm shadow-lg shadow-emerald-500/30 flex items-center justify-center gap-3 transition-all hover:-translate-y-0.5">
                            <i class="fa-brands fa-whatsapp text-lg"></i>
                            <span>Konsultasi via WhatsApp</span>
                        </a>

                        <!-- Email Button -->
                        <a href="mailto:{{ $cms->footer_email ?: 'admin@vxpos.id' }}?subject=Konsultasi%20Implementasi%20Sistem%20VxPOS&body=Halo%20Admin%20VxPOS,%0A%0ASaya%20tertarik%20untuk%20konsultasi%20mengenai%20implementasi%20sistem%20VxPOS%20untuk%20toko%20saya.%0A%0ANama:%0ANama%20Toko:%0AJumlah%20Cabang:%0ANo.%20WhatsApp:" 
                           class="w-full py-3.5 px-5 rounded-xl bg-white/10 hover:bg-white/20 text-white font-bold text-sm border border-white/15 flex items-center justify-center gap-3 transition-all hover:-translate-y-0.5">
                            <i class="fa-regular fa-envelope text-lg"></i>
                            <span>Hubungi via Email</span>
                        </a>

                        <div class="pt-2 text-center">
                            <span class="text-[11px] text-slate-400">Respon cepat dalam hitungan jam kerja</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Section: Testimoni Pelanggan -->
    <section id="testimoni" class="py-20 bg-white border-t border-slate-100 relative overflow-hidden">
        <!-- Section Background Accent -->
        <div class="absolute top-10 left-0 w-80 h-80 bg-amber-400/5 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute bottom-10 right-0 w-64 h-64 bg-brand-500/5 rounded-full blur-3xl pointer-events-none"></div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
            <div class="text-center max-w-3xl mx-auto mb-16 reveal-on-scroll">
                <span class="text-amber-600 text-xs sm:text-sm font-bold uppercase tracking-wider">Testimoni Pelanggan</span>
                <h2 class="text-3xl sm:text-4xl font-extrabold text-slate-900 mt-2 mb-4">
                    Dipercaya Pemilik Toko di Seluruh Indonesia
                </h2>
                <p class="text-slate-600 text-base sm:text-lg">
                    Dengarkan langsung pengalaman mereka yang telah menggunakan VxPOS untuk mengelola bisnis retail dan suku cadang.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <!-- Testimoni 1 -->
                <div class="group p-7 rounded-2xl bg-gradient-to-br from-white to-slate-50 border border-slate-100 hover:border-amber-200 hover:shadow-2xl hover:shadow-amber-500/10 transition-all interactive-card reveal-on-scroll relative overflow-hidden">
                    <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-amber-400 to-orange-500 opacity-0 group-hover:opacity-100 transition-opacity"></div>
                    <div class="absolute -right-6 -top-6 w-24 h-24 bg-amber-400/5 rounded-full group-hover:bg-amber-400/10 transition-colors"></div>
                    <div class="relative z-10">
                        <!-- Stars -->
                        <div class="flex gap-1 mb-4">
                            <i class="fa-solid fa-star text-amber-400 text-sm"></i>
                            <i class="fa-solid fa-star text-amber-400 text-sm"></i>
                            <i class="fa-solid fa-star text-amber-400 text-sm"></i>
                            <i class="fa-solid fa-star text-amber-400 text-sm"></i>
                            <i class="fa-solid fa-star text-amber-400 text-sm"></i>
                        </div>
                        <!-- Quote -->
                        <p class="text-slate-600 text-sm leading-relaxed mb-6 italic">
                            "Sejak pakai VxPOS, stok 3 cabang toko saya tidak pernah selisih lagi. Sistem verifikasi gudangnya sangat membantu, kasir dan gudang bisa kerja sinkron tanpa ribut."
                        </p>
                        <!-- Profile -->
                        <div class="flex items-center gap-3 pt-4 border-t border-slate-100">
                            <div class="w-11 h-11 rounded-full bg-gradient-to-br from-amber-400 to-orange-500 flex items-center justify-center text-white font-bold text-sm shadow-md">
                                AH
                            </div>
                            <div>
                                <p class="text-sm font-bold text-slate-900">Andi Hermawan</p>
                                <p class="text-xs text-slate-500">Owner, Berkah Jaya Motor — 3 Cabang</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Testimoni 2 -->
                <div class="group p-7 rounded-2xl bg-gradient-to-br from-white to-slate-50 border border-slate-100 hover:border-brand-200 hover:shadow-2xl hover:shadow-brand-500/10 transition-all interactive-card reveal-on-scroll relative overflow-hidden">
                    <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-brand-500 to-indigo-500 opacity-0 group-hover:opacity-100 transition-opacity"></div>
                    <div class="absolute -right-6 -top-6 w-24 h-24 bg-brand-500/5 rounded-full group-hover:bg-brand-500/10 transition-colors"></div>
                    <div class="relative z-10">
                        <!-- Stars -->
                        <div class="flex gap-1 mb-4">
                            <i class="fa-solid fa-star text-amber-400 text-sm"></i>
                            <i class="fa-solid fa-star text-amber-400 text-sm"></i>
                            <i class="fa-solid fa-star text-amber-400 text-sm"></i>
                            <i class="fa-solid fa-star text-amber-400 text-sm"></i>
                            <i class="fa-solid fa-star text-amber-400 text-sm"></i>
                        </div>
                        <!-- Quote -->
                        <p class="text-slate-600 text-sm leading-relaxed mb-6 italic">
                            "Fitur anti-rugi benar-benar menyelamatkan bisnis saya. Dulu kasir sering kasih diskon di bawah modal tanpa sadar. Sekarang sistem langsung blokir otomatis, margin aman 100%."
                        </p>
                        <!-- Profile -->
                        <div class="flex items-center gap-3 pt-4 border-t border-slate-100">
                            <div class="w-11 h-11 rounded-full bg-gradient-to-br from-brand-500 to-indigo-600 flex items-center justify-center text-white font-bold text-sm shadow-md">
                                SR
                            </div>
                            <div>
                                <p class="text-sm font-bold text-slate-900">Siti Rahmawati</p>
                                <p class="text-xs text-slate-500">Manager, Sentosa Parts & Accessories</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Testimoni 3 -->
                <div class="group p-7 rounded-2xl bg-gradient-to-br from-white to-slate-50 border border-slate-100 hover:border-emerald-200 hover:shadow-2xl hover:shadow-emerald-500/10 transition-all interactive-card reveal-on-scroll relative overflow-hidden">
                    <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-emerald-500 to-teal-500 opacity-0 group-hover:opacity-100 transition-opacity"></div>
                    <div class="absolute -right-6 -top-6 w-24 h-24 bg-emerald-500/5 rounded-full group-hover:bg-emerald-500/10 transition-colors"></div>
                    <div class="relative z-10">
                        <!-- Stars -->
                        <div class="flex gap-1 mb-4">
                            <i class="fa-solid fa-star text-amber-400 text-sm"></i>
                            <i class="fa-solid fa-star text-amber-400 text-sm"></i>
                            <i class="fa-solid fa-star text-amber-400 text-sm"></i>
                            <i class="fa-solid fa-star text-amber-400 text-sm"></i>
                            <i class="fa-solid fa-star-half-stroke text-amber-400 text-sm"></i>
                        </div>
                        <!-- Quote -->
                        <p class="text-slate-600 text-sm leading-relaxed mb-6 italic">
                            "Buku piutang digital di VxPOS sangat rapi. Customer yang nyicil terdata otomatis, slip gaji karyawan tinggal cetak. Satu sistem untuk semua, saya tidak perlu aplikasi lain."
                        </p>
                        <!-- Profile -->
                        <div class="flex items-center gap-3 pt-4 border-t border-slate-100">
                            <div class="w-11 h-11 rounded-full bg-gradient-to-br from-emerald-500 to-teal-600 flex items-center justify-center text-white font-bold text-sm shadow-md">
                                BP
                            </div>
                            <div>
                                <p class="text-sm font-bold text-slate-900">Budi Prasetyo</p>
                                <p class="text-xs text-slate-500">Owner, Makmur Sparepart — 5 Cabang</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Trust Badge -->
            <div class="mt-12 text-center reveal-on-scroll">
                <div class="inline-flex items-center gap-3 px-6 py-3 rounded-full bg-slate-50 border border-slate-200 text-sm text-slate-600">
                    <div class="flex -space-x-2">
                        <div class="w-7 h-7 rounded-full bg-gradient-to-br from-amber-400 to-orange-500 flex items-center justify-center text-white text-[10px] font-bold border-2 border-white">AH</div>
                        <div class="w-7 h-7 rounded-full bg-gradient-to-br from-brand-500 to-indigo-600 flex items-center justify-center text-white text-[10px] font-bold border-2 border-white">SR</div>
                        <div class="w-7 h-7 rounded-full bg-gradient-to-br from-emerald-500 to-teal-600 flex items-center justify-center text-white text-[10px] font-bold border-2 border-white">BP</div>
                        <div class="w-7 h-7 rounded-full bg-gradient-to-br from-purple-500 to-pink-500 flex items-center justify-center text-white text-[10px] font-bold border-2 border-white">+</div>
                    </div>
                    <span class="font-semibold">Bergabung bersama <span class="text-brand-600 font-bold">ratusan pemilik toko</span> lainnya</span>
                </div>
            </div>
        </div>
    </section>

    <!-- FAQ Section -->
    <section id="faq" class="py-20 bg-slate-50 border-t border-slate-100">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12 reveal-on-scroll">
                <span class="text-brand-600 text-xs sm:text-sm font-bold uppercase tracking-wider">Tanya Jawab</span>
                <h2 class="text-3xl font-extrabold text-slate-900 mt-2">Pertanyaan yang Sering Diajukan</h2>
            </div>

            <div class="space-y-4">
                <div class="p-6 bg-white rounded-xl border border-slate-100 shadow-sm interactive-card reveal-on-scroll">
                    <h4 class="text-base font-bold text-slate-900 flex items-center justify-between">
                        <span>Apakah data antar toko saya benar-benar terpisah?</span>
                        <i class="fa-solid fa-angle-down text-slate-400 text-sm"></i>
                    </h4>
                    <p class="text-slate-600 text-sm mt-2 leading-relaxed">
                        Ya. Sistem menggunakan arsitektur multi-tenant dengan isolasi `toko_id`. Kasir dan admin di Toko A tidak akan pernah bisa melihat transaksi, stok, atau laporan keuangan milik Toko B.
                    </p>
                </div>

                <div class="p-6 bg-white rounded-xl border border-slate-100 shadow-sm interactive-card reveal-on-scroll">
                    <h4 class="text-base font-bold text-slate-900 flex items-center justify-between">
                        <span>Apakah aplikasi ini bisa diakses dari HP atau tablet kasir?</span>
                        <i class="fa-solid fa-angle-down text-slate-400 text-sm"></i>
                    </h4>
                    <p class="text-slate-600 text-sm mt-2 leading-relaxed">
                        Tentu saja. Tampilan kasir POS dan dashboard admin sudah didesain responsif, fleksibel digunakan pada layar monitor PC, laptop, iPad/tablet, hingga smartphone Android kasir.
                    </p>
                </div>

                <div class="p-6 bg-white rounded-xl border border-slate-100 shadow-sm interactive-card reveal-on-scroll">
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
                    @if(!empty($cms->logo))
                        <img src="{{ asset($cms->logo) }}" alt="{{ $cms->brand_name ?? 'VxPOS' }}" class="h-9 w-auto max-w-[140px] object-contain">
                    @else
                        <div class="w-9 h-9 rounded-xl bg-brand-600 flex items-center justify-center text-white font-bold">
                            <i class="fa-solid fa-bolt-lightning text-sm"></i>
                        </div>
                    @endif
                    <span class="text-xl font-extrabold text-white">{{ $cms->brand_name ?? 'VxPOS' }}</span>
                </div>
                <p class="text-xs text-slate-500 text-center md:text-right max-w-md">
                    {{ $cms->footer_desc ?? 'Solusi POS & Manajemen Multi-Toko Modern • Powered by Laravel 11' }}
                </p>
            </div>
            <div class="pt-8 flex flex-col sm:flex-row items-center justify-between gap-4 text-xs text-slate-500">
                <p>&copy; {{ date('Y') }} {{ strip_tags($cms->brand_name ?? 'VxPOS') }} Multi-Store Platform. Hak Cipta by. Vicky Koroh.</p>
                <div class="flex gap-6">
                    <a href="{{ route('login') }}" class="hover:text-white transition-colors">Login Toko</a>
                    <a href="#fitur" class="hover:text-white transition-colors">Fitur</a>
                    <a href="#pricing" class="hover:text-white transition-colors">Harga</a>
                    <a href="#konsultasi" class="hover:text-white transition-colors">Konsultasi</a>
                </div>
            </div>
        </div>
    </footer>

    <!-- Floating WhatsApp Widget -->
    @if(!empty($cms->wa_number))
        <a href="{{ $waUrl }}" target="_blank" 
           class="fixed bottom-6 right-6 z-50 flex items-center gap-2.5 bg-emerald-500 hover:bg-emerald-600 text-white px-4 py-3 rounded-full shadow-2xl hover:scale-105 transition-all group" 
           title="Chat WhatsApp dengan Tim Admin">
            <i class="fa-brands fa-whatsapp text-2xl animate-bounce"></i>
            <span class="hidden sm:inline font-bold text-xs tracking-wide">Konsultasi Admin</span>
        </a>
    @endif

    <!-- Interactive Motion Graphic Canvas Script -->
    <script>
        (function() {
            const canvas = document.getElementById('motionCanvas');
            if (!canvas) return;
            const ctx = canvas.getContext('2d');
            let width, height;
            let particles = [];
            const particleCount = 45;

            function resize() {
                width = canvas.width = canvas.parentElement.offsetWidth;
                height = canvas.height = canvas.parentElement.offsetHeight;
            }

            window.addEventListener('resize', resize);
            resize();

            class Particle {
                constructor() {
                    this.x = Math.random() * width;
                    this.y = Math.random() * height;
                    this.vx = (Math.random() - 0.5) * 0.8;
                    this.vy = (Math.random() - 0.5) * 0.8;
                    this.radius = Math.random() * 2.5 + 1.5;
                    this.color = Math.random() > 0.5 ? 'rgba(99, 102, 241, ' : 'rgba(168, 85, 247, ';
                }

                update() {
                    this.x += this.vx;
                    this.y += this.vy;

                    if (this.x < 0) this.x = width;
                    if (this.x > width) this.x = 0;
                    if (this.y < 0) this.y = height;
                    if (this.y > height) this.y = 0;
                }

                draw() {
                    ctx.beginPath();
                    ctx.arc(this.x, this.y, this.radius, 0, Math.PI * 2);
                    ctx.fillStyle = this.color + '0.6)';
                    ctx.fill();
                }
            }

            for (let i = 0; i < particleCount; i++) {
                particles.push(new Particle());
            }

            function connect() {
                for (let i = 0; i < particles.length; i++) {
                    for (let j = i + 1; j < particles.length; j++) {
                        const dx = particles[i].x - particles[j].x;
                        const dy = particles[i].y - particles[j].y;
                        const dist = Math.sqrt(dx * dx + dy * dy);

                        if (dist < 130) {
                            const opacity = (1 - dist / 130) * 0.35;
                            ctx.beginPath();
                            ctx.strokeStyle = `rgba(99, 102, 241, ${opacity})`;
                            ctx.lineWidth = 1;
                            ctx.moveTo(particles[i].x, particles[i].y);
                            ctx.lineTo(particles[j].x, particles[j].y);
                            ctx.stroke();
                        }
                    }
                }
            }

            function animate() {
                ctx.clearRect(0, 0, width, height);
                particles.forEach(p => {
                    p.update();
                    p.draw();
                });
                connect();
                requestAnimationFrame(animate);
            }

            animate();
        })();

        // Interactive Enhancements & Motions
        (function() {
            // 1. Ambient Glow Tracker & Hero Mockup Tilt
            const heroSection = document.querySelector('section.hero-glow');
            const cursorGlow = document.getElementById('cursorGlow');
            const heroMockup = document.getElementById('heroMockupWrapper');

            if (heroSection && cursorGlow) {
                let mouseX = 0, mouseY = 0;

                heroSection.addEventListener('mouseenter', () => {
                    cursorGlow.style.opacity = '1';
                });

                heroSection.addEventListener('mouseleave', () => {
                    cursorGlow.style.opacity = '0';
                    if (heroMockup) {
                        heroMockup.style.transform = 'perspective(1000px) rotateX(0deg) rotateY(0deg)';
                    }
                });

                heroSection.addEventListener('mousemove', (e) => {
                    const rect = heroSection.getBoundingClientRect();
                    mouseX = e.clientX - rect.left;
                    mouseY = e.clientY - rect.top;

                    cursorGlow.style.left = mouseX + 'px';
                    cursorGlow.style.top = mouseY + 'px';

                    // 3D Parallax Tilt for Hero Mockup (subtle, max +-4 deg)
                    if (heroMockup && window.innerWidth >= 768) {
                        const centerX = rect.width / 2;
                        const centerY = rect.height / 2;
                        const deltaX = (mouseX - centerX) / centerX;
                        const deltaY = (mouseY - centerY) / centerY;
                        const tiltX = -deltaY * 3.5;
                        const tiltY = deltaX * 3.5;
                        heroMockup.style.transform = `perspective(1000px) rotateX(${tiltX.toFixed(2)}deg) rotateY(${tiltY.toFixed(2)}deg)`;
                    }
                });
            }

            // 2. Scroll Reveal with IntersectionObserver
            const revealElements = document.querySelectorAll('.reveal-on-scroll');
            if ('IntersectionObserver' in window && revealElements.length > 0) {
                const revealObserver = new IntersectionObserver((entries, observer) => {
                    entries.forEach(entry => {
                        if (entry.isIntersecting) {
                            entry.target.classList.add('is-revealed');
                            observer.unobserve(entry.target);
                        }
                    });
                }, {
                    threshold: 0.1,
                    rootMargin: '0px 0px -30px 0px'
                });

                revealElements.forEach(el => revealObserver.observe(el));
            } else {
                revealElements.forEach(el => el.classList.add('is-revealed'));
            }

            // 3. Animated Number Count-Up
            const countElements = document.querySelectorAll('.count-up');
            if (countElements.length > 0) {
                function animateCount(el) {
                    const rawTarget = el.getAttribute('data-target');
                    if (!rawTarget) return;

                    const target = parseFloat(rawTarget);
                    const suffix = el.getAttribute('data-suffix') || '';
                    const isDecimal = rawTarget.includes('.');
                    const duration = 1600; // ms
                    const startTime = performance.now();

                    function updateNumber(now) {
                        const elapsed = now - startTime;
                        const progress = Math.min(elapsed / duration, 1);
                        // Ease out quad formula
                        const easeProgress = 1 - (1 - progress) * (1 - progress);
                        const currentVal = easeProgress * target;

                        if (isDecimal) {
                            el.textContent = currentVal.toFixed(1) + suffix;
                        } else {
                            const intVal = Math.floor(currentVal);
                            el.textContent = intVal.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.') + suffix;
                        }

                        if (progress < 1) {
                            requestAnimationFrame(updateNumber);
                        } else {
                            if (isDecimal) {
                                el.textContent = target.toFixed(1) + suffix;
                            } else {
                                el.textContent = Math.floor(target).toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.') + suffix;
                            }
                        }
                    }

                    requestAnimationFrame(updateNumber);
                }

                if ('IntersectionObserver' in window) {
                    const counterObserver = new IntersectionObserver((entries, observer) => {
                        entries.forEach(entry => {
                            if (entry.isIntersecting) {
                                animateCount(entry.target);
                                counterObserver.unobserve(entry.target);
                            }
                        });
                    }, { threshold: 0.2 });

                    countElements.forEach(el => counterObserver.observe(el));
                } else {
                    countElements.forEach(el => animateCount(el));
                }
            }

            // 4. Cycling Live Activity Ticker in Mockup
            const liveToast = document.getElementById('mockupLiveToast');
            const liveToastText = document.getElementById('liveToastText');
            if (liveToast && liveToastText) {
                const activities = [
                    'Multi-Tenant Ready',
                    'Kasir Toko 1: Transaksi Rp 350.000 Lunas',
                    'Gudang Sentosa: Verifikasi 14 item fisik',
                    'Anti-Rugi: Proteksi margin HPP aktif (100% aman)',
                    'Multi-Store: 5 Cabang tersinkronisasi real-time'
                ];
                let activityIndex = 0;

                setInterval(() => {
                    activityIndex = (activityIndex + 1) % activities.length;
                    liveToast.style.opacity = '0';
                    liveToast.style.transform = 'translateY(-4px)';
                    
                    setTimeout(() => {
                        liveToastText.textContent = activities[activityIndex];
                        liveToast.style.opacity = '1';
                        liveToast.style.transform = 'translateY(0)';
                    }, 400);
                }, 3800);
            }
        })();
    </script>
</body>
</html>
