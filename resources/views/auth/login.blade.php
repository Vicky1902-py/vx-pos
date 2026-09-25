@php
    $pengaturan = \Illuminate\Support\Facades\DB::table('pengaturan_toko')->first();
    $logoPath = ($pengaturan && $pengaturan->logo) ? asset('uploads/logo/' . $pengaturan->logo) : null;
    $namaToko = $pengaturan->nama_toko ?? 'Chanada Auto Parts';
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - {{ $namaToko }}</title>
    
    @if($logoPath)
        <link rel="icon" type="image/png" href="{{ $logoPath }}">
    @else
        <link rel="icon" href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><text y=%22.9em%22 font-size=%2290%22>🚗</text></svg>">
    @endif

    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Public+Sans:wght@300;400;500;600;700&display=swap');
        body { font-family: 'Public Sans', sans-serif; background-color: #28243d; }
        .bg-panel { background-color: #2f3349; }
        .text-heading { color: #cfd3ec; }
        .text-muted { color: #7983bb; }
        .border-input { border-color: rgba(207, 211, 236, 0.22); }
        .bg-input { background-color: #28243d; }
    </style>
</head>
<body class="min-h-screen flex overflow-hidden">

    <div class="hidden lg:flex w-2/3 items-center justify-center relative p-10">
        <div class="absolute top-8 left-10 flex items-center gap-3">
            @if($logoPath)
                <img src="{{ $logoPath }}" alt="{{ $namaToko }}" class="h-16 md:h-20 w-auto object-contain bg-white p-2.5 rounded-xl shadow-lg">
            @else
                <div class="w-12 h-12 bg-[#7367f0] rounded-lg flex items-center justify-center shadow-lg">
                    <i class="fa-solid fa-car-side text-white text-xl"></i>
                </div>
                <span class="text-3xl font-bold text-heading tracking-tight">Chanada<span class="text-[#7367f0]">Auto Parts</span></span>
            @endif
        </div>

        <div class="relative w-full max-w-lg">
            <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-96 h-96 border border-[#434968] rounded-full opacity-50"></div>
            <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-[450px] h-[450px] border border-[#434968] rounded-full opacity-20"></div>

            <div class="relative z-10 flex flex-col items-center justify-center text-[#7367f0] drop-shadow-[0_0_30px_rgba(115,103,240,0.4)]">
                <i class="fa-solid fa-gears text-[120px]"></i>
                <i class="fa-solid fa-wrench text-5xl absolute -bottom-4 -right-4 text-[#00cfe8] drop-shadow-lg"></i>
            </div>

            <div class="absolute -top-10 -left-10 bg-panel p-5 rounded-xl shadow-[0_10px_30px_rgba(0,0,0,0.4)] border border-[#434968] z-20 animate-bounce" style="animation-duration: 4s;">
                <p class="text-heading font-medium text-sm mb-3">Stok Tersedia</p>
                <div class="flex items-end gap-3">
                    <h3 class="text-2xl font-bold text-heading">12.5k</h3>
                    <span class="text-[#28c76f] text-xs font-semibold mb-1"><i class="fa-solid fa-arrow-trend-up"></i> +8.2%</span>
                </div>
            </div>

            <div class="absolute -bottom-10 -right-10 bg-panel p-5 rounded-xl shadow-[0_10px_30px_rgba(0,0,0,0.4)] border border-[#434968] z-20 animate-bounce" style="animation-duration: 5s; animation-delay: 1s;">
                <p class="text-heading font-medium text-sm mb-3">Distribusi</p>
                <div class="flex items-end gap-3">
                    <h3 class="text-2xl font-bold text-heading">842</h3>
                    <span class="text-[#00cfe8] text-xs font-semibold mb-1"><i class="fa-solid fa-truck-fast"></i> Aktif</span>
                </div>
            </div>
        </div>
    </div>

    <div class="w-full lg:w-1/3 bg-panel flex items-center justify-center p-8 sm:p-12 shadow-[-10px_0_30px_rgba(0,0,0,0.2)] relative z-30">
        <div class="w-full max-w-md">
            <div class="mb-6">
                <a href="{{ route('landing') }}" class="inline-flex items-center gap-2 text-xs text-[#7983bb] hover:text-white transition-colors bg-white/5 hover:bg-white/10 px-3 py-1.5 rounded-lg border border-white/5">
                    <i class="fa-solid fa-arrow-left text-[10px]"></i>
                    <span>Kembali ke Beranda Utama</span>
                </a>
            </div>

            <div class="flex lg:hidden items-center gap-3 mb-8">
                @if($logoPath)
                    <img src="{{ $logoPath }}" alt="{{ $namaToko }}" class="h-14 w-auto object-contain bg-white p-2 rounded-xl shadow-md">
                @else
                    <div class="w-10 h-10 bg-[#7367f0] rounded-lg flex items-center justify-center shadow-md">
                        <i class="fa-solid fa-car-side text-white"></i>
                    </div>
                    <span class="text-2xl font-bold text-heading tracking-tight">Chanada<span class="text-[#7367f0]">Auto</span></span>
                @endif
            </div>

            <div class="mb-8">
                <h2 class="text-2xl font-semibold text-heading mb-2">Welcome to {{ explode(' ', $namaToko)[0] }}!</h2>
                <p class="text-muted text-sm">Sistem Manajemen Penjualan & Distribusi. Silakan masuk ke akun Anda.</p>
            </div>

            @if(session('error'))
                <div class="bg-[rgba(234,84,85,0.16)] border border-[#ea5455] text-[#ea5455] p-4 mb-6 rounded-lg flex gap-3 items-start" role="alert">
                    <i class="fa-solid fa-circle-exclamation mt-1"></i>
                    <div>
                        <p class="font-bold text-sm">Gagal Login!</p>
                        <p class="text-xs mt-0.5">{{ session('error') }}</p>
                    </div>
                </div>
            @endif

            <form action="{{ route('login') }}" method="POST" class="space-y-5">
                @csrf
                
                <div>
                    <label class="block text-heading text-[13px] font-medium mb-1.5" for="username">
                        Username
                    </label>
                    <input class="w-full bg-input border border-input rounded-lg py-2.5 px-4 text-heading text-sm transition-all focus:outline-none focus:border-[#7367f0] focus:ring-1 focus:ring-[#7367f0]" 
                           id="username" name="username" type="text" placeholder="Masukkan username..." required autofocus>
                </div>

                <div>
                    <div class="flex justify-between items-center mb-1.5">
                        <label class="block text-heading text-[13px] font-medium" for="password">Password</label>
                    </div>
                    <div class="relative">
                        <input class="w-full bg-input border border-input rounded-lg py-2.5 pl-4 pr-10 text-heading text-sm transition-all focus:outline-none focus:border-[#7367f0] focus:ring-1 focus:ring-[#7367f0]" 
                               id="password" name="password" type="password" placeholder="********" required>
                        <span class="absolute inset-y-0 right-0 flex items-center pr-3 text-[#7983bb] cursor-pointer hover:text-heading">
                            <i class="fa-regular fa-eye text-sm"></i>
                        </span>
                    </div>
                </div>

                <div class="flex items-center justify-between mt-2 mb-6">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" class="w-4 h-4 rounded bg-input border-input text-[#7367f0] focus:ring-[#7367f0] focus:ring-offset-panel accent-[#7367f0]">
                        <span class="text-muted text-[13px]">Remember me</span>
                    </label>
                </div>

                <button class="w-full bg-[#7367f0] hover:bg-[#6355e6] text-white font-medium py-2.5 px-4 rounded-lg transition duration-200 shadow-[0_2px_6px_rgba(115,103,240,0.4)]" type="submit">
                    Login
                </button>
            </form>

            <p class="text-center text-muted text-xs mt-8">
                &copy; {{ date('Y') }} {{ $namaToko }}.<br>Dikembangkan Khusus untuk Sistem Internal.
            </p>
        </div>
    </div>

</body>
</html>