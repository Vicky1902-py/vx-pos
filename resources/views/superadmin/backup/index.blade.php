@extends('layouts.admin')

@section('title', 'Backup Database')

@section('content')
<div class="mb-6 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
    <div>
        <h3 class="font-bold text-[#4b465c] text-2xl">Backup Database Sistem</h3>
        <p class="text-[#a8aaae] text-sm">Amankan data transaksi, barang, dan pengaturan Anda secara berkala</p>
    </div>
</div>

<div class="max-w-3xl bg-white rounded-2xl shadow-[0_4px_18px_0_rgba(75,70,92,0.1)] overflow-hidden">
    <div class="p-8 md:p-12 text-center flex flex-col items-center">
        
        <!-- Ikon Keamanan Animasi Statis -->
        <div class="w-24 h-24 bg-indigo-50 rounded-full flex items-center justify-center mb-6">
            <i class="fa-solid fa-shield-halved text-5xl text-[#7367f0]"></i>
        </div>

        <h4 class="text-xl font-bold text-gray-800 mb-3">Sistem Pencadangan Mandiri (SQL Dumper)</h4>
        
        <p class="text-gray-500 text-sm mb-8 leading-relaxed max-w-lg">
            Sistem akan menyusun seluruh struktur tabel dan isi data toko Anda menjadi sebuah file <strong>.sql</strong>. 
            File ini akan langsung diunduh ke perangkat Anda. Harap simpan file tersebut di tempat yang aman (seperti Google Drive atau Flashdisk).
        </p>

        <div class="bg-amber-50 border border-amber-200 text-amber-700 p-4 rounded-xl text-sm font-medium mb-8 flex items-start gap-3 text-left w-full max-w-lg">
            <i class="fa-solid fa-triangle-exclamation text-lg mt-0.5"></i>
            <div>
                Jangan tinggalkan halaman ini saat proses pengunduhan sedang berjalan. Proses ini mungkin memakan waktu beberapa detik tergantung dari besarnya data toko Anda.
            </div>
        </div>

        <form action="{{ route('superadmin.backup.download') }}" method="POST" onsubmit="mulaiLoading(this)">
            @csrf
            <button type="submit" id="btnBackup" class="bg-[#7367f0] hover:bg-[#6355e6] text-white font-bold py-3.5 px-8 rounded-xl shadow-[0_4px_14px_0_rgba(115,103,240,0.4)] transition-all flex items-center gap-2 text-lg">
                <i class="fa-solid fa-cloud-arrow-down"></i> Mulai Unduh Backup Data (.sql)
            </button>
        </form>

    </div>
</div>

<script>
    function mulaiLoading(form) {
        const btn = document.getElementById('btnBackup');
        btn.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin"></i> Sedang Memproses Data...';
        btn.classList.add('opacity-75', 'cursor-not-allowed');
        
        // Kembalikan tombol ke keadaan semula setelah 3 detik (asumsi download sudah terpancing di browser)
        setTimeout(() => {
            btn.innerHTML = '<i class="fa-solid fa-cloud-arrow-down"></i> Mulai Unduh Backup Data (.sql)';
            btn.classList.remove('opacity-75', 'cursor-not-allowed');
        }, 3000);
    }
</script>
@endsection