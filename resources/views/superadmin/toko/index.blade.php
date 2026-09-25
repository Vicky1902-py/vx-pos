@extends('layouts.admin')

@section('title', 'Manajemen Multi-Toko & Cabang')

@section('content')
<div class="mb-6 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
    <div>
        <h3 class="font-bold text-[#4b465c] text-2xl flex items-center gap-2">
            <i class="fa-solid fa-store text-[#7367f0]"></i> Multi-Toko & Mitra
        </h3>
        <p class="text-[#a8aaae] text-sm">Kelola pendaftaran toko baru, paket langganan, dan beralih cabang kerja</p>
    </div>
    <button onclick="openModal('modalTambahToko')" class="bg-[#7367f0] hover:bg-[#6355e6] text-white font-medium py-2.5 px-4 rounded-xl text-sm shadow-[0_2px_6px_rgba(115,103,240,0.4)] transition-all flex items-center gap-2">
        <i class="fa-solid fa-plus"></i> <span>Daftarkan Toko Baru</span>
    </button>
</div>

@if(session('success'))
<div class="bg-emerald-100 text-emerald-700 p-4 rounded-xl mb-4 shadow-sm flex items-center gap-3">
    <i class="fa-solid fa-circle-check text-lg"></i>
    <span class="font-medium text-sm">{{ session('success') }}</span>
</div>
@endif

@if($errors->any())
<div class="bg-red-100 text-red-700 p-4 rounded-xl mb-4 shadow-sm flex items-start gap-3">
    <i class="fa-solid fa-circle-exclamation text-lg mt-0.5"></i>
    <ul class="list-disc pl-4 text-sm font-medium">
        @foreach($errors->all() as $error) <li>{{ $error }}</li> @endforeach
    </ul>
</div>
@endif

<!-- Toko Table Card -->
<div class="bg-white rounded-xl shadow-[0_4px_18px_0_rgba(75,70,92,0.1)] border border-gray-100 overflow-hidden">
    <div class="p-4 border-b border-gray-100 flex flex-col sm:flex-row justify-between items-center gap-3 bg-gray-50/50">
        <form action="" method="GET" class="w-full sm:w-80">
            <div class="relative">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama toko, alamat, telp..." class="w-full pl-10 pr-4 py-2 bg-white border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-[#7367f0] focus:border-transparent">
                <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-2.5 text-gray-400 text-sm"></i>
            </div>
        </form>
        <span class="text-xs text-gray-500">Total Toko Terdaftar: <strong>{{ $toko->total() }}</strong></span>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="border-b border-gray-100 bg-[#f8f7fa] text-[#4b465c] text-[13px] font-bold uppercase tracking-wider">
                    <th class="py-3.5 px-4">Nama Toko</th>
                    <th class="py-3.5 px-4">Paket</th>
                    <th class="py-3.5 px-4 text-center">Status</th>
                    <th class="py-3.5 px-4 text-center">Data Produk</th>
                    <th class="py-3.5 px-4 text-center">Transaksi</th>
                    <th class="py-3.5 px-4 text-right">Total Omzet</th>
                    <th class="py-3.5 px-4 text-center">Aksi & Beralih</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 text-sm text-[#4b465c]">
                @forelse($toko as $item)
                @php $isActive = ($item->id == $activeTokoId); @endphp
                <tr class="hover:bg-gray-50/80 transition-colors {{ $isActive ? 'bg-indigo-50/40' : '' }}">
                    <td class="py-3.5 px-4">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-lg {{ $isActive ? 'bg-[#7367f0] text-white' : 'bg-gray-100 text-gray-600' }} flex items-center justify-center font-bold text-base shadow-sm">
                                <i class="fa-solid fa-shop"></i>
                            </div>
                            <div>
                                <div class="font-bold flex items-center gap-2">
                                    <span>{{ $item->nama_toko }}</span>
                                    @if($isActive)
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-[#7367f0] text-white">SEDANG DIBUKA</span>
                                    @endif
                                </div>
                                <span class="text-xs text-gray-400 block">{{ $item->no_telp ?? 'Tanpa no. telepon' }}</span>
                            </div>
                        </div>
                    </td>
                    <td class="py-3.5 px-4">
                        <span class="px-2.5 py-1 rounded-full text-xs font-bold uppercase tracking-wider
                            {{ $item->paket === 'enterprise' ? 'bg-purple-100 text-purple-700' : ($item->paket === 'pro' ? 'bg-indigo-100 text-indigo-700' : 'bg-gray-100 text-gray-700') }}">
                            {{ $item->paket }}
                        </span>
                    </td>
                    <td class="py-3.5 px-4 text-center">
                        <span class="px-2.5 py-1 rounded-full text-xs font-bold
                            {{ $item->status === 'aktif' ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700' }}">
                            {{ ucfirst($item->status) }}
                        </span>
                    </td>
                    <td class="py-3.5 px-4 text-center font-medium">{{ number_format($item->total_barang) }} item</td>
                    <td class="py-3.5 px-4 text-center font-medium">{{ number_format($item->total_transaksi) }} tx</td>
                    <td class="py-3.5 px-4 text-right font-bold text-[#7367f0]">Rp {{ number_format($item->total_omzet, 0, ',', '.') }}</td>
                    <td class="py-3.5 px-4 text-center">
                        <div class="flex items-center justify-center gap-2">
                            @if(!$isActive)
                                <a href="{{ route('superadmin.toko.switch', $item->id) }}" class="px-3 py-1.5 rounded-lg text-xs font-bold text-white bg-[#7367f0] hover:bg-[#6355e6] shadow-sm transition-all flex items-center gap-1.5">
                                    <i class="fa-solid fa-arrow-right-to-bracket text-[10px]"></i>
                                    <span>Buka Toko</span>
                                </a>
                            @else
                                <span class="px-3 py-1.5 rounded-lg text-xs font-bold text-indigo-600 bg-indigo-100">
                                    <i class="fa-solid fa-check"></i> Aktif
                                </span>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="py-8 text-center text-gray-400 text-sm">Belum ada toko terdaftar.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="p-4 border-t border-gray-100">
        {{ $toko->links() }}
    </div>
</div>

<!-- Modal Tambah Toko Baru -->
<div id="modalTambahToko" class="fixed inset-0 z-50 hidden bg-gray-900/50 backdrop-blur-sm overflow-y-auto flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl relative">
        <div class="flex justify-between items-center pb-4 mb-4 border-b border-gray-100">
            <h4 class="font-bold text-lg text-[#4b465c] flex items-center gap-2">
                <i class="fa-solid fa-shop text-[#7367f0]"></i> Daftarkan Toko Baru
            </h4>
            <button onclick="closeModal('modalTambahToko')" class="text-gray-400 hover:text-gray-600">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>

        <form action="{{ route('superadmin.toko.store') }}" method="POST" class="space-y-4">
            @csrf
            
            <p class="text-xs font-bold uppercase text-gray-400 tracking-wider">Informasi Toko / Cabang</p>
            <div>
                <label class="block text-xs font-bold text-gray-600 mb-1">Nama Toko *</label>
                <input type="text" name="nama_toko" required placeholder="Contoh: Toko Cabang Surabaya" class="w-full px-3.5 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-[#7367f0] focus:outline-none">
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold text-gray-600 mb-1">No. Telepon Toko</label>
                    <input type="text" name="no_telp" placeholder="08xxxxxxxxxx" class="w-full px-3.5 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-[#7367f0] focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-600 mb-1">Paket Langganan *</label>
                    <select name="paket" required class="w-full px-3.5 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-[#7367f0] focus:outline-none">
                        <option value="starter">Starter</option>
                        <option value="pro" selected>Professional</option>
                        <option value="enterprise">Enterprise</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-600 mb-1">Alamat Toko</label>
                <textarea name="alamat" rows="2" placeholder="Alamat lengkap toko..." class="w-full px-3.5 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-[#7367f0] focus:outline-none"></textarea>
            </div>

            <hr class="my-4 border-gray-100">
            <p class="text-xs font-bold uppercase text-gray-400 tracking-wider">Akun Pemilik / Admin Toko</p>

            <div>
                <label class="block text-xs font-bold text-gray-600 mb-1">Nama Pemilik Toko *</label>
                <input type="text" name="admin_nama" required placeholder="Nama lengkap admin toko" class="w-full px-3.5 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-[#7367f0] focus:outline-none">
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold text-gray-600 mb-1">Username Login *</label>
                    <input type="text" name="admin_username" required placeholder="Username unik" class="w-full px-3.5 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-[#7367f0] focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-600 mb-1">Password Login *</label>
                    <input type="password" name="admin_password" required minlength="6" placeholder="Minimal 6 karakter" class="w-full px-3.5 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-[#7367f0] focus:outline-none">
                </div>
            </div>

            <div class="pt-4 flex justify-end gap-3">
                <button type="button" onclick="closeModal('modalTambahToko')" class="px-4 py-2 border border-gray-200 text-gray-600 rounded-lg text-sm font-medium hover:bg-gray-50">Batal</button>
                <button type="submit" class="px-5 py-2 bg-[#7367f0] hover:bg-[#6355e6] text-white rounded-lg text-sm font-bold shadow-md shadow-indigo-500/20">Daftarkan Toko</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openModal(id) {
        document.getElementById(id).classList.remove('hidden');
    }
    function closeModal(id) {
        document.getElementById(id).classList.add('hidden');
    }
</script>
@endsection
