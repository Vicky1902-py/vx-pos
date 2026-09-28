@extends('layouts.admin')

@section('title', 'Pusat Laporan & Analitika Bisnis')

@section('content')
<div class="mb-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <h3 class="font-bold text-slate-800 text-2xl tracking-tight flex items-center gap-2">
                <i class="fa-solid fa-chart-pie text-indigo-600"></i>
                Pusat Laporan & Analitika Bisnis
            </h3>
            <p class="text-slate-400 text-sm mt-0.5">Rekapitulasi komprehensif keuangan, aset stok barang, serta penggajian & bonus toko</p>
        </div>
        <div class="flex items-center gap-2">
            <span class="text-xs font-bold text-slate-500 bg-white border border-slate-200 px-3 py-1.5 rounded-xl shadow-sm flex items-center gap-1.5">
                <i class="fa-solid fa-calendar-day text-indigo-500"></i>
                {{ date('d F Y') }}
            </span>
        </div>
    </div>
</div>

<!-- ======================================================== -->
<!-- 3 KARTU UTAMA HUB LAPORAN (INTERAKTIF & RESPONSIF)       -->
<!-- ======================================================== -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
    
    <!-- KARTU 1: LAPORAN KEUANGAN -->
    <a href="{{ route('superadmin.laporan.index', ['tab' => 'keuangan']) }}" 
       class="group relative overflow-hidden rounded-2xl p-5 border transition-all duration-300 block {{ $tab === 'keuangan' ? 'bg-gradient-to-br from-indigo-900 via-indigo-950 to-slate-900 border-indigo-500/50 shadow-xl shadow-indigo-950/20 ring-2 ring-indigo-500/30 text-white' : 'bg-white border-slate-200/80 hover:border-indigo-300 shadow-sm hover:shadow-md text-slate-700' }}">
        
        <div class="flex items-start justify-between mb-3">
            <div class="w-12 h-12 rounded-xl flex items-center justify-center text-xl transition-transform group-hover:scale-110 {{ $tab === 'keuangan' ? 'bg-gradient-to-tr from-indigo-500 to-purple-600 text-white shadow-lg shadow-indigo-500/30' : 'bg-indigo-50 text-indigo-600' }}">
                <i class="fa-solid fa-wallet"></i>
            </div>
            @if($tab === 'keuangan')
                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-indigo-500/30 border border-indigo-400/40 text-[10px] font-extrabold text-indigo-200 uppercase tracking-wider">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                    Aktif
                </span>
            @else
                <span class="text-xs text-slate-400 group-hover:text-indigo-600 font-bold transition-colors">
                    Buka <i class="fa-solid fa-arrow-right text-[10px] ml-0.5"></i>
                </span>
            @endif
        </div>

        <div>
            <h4 class="font-extrabold text-base tracking-tight {{ $tab === 'keuangan' ? 'text-white' : 'text-slate-900' }}">
                1. Laporan Keuangan
            </h4>
            <p class="text-xs mt-1 leading-relaxed {{ $tab === 'keuangan' ? 'text-indigo-200' : 'text-slate-400' }}">
                Omzet transaksi, estimasi laba bersih, penerimaan kas, dan piutang penjualan.
            </p>
        </div>

        <div class="mt-4 pt-3 border-t {{ $tab === 'keuangan' ? 'border-white/10' : 'border-slate-100' }} flex justify-between items-center text-xs">
            <span class="{{ $tab === 'keuangan' ? 'text-indigo-300' : 'text-slate-400' }} font-medium">Omzet Periode Ini</span>
            <span class="font-black {{ $tab === 'keuangan' ? 'text-white' : 'text-indigo-600' }}">
                Rp {{ number_format($totalOmzet, 0, ',', '.') }}
            </span>
        </div>
    </a>

    <!-- KARTU 2: LAPORAN STOK -->
    <a href="{{ route('superadmin.laporan.index', ['tab' => 'stok']) }}" 
       class="group relative overflow-hidden rounded-2xl p-5 border transition-all duration-300 block {{ $tab === 'stok' ? 'bg-gradient-to-br from-emerald-950 via-slate-900 to-emerald-950 border-emerald-500/50 shadow-xl shadow-emerald-950/20 ring-2 ring-emerald-500/30 text-white' : 'bg-white border-slate-200/80 hover:border-emerald-300 shadow-sm hover:shadow-md text-slate-700' }}">
        
        <div class="flex items-start justify-between mb-3">
            <div class="w-12 h-12 rounded-xl flex items-center justify-center text-xl transition-transform group-hover:scale-110 {{ $tab === 'stok' ? 'bg-gradient-to-tr from-emerald-500 to-teal-600 text-white shadow-lg shadow-emerald-500/30' : 'bg-emerald-50 text-emerald-600' }}">
                <i class="fa-solid fa-boxes-stacked"></i>
            </div>
            @if($tab === 'stok')
                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-emerald-500/30 border border-emerald-400/40 text-[10px] font-extrabold text-emerald-200 uppercase tracking-wider">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                    Aktif
                </span>
            @else
                <span class="text-xs text-slate-400 group-hover:text-emerald-600 font-bold transition-colors">
                    Buka <i class="fa-solid fa-arrow-right text-[10px] ml-0.5"></i>
                </span>
            @endif
        </div>

        <div>
            <h4 class="font-extrabold text-base tracking-tight {{ $tab === 'stok' ? 'text-white' : 'text-slate-900' }}">
                2. Laporan Stok & Aset
            </h4>
            <p class="text-xs mt-1 leading-relaxed {{ $tab === 'stok' ? 'text-emerald-200' : 'text-slate-400' }}">
                Total fisik persediaan barang, total nilai aset modal (HPP), dan peringatan stok kritis.
            </p>
        </div>

        <div class="mt-4 pt-3 border-t {{ $tab === 'stok' ? 'border-white/10' : 'border-slate-100' }} flex justify-between items-center text-xs">
            <span class="{{ $tab === 'stok' ? 'text-emerald-300' : 'text-slate-400' }} font-medium">Total Aset Modal</span>
            <span class="font-black {{ $tab === 'stok' ? 'text-white' : 'text-emerald-600' }}">
                Rp {{ number_format($totalNilaiModalStok, 0, ',', '.') }}
            </span>
        </div>
    </a>

    <!-- KARTU 3: LAPORAN PENGGAJIAN DAN BONUS -->
    <a href="{{ route('superadmin.laporan.index', ['tab' => 'gaji']) }}" 
       class="group relative overflow-hidden rounded-2xl p-5 border transition-all duration-300 block {{ $tab === 'gaji' ? 'bg-gradient-to-br from-purple-950 via-slate-900 to-pink-950 border-purple-500/50 shadow-xl shadow-purple-950/20 ring-2 ring-purple-500/30 text-white' : 'bg-white border-slate-200/80 hover:border-purple-300 shadow-sm hover:shadow-md text-slate-700' }}">
        
        <div class="flex items-start justify-between mb-3">
            <div class="w-12 h-12 rounded-xl flex items-center justify-center text-xl transition-transform group-hover:scale-110 {{ $tab === 'gaji' ? 'bg-gradient-to-tr from-purple-500 to-rose-600 text-white shadow-lg shadow-purple-500/30' : 'bg-purple-50 text-purple-600' }}">
                <i class="fa-solid fa-money-check-dollar"></i>
            </div>
            @if($tab === 'gaji')
                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-purple-500/30 border border-purple-400/40 text-[10px] font-extrabold text-purple-200 uppercase tracking-wider">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                    Aktif
                </span>
            @else
                <span class="text-xs text-slate-400 group-hover:text-purple-600 font-bold transition-colors">
                    Buka <i class="fa-solid fa-arrow-right text-[10px] ml-0.5"></i>
                </span>
            @endif
        </div>

        <div>
            <h4 class="font-extrabold text-base tracking-tight {{ $tab === 'gaji' ? 'text-white' : 'text-slate-900' }}">
                3. Laporan Gaji & Bonus
            </h4>
            <p class="text-xs mt-1 leading-relaxed {{ $tab === 'gaji' ? 'text-purple-200' : 'text-slate-400' }}">
                Rekapitulasi payroll bulanan, tunjangan, potongan, pencairan bonus, dan cetak slip gaji.
            </p>
        </div>

        <div class="mt-4 pt-3 border-t {{ $tab === 'gaji' ? 'border-white/10' : 'border-slate-100' }} flex justify-between items-center text-xs">
            <span class="{{ $tab === 'gaji' ? 'text-purple-300' : 'text-slate-400' }} font-medium">Beban Payroll Periode</span>
            <span class="font-black {{ $tab === 'gaji' ? 'text-white' : 'text-purple-600' }}">
                Rp {{ number_format($totalBebanPayroll, 0, ',', '.') }}
            </span>
        </div>
    </a>
</div>

<!-- ======================================================== -->
<!-- KONTEN RINCIAN SESUAI KARTU AKTIF                       -->
<!-- ======================================================== -->

@if($tab === 'keuangan')
    <!-- ---------------------------------------------------- -->
    <!-- SECTION 1: LAPORAN KEUANGAN & PENJUALAN              -->
    <!-- ---------------------------------------------------- -->

    <!-- Filter Box Keuangan -->
    <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm mb-6">
        <form action="{{ route('superadmin.laporan.index') }}" method="GET" id="filterFormKeuangan">
            <input type="hidden" name="tab" value="keuangan">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1.5">Dari Tanggal</label>
                    <input type="date" name="start_date" value="{{ $startDate }}" class="w-full px-3 py-2 border border-slate-200 rounded-xl text-xs font-medium focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1.5">Sampai Tanggal</label>
                    <input type="date" name="end_date" value="{{ $endDate }}" class="w-full px-3 py-2 border border-slate-200 rounded-xl text-xs font-medium focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1.5">Status Pembayaran</label>
                    <select name="status" class="w-full px-3 py-2 border border-slate-200 rounded-xl text-xs font-medium focus:ring-2 focus:ring-indigo-500 focus:outline-none bg-white">
                        <option value="semua" {{ $status == 'semua' ? 'selected' : '' }}>Semua Transaksi</option>
                        <option value="lunas" {{ $status == 'lunas' ? 'selected' : '' }}>Lunas / Selesai</option>
                        <option value="piutang" {{ $status == 'piutang' ? 'selected' : '' }}>Belum Lunas (Piutang)</option>
                    </select>
                </div>
                <div class="flex gap-2">
                    <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-3 rounded-xl transition-all text-xs shadow-md shadow-indigo-600/20 flex items-center justify-center gap-1.5">
                        <i class="fa-solid fa-filter text-[11px]"></i> Filter
                    </button>
                    <a href="{{ route('superadmin.laporan.export', ['tab' => 'keuangan', 'start_date' => $startDate, 'end_date' => $endDate, 'status' => $status]) }}" class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-2 px-3 rounded-xl transition-all text-xs shadow-md shadow-emerald-600/20 flex items-center justify-center gap-1.5">
                        <i class="fa-solid fa-file-excel text-[11px]"></i> Excel
                    </a>
                </div>
            </div>
        </form>
    </div>

    <!-- Summary Cards Keuangan (4 Kolom) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-gradient-to-br from-indigo-500 to-indigo-700 rounded-2xl p-5 shadow-lg shadow-indigo-500/15 text-white">
            <div class="flex items-center justify-between opacity-80 mb-1">
                <span class="font-bold text-[10px] uppercase tracking-wider">Omzet Transaksi</span>
                <i class="fa-solid fa-money-bill-trend-up text-sm"></i>
            </div>
            <h3 class="text-2xl font-black">Rp {{ number_format($totalOmzet, 0, ',', '.') }}</h3>
            <p class="text-[11px] text-indigo-100 mt-1">Total kotor seluruh penjualan</p>
        </div>
        <div class="bg-gradient-to-br from-cyan-500 to-blue-600 rounded-2xl p-5 shadow-lg shadow-cyan-500/15 text-white">
            <div class="flex items-center justify-between opacity-80 mb-1">
                <span class="font-bold text-[10px] uppercase tracking-wider">Estimasi Laba Bersih</span>
                <i class="fa-solid fa-chart-line text-sm"></i>
            </div>
            <h3 class="text-2xl font-black">Rp {{ number_format($totalLaba, 0, ',', '.') }}</h3>
            <p class="text-[11px] text-cyan-100 mt-1">Margin profit bersih di atas HPP</p>
        </div>
        <div class="bg-gradient-to-br from-emerald-500 to-teal-700 rounded-2xl p-5 shadow-lg shadow-emerald-500/15 text-white">
            <div class="flex items-center justify-between opacity-80 mb-1">
                <span class="font-bold text-[10px] uppercase tracking-wider">Kas Riil Diterima</span>
                <i class="fa-solid fa-hand-holding-dollar text-sm"></i>
            </div>
            <h3 class="text-2xl font-black">Rp {{ number_format($totalKas, 0, ',', '.') }}</h3>
            <p class="text-[11px] text-emerald-100 mt-1">Uang tunai / transfer yang masuk</p>
        </div>
        <div class="bg-gradient-to-br from-rose-500 to-red-600 rounded-2xl p-5 shadow-lg shadow-rose-500/15 text-white">
            <div class="flex items-center justify-between opacity-80 mb-1">
                <span class="font-bold text-[10px] uppercase tracking-wider">Sisa Piutang</span>
                <i class="fa-solid fa-clock-rotate-left text-sm"></i>
            </div>
            <h3 class="text-2xl font-black">Rp {{ number_format($totalPiutang, 0, ',', '.') }}</h3>
            <p class="text-[11px] text-rose-100 mt-1">Piutang aktif yang belum lunas</p>
        </div>
    </div>

    <!-- Tabel Data Transaksi Keuangan -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="p-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
            <h4 class="font-bold text-slate-800 text-sm flex items-center gap-2">
                <i class="fa-solid fa-receipt text-indigo-600"></i>
                Rincian Transaksi Penjualan
            </h4>
            <span class="text-xs text-slate-400 font-medium">Total: {{ count($laporanKeuangan) }} Transaksi</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 text-[11px] uppercase font-bold text-slate-400 border-b border-slate-100">
                        <th class="py-3.5 px-4">Tgl / Invoice</th>
                        <th class="py-3.5 px-4">Pelanggan</th>
                        <th class="py-3.5 px-4">Sales</th>
                        <th class="py-3.5 px-4 text-right">Tot. Transaksi</th>
                        <th class="py-3.5 px-4 text-right text-cyan-600">Est. Laba</th>
                        <th class="py-3.5 px-4 text-right text-emerald-600">Terbayar</th>
                        <th class="py-3.5 px-4 text-right text-rose-500">Sisa Piutang</th>
                        <th class="py-3.5 px-4 text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="text-xs divide-y divide-slate-100 text-slate-700">
                    @forelse($laporanKeuangan as $row)
                    <tr class="hover:bg-slate-50/60 transition-colors">
                        <td class="py-3.5 px-4">
                            <div class="font-extrabold text-indigo-600">{{ $row->no_invoice }}</div>
                            <div class="text-[10px] text-slate-400 mt-0.5">{{ \Carbon\Carbon::parse($row->created_at)->format('d/m/Y H:i') }}</div>
                        </td>
                        <td class="py-3.5 px-4 font-semibold text-slate-800">{{ strtoupper($row->nama_pelanggan ?? 'UMUM') }}</td>
                        <td class="py-3.5 px-4 text-slate-500">{{ explode(' ', $row->nama_sales ?? '-')[0] }}</td>
                        <td class="py-3.5 px-4 text-right font-black text-slate-900">Rp {{ number_format($row->total_transaksi, 0, ',', '.') }}</td>
                        <td class="py-3.5 px-4 text-right font-bold text-cyan-600 bg-cyan-50/20">Rp {{ number_format($row->laba, 0, ',', '.') }}</td>
                        <td class="py-3.5 px-4 text-right font-bold text-emerald-600">Rp {{ number_format($row->dp, 0, ',', '.') }}</td>
                        <td class="py-3.5 px-4 text-right font-bold text-rose-500">Rp {{ number_format($row->piutang, 0, ',', '.') }}</td>
                        <td class="py-3.5 px-4 text-center">
                            @if($row->piutang > 0)
                                <span class="bg-rose-50 text-rose-600 border border-rose-200 px-2.5 py-0.5 rounded-full text-[10px] font-extrabold uppercase">Piutang</span>
                            @else
                                <span class="bg-emerald-50 text-emerald-600 border border-emerald-200 px-2.5 py-0.5 rounded-full text-[10px] font-extrabold uppercase">Lunas</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-10 text-slate-400 text-xs">
                            <i class="fa-regular fa-folder-open text-3xl mb-2 block"></i>
                            Tidak ada transaksi penjualan pada rentang tanggal ini.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

@elseif($tab === 'stok')
    <!-- ---------------------------------------------------- -->
    <!-- SECTION 2: LAPORAN STOK & NILAI ASET                 -->
    <!-- ---------------------------------------------------- -->

    <!-- Filter Box Stok -->
    <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm mb-6">
        <form action="{{ route('superadmin.laporan.index') }}" method="GET" id="filterFormStok">
            <input type="hidden" name="tab" value="stok">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                <div class="md:col-span-2">
                    <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1.5">Cari Nama Barang / Kode SKU</label>
                    <div class="relative">
                        <input type="text" name="search_stok" value="{{ $searchStok }}" placeholder="Ketik nama atau kode barang..." class="w-full pl-9 pr-3 py-2 border border-slate-200 rounded-xl text-xs font-medium focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                        <i class="fa-solid fa-magnifying-glass absolute left-3 top-2.5 text-slate-400 text-xs"></i>
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1.5">Status Persediaan</label>
                    <select name="filter_stok" class="w-full px-3 py-2 border border-slate-200 rounded-xl text-xs font-medium focus:ring-2 focus:ring-emerald-500 focus:outline-none bg-white">
                        <option value="semua" {{ $filterStok == 'semua' ? 'selected' : '' }}>Semua Status</option>
                        <option value="aman" {{ $filterStok == 'aman' ? 'selected' : '' }}>Stok Aman</option>
                        <option value="menipis" {{ $filterStok == 'menipis' ? 'selected' : '' }}>Stok Menipis (<= Min)</option>
                        <option value="habis" {{ $filterStok == 'habis' ? 'selected' : '' }}>Stok Habis (Kosong)</option>
                    </select>
                </div>
                <div class="flex gap-2">
                    <button type="submit" class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-2 px-3 rounded-xl transition-all text-xs shadow-md shadow-emerald-600/20 flex items-center justify-center gap-1.5">
                        <i class="fa-solid fa-filter text-[11px]"></i> Filter
                    </button>
                    <a href="{{ route('superadmin.laporan.export', ['tab' => 'stok', 'filter_stok' => $filterStok, 'search_stok' => $searchStok]) }}" class="w-full bg-emerald-800 hover:bg-emerald-900 text-white font-bold py-2 px-3 rounded-xl transition-all text-xs shadow-md flex items-center justify-center gap-1.5">
                        <i class="fa-solid fa-file-excel text-[11px]"></i> Excel
                    </a>
                </div>
            </div>
        </form>
    </div>

    <!-- Summary Cards Stok (4 Kolom) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-gradient-to-br from-emerald-600 to-teal-700 rounded-2xl p-5 shadow-lg shadow-emerald-600/15 text-white">
            <div class="flex items-center justify-between opacity-80 mb-1">
                <span class="font-bold text-[10px] uppercase tracking-wider">Total Fisik Stok</span>
                <i class="fa-solid fa-cubes text-sm"></i>
            </div>
            <h3 class="text-2xl font-black">{{ number_format($totalStokFisik, 0, ',', '.') }} Unit</h3>
            <p class="text-[11px] text-emerald-100 mt-1">Dari {{ $totalJenisBarang }} jenis barang aktif</p>
        </div>
        <div class="bg-gradient-to-br from-indigo-600 to-blue-700 rounded-2xl p-5 shadow-lg shadow-indigo-600/15 text-white">
            <div class="flex items-center justify-between opacity-80 mb-1">
                <span class="font-bold text-[10px] uppercase tracking-wider">Total Nilai Modal (HPP)</span>
                <i class="fa-solid fa-coins text-sm"></i>
            </div>
            <h3 class="text-2xl font-black">Rp {{ number_format($totalNilaiModalStok, 0, ',', '.') }}</h3>
            <p class="text-[11px] text-indigo-100 mt-1">Total aset modal barang di toko</p>
        </div>
        <div class="bg-gradient-to-br from-cyan-600 to-teal-700 rounded-2xl p-5 shadow-lg shadow-cyan-600/15 text-white">
            <div class="flex items-center justify-between opacity-80 mb-1">
                <span class="font-bold text-[10px] uppercase tracking-wider">Potensi Nilai Jual</span>
                <i class="fa-solid fa-tags text-sm"></i>
            </div>
            <h3 class="text-2xl font-black">Rp {{ number_format($totalNilaiJualStok, 0, ',', '.') }}</h3>
            <p class="text-[11px] text-cyan-100 mt-1">Estimasi laba: Rp {{ number_format($totalPotensiLabaStok, 0, ',', '.') }}</p>
        </div>
        <div class="bg-gradient-to-br from-amber-500 to-orange-600 rounded-2xl p-5 shadow-lg shadow-amber-500/15 text-white">
            <div class="flex items-center justify-between opacity-80 mb-1">
                <span class="font-bold text-[10px] uppercase tracking-wider">Peringatan Kritis</span>
                <i class="fa-solid fa-triangle-exclamation text-sm"></i>
            </div>
            <h3 class="text-2xl font-black">{{ $stokMenipisCount + $stokHabisCount }} Item</h3>
            <p class="text-[11px] text-amber-100 mt-1">{{ $stokHabisCount }} habis &bull; {{ $stokMenipisCount }} menipis</p>
        </div>
    </div>

    <!-- Tabel Data Stok & Aset -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="p-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
            <h4 class="font-bold text-slate-800 text-sm flex items-center gap-2">
                <i class="fa-solid fa-warehouse text-emerald-600"></i>
                Daftar Inventaris & Valuasi Persediaan
            </h4>
            <span class="text-xs text-slate-400 font-medium">Ditampilkan: {{ $laporanStok->count() }} Item</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 text-[11px] uppercase font-bold text-slate-400 border-b border-slate-100">
                        <th class="py-3.5 px-4">Barang / SKU</th>
                        <th class="py-3.5 px-4">Kategori</th>
                        <th class="py-3.5 px-4 text-center">Stok Fisik</th>
                        <th class="py-3.5 px-4 text-center">Status</th>
                        <th class="py-3.5 px-4 text-right">Modal Satuan</th>
                        <th class="py-3.5 px-4 text-right text-indigo-600">Total Modal (HPP)</th>
                        <th class="py-3.5 px-4 text-right">Harga Jual</th>
                        <th class="py-3.5 px-4 text-right text-emerald-600">Potensi Laba</th>
                    </tr>
                </thead>
                <tbody class="text-xs divide-y divide-slate-100 text-slate-700">
                    @forelse($laporanStok as $row)
                    <tr class="hover:bg-slate-50/60 transition-colors">
                        <td class="py-3.5 px-4">
                            <div class="font-extrabold text-slate-900">{{ $row->nama_barang }}</div>
                            <div class="text-[10px] text-slate-400 font-mono mt-0.5">Kode: {{ $row->kode_barang ?: '-' }}</div>
                        </td>
                        <td class="py-3.5 px-4">
                            <span class="bg-slate-100 text-slate-600 px-2 py-0.5 rounded text-[10px] font-bold uppercase">{{ $row->kategori ?: 'UMUM' }}</span>
                        </td>
                        <td class="py-3.5 px-4 text-center font-extrabold text-slate-900">
                            {{ $row->stok_tersedia }} <span class="text-[10px] text-slate-400 font-normal uppercase">{{ $row->satuan }}</span>
                            <div class="text-[9px] text-slate-400 font-normal">Min: {{ $row->stok_minimum }}</div>
                        </td>
                        <td class="py-3.5 px-4 text-center">
                            @if($row->status_stok === 'habis')
                                <span class="bg-rose-50 text-rose-600 border border-rose-200 px-2 py-0.5 rounded-full text-[10px] font-extrabold uppercase">Habis</span>
                            @elseif($row->status_stok === 'menipis')
                                <span class="bg-amber-50 text-amber-600 border border-amber-200 px-2 py-0.5 rounded-full text-[10px] font-extrabold uppercase">Menipis</span>
                            @else
                                <span class="bg-emerald-50 text-emerald-600 border border-emerald-200 px-2 py-0.5 rounded-full text-[10px] font-extrabold uppercase">Aman</span>
                            @endif
                        </td>
                        <td class="py-3.5 px-4 text-right text-slate-600">Rp {{ number_format($row->harga_modal, 0, ',', '.') }}</td>
                        <td class="py-3.5 px-4 text-right font-black text-indigo-700 bg-indigo-50/30">Rp {{ number_format($row->nilai_modal_total, 0, ',', '.') }}</td>
                        <td class="py-3.5 px-4 text-right font-bold text-slate-800">Rp {{ number_format($row->harga_jual, 0, ',', '.') }}</td>
                        <td class="py-3.5 px-4 text-right font-bold text-emerald-600 bg-emerald-50/20">Rp {{ number_format($row->potensi_laba, 0, ',', '.') }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-10 text-slate-400 text-xs">
                            <i class="fa-solid fa-boxes-stacked text-3xl mb-2 block"></i>
                            Tidak ada data persediaan barang yang cocok dengan filter.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

@elseif($tab === 'gaji')
    <!-- ---------------------------------------------------- -->
    <!-- SECTION 3: LAPORAN PENGGAJIAN DAN BONUS              -->
    <!-- ---------------------------------------------------- -->

    <!-- Filter Box Penggajian -->
    <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm mb-6">
        <form action="{{ route('superadmin.laporan.index') }}" method="GET" id="filterFormGaji">
            <input type="hidden" name="tab" value="gaji">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1.5">Bulan Penggajian</label>
                    <select name="periode_bulan" class="w-full px-3 py-2 border border-slate-200 rounded-xl text-xs font-medium focus:ring-2 focus:ring-purple-500 focus:outline-none bg-white">
                        @for($m=1; $m<=12; $m++)
                            @php $mPad = str_pad($m, 2, '0', STR_PAD_LEFT); @endphp
                            <option value="{{ $mPad }}" {{ $periodeBulan == $mPad ? 'selected' : '' }}>
                                {{ DateTime::createFromFormat('!m', $m)->format('F') }} ({{ $mPad }})
                            </option>
                        @endfor
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1.5">Tahun</label>
                    <select name="periode_tahun" class="w-full px-3 py-2 border border-slate-200 rounded-xl text-xs font-medium focus:ring-2 focus:ring-purple-500 focus:outline-none bg-white">
                        @for($y=date('Y'); $y>=date('Y')-3; $y--)
                            <option value="{{ $y }}" {{ $periodeTahun == $y ? 'selected' : '' }}>{{ $y }}</option>
                        @endfor
                    </select>
                </div>
                <div>
                    <a href="{{ route('superadmin.gaji.index') }}" class="w-full bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold py-2 px-3 rounded-xl transition-all text-xs flex items-center justify-center gap-1.5 h-[38px]">
                        <i class="fa-solid fa-calculator text-purple-600"></i> Buka Menu Penggajian
                    </a>
                </div>
                <div class="flex gap-2">
                    <button type="submit" class="w-full bg-purple-600 hover:bg-purple-700 text-white font-bold py-2 px-3 rounded-xl transition-all text-xs shadow-md shadow-purple-600/20 flex items-center justify-center gap-1.5">
                        <i class="fa-solid fa-filter text-[11px]"></i> Filter
                    </button>
                    <a href="{{ route('superadmin.laporan.export', ['tab' => 'gaji', 'periode_bulan' => $periodeBulan, 'periode_tahun' => $periodeTahun]) }}" class="w-full bg-purple-800 hover:bg-purple-900 text-white font-bold py-2 px-3 rounded-xl transition-all text-xs shadow-md flex items-center justify-center gap-1.5">
                        <i class="fa-solid fa-file-excel text-[11px]"></i> Excel
                    </a>
                </div>
            </div>
        </form>
    </div>

    <!-- Summary Cards Penggajian & Bonus (4 Kolom) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-gradient-to-br from-purple-600 to-indigo-700 rounded-2xl p-5 shadow-lg shadow-purple-600/15 text-white">
            <div class="flex items-center justify-between opacity-80 mb-1">
                <span class="font-bold text-[10px] uppercase tracking-wider">Total Beban Payroll</span>
                <i class="fa-solid fa-money-check-dollar text-sm"></i>
            </div>
            <h3 class="text-2xl font-black">Rp {{ number_format($totalBebanPayroll, 0, ',', '.') }}</h3>
            <p class="text-[11px] text-purple-100 mt-1">{{ $sudahDibayarCount }} dari {{ count($laporanGaji) }} karyawan terbayar</p>
        </div>
        <div class="bg-gradient-to-br from-indigo-500 to-blue-600 rounded-2xl p-5 shadow-lg shadow-indigo-500/15 text-white">
            <div class="flex items-center justify-between opacity-80 mb-1">
                <span class="font-bold text-[10px] uppercase tracking-wider">Gaji Pokok + Tunjangan</span>
                <i class="fa-solid fa-hand-holding-dollar text-sm"></i>
            </div>
            <h3 class="text-2xl font-black">Rp {{ number_format($totalGapok + $totalTunjangan, 0, ',', '.') }}</h3>
            <p class="text-[11px] text-indigo-100 mt-1">Pokok: Rp {{ number_format($totalGapok, 0, ',', '.') }}</p>
        </div>
        <div class="bg-gradient-to-br from-emerald-500 to-teal-700 rounded-2xl p-5 shadow-lg shadow-emerald-500/15 text-white">
            <div class="flex items-center justify-between opacity-80 mb-1">
                <span class="font-bold text-[10px] uppercase tracking-wider">Bonus Dicairkan</span>
                <i class="fa-solid fa-gift text-sm"></i>
            </div>
            <h3 class="text-2xl font-black">Rp {{ number_format($totalBonusPayroll, 0, ',', '.') }}</h3>
            <p class="text-[11px] text-emerald-100 mt-1">Insentif & bonus sales periode ini</p>
        </div>
        <div class="bg-gradient-to-br from-rose-500 to-pink-600 rounded-2xl p-5 shadow-lg shadow-rose-500/15 text-white">
            <div class="flex items-center justify-between opacity-80 mb-1">
                <span class="font-bold text-[10px] uppercase tracking-wider">Total Potongan</span>
                <i class="fa-solid fa-scissors text-sm"></i>
            </div>
            <h3 class="text-2xl font-black">Rp {{ number_format($totalPotongan, 0, ',', '.') }}</h3>
            <p class="text-[11px] text-rose-100 mt-1">Kasbon / keterlambatan karyawan</p>
        </div>
    </div>

    <!-- Tabel Rekapitulasi Gaji & Bonus -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="p-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
            <h4 class="font-bold text-slate-800 text-sm flex items-center gap-2">
                <i class="fa-solid fa-users text-purple-600"></i>
                Rekapitulasi Gaji & Bonus Karyawan (Periode {{ $periodeBulan }}/{{ $periodeTahun }})
            </h4>
            <span class="text-xs text-slate-400 font-medium">Total Pegawai: {{ count($laporanGaji) }}</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 text-[11px] uppercase font-bold text-slate-400 border-b border-slate-100">
                        <th class="py-3.5 px-4">Nama Pegawai</th>
                        <th class="py-3.5 px-4">Jabatan</th>
                        <th class="py-3.5 px-4 text-right">Gaji Pokok</th>
                        <th class="py-3.5 px-4 text-right">Tunjangan</th>
                        <th class="py-3.5 px-4 text-right text-emerald-600">Bonus Cair</th>
                        <th class="py-3.5 px-4 text-right text-rose-500">Potongan</th>
                        <th class="py-3.5 px-4 text-right text-purple-700">Take Home Pay</th>
                        <th class="py-3.5 px-4 text-center">Status</th>
                        <th class="py-3.5 px-4 text-center">Slip Gaji</th>
                    </tr>
                </thead>
                <tbody class="text-xs divide-y divide-slate-100 text-slate-700">
                    @forelse($laporanGaji as $row)
                    <tr class="hover:bg-slate-50/60 transition-colors">
                        <td class="py-3.5 px-4">
                            <div class="font-extrabold text-slate-900">{{ strtoupper($row->nama) }}</div>
                            <div class="text-[10px] text-slate-400">@<span>{{ $row->username }}</span></div>
                        </td>
                        <td class="py-3.5 px-4">
                            <span class="bg-indigo-50 text-indigo-600 px-2 py-0.5 rounded text-[10px] font-bold uppercase">{{ $row->role }}</span>
                        </td>
                        <td class="py-3.5 px-4 text-right font-medium">Rp {{ number_format($row->gaji_pokok, 0, ',', '.') }}</td>
                        <td class="py-3.5 px-4 text-right font-medium">Rp {{ number_format($row->tunjangan, 0, ',', '.') }}</td>
                        <td class="py-3.5 px-4 text-right font-bold text-emerald-600 bg-emerald-50/20">
                            Rp {{ number_format($row->bonus, 0, ',', '.') }}
                        </td>
                        <td class="py-3.5 px-4 text-right font-medium text-rose-500">
                            Rp {{ number_format($row->potongan, 0, ',', '.') }}
                            @if($row->potongan > 0)
                                <div class="text-[9px] text-slate-400 italic">({{ $row->keterangan_potongan }})</div>
                            @endif
                        </td>
                        <td class="py-3.5 px-4 text-right font-black text-purple-700 bg-purple-50/30">
                            Rp {{ number_format($row->total_gaji, 0, ',', '.') }}
                        </td>
                        <td class="py-3.5 px-4 text-center">
                            @if($row->status === 'dibayar')
                                <span class="bg-emerald-50 text-emerald-600 border border-emerald-200 px-2.5 py-0.5 rounded-full text-[10px] font-extrabold uppercase inline-flex items-center gap-1">
                                    <i class="fa-solid fa-check text-[9px]"></i> Dibayar
                                </span>
                            @else
                                <span class="bg-amber-50 text-amber-600 border border-amber-200 px-2.5 py-0.5 rounded-full text-[10px] font-extrabold uppercase inline-flex items-center gap-1">
                                    <i class="fa-solid fa-clock text-[9px]"></i> Menunggu
                                </span>
                            @endif
                        </td>
                        <td class="py-3.5 px-4 text-center">
                            @if($row->gaji_id)
                                <a href="{{ route('superadmin.gaji.cetak', $row->gaji_id) }}" target="_blank" class="inline-flex items-center gap-1 bg-slate-100 hover:bg-slate-200 text-slate-700 px-2.5 py-1 rounded-lg text-xs font-bold transition">
                                    <i class="fa-solid fa-print text-indigo-600"></i> Slip
                                </a>
                            @else
                                <span class="text-slate-300 text-[11px]">-</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center py-10 text-slate-400 text-xs">
                            <i class="fa-solid fa-money-check-dollar text-3xl mb-2 block"></i>
                            Tidak ada karyawan terdaftar pada toko ini.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

@endif

@endsection