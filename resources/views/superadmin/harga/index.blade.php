@extends('layouts.admin')

@section('title', 'Manajemen Harga')

@section('content')
<div class="mb-6 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
    <div>
        <h3 class="font-bold text-[#4b465c] text-2xl">Manajemen Harga & Diskon</h3>
        <p class="text-[#a8aaae] text-sm">Tetapkan harga modal, minimum, harga jual, dan diskon produk</p>
    </div>
</div>

@if(session('success'))
<div class="bg-emerald-100 text-emerald-700 p-4 rounded-xl mb-6 shadow-sm flex items-center gap-3">
    <i class="fa-solid fa-circle-check text-lg"></i>
    <span class="font-medium text-sm">{{ session('success') }}</span>
</div>
@endif
@if($errors->any())
<div class="bg-red-100 text-red-700 p-4 rounded-xl mb-6 shadow-sm">
    <ul class="list-disc pl-5 text-sm font-medium">
        @foreach($errors->all() as $error) <li>{{ $error }}</li> @endforeach
    </ul>
</div>
@endif

<div class="bg-white rounded-xl shadow-[0_4px_18px_0_rgba(75,70,92,0.1)] overflow-hidden">
    <div class="p-5 border-b border-gray-100 flex flex-col sm:flex-row justify-between items-center gap-4">
        <h5 class="font-semibold text-[#4b465c] text-lg">Daftar Harga Barang</h5>
        <form action="" method="GET" class="w-full sm:w-72">
            <div class="relative">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari kode atau nama..." class="w-full pl-10 pr-4 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-[#7367f0] focus:border-transparent">
                <i class="fa-solid fa-magnifying-glass absolute left-3 top-3 text-gray-400 text-sm"></i>
            </div>
        </form>
    </div>
    
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-[#f8f7fa] text-[#4b465c] uppercase text-xs font-semibold tracking-wider border-b border-gray-100">
                    <th class="py-4 px-6 min-w-[200px]">Produk</th>
                    <th class="py-4 px-6 text-right min-w-[130px]">Harga Modal</th>
                    <th class="py-4 px-6 text-right min-w-[130px]">Harga Minimum</th>
                    <th class="py-4 px-6 text-right min-w-[130px]">Harga Jual</th>
                    <th class="py-4 px-6 text-right min-w-[110px]">Diskon Promo</th>
                    <th class="py-4 px-6 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 text-[#4b465c] text-[15px]">
                @foreach($barangHarga as $item)
                <tr class="hover:bg-slate-50/50 transition-colors">
                    <td class="py-4 px-6">
                        <p class="font-bold text-[#4b465c]">{{ $item->nama_barang }}</p>
                        <p class="text-xs text-[#a8aaae]">{{ $item->kode_barang }} • {{ $item->kategori }}</p>
                    </td>
                    <td class="py-4 px-6 text-right font-medium {{ $item->harga_modal ? 'text-gray-700' : 'text-red-400' }}">
                        {{ $item->harga_modal ? 'Rp ' . number_format($item->harga_modal, 0, ',', '.') : 'Belum Diset' }}
                    </td>
                    <td class="py-4 px-6 text-right font-medium {{ $item->harga_minimum ? 'text-orange-500' : 'text-red-400' }}">
                        {{ $item->harga_minimum ? 'Rp ' . number_format($item->harga_minimum, 0, ',', '.') : 'Belum Diset' }}
                    </td>
                    <td class="py-4 px-6 text-right font-bold {{ $item->harga_jual ? 'text-emerald-600' : 'text-red-400' }}">
                        {{ $item->harga_jual ? 'Rp ' . number_format($item->harga_jual, 0, ',', '.') : 'Belum Diset' }}
                    </td>
                    <td class="py-4 px-6 text-right font-bold {{ $item->diskon_rupiah > 0 ? 'text-red-500' : 'text-gray-400' }}">
                        {{ $item->diskon_rupiah > 0 ? '- Rp ' . number_format($item->diskon_rupiah, 0, ',', '.') : '0' }}
                    </td>
                    <td class="py-4 px-6 text-center">
                        <button onclick="openHargaModal({{ json_encode($item) }})" class="bg-[#7367f0]/10 hover:bg-[#7367f0] text-[#7367f0] hover:text-white transition-colors px-3 py-1.5 rounded-lg text-sm font-semibold whitespace-nowrap">
                            <i class="fa-solid fa-tag mr-1"></i> Set Harga
                        </button>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="p-4 border-t border-gray-100">
        {{ $barangHarga->appends(request()->query())->links() }}
    </div>
</div>

<div id="modalHarga" class="fixed inset-0 z-50 overflow-y-auto hidden bg-black/40 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-md w-full shadow-xl overflow-hidden transform transition-all">
        <div class="px-6 py-4 bg-[#f8f7fa] border-b border-gray-100 flex justify-between items-center">
            <h3 class="font-bold text-[#4b465c] text-lg">Konfigurasi Harga & Diskon</h3>
            <button type="button" onclick="toggleModal('modalHarga')" class="text-gray-400 hover:text-gray-600 text-xl"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <form action="{{ route('superadmin.harga.store') }}" method="POST" class="p-6 space-y-4">
            @csrf
            <input type="hidden" id="barang_id" name="barang_id">
            
            <div class="mb-4 bg-indigo-50 p-3 rounded-lg border border-indigo-100">
                <p class="text-xs text-indigo-400 font-bold uppercase mb-1">Produk Terpilih:</p>
                <p id="nama_produk_display" class="font-bold text-indigo-700">-</p>
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-600 mb-1">Harga Modal (Rp)</label>
                <input type="number" id="harga_modal" name="harga_modal" required min="0" class="w-full px-3 py-2 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#7367f0]">
                <p class="text-[11px] text-gray-400 mt-1">Harga asli pembelian dari supplier/pabrik.</p>
            </div>
            
            <div class="grid grid-cols-2 gap-4">
                <div class="col-span-2">
                    <label class="block text-sm font-semibold text-gray-600 mb-1">Harga Minimum (Rp)</label>
                    <input type="number" id="harga_minimum" name="harga_minimum" required min="0" class="w-full px-3 py-2 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-400">
                    <p class="text-[11px] text-gray-400 mt-1 leading-tight">Batas Kasir tolak harga.</p>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-600 mb-1">Harga Jual (Rp)</label>
                    <input type="number" id="harga_jual" name="harga_jual" required min="0" class="w-full px-3 py-2 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-600 mb-1">Diskon Promo (Rp)</label>
                    <input type="number" id="diskon_rupiah" name="diskon_rupiah" min="0" class="w-full px-3 py-2 border border-red-200 bg-red-50 text-red-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500" placeholder="0">
                </div>
            </div>

            <div id="margin_preview_box" class="hidden mt-4 p-3 rounded-lg border">
                <p class="text-sm font-bold" id="margin_text"></p>
                <p class="text-xs text-red-500 hidden mt-1" id="error_text"></p>
            </div>

            <div class="pt-4 flex justify-end gap-3 border-t border-gray-100 mt-2">
                <button type="button" onclick="toggleModal('modalHarga')" class="px-4 py-2 border border-gray-200 rounded-lg text-gray-600 hover:bg-gray-50 text-sm font-medium">Batal</button>
                <button type="submit" id="btnSimpanHarga" class="px-4 py-2 bg-[#7367f0] hover:bg-[#6355e6] text-white rounded-lg text-sm font-medium">Simpan Konfigurasi</button>
            </div>
        </form>
    </div>
</div>

<script>
    function toggleModal(modalId) {
        const modal = document.getElementById(modalId);
        modal.classList.toggle('hidden');
    }

    function openHargaModal(item) {
        document.getElementById('barang_id').value = item.barang_id;
        document.getElementById('nama_produk_display').innerText = item.kode_barang + ' - ' + item.nama_barang;
        
        document.getElementById('harga_modal').value = item.harga_modal || '';
        document.getElementById('harga_minimum').value = item.harga_minimum || '';
        document.getElementById('harga_jual').value = item.harga_jual || '';
        document.getElementById('diskon_rupiah').value = item.diskon_rupiah || '';
        
        calculateMargin(); 
        toggleModal('modalHarga');
    }

    const inputModal = document.getElementById('harga_modal');
    const inputMin = document.getElementById('harga_minimum');
    const inputJual = document.getElementById('harga_jual');
    const inputDiskon = document.getElementById('diskon_rupiah'); // [BARU]
    const btnSimpan = document.getElementById('btnSimpanHarga');
    const marginBox = document.getElementById('margin_preview_box');
    const marginText = document.getElementById('margin_text');
    const errorText = document.getElementById('error_text');

    function calculateMargin() {
        const modal = parseInt(inputModal.value) || 0;
        const min = parseInt(inputMin.value) || 0;
        const jual = parseInt(inputJual.value) || 0;
        const diskon = parseInt(inputDiskon.value) || 0; // [BARU]

        if (inputModal.value === '' && inputMin.value === '' && inputJual.value === '') {
            marginBox.classList.add('hidden');
            btnSimpan.disabled = false;
            btnSimpan.classList.remove('opacity-50', 'cursor-not-allowed');
            return;
        }

        marginBox.classList.remove('hidden');
        let isError = false;
        let errorMsg = '';
        
        const hargaFinal = jual - diskon; // [BARU] Logika Kalkulasi Anti-Rugi

        if (min < modal) {
            isError = true;
            errorMsg = 'Harga Minimum tidak boleh lebih kecil dari Harga Modal!';
        } else if (jual < min) {
            isError = true;
            errorMsg = 'Harga Jual Normal tidak boleh lebih kecil dari Harga Minimum!';
        } else if (hargaFinal < min) {
            isError = true;
            errorMsg = 'Gagal! Harga Final (Jual - Diskon) jatuh di bawah Harga Minimum. Kurangi diskon!';
        }

        if (isError) {
            marginBox.className = 'mt-4 p-3 rounded-lg border bg-red-50 border-red-200';
            marginText.innerText = 'Peringatan Sistem:';
            marginText.className = 'text-sm font-bold text-red-600';
            errorText.innerText = errorMsg;
            errorText.classList.remove('hidden');
            
            btnSimpan.disabled = true;
            btnSimpan.classList.add('opacity-50', 'cursor-not-allowed');
        } else {
            const profit = hargaFinal - modal;
            marginBox.className = 'mt-4 p-3 rounded-lg border bg-emerald-50 border-emerald-200';
            marginText.innerText = 'Estimasi Keuntungan: Rp ' + profit.toLocaleString('id-ID') + ' / produk';
            marginText.className = 'text-sm font-bold text-emerald-600';
            errorText.classList.add('hidden');
            
            btnSimpan.disabled = false;
            btnSimpan.classList.remove('opacity-50', 'cursor-not-allowed');
        }
    }

    inputModal.addEventListener('input', calculateMargin);
    inputMin.addEventListener('input', calculateMargin);
    inputJual.addEventListener('input', calculateMargin);
    inputDiskon.addEventListener('input', calculateMargin); // [BARU]
</script>
@endsection