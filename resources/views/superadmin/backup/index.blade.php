@extends('layouts.admin')

@section('title', 'Cadangan & Restore Data Toko')

@section('content')
<!-- Header Halaman -->
<div class="mb-6 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
    <div>
        <h3 class="font-extrabold text-[#1e293b] text-2xl tracking-tight flex items-center gap-2.5">
            <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-indigo-600 to-indigo-500 text-white flex items-center justify-center shadow-md shadow-indigo-500/20">
                <i class="fa-solid fa-shield-halved text-lg"></i>
            </div>
            Cadangan & Restore Data Toko
        </h3>
        <p class="text-slate-500 text-xs mt-1">Amankan dan pulihkan data operasional toko dengan teknologi enkapsulasi <strong>Protect ID</strong></p>
    </div>

    <!-- Badge Info Toko Aktif -->
    <div class="flex items-center gap-2 bg-white px-3.5 py-2 rounded-xl border border-slate-200 shadow-sm">
        <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></span>
        <span class="text-xs text-slate-500">Toko Aktif:</span>
        <strong class="text-xs text-slate-800">{{ $toko->nama_toko ?? 'VxPOS Toko' }}</strong>
        <span class="bg-indigo-50 text-indigo-700 text-[10px] font-mono font-bold px-2 py-0.5 rounded-md border border-indigo-200">ID: #{{ $tokoId }}</span>
    </div>
</div>

@if(session('success'))
<div class="bg-emerald-50 border border-emerald-200 text-emerald-800 p-4 rounded-2xl mb-6 shadow-sm flex items-start gap-3">
    <i class="fa-solid fa-circle-check text-emerald-600 text-lg mt-0.5"></i>
    <div class="text-xs font-medium leading-relaxed">
        <strong class="font-bold block text-sm mb-0.5">Operasi Berhasil!</strong>
        {{ session('success') }}
    </div>
</div>
@endif

@if($errors->any())
<div class="bg-rose-50 border border-rose-200 text-rose-800 p-4 rounded-2xl mb-6 shadow-sm flex items-start gap-3">
    <i class="fa-solid fa-circle-xmark text-rose-600 text-lg mt-0.5"></i>
    <div class="text-xs font-medium leading-relaxed">
        <strong class="font-bold block text-sm mb-0.5">Perhatian / Akses Ditolak:</strong>
        <ul class="list-disc pl-4 space-y-1">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
</div>
@endif

<!-- Banner Status Proteksi & Ringkasan Data -->
<div class="bg-gradient-to-r from-slate-900 via-indigo-950 to-slate-900 rounded-3xl p-6 text-white mb-6 shadow-xl relative overflow-hidden">
    <div class="absolute -right-8 -bottom-8 w-40 h-40 bg-indigo-500/20 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute -left-8 -top-8 w-32 h-32 bg-purple-500/20 rounded-full blur-2xl pointer-events-none"></div>

    <div class="relative z-10 flex flex-col lg:flex-row justify-between items-start lg:items-center gap-6">
        <div>
            <div class="flex items-center gap-2 mb-2">
                <span class="inline-flex items-center gap-1.5 text-[11px] font-bold text-emerald-400 bg-emerald-500/10 px-2.5 py-1 rounded-full border border-emerald-500/20">
                    <i class="fa-solid fa-lock text-[10px]"></i> Protect ID Aktif
                </span>
                <span class="inline-flex items-center gap-1.5 text-[11px] font-bold text-indigo-300 bg-indigo-500/10 px-2.5 py-1 rounded-full border border-indigo-500/20">
                    <i class="fa-solid fa-database text-[10px]"></i> Multi-Tenant Isolated
                </span>
            </div>
            <h4 class="text-lg font-bold text-white mb-1">{{ $toko->nama_toko ?? 'Toko Retail' }}</h4>
            <p class="text-xs text-slate-300 max-w-xl leading-relaxed">
                Setiap file cadangan yang diunduh terikat khusus dengan <strong>ID Toko #{{ $tokoId }}</strong>. Berkas ini memiliki proteksi tanda tangan digital sehingga <strong>TIDAK BISA</strong> dipulihkan atau tertukar secara tidak sengaja oleh toko lain.
            </p>
        </div>

        <!-- Metric Pills -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 w-full lg:w-auto">
            <div class="bg-white/10 backdrop-blur-sm rounded-2xl p-3 border border-white/10 text-center">
                <p class="text-[10px] text-slate-300 uppercase font-semibold">Produk</p>
                <p class="text-base font-extrabold text-white mt-0.5">{{ number_format($stats['total_barang']) }}</p>
            </div>
            <div class="bg-white/10 backdrop-blur-sm rounded-2xl p-3 border border-white/10 text-center">
                <p class="text-[10px] text-slate-300 uppercase font-semibold">Transaksi</p>
                <p class="text-base font-extrabold text-white mt-0.5">{{ number_format($stats['total_transaksi']) }}</p>
            </div>
            <div class="bg-white/10 backdrop-blur-sm rounded-2xl p-3 border border-white/10 text-center">
                <p class="text-[10px] text-slate-300 uppercase font-semibold">Cicilan</p>
                <p class="text-base font-extrabold text-white mt-0.5">{{ number_format($stats['total_cicilan']) }}</p>
            </div>
            <div class="bg-white/10 backdrop-blur-sm rounded-2xl p-3 border border-white/10 text-center">
                <p class="text-[10px] text-slate-300 uppercase font-semibold">Pegawai</p>
                <p class="text-base font-extrabold text-white mt-0.5">{{ number_format($stats['total_user']) }}</p>
            </div>
        </div>
    </div>
</div>

<!-- Grid 2 Kolom: Backup dan Restore -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
    
    <!-- KARTU 1: CADANGKAN DATA TOKO (BACKUP) -->
    <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-100 shadow-sm flex flex-col justify-between">
        <div>
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-xl shadow-inner">
                    <i class="fa-solid fa-cloud-arrow-down"></i>
                </div>
                <span class="text-[11px] font-bold px-2.5 py-1 bg-indigo-50 text-indigo-700 rounded-full border border-indigo-200">
                    Snapshot Format: .vxbackup
                </span>
            </div>

            <h4 class="text-lg font-bold text-slate-800 mb-2">Cadangkan Data Toko</h4>
            <p class="text-slate-500 text-xs leading-relaxed mb-6">
                Unduh seluruh data operasional toko saat ini (Master Barang, Stok Fisik, Manajemen 3 Tingkat Harga, Riwayat Penjualan Kasir, Cicilan Piutang, Penggajian, dan Profil Toko) menjadi satu berkas terenkapsulasi.
            </p>

            <div class="bg-slate-50 border border-slate-200 rounded-2xl p-4 mb-6">
                <p class="text-[11px] font-bold text-slate-700 uppercase mb-2">Format Nama Berkas Otomatis:</p>
                <div class="font-mono text-[11px] text-indigo-700 bg-white p-2.5 rounded-xl border border-slate-200 break-all select-all">
                    VxPOS_Backup_{{ \Illuminate\Support\Str::slug($toko->nama_toko ?? 'toko') }}_ID{{ $tokoId }}_YYYY-MM-DD.vxbackup
                </div>
                <p class="text-[10px] text-slate-400 mt-2">
                    <i class="fa-solid fa-circle-info mr-1"></i> Berkas ini menyimpan hash digital Protect ID khusus toko Anda.
                </p>
            </div>
        </div>

        <form action="{{ route('superadmin.backup.download') }}" method="POST" onsubmit="mulaiLoading(this)">
            @csrf
            <button type="submit" id="btnBackup" class="w-full bg-gradient-to-r from-indigo-600 to-indigo-700 hover:from-indigo-700 hover:to-indigo-800 text-white font-bold py-3.5 px-6 rounded-2xl shadow-lg shadow-indigo-600/30 transition-all flex items-center justify-center gap-2.5 text-sm">
                <i class="fa-solid fa-cloud-arrow-down text-base"></i> Unduh Cadangan Toko (.vxbackup)
            </button>
        </form>
    </div>

    <!-- KARTU 2: PULIHKAN DATA TOKO (RESTORE DENGAN PROTECT ID) -->
    <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-100 shadow-sm flex flex-col justify-between">
        <div>
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl shadow-inner">
                    <i class="fa-solid fa-rotate-left"></i>
                </div>
                <span class="text-[11px] font-bold px-2.5 py-1 bg-emerald-50 text-emerald-700 rounded-full border border-emerald-200">
                    Protect ID Validation
                </span>
            </div>

            <h4 class="text-lg font-bold text-slate-800 mb-2">Pulihkan Data Toko (Restore)</h4>
            <p class="text-slate-500 text-xs leading-relaxed mb-4">
                Unggah berkas cadangan <strong class="text-slate-700">.vxbackup</strong> atau <strong class="text-slate-700">.json</strong> yang pernah Anda unduh sebelumnya untuk mengembalikan kondisi data toko Anda.
            </p>

            <form action="{{ route('superadmin.backup.restore') }}" method="POST" enctype="multipart/form-data" id="formRestore"
                  data-confirm="PERINGATAN: Apakah Anda yakin ingin memulihkan data toko ini? Tindakan ini akan memperbarui data katalog dan transaksi toko sesuai isi berkas cadangan."
                  data-confirm-type="warning"
                  data-confirm-title="Konfirmasi Pemulihan Data Toko"
                  data-confirm-btn="Ya, Pulihkan Sekarang">
                @csrf

                <!-- Dropzone File Upload -->
                <div class="border-2 border-dashed border-slate-200 rounded-2xl p-5 text-center hover:border-emerald-500 transition-colors relative mb-4 bg-slate-50/50">
                    <input type="file" name="backup_file" id="backupFile" accept=".vxbackup, .json" required class="absolute inset-0 w-full h-full opacity-0 cursor-pointer" onchange="previewBackupFile(event)">
                    <i class="fa-solid fa-file-shield text-2xl text-slate-300 mb-1"></i>
                    <p id="restorePrompt" class="text-xs font-semibold text-slate-600">Klik atau geser file .vxbackup ke sini</p>
                    <p class="text-[10px] text-slate-400 mt-0.5">Maksimal ukuran berkas 25 MB</p>
                </div>

                <!-- Opsi Mode Pemulihan -->
                <div class="space-y-2 mb-6">
                    <p class="text-[11px] font-bold text-slate-700 uppercase">Pilih Mode Pemulihan:</p>
                    <label class="flex items-start gap-2.5 p-2.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 cursor-pointer">
                        <input type="radio" name="restore_mode" value="replace" checked class="mt-0.5 text-indigo-600 focus:ring-indigo-500">
                        <div>
                            <span class="text-xs font-bold text-slate-800 block">Ganti & Pulihkan Penuh (Direkomendasikan)</span>
                            <span class="text-[10px] text-slate-500">Mengosongkan data transaksi dan produk toko saat ini, lalu memulihkan secara utuh sesuai snapshot cadangan.</span>
                        </div>
                    </label>
                    <label class="flex items-start gap-2.5 p-2.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 cursor-pointer">
                        <input type="radio" name="restore_mode" value="merge" class="mt-0.5 text-indigo-600 focus:ring-indigo-500">
                        <div>
                            <span class="text-xs font-bold text-slate-800 block">Gabungkan (Merge Data)</span>
                            <span class="text-[10px] text-slate-500">Menyisipkan data baru dari file cadangan tanpa menghapus data transaksi yang sudah ada.</span>
                        </div>
                    </label>
                </div>

                <button type="submit" id="btnRestore" class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-3.5 px-6 rounded-2xl shadow-lg shadow-emerald-600/30 transition-all flex items-center justify-center gap-2.5 text-sm">
                    <i class="fa-solid fa-rotate-left text-base"></i> Mulai Pulihkan Data Toko
                </button>
            </form>
        </div>
    </div>
</div>

<!-- KARTU 3: MASTER SQL DUMPER (Hanya Tampil untuk Superadmin Utama / Platform Owner) -->
@if($isPlatform)
<div class="bg-white rounded-3xl p-6 sm:p-8 border border-amber-200 shadow-sm mb-6 bg-gradient-to-br from-white to-amber-50/30">
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div class="flex items-start gap-4">
            <div class="w-12 h-12 rounded-2xl bg-amber-100 text-amber-700 flex items-center justify-center text-xl shadow-inner shrink-0">
                <i class="fa-solid fa-database"></i>
            </div>
            <div>
                <div class="flex items-center gap-2">
                    <h4 class="text-base font-bold text-slate-800">Cadangan Basis Data Global (Platform Master SQL)</h4>
                    <span class="bg-amber-100 text-amber-800 text-[10px] font-extrabold px-2 py-0.5 rounded-full">Khusus Superadmin SaaS</span>
                </div>
                <p class="text-slate-500 text-xs mt-1 leading-relaxed max-w-2xl">
                    Mengekspor seluruh struktur dan seluruh baris tabel database MySQL server ke dalam berkas <strong>.sql</strong> mentah untuk keperluan migrasi server hosting atau disaster recovery.
                </p>
            </div>
        </div>

        <form action="{{ route('superadmin.backup.global') }}" method="POST" onsubmit="mulaiLoadingGlobal(this)">
            @csrf
            <button type="submit" id="btnBackupGlobal" class="bg-slate-900 hover:bg-slate-800 text-white font-bold py-3 px-5 rounded-2xl shadow-md transition-all flex items-center gap-2 text-xs shrink-0 whitespace-nowrap">
                <i class="fa-solid fa-file-code"></i> Unduh Dump SQL Global (.sql)
            </button>
        </form>
    </div>
</div>
@endif

<script>
    function previewBackupFile(e) {
        const file = e.target.files[0];
        if (!file) return;

        const prompt = document.getElementById('restorePrompt');
        prompt.innerHTML = `<span class="text-emerald-700 font-bold"><i class="fa-solid fa-file-check mr-1"></i> ${file.name}</span> (${(file.size / 1024).toFixed(1)} KB)`;
    }

    function mulaiLoading(form) {
        const btn = document.getElementById('btnBackup');
        btn.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin"></i> Sedang Menyusun Cadangan...';
        btn.classList.add('opacity-75', 'cursor-not-allowed');
        
        setTimeout(() => {
            btn.innerHTML = '<i class="fa-solid fa-cloud-arrow-down"></i> Unduh Cadangan Toko (.vxbackup)';
            btn.classList.remove('opacity-75', 'cursor-not-allowed');
        }, 4000);
    }

    function mulaiLoadingGlobal(form) {
        const btn = document.getElementById('btnBackupGlobal');
        btn.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin"></i> Mengekstrak Database SQL...';
        btn.classList.add('opacity-75', 'cursor-not-allowed');
        
        setTimeout(() => {
            btn.innerHTML = '<i class="fa-solid fa-file-code"></i> Unduh Dump SQL Global (.sql)';
            btn.classList.remove('opacity-75', 'cursor-not-allowed');
        }, 4000);
    }
</script>
@endsection