@extends('layouts.admin')

@section('title', 'Manajemen User & Akses')

@section('content')
<div class="mb-6 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
    <div>
        <h3 class="font-bold text-[#4b465c] text-2xl">Manajemen User</h3>
        <p class="text-[#a8aaae] text-sm">Kelola akun pegawai, kata sandi, dan hak akses modul (ACL)</p>
    </div>
    <div class="flex flex-wrap gap-3">
        <button onclick="openAddModal()" class="bg-[#7367f0] hover:bg-[#6355e6] text-white font-medium py-2.5 px-4 rounded-xl text-sm shadow-[0_2px_6px_rgba(115,103,240,0.4)] transition-all flex items-center gap-2">
            <i class="fa-solid fa-user-plus text-base"></i> Tambah Pegawai
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
<div class="bg-red-100 text-red-700 p-4 rounded-xl mb-6 shadow-sm flex items-start gap-3">
    <i class="fa-solid fa-circle-exclamation text-lg mt-0.5"></i>
    <ul class="list-disc pl-4 text-sm font-medium">
        @foreach($errors->all() as $error) <li>{{ $error }}</li> @endforeach
    </ul>
</div>
@endif

<div class="bg-white rounded-xl shadow-[0_4px_18px_0_rgba(75,70,92,0.1)] overflow-hidden">
    <div class="p-5 border-b border-gray-100 flex flex-col sm:flex-row justify-between items-center gap-4">
        <h5 class="font-semibold text-[#4b465c] text-lg">Daftar Akun Sistem</h5>
        <form action="" method="GET" class="w-full sm:w-72">
            <div class="relative">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama, username, role..." class="w-full pl-10 pr-4 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-[#7367f0] focus:border-transparent">
                <i class="fa-solid fa-magnifying-glass absolute left-3 top-3 text-gray-400 text-sm"></i>
            </div>
        </form>
    </div>
    
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-[#f8f7fa] text-[#4b465c] uppercase text-xs font-semibold tracking-wider border-b border-gray-100">
                    <th class="py-4 px-6">Identitas Pegawai</th>
                    <th class="py-4 px-6 text-center">Role / Jabatan</th>
                    <th class="py-4 px-6 text-center">Status</th>
                    <th class="py-4 px-6 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 text-[#4b465c] text-[15px]">
                @foreach($users as $item)
                <tr class="hover:bg-slate-50/50 transition-colors">
                    <td class="py-4 px-6 flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full flex items-center justify-center font-bold text-white shadow-sm
                            {{ $item->role == 'superadmin' ? 'bg-[#7367f0]' : (in_array($item->role, ['kasir', 'admin']) ? 'bg-[#28c76f]' : ($item->role == 'sales' ? 'bg-[#00cfe8]' : 'bg-[#ff9f43]')) }}">
                            {{ substr($item->nama, 0, 1) }}
                        </div>
                        <div>
                            <p class="font-bold text-[#4b465c]">{{ $item->nama }}</p>
                            <p class="text-xs text-[#a8aaae]">@ {{ $item->username }}</p>
                        </div>
                    </td>
                    <td class="py-4 px-6 text-center">
                        @if($item->role == 'superadmin')
                            <span class="bg-[#7367f0]/10 text-[#7367f0] px-3 py-1 rounded-md text-xs font-bold uppercase"><i class="fa-solid fa-chess-king mr-1"></i> Super Admin</span>
                        @elseif(in_array($item->role, ['kasir', 'admin']))
                            <span class="bg-[#28c76f]/10 text-[#28c76f] px-3 py-1 rounded-md text-xs font-bold uppercase"><i class="fa-solid fa-desktop mr-1"></i> Kasir</span>
                        @elseif($item->role == 'sales')
                            <span class="bg-[#00cfe8]/10 text-[#00cfe8] px-3 py-1 rounded-md text-xs font-bold uppercase"><i class="fa-solid fa-briefcase mr-1"></i> Sales</span>
                        @else
                            <span class="bg-[#ff9f43]/10 text-[#ff9f43] px-3 py-1 rounded-md text-xs font-bold uppercase"><i class="fa-solid fa-boxes-stacked mr-1"></i> Gudang</span>
                        @endif
                    </td>
                    <td class="py-4 px-6 text-center">
                        @if($item->status == 'aktif')
                            <span class="bg-emerald-50 text-emerald-600 px-2.5 py-1 rounded-md text-xs font-semibold border border-emerald-100">Aktif</span>
                        @else
                            <span class="bg-red-50 text-red-500 px-2.5 py-1 rounded-md text-xs font-semibold border border-red-100">Nonaktif</span>
                        @endif
                    </td>
                    <td class="py-4 px-6 text-center">
                        <div class="flex items-center justify-center gap-3">
                            <button onclick="openEditModal({{ json_encode($item) }})" class="text-amber-500 hover:text-amber-600 transition-colors" title="Edit Data & Hak Akses"><i class="fa-regular fa-pen-to-square text-lg"></i></button>
                            @if(auth()->id() != $item->id)
                            <form action="{{ route('superadmin.user.destroy', $item->id) }}" method="POST" onsubmit="return confirm('Hapus akun ini secara permanen?');" class="inline">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-red-400 hover:text-red-500 transition-colors" title="Hapus Akun"><i class="fa-regular fa-trash-can text-lg"></i></button>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="p-4 border-t border-gray-100">
        {{ $users->appends(request()->query())->links() }}
    </div>
</div>

<div id="modalUser" class="fixed inset-0 z-50 overflow-y-auto hidden bg-black/40 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-4xl w-full shadow-xl overflow-hidden transform transition-all">
        <div class="px-6 py-4 bg-[#f8f7fa] border-b border-gray-100 flex justify-between items-center">
            <h3 id="modalTitle" class="font-bold text-[#4b465c] text-lg">Tambah Akun Pegawai</h3>
            <button onclick="toggleModal('modalUser')" class="text-gray-400 hover:text-gray-600 text-xl"><i class="fa-solid fa-xmark"></i></button>
        </div>
        
        <form id="formUser" action="{{ route('superadmin.user.store') }}" method="POST" class="p-6">
            @csrf
            <input type="hidden" id="methodField" name="_method" value="POST">
            
            <div class="flex flex-col md:flex-row gap-6 mb-4">
                <div class="w-full md:w-1/2 space-y-4">
                    <h5 class="text-xs font-bold text-[#7367f0] uppercase tracking-wider border-b pb-2">1. Identitas & Login</h5>
                    <div>
                        <label class="block text-sm font-semibold text-gray-600 mb-1">Nama Lengkap</label>
                        <input type="text" id="nama" name="nama" required class="w-full px-3 py-2 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#7367f0]">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-600 mb-1">Username</label>
                        <input type="text" id="username" name="username" required class="w-full px-3 py-2 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#7367f0] bg-gray-50">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-600 mb-1">Password</label>
                        <input type="password" id="password" name="password" class="w-full px-3 py-2 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#7367f0]" placeholder="Minimal 6 karakter">
                        <p id="passwordHelp" class="text-[11px] text-gray-400 mt-1">Isi untuk password baru/reset. Kosongkan jika tidak diubah.</p>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-sm font-semibold text-gray-600 mb-1">Jabatan (Role)</label>
                            <select id="role" name="role" required class="w-full px-3 py-2 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#7367f0]">
                                <option value="sales">Sales</option>
                                <option value="kasir">Kasir</option>
                                <option value="gudang">Gudang</option>
                                <option value="superadmin">Super Admin</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-600 mb-1">Status Akun</label>
                            <select id="status" name="status" class="w-full px-3 py-2 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#7367f0]">
                                <option value="aktif">Aktif</option>
                                <option value="nonaktif">Nonaktif</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="w-full md:w-1/2 space-y-4">
                    <h5 class="text-xs font-bold text-[#7367f0] uppercase tracking-wider border-b pb-2">2. Manajemen Hak Akses (ACL)</h5>
                    <div class="bg-gray-50 rounded-lg border border-gray-200 p-4 max-h-[350px] overflow-y-auto space-y-4">
                        
                        <div>
                            <p class="text-xs font-bold text-gray-500 uppercase mb-2">Modul Master & Harga</p>
                            <label class="flex items-center gap-2 mb-2 cursor-pointer hover:bg-gray-200/50 p-1.5 rounded transition">
                                <input type="checkbox" name="hak_akses[]" value="master_barang" class="w-4 h-4 text-[#7367f0] rounded border-gray-300">
                                <span class="text-sm font-medium text-gray-700">Akses Master Barang & Stok Awal</span>
                            </label>
                            <label class="flex items-center gap-2 mb-2 cursor-pointer hover:bg-gray-200/50 p-1.5 rounded transition">
                                <input type="checkbox" name="hak_akses[]" value="manajemen_harga" class="w-4 h-4 text-[#7367f0] rounded border-gray-300">
                                <span class="text-sm font-medium text-gray-700">Akses Manajemen Harga</span>
                            </label>
                        </div>

                        <div>
                            <p class="text-xs font-bold text-gray-500 uppercase mb-2">Operasional Utama</p>
                            <label class="flex items-center gap-2 mb-2 cursor-pointer hover:bg-gray-200/50 p-1.5 rounded transition">
                                <input type="checkbox" name="hak_akses[]" value="transaksi_sales" class="w-4 h-4 text-[#00cfe8] rounded border-gray-300">
                                <span class="text-sm font-medium text-gray-700">Input Penjualan / Transaksi</span>
                            </label>
                            <label class="flex items-center gap-2 mb-2 cursor-pointer hover:bg-gray-200/50 p-1.5 rounded transition">
                                <input type="checkbox" name="hak_akses[]" value="validasi_kasir" class="w-4 h-4 text-[#28c76f] rounded border-gray-300">
                                <span class="text-sm font-medium text-gray-700">Validasi & Cetak Nota (Kasir)</span>
                            </label>
                            <label class="flex items-center gap-2 mb-2 cursor-pointer hover:bg-gray-200/50 p-1.5 rounded transition">
                                <input type="checkbox" name="hak_akses[]" value="stok_gudang" class="w-4 h-4 text-[#ff9f43] rounded border-gray-300">
                                <span class="text-sm font-medium text-gray-700">Penyiapan Permintaan Gudang</span>
                            </label>
                        </div>

                        <div>
                            <p class="text-xs font-bold text-gray-500 uppercase mb-2">Laporan & Administrasi</p>
                            <label class="flex items-center gap-2 mb-2 cursor-pointer hover:bg-gray-200/50 p-1.5 rounded transition">
                                <input type="checkbox" name="hak_akses[]" value="laporan_penjualan" class="w-4 h-4 text-[#7367f0] rounded border-gray-300">
                                <span class="text-sm font-medium text-gray-700">Akses Laporan & Export Excel</span>
                            </label>
                            <label class="flex items-center gap-2 mb-2 cursor-pointer hover:bg-gray-200/50 p-1.5 rounded transition">
                                <input type="checkbox" name="hak_akses[]" value="kelola_bonus" class="w-4 h-4 text-[#7367f0] rounded border-gray-300">
                                <span class="text-sm font-medium text-gray-700">Setting & Pencairan Bonus</span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer hover:bg-gray-200/50 p-1.5 rounded transition">
                                <input type="checkbox" name="hak_akses[]" value="manajemen_user" class="w-4 h-4 text-red-500 rounded border-gray-300">
                                <span class="text-sm font-medium text-red-600">Kelola Akun & Hak Akses (God Mode)</span>
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <div class="pt-4 flex justify-end gap-3 border-t border-gray-100 mt-2">
                <button type="button" onclick="toggleModal('modalUser')" class="px-4 py-2 border border-gray-200 rounded-lg text-gray-600 hover:bg-gray-50 text-sm font-medium">Batal</button>
                <button type="submit" class="px-4 py-2 bg-[#7367f0] hover:bg-[#6355e6] text-white rounded-lg text-sm font-medium shadow-[0_2px_6px_rgba(115,103,240,0.4)]">Simpan Akun</button>
            </div>
        </form>
    </div>
</div>

<script>
    function toggleModal(modalId) {
        document.getElementById(modalId).classList.toggle('hidden');
    }

    function resetCheckboxes() {
        document.querySelectorAll('input[name="hak_akses[]"]').forEach(cb => cb.checked = false);
    }

    function openAddModal() {
        document.getElementById('modalTitle').innerText = 'Tambah Akun Pegawai';
        document.getElementById('formUser').action = "{{ route('superadmin.user.store') }}";
        document.getElementById('methodField').value = "POST";
        document.getElementById('formUser').reset();
        document.getElementById('password').required = true;
        resetCheckboxes();
        toggleModal('modalUser');
    }

    function openEditModal(item) {
        document.getElementById('modalTitle').innerText = 'Perbarui Data & Hak Akses';
        document.getElementById('formUser').action = `/superadmin/user/${item.id}`;
        document.getElementById('methodField').value = "PUT";
        
        document.getElementById('nama').value = item.nama;
        document.getElementById('username').value = item.username;
        document.getElementById('role').value = item.role;
        document.getElementById('status').value = item.status;
        
        document.getElementById('password').required = false;
        document.getElementById('password').value = '';

        resetCheckboxes();

        // Mengembalikan centang dari database
        if (item.hak_akses) {
            try {
                const aksesArray = JSON.parse(item.hak_akses);
                aksesArray.forEach(val => {
                    const cb = document.querySelector(`input[name="hak_akses[]"][value="${val}"]`);
                    if (cb) cb.checked = true;
                });
            } catch (e) { console.error("Error parsing hak_akses JSON"); }
        }
        
        toggleModal('modalUser');
    }
</script>
@endsection