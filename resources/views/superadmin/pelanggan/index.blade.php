@extends('layouts.admin')

@section('title', 'Buku Piutang & Master Pelanggan')

@section('content')
<div class="mb-6 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
    <div>
        <h3 class="font-bold text-[#4b465c] text-2xl">Buku Piutang & Pelanggan</h3>
        <p class="text-[#a8aaae] text-sm">Rekapitulasi riwayat belanja dan sisa tagihan pelanggan</p>
    </div>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
    <div class="bg-white rounded-xl p-5 shadow-[0_4px_18px_0_rgba(75,70,92,0.1)] flex items-center gap-4">
        <div class="w-14 h-14 bg-indigo-100 rounded-lg flex items-center justify-center text-indigo-600 text-2xl">
            <i class="fa-solid fa-users"></i>
        </div>
        <div>
            <p class="text-gray-500 font-medium text-sm">Total Pelanggan Terdaftar</p>
            <h4 class="font-bold text-2xl text-[#4b465c]">{{ $ringkasan->total_pelanggan }} <span class="text-sm font-normal text-gray-400">Toko/Orang</span></h4>
        </div>
    </div>
    
    <div class="bg-white rounded-xl p-5 shadow-[0_4px_18px_0_rgba(75,70,92,0.1)] flex items-center gap-4 border-l-4 border-red-500">
        <div class="w-14 h-14 bg-red-100 rounded-lg flex items-center justify-center text-red-600 text-2xl">
            <i class="fa-solid fa-hand-holding-dollar"></i>
        </div>
        <div>
            <p class="text-gray-500 font-medium text-sm">Total Piutang Berjalan</p>
            <h4 class="font-bold text-2xl text-red-600">Rp {{ number_format($ringkasan->total_piutang_global, 0, ',', '.') }}</h4>
        </div>
    </div>
</div>

<div class="bg-white rounded-xl shadow-[0_4px_18px_0_rgba(75,70,92,0.1)] overflow-hidden">
    <div class="p-5 border-b border-gray-100 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <h5 class="font-semibold text-[#4b465c] text-lg">Daftar Pelanggan</h5>
        <div class="flex gap-2 w-full md:w-auto">
            <form action="" method="GET" class="w-full sm:w-64">
                <div class="relative">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari Nama Pelanggan..." class="w-full pl-10 pr-4 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-[#7367f0]">
                    <i class="fa-solid fa-magnifying-glass absolute left-3 top-3 text-gray-400 text-sm"></i>
                </div>
            </form>
            <!-- [BARU] Tombol Export Excel -->
            <a href="{{ route('superadmin.pelanggan.export') }}" class="bg-[#28c76f] hover:bg-[#23af61] text-white font-medium py-2 px-4 rounded-lg text-sm shadow-md transition-all flex items-center gap-2 whitespace-nowrap">
                <i class="fa-solid fa-file-excel"></i> Export
            </a>
        </div>
    </div>
    
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-[#f8f7fa] text-[#4b465c] uppercase text-xs font-semibold tracking-wider border-b border-gray-100">
                    <th class="py-4 px-6">Nama Toko / Pelanggan</th>
                    <th class="py-4 px-6 text-center">Jml Transaksi</th>
                    <th class="py-4 px-6 text-right">Total Uang Masuk</th>
                    <th class="py-4 px-6 text-right">Total Belanja (Gross)</th>
                    <th class="py-4 px-6 text-right">Sisa Piutang (Net)</th>
                    <th class="py-4 px-6 text-center">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 text-[#4b465c] text-[15px]">
                @forelse($pelanggan as $item)
                <tr class="hover:bg-slate-50/50 transition-colors">
                    <td class="py-4 px-6 font-bold">
                        <!-- [BARU] Nama menjadi tautan ke halaman Detail Histori -->
                        <a href="{{ route('superadmin.pelanggan.show', urlencode(strtoupper($item->nama ?? 'UMUM'))) }}" class="text-[#7367f0] hover:text-[#5e50ee] hover:underline transition-colors flex items-center gap-2">
                            <i class="fa-solid fa-address-card text-gray-300"></i> {{ strtoupper($item->nama ?? 'UMUM') }}
                        </a>
                    </td>
                    <td class="py-4 px-6 text-center font-medium">{{ $item->total_transaksi }} x</td>
                    <td class="py-4 px-6 text-right font-medium text-emerald-600">Rp {{ number_format($item->total_dibayar, 0, ',', '.') }}</td>
                    <td class="py-4 px-6 text-right font-bold text-gray-700">Rp {{ number_format($item->total_belanja, 0, ',', '.') }}</td>
                    <td class="py-4 px-6 text-right font-bold {{ $item->total_piutang > 0 ? 'text-red-500' : 'text-gray-400' }}">
                        Rp {{ number_format($item->total_piutang, 0, ',', '.') }}
                    </td>
                    <td class="py-4 px-6 text-center">
                        @if($item->total_piutang > 0)
                            <span class="bg-red-100 text-red-600 px-3 py-1 rounded-md text-xs font-bold uppercase">Ada Tunggakan</span>
                        @else
                            <span class="bg-emerald-100 text-emerald-600 px-3 py-1 rounded-md text-xs font-bold uppercase">Aman</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="py-8 text-center text-gray-400">Belum ada data pelanggan yang tercatat.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="p-4 border-t border-gray-100">
        {{ $pelanggan->appends(request()->query())->links() }}
    </div>
</div>
@endsection