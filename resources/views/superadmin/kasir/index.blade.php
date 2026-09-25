@extends('layouts.admin')

@section('title', 'Validasi Kasir')

@section('content')
<div class="mb-6">
    <h3 class="font-bold text-[#4b465c] text-2xl">Validasi Kasir</h3>
    <p class="text-[#a8aaae] text-sm">Persetujuan pembayaran dan pencetakan nota/invoice</p>
</div>

@if(session('success'))
<div class="bg-emerald-100 text-emerald-700 p-4 rounded-xl mb-6 shadow-sm flex items-center gap-3">
    <i class="fa-solid fa-circle-check text-lg"></i>
    <span class="font-medium text-sm">{{ session('success') }}</span>
</div>
@endif

@if($errors->any())
<div class="bg-red-100 text-red-700 p-4 rounded-xl mb-6 shadow-sm flex items-start gap-3">
    <i class="fa-solid fa-circle-exclamation text-lg mt-0.5"></i>
    <ul class="list-disc pl-4 text-sm font-medium">
        @foreach($errors->all() as $error) <li>{{ $error }}</li> @endforeach
    </ul>
</div>
@endif

<div class="bg-white rounded-xl shadow-[0_4px_18px_0_rgba(75,70,92,0.1)] overflow-hidden">
    <div class="p-5 border-b border-gray-100 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <h5 class="font-semibold text-[#4b465c] text-lg">Antrean Pembayaran</h5>
        <form action="" method="GET" class="w-full sm:w-72">
            <div class="relative">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari No. Invoice..." class="w-full pl-10 pr-4 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-[#7367f0]">
                <i class="fa-solid fa-magnifying-glass absolute left-3 top-3 text-gray-400 text-sm"></i>
            </div>
        </form>
    </div>
    
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-[#f8f7fa] text-[#4b465c] uppercase text-xs font-semibold tracking-wider border-b border-gray-100">
                    <th class="py-4 px-6">No. Invoice & Tanggal</th>
                    <th class="py-4 px-6">Dibuat Oleh</th>
                    <th class="py-4 px-6">Total Tagihan</th>
                    <th class="py-4 px-6 text-center">Status & Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 text-[#4b465c] text-[15px]">
                @forelse($transaksi as $item)
                <tr class="hover:bg-slate-50/50 transition-colors">
                    <td class="py-4 px-6">
                        <p class="font-bold text-[#7367f0]">{{ $item->no_invoice }}</p>
                        <p class="text-xs text-[#a8aaae]">{{ \Carbon\Carbon::parse($item->created_at)->translatedFormat('d M Y, H:i') }}</p>
                    </td>
                    <td class="py-4 px-6 font-medium">{{ $item->nama_sales ?? 'Pelanggan' }}</td>
                    <td class="py-4 px-6 font-bold text-gray-700 text-lg">Rp {{ number_format($item->total_transaksi, 0, ',', '.') }}</td>
                    <td class="py-4 px-6 text-center">
                        @if($item->status == 'disiapkan_gudang')
                            <span class="bg-amber-100 text-amber-600 px-3 py-1 rounded-md text-xs font-bold uppercase block mb-3 w-fit mx-auto">Menunggu Pembayaran</span>
                            <form action="{{ route('superadmin.kasir.approve', $item->id) }}" method="POST" onsubmit="return confirm('Pastikan uang pembayaran sudah diterima dengan nominal yang sesuai. Lanjutkan?');">
                                @csrf
                                <button type="submit" class="bg-[#7367f0] hover:bg-[#6355e6] text-white text-sm font-bold py-2 px-4 rounded-lg shadow-sm transition-all w-full flex items-center justify-center gap-2">
                                    <i class="fa-solid fa-check-double"></i> Setujui & Lunas
                                </button>
                            </form>
                        <!-- PERUBAHAN: Mengecek kata 'selesai' -->
                        @elseif($item->status == 'selesai')
                            <span class="bg-emerald-100 text-emerald-600 px-3 py-1 rounded-md text-xs font-bold uppercase block mb-3 w-fit mx-auto"><i class="fa-solid fa-check"></i> Selesai / Lunas</span>
                            <a href="{{ route('superadmin.kasir.nota', $item->id) }}" target="_blank" class="bg-gray-800 hover:bg-gray-900 text-white text-sm font-bold py-2 px-4 rounded-lg shadow-sm transition-all w-full flex items-center justify-center gap-2">
                                <i class="fa-solid fa-print"></i> Cetak Nota
                            </a>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="py-12 text-center text-gray-400">Belum ada antrean validasi dari Gudang.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="p-4 border-t border-gray-100">
        {{ $transaksi->appends(request()->query())->links() }}
    </div>
</div>
@endsection