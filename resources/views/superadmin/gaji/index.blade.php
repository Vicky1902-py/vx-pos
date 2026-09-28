@extends('layouts.admin')

@section('title', 'Manajemen Penggajian')

@section('content')
<div class="mb-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <h3 class="font-bold text-[#4b465c] text-2xl flex items-center gap-2">
                <i class="fa-solid fa-envelope-open-text text-[#7367f0]"></i>
                Penggajian Karyawan
            </h3>
            <p class="text-[#a8aaae] text-sm">Proses gaji bulanan, potongan, dan cetak slip gaji</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('superadmin.laporan.index', ['tab' => 'gaji']) }}" class="bg-indigo-50 hover:bg-indigo-100 text-[#7367f0] font-bold text-xs px-3 py-2 rounded-xl border border-indigo-100 transition flex items-center gap-1.5">
                <i class="fa-solid fa-chart-pie text-xs"></i> Laporan Rekap Payroll
            </a>
        </div>
    </div>
</div>

<!-- Filter Periode -->
<div class="bg-white p-5 rounded-xl shadow-[0_4px_18px_0_rgba(75,70,92,0.1)] mb-6">
    <form action="{{ route('superadmin.gaji.index') }}" method="GET" class="flex flex-wrap gap-4 items-end">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Pilih Bulan</label>
            <select name="bulan" class="p-2.5 border border-gray-300 rounded-lg text-sm focus:ring-[#7367f0] focus:border-[#7367f0] min-w-[150px] bg-white">
                @for($m=1; $m<=12; $m++)
                    @php $mPad = str_pad($m, 2, '0', STR_PAD_LEFT); @endphp
                    <option value="{{ $mPad }}" {{ $bulan == $mPad ? 'selected' : '' }}>
                        {{ DateTime::createFromFormat('!m', $m)->format('F') }}
                    </option>
                @endfor
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Pilih Tahun</label>
            <select name="tahun" class="p-2.5 border border-gray-300 rounded-lg text-sm focus:ring-[#7367f0] focus:border-[#7367f0] min-w-[120px] bg-white">
                @for($y=date('Y'); $y>=date('Y')-3; $y--)
                    <option value="{{ $y }}" {{ $tahun == $y ? 'selected' : '' }}>{{ $y }}</option>
                @endfor
            </select>
        </div>
        <button type="submit" class="bg-[#7367f0] hover:bg-[#6355e6] text-white font-medium py-2.5 px-5 rounded-lg transition-all text-sm shadow-md cursor-pointer">
            Tampilkan Data
        </button>
    </form>
</div>

<!-- Tabel Karyawan & Status Penggajian -->
<div class="bg-white rounded-xl shadow-[0_4px_18px_0_rgba(75,70,92,0.1)] overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-gray-50 border-b border-gray-100 text-xs uppercase text-gray-500">
                    <th class="py-4 px-6">Nama Karyawan</th>
                    <th class="py-4 px-6">Posisi / Role</th>
                    <th class="py-4 px-6 text-center">Status Gaji (Bulan {{ $bulan }}/{{ $tahun }})</th>
                    <th class="py-4 px-6 text-center">Aksi / Cetak</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 text-[#4b465c] text-[15px]">
                @foreach($karyawan as $k)
                <tr class="hover:bg-slate-50/50 transition-colors">
                    <td class="py-4 px-6">
                        <div class="font-extrabold text-slate-800 text-base">{{ strtoupper($k->nama) }}</div>
                        <div class="flex items-center gap-2 mt-1">
                            <span class="text-xs text-slate-500 font-medium flex items-center gap-1">
                                <i class="fa-solid fa-coins text-amber-500 text-[11px]"></i>
                                Gaji Pokok:
                                <span class="font-extrabold text-indigo-600" id="standar_gapok_val_{{ $k->id }}">
                                    {{ ($k->gaji_pokok_standar ?? 0) > 0 ? 'Rp ' . number_format($k->gaji_pokok_standar, 0, ',', '.') : 'Belum diatur' }}
                                </span>
                            </span>
                            <button type="button" onclick="editGajiStandarModal({{ $k->id }}, '{{ addslashes($k->nama) }}', {{ (float)($k->gaji_pokok_standar ?? 0) }})" class="text-[11px] text-indigo-500 hover:text-indigo-700 bg-indigo-50 hover:bg-indigo-100 px-1.5 py-0.5 rounded transition cursor-pointer" title="Atur Gaji Pokok Standar">
                                <i class="fa-solid fa-pen-to-square text-[10px]"></i> Ubah
                            </button>
                        </div>
                        @if(($k->estimasi_bonus ?? 0) > 0)
                            <span class="bg-emerald-50 text-emerald-600 border border-emerald-200 px-2 py-0.5 rounded-full text-[10px] font-extrabold inline-flex items-center gap-1 mt-1.5">
                                <i class="fa-solid fa-gift text-[9px]"></i> Bonus: Rp {{ number_format($k->estimasi_bonus, 0, ',', '.') }}
                            </span>
                        @endif
                    </td>
                    <td class="py-4 px-6">
                        <span class="bg-indigo-50 text-indigo-600 px-2.5 py-1 rounded-md text-[11px] font-bold uppercase">{{ $k->role }}</span>
                    </td>
                    <td class="py-4 px-6 text-center">
                        @if($k->status_gaji == 'dibayar')
                            <span class="bg-emerald-100 text-emerald-600 px-3 py-1.5 rounded-lg text-xs font-bold uppercase"><i class="fa-solid fa-check mr-1"></i> Sudah Dibayar</span>
                            <div class="text-[11px] text-gray-400 mt-1 font-bold">Rp {{ number_format($k->data_gaji->total_gaji, 0, ',', '.') }}</div>
                            @if(($k->estimasi_bonus ?? 0) > 0 && (float)($k->data_gaji->bonus ?? 0) != (float)$k->estimasi_bonus)
                                <div class="text-[10px] text-amber-600 font-semibold mt-1">
                                    <i class="fa-solid fa-arrows-rotate mr-0.5"></i> Bonus baru cair: Rp {{ number_format($k->estimasi_bonus, 0, ',', '.') }}
                                </div>
                            @endif
                        @else
                            <span class="bg-orange-100 text-orange-600 px-3 py-1.5 rounded-lg text-xs font-bold uppercase"><i class="fa-solid fa-clock mr-1"></i> Menunggu Proses</span>
                            @if(($k->estimasi_bonus ?? 0) > 0)
                                <div class="text-[11px] text-emerald-600 font-bold mt-1">+ Bonus Rp {{ number_format($k->estimasi_bonus, 0, ',', '.') }}</div>
                            @endif
                        @endif
                    </td>
                    <td class="py-4 px-6 text-center">
                        @if($k->status_gaji == 'dibayar')
                            <a href="{{ route('superadmin.gaji.cetak', $k->data_gaji->id) }}" target="_blank" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-3 py-1.5 rounded-lg text-sm font-semibold transition-all inline-flex items-center gap-1">
                                <i class="fa-solid fa-print"></i> Slip
                            </a>
                            <button onclick="openGajiModal({{ $k->id }}, '{{ addslashes($k->nama) }}', {{ json_encode($k->data_gaji) }}, {{ (float)($k->estimasi_bonus ?? 0) }}, {{ (float)($k->gaji_pokok_standar ?? 0) }})" class="text-[#7367f0] hover:text-indigo-800 ml-2 text-sm underline font-medium cursor-pointer">Revisi</button>
                        @else
                            <button onclick="openGajiModal({{ $k->id }}, '{{ addslashes($k->nama) }}', null, {{ (float)($k->estimasi_bonus ?? 0) }}, {{ (float)($k->gaji_pokok_standar ?? 0) }})" class="bg-[#7367f0] hover:bg-[#6355e6] text-white px-3 py-1.5 rounded-lg text-sm font-semibold shadow-sm transition-all inline-flex items-center gap-1 cursor-pointer">
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

            <!-- Pendapatan -->
            <div class="space-y-3">
                <h6 class="text-xs font-bold text-gray-500 uppercase tracking-wider border-b pb-1">Pendapatan</h6>
                <div>
                    <div class="flex justify-between items-center mb-1">
                        <label class="block text-sm font-semibold text-gray-600">Gaji Pokok (Rp)</label>
                        <span id="label_gapok_standar_info" class="text-[11px] text-indigo-600 font-bold bg-indigo-50 px-2 py-0.5 rounded border border-indigo-100">
                            Standar: Rp 0
                        </span>
                    </div>
                    <input type="number" id="input_gapok" name="gaji_pokok" oninput="kalkulasiGaji()" required min="0" class="w-full px-3 py-2 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500 font-bold text-gray-800">
                    <div class="flex items-center gap-2 mt-1.5">
                        <input type="checkbox" id="check_simpan_standar" name="simpan_sebagai_standar" value="1" checked class="rounded text-[#7367f0] focus:ring-[#7367f0] cursor-pointer">
                        <label for="check_simpan_standar" class="text-xs text-gray-500 font-medium cursor-pointer">
                            Simpan sebagai gaji pokok standar pegawai ini (otomatis terbaca di periode berikutnya)
                        </label>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-600 mb-1">Tunjangan / Lain-lain (Rp)</label>
                    <input type="number" id="input_tunjangan" name="tunjangan" oninput="kalkulasiGaji()" value="0" min="0" class="w-full px-3 py-2 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500 font-semibold">
                </div>
                <div>
                    <div class="flex justify-between items-center mb-1">
                        <label class="block text-sm font-semibold text-gray-600">Bonus Pencairan Bulan Ini (Rp)</label>
                        <button type="button" id="btn_sync_bonus" onclick="tarikBonusOtomatis()" class="text-xs text-emerald-600 hover:text-emerald-800 font-bold flex items-center gap-1 transition px-2 py-0.5 rounded bg-emerald-50 border border-emerald-200 cursor-pointer">
                            <i class="fa-solid fa-arrows-rotate text-[10px]"></i> Tarik Bonus (<span id="span_estimasi_bonus">Rp 0</span>)
                        </button>
                    </div>
                    <div class="relative">
                        <input type="number" id="input_bonus" name="bonus" oninput="kalkulasiGaji()" min="0" class="w-full px-3 py-2 border border-emerald-200 bg-emerald-50/60 rounded-lg text-emerald-700 font-bold focus:outline-none focus:ring-2 focus:ring-emerald-500">
                    </div>
                    <p id="info_bonus_text" class="text-[11px] text-gray-400 mt-1">
                        <i class="fa-solid fa-circle-info mr-1"></i>Otomatis disinkronkan dengan riwayat pencairan bonus sales periode {{ $bulan }}/{{ $tahun }}.
                    </p>
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
                <button type="button" onclick="toggleModal('modalGaji')" class="px-4 py-2 border border-gray-200 rounded-lg text-gray-600 hover:bg-gray-50 text-sm font-medium cursor-pointer">Batal</button>
                <button type="submit" class="px-4 py-2 bg-[#7367f0] hover:bg-[#6355e6] text-white rounded-lg text-sm font-medium shadow-md cursor-pointer">Simpan & Terbitkan Slip</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Quick Edit Gaji Pokok Standar -->
<div id="modalEditStandar" class="fixed inset-0 z-50 overflow-y-auto hidden bg-black/40 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-sm w-full shadow-xl overflow-hidden p-6 border border-slate-100">
        <h4 class="font-bold text-slate-800 text-base mb-0.5">Atur Gaji Pokok Standar</h4>
        <p class="text-xs text-slate-400 mb-3">Nominal ini otomatis terbaca saat memproses slip gaji bulanan.</p>
        
        <div class="bg-indigo-50 border border-indigo-100 rounded-lg p-2.5 mb-3 text-center">
            <span id="edit_standar_nama" class="text-xs text-indigo-700 font-bold uppercase">-</span>
        </div>

        <input type="hidden" id="edit_standar_user_id">
        <div class="mb-4">
            <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Gaji Pokok Standar (Rp)</label>
            <input type="number" id="edit_standar_nominal" min="0" class="w-full px-3 py-2 border border-slate-200 rounded-xl text-sm font-bold text-slate-800 focus:ring-2 focus:ring-indigo-500 focus:outline-none">
        </div>
        <div class="flex justify-end gap-2">
            <button type="button" onclick="toggleModal('modalEditStandar')" class="px-3 py-2 text-xs font-semibold text-slate-500 hover:bg-slate-50 rounded-lg cursor-pointer">Batal</button>
            <button type="button" id="btn_save_standar_ajax" onclick="simpanGajiStandarAjax()" class="px-4 py-2 text-xs font-bold bg-[#7367f0] hover:bg-[#6355e6] text-white rounded-xl shadow-md cursor-pointer">
                Simpan Standar
            </button>
        </div>
    </div>
</div>

<script>
    let currentEstimasiBonus = 0;
    let currentGajiPokokStandar = 0;

    function toggleModal(modalId) {
        document.getElementById(modalId).classList.toggle('hidden');
    }

    function openGajiModal(id, nama, dataGaji, estimasiBonus, gajiPokokStandar) {
        document.getElementById('input_user_id').value = id;
        document.getElementById('display_nama').innerText = nama;
        currentEstimasiBonus = parseFloat(estimasiBonus) || 0;
        currentGajiPokokStandar = parseFloat(gajiPokokStandar) || 0;
        
        let labelGapok = document.getElementById('label_gapok_standar_info');
        if (labelGapok) {
            labelGapok.innerText = 'Standar: Rp ' + currentGajiPokokStandar.toLocaleString('id-ID');
        }

        let spanBonus = document.getElementById('span_estimasi_bonus');
        if (spanBonus) {
            spanBonus.innerText = 'Rp ' + currentEstimasiBonus.toLocaleString('id-ID');
        }

        let infoBonus = document.getElementById('info_bonus_text');
        
        if (dataGaji) {
            // Mode Revisi (Data Sudah Ada)
            document.getElementById('input_gapok').value = dataGaji.gaji_pokok || 0;
            document.getElementById('input_tunjangan').value = dataGaji.tunjangan || 0;
            
            let recordedBonus = parseFloat(dataGaji.bonus) || 0;
            if (recordedBonus === 0 && currentEstimasiBonus > 0) {
                document.getElementById('input_bonus').value = currentEstimasiBonus;
                if (infoBonus) {
                    infoBonus.innerHTML = '<span class="text-emerald-600 font-semibold"><i class="fa-solid fa-circle-check mr-1"></i>Bonus pencairan baru terdeteksi (Rp ' + currentEstimasiBonus.toLocaleString('id-ID') + ') dan otomatis diterapkan.</span>';
                }
            } else {
                document.getElementById('input_bonus').value = recordedBonus;
                if (recordedBonus !== currentEstimasiBonus && currentEstimasiBonus > 0) {
                    if (infoBonus) {
                        infoBonus.innerHTML = '<span class="text-amber-600 font-medium"><i class="fa-solid fa-triangle-exclamation mr-1"></i>Tersimpan Rp ' + recordedBonus.toLocaleString('id-ID') + ', ada pencairan bonus terbaru Rp ' + currentEstimasiBonus.toLocaleString('id-ID') + '. Klik tombol "Tarik Bonus" di atas untuk memperbarui.</span>';
                    }
                } else {
                    if (infoBonus) {
                        infoBonus.innerHTML = '<i class="fa-solid fa-circle-info mr-1"></i>Bonus telah tersinkronisasi dengan pencairan bonus periode ini.';
                    }
                }
            }
            
            document.getElementById('input_potongan').value = dataGaji.potongan || 0;
            document.getElementById('input_ket_potongan').value = dataGaji.keterangan_potongan || '';
        } else {
            // Mode Baru (Sistem Otomatis Membaca Gaji Pokok Standar!)
            document.getElementById('input_gapok').value = currentGajiPokokStandar > 0 ? currentGajiPokokStandar : '';
            document.getElementById('input_tunjangan').value = '0';
            document.getElementById('input_bonus').value = currentEstimasiBonus;
            document.getElementById('input_potongan').value = '0';
            document.getElementById('input_ket_potongan').value = '';
            if (infoBonus) {
                if (currentEstimasiBonus > 0) {
                    infoBonus.innerHTML = '<span class="text-emerald-600 font-semibold"><i class="fa-solid fa-circle-check mr-1"></i>Bonus sebesar Rp ' + currentEstimasiBonus.toLocaleString('id-ID') + ' otomatis dimuat dari pencairan bonus.</span>';
                } else {
                    infoBonus.innerHTML = '<i class="fa-solid fa-circle-info mr-1"></i>Belum ada bonus yang dicairkan untuk periode ini.';
                }
            }
        }
        
        kalkulasiGaji();
        toggleModal('modalGaji');
    }

    function editGajiStandarModal(userId, nama, nominal) {
        document.getElementById('edit_standar_user_id').value = userId;
        document.getElementById('edit_standar_nama').innerText = nama;
        document.getElementById('edit_standar_nominal').value = nominal || 0;
        toggleModal('modalEditStandar');
    }

    function simpanGajiStandarAjax() {
        let userId = document.getElementById('edit_standar_user_id').value;
        let nominal = parseFloat(document.getElementById('edit_standar_nominal').value) || 0;
        let btn = document.getElementById('btn_save_standar_ajax');
        btn.innerText = 'Menyimpan...';
        btn.disabled = true;

        fetch("{{ route('superadmin.gaji.standar') }}", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": "{{ csrf_token() }}"
            },
            body: JSON.stringify({ user_id: userId, gaji_pokok: nominal })
        })
        .then(res => res.json())
        .then(data => {
            btn.innerText = 'Simpan Standar';
            btn.disabled = false;
            if (data.success) {
                let el = document.getElementById('standar_gapok_val_' + userId);
                if (el) el.innerText = data.formatted;
                toggleModal('modalEditStandar');
                window.location.reload();
            } else {
                alert(data.message || 'Gagal menyimpan.');
            }
        })
        .catch(err => {
            btn.innerText = 'Simpan Standar';
            btn.disabled = false;
            console.error(err);
            alert('Terjadi kesalahan koneksi.');
        });
    }

    function tarikBonusOtomatis() {
        document.getElementById('input_bonus').value = currentEstimasiBonus;
        let infoBonus = document.getElementById('info_bonus_text');
        if (infoBonus) {
            infoBonus.innerHTML = '<span class="text-emerald-600 font-bold"><i class="fa-solid fa-arrows-rotate mr-1"></i>Bonus berhasil diperbarui dari data pencairan: Rp ' + currentEstimasiBonus.toLocaleString('id-ID') + '</span>';
        }
        kalkulasiGaji();
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