@php
    $pengaturan = null;
    try {
        if (\Illuminate\Support\Facades\Schema::hasTable('pengaturan_toko')) {
            $pengaturan = \Illuminate\Support\Facades\DB::table('pengaturan_toko')->first();
        }
    } catch (\Throwable $e) {}
    $logoPath = ($pengaturan && !empty($pengaturan->logo)) ? asset('uploads/logo/' . $pengaturan->logo) : null;
    $namaToko = $pengaturan->nama_toko ?? 'VxPOS';
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Login - {{ $namaToko }}</title>
    
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

    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .glass-card {
            background: rgba(21, 24, 40, 0.85);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.08);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5), 0 0 0 1px rgba(255, 255, 255, 0.05);
        }
        .btn-finnova-primary {
            background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
            box-shadow: 0 8px 20px -4px rgba(99, 102, 241, 0.5);
        }
        .btn-finnova-primary:hover {
            background: linear-gradient(135deg, #4f46e5 0%, #4338ca 100%);
        }
    </style>
</head>
<body class="min-h-screen bg-[#0b0d17] text-slate-100 flex items-center justify-center p-4 sm:p-6 relative overflow-hidden">

    <!-- Ambient Glowing Gradient Orbs (Finnova Style) -->
    <div class="absolute -top-40 -left-40 w-96 h-96 bg-indigo-600/20 rounded-full blur-[120px] pointer-events-none"></div>
    <div class="absolute -bottom-40 -right-40 w-96 h-96 bg-purple-600/20 rounded-full blur-[120px] pointer-events-none"></div>
    <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[600px] h-[600px] bg-blue-600/10 rounded-full blur-[150px] pointer-events-none"></div>

    <div class="w-full max-w-md relative z-10">
        
        <!-- Back Link -->
        <div class="mb-5 flex justify-between items-center">
            <a href="{{ route('landing') }}" class="inline-flex items-center gap-2 text-xs font-semibold text-slate-400 hover:text-white transition-all bg-white/5 hover:bg-white/10 px-3 py-1.5 rounded-full border border-white/5">
                <i class="fa-solid fa-arrow-left text-[10px]"></i>
                <span>Kembali ke Beranda</span>
            </a>
            <span class="text-[11px] font-bold uppercase tracking-wider text-indigo-400/80 bg-indigo-500/10 px-2.5 py-1 rounded-full border border-indigo-500/20">
                Portal Akses
            </span>
        </div>

        <!-- Main Login Card -->
        <div class="glass-card rounded-3xl p-6 sm:p-8">
            
            <!-- Brand Header -->
            <div class="flex items-center gap-3 mb-6">
                @if($logoPath)
                    <img src="{{ $logoPath }}" alt="{{ $namaToko }}" class="h-11 w-auto object-contain bg-white/10 p-2 rounded-2xl border border-white/10 shadow-md">
                @else
                    <div class="w-11 h-11 rounded-2xl bg-gradient-to-tr from-indigo-500 via-indigo-600 to-purple-600 flex items-center justify-center text-white shadow-lg shadow-indigo-500/30 font-black text-lg">
                        <i class="fa-solid fa-bolt"></i>
                    </div>
                @endif
                <div>
                    <h1 class="text-xl font-extrabold text-white tracking-tight leading-tight">
                        {{ $namaToko }}
                    </h1>
                    <p class="text-xs text-slate-400 font-medium">Smart Retail & Enterprise POS</p>
                </div>
            </div>

            <!-- Title -->
            <div class="mb-6">
                <h2 class="text-lg font-bold text-white mb-1">Masuk ke Sistem</h2>
                <p class="text-xs text-slate-400">Silakan masukkan username dan password akun Anda.</p>
            </div>

            <!-- Error Notification -->
            @if(session('error'))
                <div class="bg-red-500/10 border border-red-500/30 text-red-400 p-3.5 mb-5 rounded-2xl flex gap-3 items-start text-xs" role="alert">
                    <i class="fa-solid fa-circle-exclamation mt-0.5 text-sm"></i>
                    <div>
                        <p class="font-bold">Gagal Masuk</p>
                        <p class="text-[11px] text-red-300/90 mt-0.5">{{ session('error') }}</p>
                    </div>
                </div>
            @endif

            <!-- Login Form -->
            <form action="{{ route('login') }}" method="POST" class="space-y-4">
                @csrf
                
                <div>
                    <label class="block text-slate-300 text-xs font-semibold mb-1.5" for="username">
                        Username atau Email
                    </label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400 pointer-events-none">
                            <i class="fa-regular fa-user text-xs"></i>
                        </span>
                        <input class="w-full bg-[#1b1f33] border border-white/10 rounded-xl py-2.5 pl-10 pr-4 text-white text-xs placeholder:text-slate-500 transition-all focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500" 
                               id="username" name="username" type="text" placeholder="Masukkan username..." required autofocus>
                    </div>
                </div>

                <div>
                    <label class="block text-slate-300 text-xs font-semibold mb-1.5" for="password">
                        Password
                    </label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400 pointer-events-none">
                            <i class="fa-solid fa-lock text-xs"></i>
                        </span>
                        <input class="w-full bg-[#1b1f33] border border-white/10 rounded-xl py-2.5 pl-10 pr-10 text-white text-xs placeholder:text-slate-500 transition-all focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500" 
                               id="password" name="password" type="password" placeholder="••••••••" required>
                        <span id="togglePasswordBtn" class="absolute inset-y-0 right-0 flex items-center pr-3.5 text-slate-400 cursor-pointer hover:text-white transition-colors">
                            <i id="togglePasswordIcon" class="fa-regular fa-eye text-xs"></i>
                        </span>
                    </div>
                </div>

                <div class="flex items-center justify-between text-xs pt-1">
                    <label class="flex items-center gap-2 cursor-pointer text-slate-400 hover:text-slate-300">
                        <input type="checkbox" name="remember" class="w-4 h-4 rounded bg-[#1b1f33] border-white/10 text-indigo-600 focus:ring-indigo-500 accent-indigo-600">
                        <span>Ingat saya di perangkat ini</span>
                    </label>
                </div>

                <button class="w-full btn-finnova-primary text-white font-bold py-3 px-4 rounded-xl text-xs transition duration-200 flex items-center justify-center gap-2 mt-2" type="submit">
                    <span>Masuk ke Akun</span>
                    <i class="fa-solid fa-arrow-right text-[10px]"></i>
                </button>
            </form>

            <!-- 1-Click Fast Demo Testing Section -->
            <div class="mt-6 pt-5 border-t border-white/10 space-y-3">
                <div class="flex items-center justify-between">
                    <span class="text-[11px] text-slate-300 font-bold uppercase tracking-wider flex items-center gap-1.5">
                        <i class="fa-solid fa-bolt-lightning text-amber-400"></i> Mode Uji Coba Cepat (1-Klik):
                    </span>
                </div>
                
                <div class="grid grid-cols-2 gap-2.5">
                    <a href="{{ route('login.demo.quick', 'admin') }}" class="p-3 rounded-2xl bg-amber-500/10 hover:bg-amber-500/20 text-left border border-amber-500/20 transition-all group block">
                        <div class="flex items-center justify-between mb-1">
                            <span class="text-xs font-bold text-amber-300 flex items-center gap-1">
                                <i class="fa-solid fa-store text-[10px]"></i> Admin Demo
                            </span>
                            <i class="fa-solid fa-arrow-right text-[10px] text-amber-400 group-hover:translate-x-0.5 transition-transform"></i>
                        </div>
                        <p class="text-[10px] text-slate-400 line-clamp-1">Kelola Seluruh Toko</p>
                    </a>

                    <a href="{{ route('login.demo.quick', 'kasir') }}" class="p-3 rounded-2xl bg-emerald-500/10 hover:bg-emerald-500/20 text-left border border-emerald-500/20 transition-all group block">
                        <div class="flex items-center justify-between mb-1">
                            <span class="text-xs font-bold text-emerald-300 flex items-center gap-1">
                                <i class="fa-solid fa-cash-register text-[10px]"></i> Kasir Demo
                            </span>
                            <i class="fa-solid fa-arrow-right text-[10px] text-emerald-400 group-hover:translate-x-0.5 transition-transform"></i>
                        </div>
                        <p class="text-[10px] text-slate-400 line-clamp-1">Langsung Layar POS</p>
                    </a>
                </div>
            </div>

            <!-- Footer copyright -->
            <p class="text-center text-slate-500 text-[11px] mt-6 font-medium">
                &copy; {{ date('Y') }} {{ $namaToko }}. Enterprise Retail & POS Architecture.
            </p>
        </div>
    </div>

    <!-- Password visibility toggle script -->
    <script>
        const toggleBtn = document.getElementById('togglePasswordBtn');
        const passwordInput = document.getElementById('password');
        const toggleIcon = document.getElementById('togglePasswordIcon');

        if (toggleBtn && passwordInput && toggleIcon) {
            toggleBtn.addEventListener('click', function () {
                if (passwordInput.type === 'password') {
                    passwordInput.type = 'text';
                    toggleIcon.classList.remove('fa-eye');
                    toggleIcon.classList.add('fa-eye-slash');
                } else {
                    passwordInput.type = 'password';
                    toggleIcon.classList.remove('fa-eye-slash');
                    toggleIcon.classList.add('fa-eye');
                }
            });
        }
    </script>
</body>
</html>