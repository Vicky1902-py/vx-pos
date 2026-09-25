@extends('layouts.admin')

@section('title', 'Laporan Penjualan & Keuangan')

@section('content')
<div class="mb-6">
    <h3 class="font-bold text-[#4b465c] text-2xl">Laporan Penjualan</h3>
    <p class="text-[#a8aaae] text-sm">Rekapitulasi transaksi, omzet, margin profit, dan piutang pelanggan</p>
</div>

<!-- Filter Box -->
<div class="bg-white p-5 rounded-xl shadow-[0_4px_18px_0_rgba(75,70,92,0.1)] mb-6">
    <form action="{{ route('superadmin.laporan.index') }}" method="GET" id="filterForm">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Dari Tanggal</label>
                <input type="date" name="start_date" value="{{ $startDate }}" class="w-full p-2.5 border border-gray-300 rounded-lg text-sm focus:ring-[#7367f0] focus:border-[#7367f0]">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Sampai Tanggal</label>
                <input type="date" name="end_date" value="{{ $endDate }}" class="w-full p-2.5 border border-gray-300 rounded-lg text-sm focus:ring-[#7367f0] focus:border-[#7367f0]">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Status Pembayaran</label>
                <select name="status" class="w-full p-2.5 border border-gray-300 rounded-lg text-sm focus:ring-[#7367f0] focus:border-[#7367f0]">
                    <option value="semua" {{ $status == 'semua' ? 'selected' : '' }}>Semua Transaksi</option>
                    <option value="lunas" {{ $status == 'lunas' ? 'selected' : '' }}>Lunas / Selesai</option>
                    <option value="piutang" {{ $status == 'piutang' ? 'selected' : '' }}>Belum Lunas (Piutang)</option>
                </select>
            </div>
            <div class="flex gap-2">
                <button type="submit" class="w-full bg-[#7367f0] hover:bg-[#6355e6] text-white font-medium py-2.5 rounded-lg transition-all text-sm shadow-md">
                    <i class="fa-solid fa-filter mr-1"></i> Filter
                </button>
                <button type="button" onclick="exportToExcel()" class="w-full bg-[#28c76f] hover:bg-[#23af61] text-white font-medium py-2.5 rounded-lg transition-all text-sm shadow-md">
                    <i class="fa-solid fa-file-excel mr-1"></i> Excel
                </button>
            </div>
        </div>
    </form>
</div>

<!-- [BARU] Summary Cards 4 Kolom (Keuangan Super Lengkap) -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <div class="bg-gradient-to-r from-indigo-500 to-purple-600 rounded-xl p-5 shadow-lg text-white">
        <p class="text-indigo-100 font-bold text-xs mb-1 uppercase tracking-wider">Omzet / Kotor</p>
        <h3 class="text-2xl font-bold">Rp {{ number_format($totalOmzet, 0, ',', '.') }}</h3>
    </div>
    <div class="bg-gradient-to-r from-cyan-500 to-blue-500 rounded-xl p-5 shadow-lg text-white">
        <p class="text-cyan-100 font-bold text-xs mb-1 uppercase tracking-wider">Estimasi Profit Laba</p>
        <h3 class="text-2xl font-bold">Rp {{ number_format($totalLaba, 0, ',', '.') }}</h3>
    </div>
    <div class="bg-gradient-to-r from-emerald-500 to-teal-500 rounded-xl p-5 shadow-lg text-white">
        <p class="text-emerald-100 font-bold text-xs mb-1 uppercase tracking-wider">Kas Riil Diterima</p>
        <h3 class="text-2xl font-bold">Rp {{ number_format($totalKas, 0, ',', '.') }}</h3>
    </div>
    <div class="bg-gradient-to-r from-red-500 to-orange-500 rounded-xl p-5 shadow-lg text-white">
        <p class="text-red-100 font-bold text-xs mb-1 uppercase tracking-wider">Total Piutang</p>
        <h3 class="text-2xl font-bold">Rp {{ number_format($totalPiutang, 0, ',', '.') }}</h3>
    </div>
</div>

<!-- Table Data -->
<div class="bg-white rounded-xl shadow-[0_4px_18px_0_rgba(75,70,92,0.1)] overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-gray-50 border-b border-gray-100 text-xs uppercase text-gray-500">
                    <th class="p-4 font-semibold">Tgl / Invoice</th>
                    <th class="p-4 font-semibold">Pelanggan</th>
                    <th class="p-4 font-semibold">Sales</th>
                    <th class="p-4 font-semibold text-right">Tot. Transaksi</th>
                    <th class="p-4 font-semibold text-right text-cyan-600">Est. Laba</th>
                    <th class="p-4 font-semibold text-right text-emerald-600">Terbayar</th>
                    <th class="p-4 font-semibold text-right text-red-500">Sisa Piutang</th>
                    <th class="p-4 font-semibold text-center">Status</th>
                </tr>
            </thead>
            <tbody class="text-sm text-[#4b465c]">
                @forelse($laporan as $row)
                <tr class="border-b border-gray-50 hover:bg-gray-50/50 transition-colors">
                    <td class="p-4">
                        <div class="font-bold text-[#7367f0]">{{ $row->no_invoice }}</div>
                        <div class="text-xs text-gray-400 mt-1">{{ \Carbon\Carbon::parse($row->created_at)->format('d/m/Y H:i') }}</div>
                    </td>
                    <td class="p-4 font-medium">{{ strtoupper($row->nama_pelanggan ?? 'UMUM') }}</td>
                    <td class="p-4">{{ explode(' ', $row->nama_sales)[0] }}</td>
                    <td class="p-4 text-right font-bold">Rp {{ number_format($row->total_transaksi, 0, ',', '.') }}</td>
                    
                    <!-- [BARU] Kolom Laba -->
                    <td class="p-4 text-right font-bold text-cyan-600 bg-cyan-50/20">Rp {{ number_format($row->laba, 0, ',', '.') }}</td>
                    
                    <td class="p-4 text-right text-emerald-600 font-bold">Rp {{ number_format($row->dp, 0, ',', '.') }}</td>
                    <td class="p-4 text-right text-red-500 font-bold">Rp {{ number_format($row->piutang, 0, ',', '.') }}</td>
                    <td class="p-4 text-center">
                        @if($row->piutang > 0)
                            <span class="bg-red-100 text-red-600 px-2.5 py-1 rounded-md text-[11px] font-bold uppercase">Piutang</span>
                        @else
                            <span class="bg-emerald-100 text-emerald-600 px-2.5 py-1 rounded-md text-[11px] font-bold uppercase">Lunas</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="p-8 text-center text-gray-400 font-medium">Tidak ada data transaksi pada rentang filter ini.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<script>
    function exportToExcel() {
        const form = document.getElementById('filterForm');
        const url = new URL("{{ route('superadmin.laporan.export') }}");
        const formData = new FormData(form);
        
        for (const [key, value] of formData.entries()) {
            url.searchParams.append(key, value);
        }
        
        window.location.href = url.toString();
    }
</script>
@endsection