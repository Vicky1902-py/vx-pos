@extends('layouts.admin')

@section('title', 'Detail Histori Pelanggan')

@section('content')
<div class="mb-6 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
    <div>
        <h3 class="font-bold text-[#4b465c] text-2xl">Kartu Piutang Pelanggan</h3>
        <p class="text-[#a8aaae] text-sm">Rincian invoice dan rekam jejak pembayaran cicilan</p>
    </div>
    <a href="{{ route('superadmin.pelanggan.index') }}" class="bg-white border border-gray-200 hover:bg-gray-50 text-gray-600 font-medium py-2 px-4 rounded-xl text-sm shadow-sm transition-all flex items-center gap-2">
        <i class="fa-solid fa-arrow-left"></i> Kembali
    </a>
</div>

<div class="bg-gradient-to-r from-[#7367f0] to-[#9e95f5] rounded-xl p-6 shadow-lg text-white mb-6 flex flex-col md:flex-row justify-between items-center gap-4">
    <div class="flex items-center gap-4">
        <div class="w-16 h-16 bg-white/20 rounded-full flex items-center justify-center text-3xl backdrop-blur-sm">
            <i class="fa-solid fa-user-tie"></i>
        </div>
        <div>
            <h2 class="text-2xl font-bold uppercase">{{ $namaPelanggan }}</h2>
            <p class="text-indigo-100 text-sm">Data dirangkum berdasarkan aktivitas transaksi.</p>
        </div>
    </div>
    <div class="flex gap-6 text-right">
        <div>
            <p class="text-indigo-200 text-xs uppercase tracking-wider font-bold mb-1">Total Belanja</p>
            <p class="text-xl font-bold">Rp {{ number_format($ringkasan['total_belanja'], 0, ',', '.') }}</p>
        </div>
        <div>
            <p class="text-indigo-200 text-xs uppercase tracking-wider font-bold mb-1">Sisa Piutang</p>
            <p class="text-xl font-bold {{ $ringkasan['total_piutang'] > 0 ? 'text-red-200' : 'text-emerald-300' }}">Rp {{ number_format($ringkasan['total_piutang'], 0, ',', '.') }}</p>
        </div>
    </div>
</div>

<div class="space-y-4">
    @forelse($transaksi as $inv)
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <!-- Header Invoice -->
        <div class="p-4 bg-gray-50 flex flex-wrap justify-between items-center gap-4 border-b border-gray-100">
            <div>
                <span class="font-bold text-[#7367f0] text-lg">{{ $inv->no_invoice }}</span>
                <span class="ml-2 text-xs text-gray-400"><i class="fa-regular fa-clock"></i> {{ \Carbon\Carbon::parse($inv->created_at)->translatedFormat('d M Y, H:i') }}</span>
            </div>
            <div class="flex gap-4 items-center">
                <div class="text-right">
                    <p class="text-[11px] text-gray-400 uppercase font-bold">Total Transaksi</p>
                    <p class="font-bold text-gray-700">Rp {{ number_format($inv->total_transaksi, 0, ',', '.') }}</p>
                </div>
                <div class="text-right">
                    <p class="text-[11px] text-gray-400 uppercase font-bold">Sisa Piutang</p>
                    <p class="font-bold {{ $inv->piutang > 0 ? 'text-red-500' : 'text-emerald-500' }}">Rp {{ number_format($inv->piutang, 0, ',', '.') }}</p>
                </div>
                <div>
                    @if($inv->piutang > 0)
                        <span class="bg-red-100 text-red-600 px-3 py-1 rounded-md text-xs font-bold uppercase">PIUTANG</span>
                    @else
                        <span class="bg-emerald-100 text-emerald-600 px-3 py-1 rounded-md text-xs font-bold uppercase">LUNAS</span>
                    @endif
                </div>
            </div>
        </div>
        
        <!-- Riwayat Cicilan untuk Invoice ini -->
        <div class="p-4">
            <h6 class="text-xs font-bold text-gray-400 uppercase mb-3"><i class="fa-solid fa-list-check mr-1"></i> Rekam Jejak Pembayaran (Uang Masuk)</h6>
            @if(isset($riwayat[$inv->id]) && count($riwayat[$inv->id]) > 0)
                <div class="space-y-2">
                    @foreach($riwayat[$inv->id] as $cicilan)
                    <div class="flex justify-between items-center bg-emerald-50/50 p-3 rounded border border-emerald-100/50">
                        <div>
                            <p class="text-sm font-medium text-[#4b465c]">{{ $cicilan->keterangan }}</p>
                            <p class="text-[11px] text-gray-500">{{ \Carbon\Carbon::parse($cicilan->tanggal_bayar)->translatedFormat('l, d F Y - H:i') }}</p>
                        </div>
                        <p class="font-bold text-emerald-600">+ Rp {{ number_format($cicilan->nominal_bayar, 0, ',', '.') }}</p>
                    </div>
                    @endforeach
                </div>
            @else
                <p class="text-sm text-gray-400 italic">Belum ada riwayat pembayaran yang tercatat.</p>
            @endif
        </div>
    </div>
    @empty
    <div class="text-center py-10 bg-white rounded-xl border border-gray-100">
        <p class="text-gray-400">Tidak ada riwayat transaksi untuk pelanggan ini.</p>
    </div>
    @endforelse
</div>
@endsection