@extends('layouts.admin')

@section('title', 'Manajemen Bonus & Pencairan')

@section('content')
<div class="mb-6 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
    <div>
        <h3 class="font-bold text-[#4b465c] text-2xl">Manajemen Bonus Sales</h3>
        <p class="text-[#a8aaae] text-sm">Pantau performa lunas dan kelola pencairan bonus karyawan</p>
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

<!-- Tabel Rekap Performa Sales -->
<div class="bg-white rounded-xl shadow-[0_4px_18px_0_rgba(75,70,92,0.1)] overflow-hidden mb-8">
    <div class="p-5 border-b border-gray-100">
        <h5 class="font-semibold text-[#4b465c] text-lg">Kalkulasi Performa Sales (Hanya Transaksi Lunas)</h5>
        <p class="text-xs text-gray-400 mt-1">Sistem otomatis menghitung total penjualan lunas untuk mencegah bonus dari piutang bodong.</p>
    </div>
    
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-[#f8f7fa] text-[#4b465c] uppercase text-xs font-semibold tracking-wider border-b border-gray-100">
                    <th class="py-4 px-6">Nama Sales</th>
                    <th class="py-4 px-6 text-right">Tot. Omzet Lunas</th>
                    <th class="py-4 px-6 text-right">Tot. Laba / Profit</th>
                    <th class="py-4 px-6 text-right text-emerald-600">Bonus Terbayar</th>
                    <th class="py-4 px-6 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 text-[#4b465c] text-[15px]">
                @forelse($salesList as $s)
                <tr class="hover:bg-slate-50/50 transition-colors">
                    <td class="py-4 px-6 font-bold text-[#7367f0]">{{ strtoupper($s->nama) }}</td>
                    <td class="py-4 px-6 text-right font-medium">Rp {{ number_format($s->omzet_lunas, 0, ',', '.') }}</td>
                    <td class="py-4 px-6 text-right font-bold text-cyan-600">Rp {{ number_format($s->laba_lunas, 0, ',', '.') }}</td>
                    <td class="py-4 px-6 text-right font-bold text-emerald-600">Rp {{ number_format($s->bonus_cair, 0, ',', '.') }}</td>
                    <td class="py-4 px-6 text-center">
                        <button onclick="openBonusModal({{ $s->id }}, '{{ $s->nama }}', {{ $s->laba_lunas }}, {{ $s->omzet_lunas }})" class="bg-[#7367f0] hover:bg-[#6355e6] text-white px-3 py-1.5 rounded-lg text-sm font-semibold shadow-sm transition-all">
                            <i class="fa-solid fa-hand-holding-dollar mr-1"></i> Cairkan Bonus
                        </button>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="text-center py-6 text-gray-400">Belum ada data sales atau transaksi.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Tabel Riwayat Pencairan -->
<div class="bg-white rounded-xl shadow-[0_4px_18px_0_rgba(75,70,92,0.1)] overflow-hidden">
    <div class="p-5 border-b border-gray-100 flex justify-between items-center">
        <h5 class="font-semibold text-[#4b465c] text-lg">Riwayat Pencairan Terakhir</h5>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-gray-50 border-b border-gray-100 text-xs uppercase text-gray-500">
                    <th class="p-4">Waktu Pencairan</th>
                    <th class="p-4">Nama Sales</th>
                    <th class="p-4">Keterangan / Opsi</th>
                    <th class="p-4 text-right">Nominal (Rp)</th>
                    <th class="p-4 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="text-sm text-[#4b465c]">
                @forelse($riwayatBonus as $riwayat)
                <tr class="border-b border-gray-50 hover:bg-gray-50/50">
                    <td class="p-4">{{ \Carbon\Carbon::parse($riwayat->created_at)->format('d M Y - H:i') }}</td>
                    <td class="p-4 font-bold">{{ strtoupper($riwayat->nama_sales ?? 'Terhapus') }}</td>
                    <td class="p-4">{{ $riwayat->keterangan }}</td>
                    <td class="p-4 text-right font-bold text-emerald-600">Rp {{ number_format($riwayat->total_bonus, 0, ',', '.') }}</td>
                    <td class="p-4 text-center">
                        <form action="{{ route('superadmin.bonus.destroy', $riwayat->id) }}" method="POST" onsubmit="return confirm('Batalkan riwayat pencairan ini?');">
                            @csrf @method('DELETE')
                            <button type="submit" class="text-red-400 hover:text-red-600"><i class="fa-solid fa-trash-can"></i></button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="p-6 text-center text-gray-400">Belum ada riwayat pencairan.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="p-4 border-t border-gray-100">
        {{ $riwayatBonus->links() }}
    </div>
</div>

<!-- Modal Pencairan Cerdas (SUDAH DIPERBAIKI TATA LETAKNYA) -->
<div id="modalBonus" class="fixed inset-0 z-50 overflow-y-auto hidden bg-black/40 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-lg w-full shadow-xl overflow-hidden transform transition-all">
        <div class="px-6 py-4 bg-[#f8f7fa] border-b border-gray-100 flex justify-between items-center">
            <h3 class="font-bold text-[#4b465c] text-lg">Form Pencairan Bonus</h3>
            <button type="button" onclick="toggleModal('modalBonus')" class="text-gray-400 hover:text-gray-600 text-xl"><i class="fa-solid fa-xmark"></i></button>
        </div>
        
        <!-- Penambahan max-h-[75vh] dan overflow-y-auto agar form bisa di-scroll jika kepanjangan -->
        <form action="{{ route('superadmin.bonus.store') }}" method="POST" class="p-6 space-y-5 max-h-[75vh] overflow-y-auto">
            @csrf
            <input type="hidden" name="sales_id" id="input_sales_id">
            
            <div class="bg-indigo-50 border border-indigo-100 rounded-lg p-3 text-sm">
                <p class="text-indigo-400 font-bold uppercase text-[10px] tracking-wider mb-1">Penerima Bonus</p>
                <p id="display_nama_sales" class="font-bold text-indigo-700 text-base mb-2">-</p>
                <div class="flex justify-between border-t border-indigo-100 pt-2 mt-1">
                    <span class="text-gray-500 text-xs">Total Profit: <strong id="display_laba" class="text-cyan-600">Rp 0</strong></span>
                    <span class="text-gray-500 text-xs">Total Omzet: <strong id="display_omzet" class="text-gray-700">Rp 0</strong></span>
                </div>
            </div>

            <!-- Kalkulator Cerdas -->
            <div>
                <label class="block text-sm font-bold text-gray-600 mb-2">Pilih Skema Perhitungan:</label>
                <div class="space-y-2">
                    <label class="flex items-center p-3 border border-gray-200 rounded-lg cursor-pointer hover:bg-gray-50">
                        <input type="radio" name="skema" value="opsi_a" onchange="kalkulasiOtomatis()" class="w-4 h-4 text-[#7367f0] focus:ring-[#7367f0] border-gray-300">
                        <span class="ml-3 text-sm font-medium text-gray-700">Opsi A (10% dari Profit/Laba) <span class="bg-emerald-100 text-emerald-600 text-[10px] px-2 py-0.5 rounded ml-1 font-bold">Rekomendasi</span></span>
                    </label>
                    <label class="flex items-center p-3 border border-gray-200 rounded-lg cursor-pointer hover:bg-gray-50">
                        <input type="radio" name="skema" value="opsi_b" onchange="kalkulasiOtomatis()" class="w-4 h-4 text-[#7367f0] focus:ring-[#7367f0] border-gray-300">
                        <span class="ml-3 text-sm font-medium text-gray-700">Opsi B (2% dari Total Omzet)</span>
                    </label>
                    <label class="flex items-center p-3 border border-gray-200 rounded-lg cursor-pointer hover:bg-gray-50">
                        <input type="radio" name="skema" value="opsi_bebas" onchange="kalkulasiOtomatis()" checked class="w-4 h-4 text-[#7367f0] focus:ring-[#7367f0] border-gray-300">
                        <span class="ml-3 text-sm font-medium text-gray-700">Opsi Bebas (Tentukan Sendiri)</span>
                    </label>
                </div>
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-600 mb-1">Nominal Bonus (Rp)</label>
                <input type="number" name="total_bonus" id="input_nominal" required min="1" class="w-full px-3 py-2.5 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#7367f0] text-lg font-bold text-emerald-600">
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-600 mb-1">Keterangan / Memo</label>
                <input type="text" name="keterangan" id="input_keterangan" required placeholder="Contoh: Pencairan Bonus Bulan Juli" class="w-full px-3 py-2 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#7367f0] text-sm">
            </div>

            <!-- Penambahan sticky dan bg-white agar tombol selalu melayang di posisi bawah form -->
            <div class="pt-3 pb-1 flex justify-end gap-3 border-t border-gray-100 sticky -bottom-6 bg-white z-10">
                <button type="button" onclick="toggleModal('modalBonus')" class="px-4 py-2 border border-gray-200 rounded-lg text-gray-600 hover:bg-gray-50 text-sm font-medium">Batal</button>
                <button type="submit" class="px-4 py-2 bg-[#7367f0] hover:bg-[#6355e6] text-white rounded-lg text-sm font-medium shadow-md">Simpan & Cairkan</button>
            </div>
        </form>
    </div>
</div>

<script>
    // Variabel penampung data kalkulasi realtime
    let currentLaba = 0;
    let currentOmzet = 0;

    function toggleModal(modalId) {
        document.getElementById(modalId).classList.toggle('hidden');
    }

    function openBonusModal(id, nama, laba, omzet) {
        document.getElementById('input_sales_id').value = id;
        document.getElementById('display_nama_sales').innerText = nama.toUpperCase();
        document.getElementById('display_laba').innerText = 'Rp ' + laba.toLocaleString('id-ID');
        document.getElementById('display_omzet').innerText = 'Rp ' + omzet.toLocaleString('id-ID');
        
        currentLaba = laba;
        currentOmzet = omzet;
        
        // Reset form ke opsi bebas
        document.querySelector('input[name="skema"][value="opsi_bebas"]').checked = true;
        document.getElementById('input_nominal').value = '';
        document.getElementById('input_nominal').readOnly = false;
        document.getElementById('input_keterangan').value = 'Pencairan Bonus Khusus';

        toggleModal('modalBonus');
    }

    function kalkulasiOtomatis() {
        const skema = document.querySelector('input[name="skema"]:checked').value;
        const inputNominal = document.getElementById('input_nominal');
        const inputKet = document.getElementById('input_keterangan');

        if (skema === 'opsi_a') {
            // Opsi A: 10% dari Profit (0.10)
            const bonusA = Math.floor(currentLaba * 0.10);
            inputNominal.value = bonusA > 0 ? bonusA : 0;
            inputNominal.readOnly = false; 
            inputKet.value = 'Pencairan Opsi A (10% Profit)';
        } 
        else if (skema === 'opsi_b') {
            // Opsi B: 2% dari Omzet (0.02)
            const bonusB = Math.floor(currentOmzet * 0.02);
            inputNominal.value = bonusB > 0 ? bonusB : 0;
            inputNominal.readOnly = false; 
            inputKet.value = 'Pencairan Opsi B (2% Omzet)';
        } 
        else {
            // Opsi Bebas
            inputNominal.value = '';
            inputNominal.readOnly = false;
            inputKet.value = 'Pencairan Bonus Khusus';
        }
    }
</script>
@endsection