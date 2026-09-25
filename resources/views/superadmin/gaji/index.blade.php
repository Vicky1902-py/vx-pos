@extends('layouts.admin')

@section('title', 'Manajemen Penggajian')

@section('content')
<div class="mb-6 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
    <div>
        <h3 class="font-bold text-[#4b465c] text-2xl">Penggajian Karyawan</h3>
        <p class="text-[#a8aaae] text-sm">Proses gaji bulanan, potongan, dan cetak slip gaji</p>
    </div>
</div>

@if(session('success'))
<div class="bg-emerald-100 text-emerald-700 p-4 rounded-xl mb-6 shadow-sm flex items-center gap-3">
    <i class="fa-solid fa-circle-check text-lg"></i>
    <span class="font-medium text-sm">{{ session('success') }}</span>
</div>
@endif

<div class="bg-white p-5 rounded-xl shadow-[0_4px_18px_0_rgba(75,70,92,0.1)] mb-6">
    <form action="{{ route('superadmin.gaji.index') }}" method="GET" class="flex flex-col sm:flex-row gap-4 items-end">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Pilih Bulan</label>
            <select name="bulan" class="w-full sm:w-48 p-2.5 border border-gray-300 rounded-lg text-sm focus:ring-[#7367f0] focus:border-[#7367f0]">
                @for($i=1; $i<=12; $i++)
                    @php $m = str_pad($i, 2, '0', STR_PAD_LEFT); @endphp
                    <option value="{{ $m }}" {{ $bulan == $m ? 'selected' : '' }}>{{ date('F', mktime(0, 0, 0, $i, 10)) }}</option>
                @endfor
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Pilih Tahun</label>
            <select name="tahun" class="w-full sm:w-32 p-2.5 border border-gray-300 rounded-lg text-sm focus:ring-[#7367f0] focus:border-[#7367f0]">
                @for($t=date('Y'); $t>=date('Y')-3; $t--)
                    <option value="{{ $t }}" {{ $tahun == $t ? 'selected' : '' }}>{{ $t }}</option>
                @endfor
            </select>
        </div>
        <button type="submit" class="bg-[#7367f0] hover:bg-[#6355e6] text-white font-medium py-2.5 px-6 rounded-lg transition-all text-sm shadow-md">
            Tampilkan Data
        </button>
    </form>
</div>

<div class="bg-white rounded-xl shadow-[0_4px_18px_0_rgba(75,70,92,0.1)] overflow-hidden mb-8">
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-[#f8f7fa] text-[#4b465c] uppercase text-xs font-semibold tracking-wider border-b border-gray-100">
                    <th class="py-4 px-6">Nama Karyawan</th>
                    <th class="py-4 px-6">Posisi / Role</th>
                    <th class="py-4 px-6 text-center">Status Gaji (Bulan {{ $bulan }}/{{ $tahun }})</th>
                    <th class="py-4 px-6 text-center">Aksi / Cetak</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 text-[#4b465c] text-[15px]">
                @foreach($karyawan as $k)
                <tr class="hover:bg-slate-50/50 transition-colors">
                    <td class="py-4 px-6 font-bold text-gray-800">{{ strtoupper($k->nama) }}</td>
                    <td class="py-4 px-6">
                        <span class="bg-indigo-50 text-indigo-600 px-2.5 py-1 rounded-md text-[11px] font-bold uppercase">{{ $k->role }}</span>
                    </td>
                    <td class="py-4 px-6 text-center">
                        @if($k->status_gaji == 'dibayar')
                            <span class="bg-emerald-100 text-emerald-600 px-3 py-1.5 rounded-lg text-xs font-bold uppercase"><i class="fa-solid fa-check mr-1"></i> Sudah Dibayar</span>
                            <div class="text-[11px] text-gray-400 mt-1">Rp {{ number_format($k->data_gaji->total_gaji, 0, ',', '.') }}</div>
                        @else
                            <span class="bg-orange-100 text-orange-600 px-3 py-1.5 rounded-lg text-xs font-bold uppercase"><i class="fa-solid fa-clock mr-1"></i> Menunggu Proses</span>
                        @endif
                    </td>
                    <td class="py-4 px-6 text-center">
                        @if($k->status_gaji == 'dibayar')
                            <a href="{{ route('superadmin.gaji.cetak', $k->data_gaji->id) }}" target="_blank" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-3 py-1.5 rounded-lg text-sm font-semibold transition-all inline-flex items-center gap-1">
                                <i class="fa-solid fa-print"></i> Slip
                            </a>
                            <button onclick="openGajiModal({{ $k->id }}, '{{ $k->nama }}', {{ json_encode($k->data_gaji) }}, 0)" class="text-[#7367f0] hover:text-indigo-800 ml-2 text-sm underline font-medium">Revisi</button>
                        @else
                            <button onclick="openGajiModal({{ $k->id }}, '{{ $k->nama }}', null, {{ $k->estimasi_bonus }})" class="bg-[#7367f0] hover:bg-[#6355e6] text-white px-3 py-1.5 rounded-lg text-sm font-semibold shadow-sm transition-all inline-flex items-center gap-1">
                                <i class="fa-solid fa-calculator"></i> Proses Gaji
                            </button>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Input Gaji -->
<div id="modalGaji" class="fixed inset-0 z-50 overflow-y-auto hidden bg-black/40 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-lg w-full shadow-xl overflow-hidden transform transition-all">
        <div class="px-6 py-4 bg-[#f8f7fa] border-b border-gray-100 flex justify-between items-center">
            <h3 class="font-bold text-[#4b465c] text-lg">Proses Slip Gaji</h3>
            <button type="button" onclick="toggleModal('modalGaji')" class="text-gray-400 hover:text-gray-600 text-xl"><i class="fa-solid fa-xmark"></i></button>
        </div>
        
        <form action="{{ route('superadmin.gaji.store') }}" method="POST" class="p-6 space-y-4 max-h-[75vh] overflow-y-auto">
            @csrf
            <input type="hidden" name="user_id" id="input_user_id">
            <input type="hidden" name="periode_bulan" value="{{ $bulan }}">
            <input type="hidden" name="periode_tahun" value="{{ $tahun }}">
            
            <div class="bg-indigo-50 border border-indigo-100 rounded-lg p-3 text-sm text-center">
                <p class="text-indigo-400 font-bold uppercase text-[10px] tracking-wider mb-1">Periode: {{ $bulan }}/{{ $tahun }}</p>
                <p id="display_nama" class="font-bold text-indigo-700 text-lg uppercase">-</p>
            </div>

            <!-- Pemasukan -->
            <div class="space-y-3">
                <h6 class="text-xs font-bold text-gray-500 uppercase tracking-wider border-b pb-1">Pendapatan</h6>
                <div>
                    <label class="block text-sm font-semibold text-gray-600 mb-1">Gaji Pokok (Rp)</label>
                    <input type="number" id="input_gapok" name="gaji_pokok" oninput="kalkulasiGaji()" required min="0" class="w-full px-3 py-2 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500 font-bold">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-600 mb-1">Tunjangan / Lain-lain (Rp)</label>
                    <input type="number" id="input_tunjangan" name="tunjangan" oninput="kalkulasiGaji()" value="0" min="0" class="w-full px-3 py-2 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-600 mb-1">Bonus Bulan Ini (Otomatis) (Rp)</label>
                    <input type="number" id="input_bonus" name="bonus" readonly class="w-full px-3 py-2 border border-emerald-200 bg-emerald-50 rounded-lg text-emerald-700 font-bold">
                    <p class="text-[10px] text-gray-400 mt-1">Ditarik otomatis dari riwayat pencairan bonus periode ini.</p>
                </div>
            </div>

            <!-- Potongan -->
            <div class="space-y-3 pt-2">
                <h6 class="text-xs font-bold text-red-500 uppercase tracking-wider border-b border-red-100 pb-1">Potongan</h6>
                <div class="flex gap-3">
                    <div class="w-1/3">
                        <label class="block text-sm font-semibold text-gray-600 mb-1">Nominal (Rp)</label>
                        <input type="number" id="input_potongan" name="potongan" oninput="kalkulasiGaji()" value="0" min="0" class="w-full px-3 py-2 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-400 font-bold text-red-500">
                    </div>
                    <div class="w-2/3">
                        <label class="block text-sm font-semibold text-gray-600 mb-1">Keterangan Potongan</label>
                        <input type="text" id="input_ket_potongan" name="keterangan_potongan" placeholder="Misal: Kasbon / Terlambat" class="w-full px-3 py-2 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-400">
                    </div>
                </div>
            </div>

            <!-- Grand Total -->
            <div class="mt-4 p-4 bg-gray-50 rounded-xl border border-gray-200 flex justify-between items-center">
                <span class="font-bold text-gray-600">Gaji Bersih / Take Home Pay</span>
                <span id="display_netto" class="font-bold text-2xl text-[#7367f0]">Rp 0</span>
            </div>

            <div class="pt-3 pb-1 flex justify-end gap-3 sticky -bottom-6 bg-white z-10">
                <button type="button" onclick="toggleModal('modalGaji')" class="px-4 py-2 border border-gray-200 rounded-lg text-gray-600 hover:bg-gray-50 text-sm font-medium">Batal</button>
                <button type="submit" class="px-4 py-2 bg-[#7367f0] hover:bg-[#6355e6] text-white rounded-lg text-sm font-medium shadow-md">Simpan & Terbitkan Slip</button>
            </div>
        </form>
    </div>
</div>

<script>
    function toggleModal(modalId) {
        document.getElementById(modalId).classList.toggle('hidden');
    }

    function openGajiModal(id, nama, dataGaji, estimasiBonus) {
        document.getElementById('input_user_id').value = id;
        document.getElementById('display_nama').innerText = nama;
        
        if (dataGaji) {
            // Mode Revisi (Data Sudah Ada)
            document.getElementById('input_gapok').value = dataGaji.gaji_pokok;
            document.getElementById('input_tunjangan').value = dataGaji.tunjangan;
            document.getElementById('input_bonus').value = dataGaji.bonus;
            document.getElementById('input_potongan').value = dataGaji.potongan;
            document.getElementById('input_ket_potongan').value = dataGaji.keterangan_potongan;
        } else {
            // Mode Baru
            document.getElementById('input_gapok').value = '';
            document.getElementById('input_tunjangan').value = '0';
            document.getElementById('input_bonus').value = estimasiBonus || 0;
            document.getElementById('input_potongan').value = '0';
            document.getElementById('input_ket_potongan').value = '';
        }
        
        kalkulasiGaji();
        toggleModal('modalGaji');
    }

    function kalkulasiGaji() {
        let gapok = parseFloat(document.getElementById('input_gapok').value) || 0;
        let tunjangan = parseFloat(document.getElementById('input_tunjangan').value) || 0;
        let bonus = parseFloat(document.getElementById('input_bonus').value) || 0;
        let potongan = parseFloat(document.getElementById('input_potongan').value) || 0;
        
        let netto = (gapok + tunjangan + bonus) - potongan;
        if(netto < 0) netto = 0;
        
        document.getElementById('display_netto').innerText = 'Rp ' + netto.toLocaleString('id-ID');
    }
</script>
@endsection