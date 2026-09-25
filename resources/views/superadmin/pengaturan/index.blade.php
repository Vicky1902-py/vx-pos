@extends('layouts.admin')

@section('title', 'Pengaturan Toko')

@section('content')
<div class="mb-6">
    <h3 class="font-bold text-[#4b465c] text-2xl">Pengaturan Toko</h3>
    <p class="text-[#a8aaae] text-sm">Sesuaikan informasi dan logo yang akan tampil di Invoice</p>
</div>

@if(session('success'))
<div class="bg-emerald-100 text-emerald-700 p-4 rounded-xl mb-6 shadow-sm">
    <i class="fa-solid fa-circle-check mr-2"></i> {{ session('success') }}
</div>
@endif

<form action="{{ route('superadmin.pengaturan.update') }}" method="POST" enctype="multipart/form-data" class="bg-white p-6 rounded-xl shadow-[0_4px_18px_0_rgba(75,70,92,0.1)]">
    @csrf
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Nama Toko</label>
            <input type="text" name="nama_toko" value="{{ $pengaturan->nama_toko ?? '' }}" class="w-full p-2 border border-gray-300 rounded-lg">
            
            <label class="block text-sm font-medium text-gray-700 mb-1 mt-4">Alamat</label>
            <textarea name="alamat" rows="3" class="w-full p-2 border border-gray-300 rounded-lg">{{ $pengaturan->alamat ?? '' }}</textarea>
            
            <label class="block text-sm font-medium text-gray-700 mb-1 mt-4">Telepon / HP</label>
            <input type="text" name="telepon" value="{{ $pengaturan->telepon ?? '' }}" class="w-full p-2 border border-gray-300 rounded-lg">
            
            <label class="block text-sm font-medium text-gray-700 mb-1 mt-4">Email</label>
            <input type="email" name="email" value="{{ $pengaturan->email ?? '' }}" class="w-full p-2 border border-gray-300 rounded-lg">
        </div>
        
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Logo Toko (Opsional)</label>
            @if(isset($pengaturan->logo) && $pengaturan->logo)
                <img src="{{ asset('uploads/logo/' . $pengaturan->logo) }}" alt="Logo" class="h-24 mb-3 border p-1 rounded">
            @endif
            <input type="file" name="logo" accept="image/*" class="w-full p-2 border border-gray-300 rounded-lg bg-gray-50">
            
            <div class="p-4 bg-gray-50 rounded-lg border border-gray-200 mt-6">
                <h4 class="font-bold text-gray-700 mb-3 border-b pb-2">Informasi Pembayaran (Footer Nota)</h4>
                
                <label class="block text-sm font-medium text-gray-700 mb-1">Bank</label>
                <input type="text" name="bank" value="{{ $pengaturan->bank ?? '' }}" class="w-full p-2 border border-gray-300 rounded-lg mb-3">
                
                <label class="block text-sm font-medium text-gray-700 mb-1">No. Rekening</label>
                <input type="text" name="no_rekening" value="{{ $pengaturan->no_rekening ?? '' }}" class="w-full p-2 border border-gray-300 rounded-lg mb-3">
                
                <label class="block text-sm font-medium text-gray-700 mb-1">Atas Nama</label>
                <input type="text" name="atas_nama" value="{{ $pengaturan->atas_nama ?? '' }}" class="w-full p-2 border border-gray-300 rounded-lg mb-3">
            </div>
        </div>
    </div>
    
    <div class="mt-6 border-t pt-4">
        <label class="block text-sm font-medium text-gray-700 mb-1">Keterangan Tambahan / Salam Penutup</label>
        <textarea name="keterangan" rows="2" class="w-full p-2 border border-gray-300 rounded-lg">{{ $pengaturan->keterangan ?? '' }}</textarea>
    </div>

    <button type="submit" class="mt-6 bg-[#7367f0] hover:bg-[#6355e6] text-white font-bold py-2.5 px-6 rounded-lg">
        Simpan Pengaturan
    </button>
</form>
@endsection