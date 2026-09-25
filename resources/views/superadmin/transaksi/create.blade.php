@extends('layouts.admin')

@section('title', 'Input Penjualan (POS)')

@section('content')
<div class="mb-4 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
    <div>
        <h3 class="font-bold text-[#4b465c] text-2xl">Kasir / Input Penjualan</h3>
        <p class="text-[#a8aaae] text-sm">Pilih barang dan atur diskon (%) per item di keranjang</p>
    </div>
    <a href="{{ route('superadmin.transaksi.index') }}" class="bg-white border border-gray-200 hover:bg-gray-50 text-gray-600 font-medium py-2 px-4 rounded-xl text-sm shadow-sm transition-all flex items-center gap-2">
        <i class="fa-solid fa-arrow-left"></i> <span class="hidden sm:inline">Kembali ke Riwayat</span>
    </a>
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

<div class="flex flex-col lg:flex-row gap-4 lg:gap-6 lg:h-[calc(100vh-140px)] pb-20 lg:pb-0">
    <!-- Kolom Kiri: Daftar Barang -->
    <div class="w-full lg:w-3/5 xl:w-2/3 bg-white rounded-xl shadow-[0_4px_18px_0_rgba(75,70,92,0.1)] flex flex-col overflow-hidden h-[60vh] lg:h-auto">
        <div class="p-3 sm:p-4 border-b border-gray-100 bg-[#f8f7fa]">
            <form action="" method="GET" class="w-full">
                <div class="relative">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari kode atau nama barang..." class="w-full pl-10 pr-4 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-[#7367f0] focus:border-transparent">
                    <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-2.5 text-gray-400 text-sm"></i>
                </div>
            </form>
        </div>
        
        <div class="p-3 sm:p-4 overflow-y-auto flex-1 bg-gray-50/30">
            <!-- [PERBAIKAN] Grid Responsif: 1 HP, 2 Tablet, 3 PC Besar -->
            <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-3 sm:gap-4">
                @forelse($barang as $item)
                @php
                    $diskonPromo = $item->diskon_rupiah ?? 0;
                    $hargaFinal = $item->harga_jual - $diskonPromo;
                    if($hargaFinal < 0) $hargaFinal = 0;
                    $hargaMin = isset($item->harga_minimum) ? $item->harga_minimum : 0; 
                @endphp
                
                <div class="bg-white border {{ $diskonPromo > 0 ? 'border-red-200 shadow-sm' : 'border-gray-100 hover:shadow-md' }} rounded-xl p-3 sm:p-4 hover:border-[#7367f0]/30 transition-all cursor-pointer flex flex-col justify-between relative group"
                     onclick="addToCart({{ $item->id }}, '{{ addslashes($item->nama_barang) }}', {{ $item->harga_jual }}, {{ $diskonPromo }}, {{ $hargaMin }}, {{ $item->stok_tersedia }})">
                    
                    @if($diskonPromo > 0)
                        <div class="absolute top-0 right-0 bg-red-500 text-white text-[9px] font-bold px-2 py-1 rounded-bl-lg rounded-tr-xl uppercase tracking-wider">Promo</div>
                    @endif

                    <div>
                        <span class="text-[9px] sm:text-[10px] font-bold px-2 py-0.5 bg-gray-100 text-gray-500 rounded uppercase tracking-wider">{{ $item->kode_barang }}</span>
                        <h5 class="font-bold text-[#4b465c] text-sm mt-2 leading-tight pr-4 group-hover:text-[#7367f0] transition-colors">{{ $item->nama_barang }}</h5>
                        <p class="text-[11px] sm:text-xs text-emerald-500 font-medium mt-1">Stok: {{ $item->stok_tersedia }}</p>
                    </div>
                    <div class="mt-3 pt-3 border-t border-gray-50 flex justify-between items-end">
                        <div class="flex flex-col">
                            @if($diskonPromo > 0)
                                <span class="text-[10px] text-gray-400 line-through mb-0.5">Rp {{ number_format($item->harga_jual, 0, ',', '.') }}</span>
                            @endif
                            <span class="font-bold text-[#7367f0] text-[15px]">Rp {{ number_format($hargaFinal, 0, ',', '.') }}</span>
                        </div>
                        <div class="w-7 h-7 rounded-full bg-[#7367f0]/10 flex items-center justify-center text-[#7367f0] group-hover:bg-[#7367f0] group-hover:text-white transition-colors mb-0.5">
                            <i class="fa-solid fa-plus text-xs"></i>
                        </div>
                    </div>
                </div>
                @empty
                <div class="col-span-full text-center py-10">
                    <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-3"><i class="fa-solid fa-box-open text-2xl text-gray-400"></i></div>
                    <p class="text-gray-400 text-sm">Barang tidak ditemukan atau stok kosong.</p>
                </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Kolom Kanan: Keranjang & Checkout -->
    <div class="w-full lg:w-2/5 xl:w-1/3 bg-white rounded-xl shadow-[0_4px_18px_0_rgba(75,70,92,0.1)] flex flex-col overflow-hidden">
        <div class="p-3 sm:p-4 border-b border-gray-100 bg-[#7367f0] text-white flex justify-between items-center shadow-sm z-10">
            <h4 class="font-bold text-sm sm:text-base"><i class="fa-solid fa-cart-shopping mr-2"></i> Keranjang</h4>
            <span id="cartCount" class="bg-white text-[#7367f0] text-[11px] sm:text-xs font-bold px-2 py-1 rounded-md shadow-sm">0 Item</span>
        </div>
        
        <div class="max-h-[35vh] lg:max-h-none overflow-y-auto p-2 sm:p-3 bg-gray-50/50 flex-1 scrollbar-thin" id="cartContainer">
            <div class="h-32 lg:h-full flex flex-col items-center justify-center text-gray-300 space-y-3" id="emptyCartMsg">
                <i class="fa-solid fa-basket-shopping text-3xl sm:text-4xl opacity-50"></i>
                <p class="text-xs sm:text-sm font-medium">Keranjang masih kosong</p>
            </div>
        </div>

        <div class="p-3 sm:p-4 border-t border-gray-200 bg-[#f8f7fa] shadow-[0_-4px_10px_rgba(0,0,0,0.02)] z-10">
            <form action="{{ route('superadmin.transaksi.store') }}" method="POST" id="checkoutForm">
                @csrf
                
                <div class="mb-2">
                    <label class="block text-[11px] sm:text-xs font-bold text-gray-500 mb-1">Sales Bertugas <span class="text-red-400">*</span></label>
                    <select name="sales_id" class="w-full border border-gray-300 rounded-lg p-2 text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-[#7367f0] bg-white transition-shadow">
                        <option value="">-- Dibuat Oleh Akun Saat Ini --</option>
                        @foreach($listSales as $sales)
                            <option value="{{ $sales->id }}">{{ strtoupper($sales->nama) }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-2 mb-3">
                    <div>
                        <label class="block text-[11px] sm:text-xs font-bold text-gray-500 mb-1">Pelanggan</label>
                        <input type="text" name="nama_pelanggan" placeholder="Umum" class="w-full border border-gray-300 rounded-lg p-2 text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-[#7367f0]">
                    </div>
                    <div>
                        <label class="block text-[11px] sm:text-xs font-bold text-gray-500 mb-1">DP (Rp)</label>
                        <input type="number" name="dp" id="inputDp" value="0" min="0" oninput="hitungPiutang()" class="w-full border border-gray-300 rounded-lg p-2 text-xs sm:text-sm font-bold text-[#4b465c] focus:outline-none focus:ring-2 focus:ring-[#7367f0]">
                    </div>
                </div>
                
                <div class="bg-white rounded-lg p-3 border border-gray-200 mb-3 shadow-sm">
                    <div class="flex justify-between items-center mb-1.5">
                        <span class="font-bold text-gray-400 text-[11px] sm:text-xs uppercase">Total Tagihan</span>
                        <span class="font-bold text-lg sm:text-xl text-[#4b465c]" id="totalTagihan">Rp 0</span>
                    </div>
                    <div class="flex justify-between items-center pt-1.5 border-t border-gray-100">
                        <span class="font-bold text-gray-400 text-[11px] sm:text-xs uppercase">Sisa Piutang</span>
                        <span class="font-bold text-sm sm:text-base text-red-500" id="sisaPiutang">Rp 0</span>
                    </div>
                </div>
                
                <input type="hidden" name="cart_data" id="cartDataInput">
                <button type="submit" id="btnCheckout" disabled class="w-full py-2.5 sm:py-3 bg-[#28c76f] hover:bg-[#23af61] active:scale-[0.98] text-white font-bold rounded-xl shadow-[0_2px_6px_rgba(40,199,111,0.3)] transition-all disabled:opacity-50 disabled:cursor-not-allowed text-sm sm:text-base flex items-center justify-center gap-2">
                    <i class="fa-solid fa-check-circle"></i> Proses Transaksi
                </button>
            </form>
        </div>
    </div>
</div>

<script>
    let cart = [];
    let globalTotal = 0;

    function addToCart(id, nama, harga, diskon_promo, harga_min, maxStok) {
        let item = cart.find(i => i.id === id);
        
        let hargaFinal = harga - diskon_promo;
        if(hargaFinal < harga_min) hargaFinal = harga_min;

        if (item) {
            if (item.jumlah < maxStok) item.jumlah++;
            else alert('Maksimal stok tercapai!');
        } else {
            cart.push({ 
                id: id, 
                nama_barang: nama, 
                harga_jual: harga, 
                harga_minimum: harga_min,
                diskon_promo: diskon_promo, 
                persen_diskon: 0, 
                diskon_item: diskon_promo, 
                harga_final: hargaFinal, 
                jumlah: 1, 
                max_stok: maxStok 
            });
        }
        renderCart();
    }

    function setDiskonPersen(id, value) {
        let item = cart.find(i => i.id === id);
        if (item) {
            let persen = parseFloat(value) || 0;
            if (persen < 0) persen = 0;
            if (persen > 100) persen = 100;

            let nominalDiskonKasir = (item.harga_jual * persen) / 100;
            let totalDiskon = item.diskon_promo + nominalDiskonKasir;
            let hargaFinalCek = item.harga_jual - totalDiskon;

            if (hargaFinalCek < item.harga_minimum) {
                alert('DITOLAK! Diskon terlalu besar. Harga jatuh di bawah Harga Minimum (Rp ' + item.harga_minimum.toLocaleString('id-ID') + ').');
                item.persen_diskon = 0;
                item.diskon_item = item.diskon_promo;
                item.harga_final = item.harga_jual - item.diskon_promo;
            } else {
                item.persen_diskon = persen;
                item.diskon_item = totalDiskon;
                item.harga_final = hargaFinalCek;
            }
            renderCart();
        }
    }

    function ubahQty(id, delta) {
        let item = cart.find(i => i.id === id);
        if (item) {
            item.jumlah += delta;
            if (item.jumlah > item.max_stok) {
                item.jumlah = item.max_stok;
                alert('Maksimal stok gudang hanya ' + item.max_stok);
            }
            if (item.jumlah <= 0) {
                cart = cart.filter(i => i.id !== id);
            }
            renderCart();
        }
    }

    function setQtyManual(id, value) {
        let item = cart.find(i => i.id === id);
        if (item) {
            let val = parseInt(value);
            if (isNaN(val) || val <= 0) val = 1;
            
            if (val > item.max_stok) {
                val = item.max_stok;
                alert('Maksimal stok gudang hanya ' + item.max_stok);
            }
            
            item.jumlah = val;
            renderCart();
        }
    }

    function hitungPiutang() {
        let dp = parseFloat(document.getElementById('inputDp').value) || 0;
        let piutang = globalTotal - dp;
        if(piutang < 0) piutang = 0;
        
        document.getElementById('totalTagihan').innerText = 'Rp ' + globalTotal.toLocaleString('id-ID');
        document.getElementById('sisaPiutang').innerText = 'Rp ' + piutang.toLocaleString('id-ID');
    }

    function renderCart() {
        const container = document.getElementById('cartContainer');
        const cartDataInput = document.getElementById('cartDataInput');
        const btnCheckout = document.getElementById('btnCheckout');
        
        let total = 0;
        let html = '';

        if (cart.length === 0) {
            container.innerHTML = `<div class="h-32 lg:h-full flex flex-col items-center justify-center text-gray-300 space-y-3"><i class="fa-solid fa-basket-shopping text-3xl sm:text-4xl opacity-50"></i><p class="text-xs sm:text-sm font-medium">Keranjang masih kosong</p></div>`;
            btnCheckout.disabled = true;
            cartDataInput.value = '';
            document.getElementById('cartCount').innerText = '0 Item';
            document.getElementById('totalTagihan').innerText = 'Rp 0';
            document.getElementById('sisaPiutang').innerText = 'Rp 0';
            globalTotal = 0;
            return;
        }

        cart.forEach(item => {
            let subtotal = item.harga_final * item.jumlah;
            total += subtotal;
            
            let badgeDiskon = item.diskon_item > 0 ? `<span class="bg-red-100 text-red-600 px-1 py-0.5 rounded text-[8px] sm:text-[9px] font-bold uppercase ml-1 relative -top-0.5">Disc</span>` : '';
            let textHargaAsli = item.diskon_item > 0 ? `<span class="text-[9px] sm:text-[10px] text-gray-400 line-through mr-1 block sm:inline">Rp ${item.harga_jual.toLocaleString('id-ID')}</span>` : '';
            
            // [PERBAIKAN] Flexbox Auto-Wrapping untuk layar sempit (HP)
            html += `
            <div class="p-2 sm:p-3 bg-white border border-gray-200 rounded-xl mb-2 shadow-[0_2px_4px_rgba(0,0,0,0.02)] flex flex-col gap-1.5">
                <div class="flex justify-between items-start">
                    <p class="text-xs sm:text-sm font-bold text-[#4b465c] leading-tight flex-1 pr-2">${item.nama_barang} ${badgeDiskon}</p>
                    <p class="text-xs sm:text-sm font-bold text-[#7367f0] whitespace-nowrap">Rp ${subtotal.toLocaleString('id-ID')}</p>
                </div>
                
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mt-1 pt-1.5 border-t border-gray-50 gap-2 sm:gap-0">
                    <div class="text-[10px] sm:text-[11px] text-gray-500 font-medium">
                        ${textHargaAsli}<span class="text-gray-700">Rp ${item.harga_final.toLocaleString('id-ID')}</span> / item
                    </div>
                    
                    <div class="flex items-center justify-between sm:justify-end w-full sm:w-auto gap-2">
                        <div class="flex items-center border border-red-200 bg-red-50 rounded-lg p-0.5 shadow-sm">
                            <span class="text-[9px] sm:text-[10px] text-red-500 font-bold px-1.5">Disc %</span>
                            <input type="number" min="0" max="100" value="${item.persen_diskon}" 
                                   onchange="setDiskonPersen(${item.id}, this.value)" 
                                   class="w-10 sm:w-12 text-xs font-bold text-center bg-white border border-red-200 text-red-600 rounded-md focus:outline-none focus:ring-1 focus:ring-red-500 h-6 sm:h-7 m-0 p-0 shadow-inner">
                        </div>
                        
                        <div class="flex items-center gap-1 bg-gray-50 rounded-lg border border-gray-200 p-0.5 shadow-sm">
                            <button type="button" onclick="ubahQty(${item.id}, -1)" class="w-6 h-6 sm:w-7 sm:h-7 flex items-center justify-center text-red-500 hover:bg-red-100 rounded-md transition-colors"><i class="fa-solid fa-minus text-[10px]"></i></button>
                            
                            <input type="number" min="1" max="${item.max_stok}" value="${item.jumlah}" 
                                   onchange="setQtyManual(${item.id}, this.value)"
                                   class="w-10 sm:w-12 text-xs font-bold text-center border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-[#7367f0] bg-white h-6 sm:h-7 m-0 p-0 shadow-inner">
                            
                            <button type="button" onclick="ubahQty(${item.id}, 1)" class="w-6 h-6 sm:w-7 sm:h-7 flex items-center justify-center text-emerald-500 hover:bg-emerald-100 rounded-md transition-colors"><i class="fa-solid fa-plus text-[10px]"></i></button>
                        </div>
                    </div>
                </div>
            </div>`;
        });
        
        container.innerHTML = html;
        cartDataInput.value = JSON.stringify(cart);
        btnCheckout.disabled = false;
        
        globalTotal = total;
        document.getElementById('cartCount').innerText = cart.length + ' Item';
        hitungPiutang();
    }
</script>
@endsection