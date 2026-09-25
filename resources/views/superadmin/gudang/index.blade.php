@extends('layouts.admin')

@section('title', 'Manajemen Gudang - Permintaan Barang')

@section('content')
<div class="mb-6">
    <h3 class="font-bold text-[#4b465c] text-2xl">Manajemen Gudang</h3>
    <p class="text-[#a8aaae] text-sm">Daftar permintaan penyiapan barang dari transaksi penjualan</p>
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
        <h5 class="font-semibold text-[#4b465c] text-lg">Daftar Permintaan Masuk</h5>
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
                    <th class="py-4 px-6 w-1/4">Info Transaksi</th>
                    <th class="py-4 px-6 w-2/4">Daftar Barang & Jumlah (Kuantitas)</th>
                    <th class="py-4 px-6 text-center w-1/4">Status & Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 text-[#4b465c] text-[15px]">
                @forelse($permintaan as $item)
                <tr class="hover:bg-slate-50/50 transition-colors">
                    <td class="py-4 px-6 align-top">
                        <p class="font-bold text-[#7367f0]">{{ $item->no_invoice }}</p>
                        <p class="text-xs text-[#a8aaae] mt-1"><i class="fa-regular fa-clock"></i> {{ \Carbon\Carbon::parse($item->tgl_transaksi)->translatedFormat('d M Y, H:i') }}</p>
                        <p class="text-xs font-medium mt-2"><span class="text-gray-400">Diminta Oleh:</span> {{ $item->nama_sales ?? 'Pesanan Online' }}</p>
                    </td>
                    <td class="py-4 px-6 align-top">
                        <ul class="space-y-2">
                            @foreach($item->detail as $dt)
                            <li class="flex items-center justify-between text-sm bg-gray-50 p-2 rounded border border-gray-100">
                                <div>
                                    <span class="font-bold text-gray-700">{{ $dt->nama_barang }}</span>
                                    <span class="text-[10px] bg-gray-200 text-gray-500 px-1.5 py-0.5 rounded ml-1">{{ $dt->kode_barang }}</span>
                                </div>
                                <span class="font-bold text-[#7367f0] bg-[#7367f0]/10 px-3 py-1 rounded">x{{ $dt->jumlah }}</span>
                            </li>
                            @endforeach
                        </ul>
                    </td>
                    <td class="py-4 px-6 text-center align-top">
                        @if($item->status == 'menunggu')
                            <span class="bg-amber-100 text-amber-600 px-3 py-1 rounded-md text-xs font-bold uppercase block mb-3 w-fit mx-auto">Menunggu Disiapkan</span>
                            <form action="{{ route('superadmin.gudang.proses', $item->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin barang sudah selesai di-packing? Stok fisik akan langsung dipotong setelah Anda mengeklik OK.');">
                                @csrf
                                <button type="submit" class="bg-[#28c76f] hover:bg-[#23af61] text-white text-sm font-bold py-2.5 px-4 rounded-lg shadow-[0_2px_6px_rgba(40,199,111,0.4)] transition-all w-full flex items-center justify-center gap-2">
                                    <i class="fa-solid fa-box-open"></i> Siapkan Barang
                                </button>
                            </form>
                        @else
                            <span class="bg-emerald-100 text-emerald-600 px-3 py-1 rounded-md text-xs font-bold uppercase block w-fit mx-auto"><i class="fa-solid fa-check"></i> Sudah Disiapkan</span>
                            <p class="text-xs text-gray-400 mt-2 font-medium">Lanjut Validasi Kasir</p>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="3" class="py-12 text-center text-gray-400">
                        <i class="fa-solid fa-box-archive text-4xl mb-3 text-gray-200 block"></i>
                        Tidak ada permintaan penyiapan barang saat ini.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="p-4 border-t border-gray-100">
        {{ $permintaan->appends(request()->query())->links() }}
    </div>
</div>
@endsection