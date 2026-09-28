@extends('layouts.admin')

@section('title', 'Validasi Kasir')

@section('content')
<!-- Header Finnova Subheader -->
<div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
    <div>
        <h1 class="text-2xl font-black text-slate-900 tracking-tight">Validasi Kasir</h1>
        <p class="text-xs text-slate-400 font-medium">Persetujuan pembayaran uang masuk dan pencetakan nota/struk kasir resmi.</p>
    </div>
    
    <div class="flex items-center gap-2.5">
        <a href="{{ route('superadmin.transaksi.create') }}" class="h-10 px-5 rounded-full bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white text-xs font-bold shadow-lg shadow-indigo-500/25 inline-flex items-center gap-2 transition-all hover:scale-[1.02]">
            <i class="fa-solid fa-plus text-xs"></i>
            <span>Transaksi POS Baru</span>
        </a>
    </div>
</div>

@if(session('success'))
<div class="bg-emerald-50 border border-emerald-200/80 text-emerald-800 p-4 rounded-2xl mb-6 shadow-sm flex items-center gap-3">
    <div class="w-8 h-8 rounded-xl bg-emerald-500 text-white flex items-center justify-center text-sm shadow-sm flex-shrink-0">
        <i class="fa-solid fa-check"></i>
    </div>
    <span class="font-bold text-xs">{{ session('success') }}</span>
</div>
@endif

@if($errors->any())
<div class="bg-rose-50 border border-rose-200/80 text-rose-800 p-4 rounded-2xl mb-6 shadow-sm flex items-start gap-3">
    <div class="w-8 h-8 rounded-xl bg-rose-500 text-white flex items-center justify-center text-sm shadow-sm flex-shrink-0 mt-0.5">
        <i class="fa-solid fa-triangle-exclamation"></i>
    </div>
    <ul class="list-disc pl-4 text-xs font-semibold space-y-1">
        @foreach($errors->all() as $error) <li>{{ $error }}</li> @endforeach
    </ul>
</div>
@endif

<div class="bg-white rounded-3xl border border-slate-100 shadow-[0_8px_30px_rgb(0,0,0,0.03)] overflow-hidden mb-8">
    <div class="p-5 border-b border-slate-100 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h5 class="font-extrabold text-slate-800 text-base">Antrean Pembayaran Kasir</h5>
            <p class="text-xs text-slate-400 font-medium">Validasi pembayaran tunai, transfer, atau kartu sebelum penerbitan nota</p>
        </div>
        
        <form action="" method="GET" class="w-full sm:w-72">
            <div class="relative">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari No. Invoice..." class="w-full pl-9 pr-4 py-2 border border-slate-200 rounded-full text-xs font-medium focus:outline-none focus:ring-2 focus:ring-indigo-500 text-slate-700">
                <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-3 text-slate-400 text-xs"></i>
            </div>
        </form>
    </div>
    
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-slate-50/70 text-slate-400 uppercase text-[10px] font-extrabold tracking-wider border-b border-slate-100">
                    <th class="py-4 px-6">No. Invoice & Tanggal</th>
                    <th class="py-4 px-6">Dibuat Oleh</th>
                    <th class="py-4 px-6">Total Tagihan</th>
                    <th class="py-4 px-6 text-center">Status & Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 text-slate-700 text-xs font-medium">
                @forelse($transaksi as $item)
                <tr class="hover:bg-slate-50/50 transition-colors">
                    <td class="py-4 px-6">
                        <p class="font-bold text-indigo-600 text-xs tracking-tight"># {{ $item->no_invoice }}</p>
                        <p class="text-[11px] text-slate-400 mt-0.5 font-normal">{{ \Carbon\Carbon::parse($item->created_at)->translatedFormat('d M Y, H:i') }}</p>
                    </td>
                    <td class="py-4 px-6">
                        <div class="flex items-center gap-2.5">
                            <div class="w-8 h-8 rounded-full bg-slate-100 text-slate-600 flex items-center justify-center font-black text-[11px]">
                                {{ strtoupper(substr($item->nama_sales ?? 'K', 0, 1)) }}
                            </div>
                            <span class="font-bold text-slate-800">{{ $item->nama_sales ?? 'Kasir / Sales' }}</span>
                        </div>
                    </td>
                    <td class="py-4 px-6 font-black text-slate-900 text-base">
                        Rp {{ number_format($item->total_transaksi ?? 0, 0, ',', '.') }}
                    </td>
                    <td class="py-4 px-6 text-center">
                        @if($item->status == 'disiapkan_gudang')
                            <span class="bg-amber-50 text-amber-600 border border-amber-200 px-3 py-1 rounded-full text-[10px] font-extrabold uppercase inline-block mb-2">
                                <i class="fa-regular fa-clock mr-1"></i> Menunggu Pembayaran
                            </span>
                            
                            <!-- Custom Modal Trigger Button (NO BROWSER POPUP) -->
                            <button type="button" 
                                    onclick="openConfirmModal('{{ $item->id }}', '{{ $item->no_invoice }}', 'Rp {{ number_format($item->total_transaksi ?? 0, 0, ',', '.') }}', '{{ addslashes($item->nama_sales ?? 'Kasir') }}')"
                                    class="bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold py-2 px-4 rounded-xl shadow-md shadow-indigo-500/20 transition-all w-full flex items-center justify-center gap-2 hover:scale-[1.02]">
                                <i class="fa-solid fa-check-double text-xs"></i>
                                <span>Setujui & Lunas</span>
                            </button>
                        @elseif($item->status == 'selesai')
                            <span class="bg-emerald-50 text-emerald-600 border border-emerald-200 px-3 py-1 rounded-full text-[10px] font-extrabold uppercase inline-block mb-2">
                                <i class="fa-solid fa-check mr-1"></i> Selesai / Lunas
                            </span>
                            <a href="{{ route('superadmin.kasir.nota', $item->id) }}" target="_blank" class="bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold py-2 px-4 rounded-xl shadow-sm transition-all w-full flex items-center justify-center gap-2">
                                <i class="fa-solid fa-print text-xs"></i>
                                <span>Cetak Nota</span>
                            </a>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="py-12 text-center text-slate-400">
                        <i class="fa-solid fa-file-invoice text-3xl text-slate-300 mb-2 block"></i>
                        <p class="text-xs font-semibold">Belum ada antrean validasi dari kasir/gudang.</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="p-4 border-t border-slate-100">
        {{ $transaksi->appends(request()->query())->links() }}
    </div>
</div>

<!-- ======================================================== -->
<!-- FINNOVA CUSTOM CONFIRMATION MODAL (PENGGANTI POPUP BROWSER) -->
<!-- ======================================================== -->
<div id="modalConfirmBackdrop" class="fixed inset-0 z-50 bg-slate-950/60 backdrop-blur-sm hidden items-center justify-center p-4 transition-all duration-300">
    <div id="modalConfirmCard" class="bg-white rounded-3xl p-6 sm:p-7 max-w-md w-full shadow-2xl border border-slate-100 transform scale-95 opacity-0 transition-all duration-300">
        
        <!-- Icon Top -->
        <div class="w-14 h-14 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-2xl mx-auto mb-4 border border-emerald-100 shadow-sm">
            <i class="fa-solid fa-money-bill-wave"></i>
        </div>

        <div class="text-center mb-5">
            <h3 class="text-lg font-black text-slate-900 tracking-tight">Konfirmasi Pelunasan Kasir</h3>
            <p class="text-xs text-slate-400 mt-1">Pastikan uang tunai atau bukti transfer pelanggan telah diterima secara valid sebelum menandai transaksi ini lunas.</p>
        </div>

        <!-- Detail Box Preview -->
        <div class="bg-slate-50 rounded-2xl p-4 border border-slate-100 mb-6 space-y-2.5">
            <div class="flex justify-between items-center text-xs">
                <span class="text-slate-400 font-medium">Nomor Invoice:</span>
                <span id="modalNoInvoice" class="font-bold text-slate-900"># INV-0000</span>
            </div>
            <div class="flex justify-between items-center text-xs">
                <span class="text-slate-400 font-medium">Petugas / Kasir:</span>
                <span id="modalNamaSales" class="font-bold text-slate-700">-</span>
            </div>
            <div class="pt-2 border-t border-slate-200/80 flex justify-between items-center">
                <span class="text-xs font-bold text-slate-600">Nominal Pelunasan:</span>
                <span id="modalTotalTagihan" class="text-lg font-black text-emerald-600">Rp 0</span>
            </div>
        </div>

        <!-- Action Form -->
        <form id="formApproveKasir" action="" method="POST" class="grid grid-cols-2 gap-3">
            @csrf
            <button type="button" onclick="closeConfirmModal()" class="w-full py-3 px-4 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs transition-colors">
                Batal
            </button>
            <button type="submit" class="w-full py-3 px-4 rounded-xl bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 text-white font-bold text-xs shadow-lg shadow-emerald-500/30 transition-all flex items-center justify-center gap-1.5 hover:scale-[1.02]">
                <i class="fa-solid fa-check text-xs"></i>
                <span>Ya, Setujui Lunas</span>
            </button>
        </form>
    </div>
</div>

<script>
    function openConfirmModal(id, noInvoice, totalFormatted, namaSales) {
        const backdrop = document.getElementById('modalConfirmBackdrop');
        const card = document.getElementById('modalConfirmCard');
        const form = document.getElementById('formApproveKasir');
        
        document.getElementById('modalNoInvoice').textContent = noInvoice;
        document.getElementById('modalTotalTagihan').textContent = totalFormatted;
        document.getElementById('modalNamaSales').textContent = namaSales;
        
        form.action = "{{ url('superadmin/kasir/validasi') }}/" + id;

        backdrop.classList.remove('hidden');
        backdrop.classList.add('flex');
        setTimeout(() => {
            card.classList.remove('scale-95', 'opacity-0');
            card.classList.add('scale-100', 'opacity-100');
        }, 10);
    }

    function closeConfirmModal() {
        const backdrop = document.getElementById('modalConfirmBackdrop');
        const card = document.getElementById('modalConfirmCard');
        
        card.classList.remove('scale-100', 'opacity-100');
        card.classList.add('scale-95', 'opacity-0');
        setTimeout(() => {
            backdrop.classList.remove('flex');
            backdrop.classList.add('hidden');
        }, 200);
    }

    // Tutup modal jika klik di luar box modal atau tekan Escape
    document.getElementById('modalConfirmBackdrop').addEventListener('click', function(e) {
        if (e.target === this) closeConfirmModal();
    });
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') closeConfirmModal();
    });
</script>
@endsection