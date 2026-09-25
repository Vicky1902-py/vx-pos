@extends('layouts.admin')

@section('title', 'Master Barang')

@section('content')
<div class="mb-6 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
    <div>
        <h3 class="font-bold text-[#4b465c] text-2xl">Master Barang</h3>
        <p class="text-[#a8aaae] text-sm">Kelola inventaris suku cadang, produk, dan stok VxPOS</p>
    </div>
    <div class="flex flex-wrap gap-3">
        <button onclick="downloadTemplate()" class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium py-2.5 px-4 rounded-xl text-sm transition-all flex items-center gap-2">
            <i class="fa-solid fa-file-arrow-down text-base"></i> Template Excel
        </button>
        <button onclick="toggleModal('modalMasal')" class="bg-emerald-500 hover:bg-emerald-600 text-white font-medium py-2.5 px-4 rounded-xl text-sm shadow-[0_2px_6px_rgba(40,199,111,0.4)] transition-all flex items-center gap-2">
            <i class="fa-solid fa-file-excel text-base"></i> Input Masal (.xlsx)
        </button>
        <button onclick="openAddModal()" class="bg-[#7367f0] hover:bg-[#6355e6] text-white font-medium py-2.5 px-4 rounded-xl text-sm shadow-[0_2px_6px_rgba(115,103,240,0.4)] transition-all flex items-center gap-2">
            <i class="fa-solid fa-plus text-base"></i> Tambah Barang
        </button>
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
        <h5 class="font-semibold text-[#4b465c] text-lg">Daftar Produk</h5>
        <form action="" method="GET" class="w-full sm:w-72">
            <div class="relative">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari kode, nama, atau kategori..." class="w-full pl-10 pr-4 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-[#7367f0] focus:border-transparent">
                <i class="fa-solid fa-magnifying-glass absolute left-3 top-3 text-gray-400 text-sm"></i>
            </div>
        </form>
    </div>
    
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-[#f8f7fa] text-[#4b465c] uppercase text-xs font-semibold tracking-wider border-b border-gray-100">
                    <th class="py-4 px-6">Kode Barang</th>
                    <th class="py-4 px-6">Nama Barang</th>
                    <th class="py-4 px-6">Kategori</th>
                    <th class="py-4 px-6">Stok Fisik</th>
                    <th class="py-4 px-6 text-center">Status</th>
                    <th class="py-4 px-6 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 text-[#4b465c] text-[15px]">
                @forelse($barang as $item)
                <tr class="hover:bg-slate-50/50 transition-colors">
                    <td class="py-4 px-6 font-semibold text-[#7367f0]">{{ $item->kode_barang }}</td>
                    <td class="py-4 px-6 font-medium">
                        {{ $item->nama_barang }}
                        @if($item->harga_jual)
                            <span class="block text-xs text-emerald-500 font-bold mt-1">Rp {{ number_format($item->harga_jual, 0, ',', '.') }}</span>
                        @endif
                    </td>
                    <td class="py-4 px-6"><span class="bg-slate-100 text-slate-600 px-2.5 py-1 rounded-md text-xs font-medium">{{ $item->kategori }}</span></td>
                    <td class="py-4 px-6">
                        <div class="flex items-baseline gap-1">
                            <span class="font-bold text-lg {{ $item->status_warning == 'warning' ? 'text-red-500' : 'text-emerald-600' }}">
                                {{ $item->stok_tersedia ?? 0 }}
                            </span>
                            <span class="text-xs text-gray-400">{{ $item->satuan }}</span>
                        </div>
                        <p class="text-[11px] text-gray-400 mt-0.5">Min: {{ $item->stok_minimum ?? 0 }}</p>
                    </td>
                    <td class="py-4 px-6 text-center">
                        @if($item->status == 'aktif')
                            <span class="bg-emerald-50 text-emerald-600 px-2.5 py-1 rounded-md text-xs font-semibold">Aktif</span>
                        @else
                            <span class="bg-gray-100 text-gray-500 px-2.5 py-1 rounded-md text-xs font-semibold">Nonaktif</span>
                        @endif
                    </td>
                    <td class="py-4 px-6 text-center">
                        <div class="flex items-center justify-center gap-3">
                            <button onclick="openEditModal({{ json_encode($item) }})" class="text-amber-500 hover:text-amber-600 transition-colors"><i class="fa-regular fa-pen-to-square text-lg"></i></button>
                            <form action="{{ route('superadmin.barang.destroy', $item->id) }}" method="POST" onsubmit="return confirm('Hapus produk ini secara permanen? Data stok dan harga juga akan terhapus.');" class="inline">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-red-400 hover:text-red-500 transition-colors"><i class="fa-regular fa-trash-can text-lg"></i></button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center py-8 text-gray-400 font-medium">Belum ada data barang di dalam sistem.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="p-4 border-t border-gray-100">
        {{ $barang->appends(request()->query())->links() }}
    </div>
</div>

<div id="modalManual" class="fixed inset-0 z-50 overflow-y-auto hidden bg-black/40 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-lg w-full shadow-xl overflow-hidden transform transition-all">
        <div class="px-6 py-4 bg-[#f8f7fa] border-b border-gray-100 flex justify-between items-center">
            <h3 id="modalTitle" class="font-bold text-[#4b465c] text-lg">Tambah Barang Baru</h3>
            <button onclick="toggleModal('modalManual')" class="text-gray-400 hover:text-gray-600 text-xl"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <form id="formManual" action="{{ route('superadmin.barang.store') }}" method="POST" class="p-6 space-y-4 max-h-[75vh] overflow-y-auto">
            @csrf
            <input type="hidden" id="methodField" name="_method" value="POST">
            <div>
                <label class="block text-sm font-semibold text-gray-600 mb-1">Kode Barang</label>
                <input type="text" id="kode_barang" name="kode_barang" required class="w-full px-3 py-2 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#7367f0]">
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-600 mb-1">Nama Barang</label>
                <input type="text" id="nama_barang" name="nama_barang" required class="w-full px-3 py-2 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#7367f0]">
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-600 mb-1">Kategori</label>
                    <input type="text" id="kategori" name="kategori" placeholder="Contoh: Ban, Busi" required class="w-full px-3 py-2 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#7367f0]">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-600 mb-1">Satuan</label>
                    <input type="text" id="satuan" name="satuan" placeholder="Contoh: Pcs, Set" required class="w-full px-3 py-2 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#7367f0]">
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4 bg-gray-50 p-3 rounded-lg border border-gray-100">
                <div>
                    <label class="block text-sm font-semibold text-gray-600 mb-1">Stok Tersedia</label>
                    <input type="number" id="stok_tersedia" name="stok_tersedia" required min="0" class="w-full px-3 py-2 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-600 mb-1">Stok Minimum</label>
                    <input type="number" id="stok_minimum" name="stok_minimum" required min="0" class="w-full px-3 py-2 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-400">
                </div>
            </div>

            <div class="grid grid-cols-3 gap-3 bg-emerald-50/50 p-3 rounded-lg border border-emerald-100">
                <div>
                    <label class="block text-xs font-bold text-gray-600 mb-1">Harga Modal</label>
                    <input type="number" id="harga_modal" name="harga_modal" min="0" class="w-full px-2 py-1.5 border border-gray-200 rounded text-sm focus:outline-none focus:ring-1 focus:ring-[#7367f0]">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-600 mb-1">Harga Min.</label>
                    <input type="number" id="harga_minimum" name="harga_minimum" min="0" class="w-full px-2 py-1.5 border border-gray-200 rounded text-sm focus:outline-none focus:ring-1 focus:ring-orange-400">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-600 mb-1">Harga Jual</label>
                    <input type="number" id="harga_jual" name="harga_jual" min="0" class="w-full px-2 py-1.5 border border-gray-200 rounded text-sm focus:outline-none focus:ring-1 focus:ring-emerald-500">
                </div>
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-600 mb-1">Status Keaktifan</label>
                <select id="status" name="status" class="w-full px-3 py-2 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#7367f0]">
                    <option value="aktif">Aktif</option>
                    <option value="nonaktif">Nonaktif</option>
                </select>
            </div>
            <div class="pt-2 flex justify-end gap-3 sticky bottom-0 bg-white">
                <button type="button" onclick="toggleModal('modalManual')" class="px-4 py-2 border border-gray-200 rounded-lg text-gray-600 hover:bg-gray-50 text-sm font-medium">Batal</button>
                <button type="submit" class="px-4 py-2 bg-[#7367f0] hover:bg-[#6355e6] text-white rounded-lg text-sm font-medium">Simpan Data</button>
            </div>
        </form>
    </div>
</div>

<div id="modalMasal" class="fixed inset-0 z-50 overflow-y-auto hidden bg-black/40 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-4xl w-full shadow-xl overflow-hidden transform transition-all">
        <div class="px-6 py-4 bg-[#f8f7fa] border-b border-gray-100 flex justify-between items-center">
            <h3 class="font-bold text-[#4b465c] text-lg">Import Massal & Update via Excel (.xlsx)</h3>
            <button onclick="toggleModal('modalMasal')" class="text-gray-400 hover:text-gray-600 text-xl"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <div class="p-6 space-y-4">
            <div class="border-2 border-dashed border-gray-200 rounded-xl p-6 text-center hover:border-[#7367f0] transition-colors relative">
                <input type="file" id="excelFile" accept=".xlsx" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer" onchange="handleExcelDrop(event)">
                <i class="fa-solid fa-cloud-arrow-up text-3xl text-gray-300 mb-2"></i>
                <p id="uploadPrompt" class="text-sm font-medium text-gray-600">Pilih atau letakkan file Excel template Anda di sini</p>
                <p class="text-xs text-gray-400 mt-1">Sistem akan otomatis mendeteksi Harga dan memperbarui stok jika kode barang sudah ada.</p>
            </div>
            
            <div id="previewArea" class="hidden">
                <h5 class="text-xs font-bold uppercase text-gray-400 mb-2">Pratinjau Data (Maksimal 5 Baris Pertama)</h5>
                <div class="overflow-x-auto border border-gray-100 rounded-lg max-h-40">
                    <table class="w-full text-xs text-left border-collapse whitespace-nowrap">
                        <thead class="bg-gray-50 sticky top-0">
                            <tr>
                                <th class="p-2 border-b">Kode</th>
                                <th class="p-2 border-b">Nama Barang</th>
                                <th class="p-2 border-b">Stok</th>
                                <th class="p-2 border-b">Harga Modal</th>
                                <th class="p-2 border-b">Harga Minimum</th>
                                <th class="p-2 border-b">Harga Jual</th>
                                <th class="p-2 border-b">Status</th>
                            </tr>
                        </thead>
                        <tbody id="previewBody" class="divide-y divide-gray-50"></tbody>
                    </table>
                </div>
            </div>

            <div class="flex justify-end gap-3">
                <button type="button" onclick="toggleModal('modalMasal')" class="px-4 py-2 border border-gray-200 rounded-lg text-gray-600 hover:bg-gray-50 text-sm font-medium">Batal</button>
                <button type="button" id="btnProsesImport" disabled onclick="submitImport()" class="px-4 py-2 bg-emerald-500 hover:bg-emerald-600 text-white rounded-lg text-sm font-medium disabled:opacity-50 disabled:cursor-not-allowed">Mulai Eksekusi Import</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>

<script>
    let parsedExcelData = [];

    function toggleModal(modalId) {
        const modal = document.getElementById(modalId);
        modal.classList.toggle('hidden');
    }

    function openAddModal() {
        document.getElementById('modalTitle').innerText = 'Tambah Barang Baru';
        document.getElementById('formManual').action = "{{ route('superadmin.barang.store') }}";
        document.getElementById('methodField').value = "POST";
        document.getElementById('formManual').reset();
        toggleModal('modalManual');
    }

    function openEditModal(item) {
        document.getElementById('modalTitle').innerText = 'Perbarui Data Barang';
        document.getElementById('formManual').action = `/superadmin/barang/${item.id}`;
        document.getElementById('methodField').value = "PUT";
        
        document.getElementById('kode_barang').value = item.kode_barang;
        document.getElementById('nama_barang').value = item.nama_barang;
        document.getElementById('kategori').value = item.kategori;
        document.getElementById('satuan').value = item.satuan;
        document.getElementById('status').value = item.status;
        document.getElementById('stok_tersedia').value = item.stok_tersedia || 0;
        document.getElementById('stok_minimum').value = item.stok_minimum || 0;
        
        // Panggil 3 Tingkat Harga
        document.getElementById('harga_modal').value = item.harga_modal || '';
        document.getElementById('harga_minimum').value = item.harga_minimum || '';
        document.getElementById('harga_jual').value = item.harga_jual || '';
        
        toggleModal('modalManual');
    }

    function handleExcelDrop(e) {
        const file = e.target.files[0];
        if (!file) return;

        document.getElementById('uploadPrompt').innerText = file.name;
        const reader = new FileReader();
        
        reader.onload = function(event) {
            const data = new Uint8Array(event.target.result);
            const workbook = XLSX.read(data, { type: 'array' });
            const firstSheetName = workbook.SheetNames[0];
            const worksheet = workbook.Sheets[firstSheetName];
            
            parsedExcelData = XLSX.utils.sheet_to_json(worksheet);

            if(parsedExcelData.length === 0) {
                alert('File kosong atau format struktur tidak sesuai.');
                return;
            }

            const previewBody = document.getElementById('previewBody');
            previewBody.innerHTML = '';
            parsedExcelData.slice(0, 5).forEach(row => {
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td class="p-2 border-b font-medium text-indigo-600">${row['Kode Barang'] || row['kode_barang'] || '-'}</td>
                    <td class="p-2 border-b">${row['Nama Barang'] || row['nama_barang'] || '-'}</td>
                    <td class="p-2 border-b font-bold text-emerald-600">${row['Stok Tersedia'] || 0}</td>
                    <td class="p-2 border-b">${row['Harga Modal'] ? 'Rp ' + row['Harga Modal'].toLocaleString('id-ID') : '-'}</td>
                    <td class="p-2 border-b text-orange-500">${row['Harga Minimum'] ? 'Rp ' + row['Harga Minimum'].toLocaleString('id-ID') : '-'}</td>
                    <td class="p-2 border-b text-[#7367f0] font-bold">${row['Harga Jual'] ? 'Rp ' + row['Harga Jual'].toLocaleString('id-ID') : '-'}</td>
                    <td class="p-2 border-b">${row['Status'] || row['status'] || 'aktif'}</td>
                `;
                previewBody.appendChild(tr);
            });

            document.getElementById('previewArea').classList.remove('hidden');
            document.getElementById('btnProsesImport').removeAttribute('disabled');
        };
        
        reader.readAsArrayBuffer(file);
    }

    function submitImport() {
        if(parsedExcelData.length === 0) return;

        const btn = document.getElementById('btnProsesImport');
        btn.innerText = 'Memproses Data...';
        btn.setAttribute('disabled', 'true');

        fetch("{{ route('superadmin.barang.import') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ data: parsedExcelData })
        })
        .then(response => response.json())
        .then(res => {
            if(res.success) {
                alert(res.message);
                window.location.reload();
            } else {
                alert('Gagal: ' + res.message);
                btn.innerText = 'Mulai Eksekusi Import';
                btn.removeAttribute('disabled');
            }
        })
        .catch(err => {
            alert('Terjadi kesalahan koneksi sistem jaringan.');
            btn.innerText = 'Mulai Eksekusi Import';
            btn.removeAttribute('disabled');
        });
    }

    function downloadTemplate() {
        // Kolom disesuaikan dengan struktur Anti-Rugi Manajemen Harga
        const header = [["Kode Barang", "Nama Barang", "Kategori", "Satuan", "Status", "Stok Tersedia", "Stok Minimum", "Harga Modal", "Harga Minimum", "Harga Jual"]];
        const contohData = [
            ["BRG-001", "Busi Denso Iridium Premium", "Elektronik", "Pcs", "aktif", 50, 10, 50000, 55000, 75000],
            ["BRG-002", "Oli Motor Shell Helix 4L", "Pelumas", "Botol", "aktif", 120, 20, 300000, 320000, 350000]
        ];
        
        const worksheet = XLSX.utils.aoa_to_sheet(header.concat(contohData));
        const workbook = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(workbook, worksheet, "Template Barang Stok Harga");
        
        XLSX.writeFile(workbook, "Template_Import_Barang_Cerdas.xlsx");
    }
</script>
@endsection