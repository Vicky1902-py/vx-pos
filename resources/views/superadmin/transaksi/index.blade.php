@extends('layouts.admin')

@section('title', 'Riwayat Transaksi Penjualan')

@section('content')
<div class="mb-6 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
    <div>
        <h3 class="font-bold text-[#4b465c] text-2xl">Riwayat Transaksi</h3>
        <p class="text-[#a8aaae] text-sm">Rekapitulasi dan manajemen data penjualan (Invoice)</p>
    </div>
    <div class="flex gap-3">
        <a href="{{ route('superadmin.transaksi.create') }}" class="bg-[#7367f0] hover:bg-[#6355e6] text-white font-medium py-2.5 px-4 rounded-xl text-sm shadow-[0_2px_6px_rgba(115,103,240,0.4)] transition-all flex items-center gap-2">
            <i class="fa-solid fa-plus text-base"></i> Transaksi Baru
        </a>
    </div>
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
    <div class="p-5 border-b border-gray-100 flex justify-between items-center">
        <h5 class="font-semibold text-[#4b465c] text-lg">Daftar Invoice</h5>
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
                    <th class="py-4 px-6">Tanggal & Invoice</th>
                    <th class="py-4 px-6">Pelanggan & Sales</th>
                    <th class="py-4 px-6">Piutang / Tagihan</th>
                    <th class="py-4 px-6 text-center">Status</th>
                    <th class="py-4 px-6 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 text-[#4b465c] text-[15px]">
                @forelse($transaksi as $item)
                <tr class="hover:bg-slate-50/50 transition-colors">
                    <td class="py-4 px-6">
                        <p class="font-bold text-[#7367f0]">{{ $item->no_invoice }}</p>
                        <p class="text-xs text-[#a8aaae]">{{ \Carbon\Carbon::parse($item->created_at)->translatedFormat('d M Y, H:i') }}</p>
                    </td>
                    <td class="py-4 px-6">
                        <p class="font-bold text-gray-700">{{ strtoupper($item->nama_pelanggan ?? 'UMUM') }}</p>
                        <p class="text-xs text-[#a8aaae]"><i class="fa-solid fa-user-tie"></i> {{ explode(' ', $item->nama_sales)[0] ?? 'Online' }}</p>
                    </td>
                    <td class="py-4 px-6">
                        <p class="font-bold text-gray-700">Rp {{ number_format($item->total_transaksi, 0, ',', '.') }}</p>
                        @if($item->piutang > 0)
                            <p class="text-xs font-bold text-red-500">Sisa: Rp {{ number_format($item->piutang, 0, ',', '.') }}</p>
                        @endif
                    </td>
                    <td class="py-4 px-6 text-center">
                        @if($item->piutang > 0)
                            <span class="bg-red-100 text-red-600 px-3 py-1 rounded-md text-xs font-bold uppercase">PIUTANG</span>
                        @else
                            <span class="bg-emerald-100 text-emerald-600 px-3 py-1 rounded-md text-xs font-bold uppercase">LUNAS</span>
                        @endif
                    </td>
                    <td class="py-4 px-6 text-center">
                        <div class="flex items-center justify-center gap-3">
                            <!-- [BARU] Tombol Bayar Cicilan (Hanya muncul jika ada piutang) -->
                            @if($item->piutang > 0)
                            <button type="button" onclick="openCicilanModal({{ $item->id }}, '{{ $item->no_invoice }}', {{ $item->piutang }})" class="text-emerald-500 hover:text-emerald-600 transition-colors" title="Bayar Cicilan Piutang">
                                <i class="fa-solid fa-wallet text-lg"></i>
                            </button>
                            @endif
                            
                            <!-- Tombol Cetak Nota -->
                            <a target="_blank" href="{{ route('superadmin.transaksi.print', $item->id) }}" class="text-[#00cfe8] hover:text-[#00a8bd] transition-colors" title="Cetak Nota / Surat Jalan">
                                <i class="fa-solid fa-print text-lg"></i>
                            </a>
                            <!-- Tombol Hapus -->
                            <form action="{{ route('superadmin.transaksi.destroy', $item->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus transaksi ini? Stok tidak akan dikembalikan secara otomatis.');" class="inline">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-red-400 hover:text-red-500 transition-colors" title="Hapus Transaksi"><i class="fa-regular fa-trash-can text-lg"></i></button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="py-8 text-center text-gray-400">Belum ada transaksi yang tercatat.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="p-4 border-t border-gray-100">
        {{ $transaksi->appends(request()->query())->links() }}
    </div>
</div>

<!-- [BARU] Modal Popup Pembayaran Cicilan -->
<div id="cicilanModal" class="fixed inset-0 z-50 hidden flex items-center justify-center bg-gray-900/50 backdrop-blur-sm transition-opacity">
    <div class="bg-white rounded-xl shadow-xl w-full max-w-md mx-4 overflow-hidden">
        <div class="p-4 border-b border-gray-100 flex justify-between items-center bg-gray-50">
            <h4 class="font-bold text-gray-700">Pembayaran Cicilan Piutang</h4>
            <button type="button" onclick="closeCicilanModal()" class="text-gray-400 hover:text-red-500 transition-colors"><i class="fa-solid fa-xmark text-lg"></i></button>
        </div>
        <form id="formCicilan" method="POST" action="">
            @csrf
            <div class="p-5">
                <div class="bg-gray-50 p-3 rounded-lg mb-4 border border-gray-100">
                    <p class="text-xs text-gray-500 mb-1">No. Invoice:</p>
                    <p id="modalInvoice" class="font-bold text-[#7367f0] mb-2"></p>
                    <p class="text-xs text-gray-500 mb-1">Sisa Piutang Saat Ini:</p>
                    <p id="modalPiutang" class="font-bold text-red-500 text-lg"></p>
                </div>

                <label class="block text-sm font-bold text-gray-600 mb-2">Nominal Pembayaran (Rp)</label>
                <input type="number" name="nominal_cicilan" id="inputNominal" required min="1" class="w-full border border-gray-300 rounded-lg p-3 text-sm focus:ring-[#7367f0] focus:border-[#7367f0] outline-none" placeholder="Masukkan jumlah uang...">
                <p class="text-[11px] text-gray-400 mt-2">*Sistem akan otomatis mengubah status menjadi LUNAS jika pembayaran memenuhi sisa tagihan.</p>
            </div>
            <div class="p-4 border-t border-gray-100 flex justify-end gap-2 bg-gray-50">
                <button type="button" onclick="closeCicilanModal()" class="px-4 py-2.5 bg-white border border-gray-300 text-gray-700 rounded-lg text-sm font-medium hover:bg-gray-50 transition-colors">Batal</button>
                <button type="submit" class="px-4 py-2.5 bg-emerald-500 text-white rounded-lg text-sm font-bold hover:bg-emerald-600 transition-colors shadow-md">Simpan Pembayaran</button>
            </div>
        </form>
    </div>
</div>

<!-- [BARU] Script Pengontrol Modal -->
<script>
    function openCicilanModal(id, invoice, piutang) {
        document.getElementById('cicilanModal').classList.remove('hidden');
        document.getElementById('modalInvoice').innerText = invoice;
        document.getElementById('modalPiutang').innerText = 'Rp ' + piutang.toLocaleString('id-ID');
        
        const inputNominal = document.getElementById('inputNominal');
        inputNominal.value = '';
        
        // Memasukkan rute dinamis ke dalam action form
        document.getElementById('formCicilan').action = `/superadmin/transaksi/${id}/cicilan`; 
    }

    function closeCicilanModal() {
        document.getElementById('cicilanModal').classList.add('hidden');
    }
</script>
@endsection