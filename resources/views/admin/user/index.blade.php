@extends('layouts.admin')
@section('title', 'Manajemen User & Hak Akses')
@section('header_title', 'Manajemen Hak Akses')

@section('content')
<div x-data="userManager()" class="space-y-6">

    <!-- Header Card -->
    <div class="bg-base p-6 rounded-2xl border border-gray-100 shadow-sm flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <h3 class="font-bold text-xl text-secondary">Manajemen Akun User</h3>
            <p class="text-sm text-gray-500 mt-1">Kelola email, password, dan hak akses khusus administrator sistem.</p>
        </div>
        <button @click="openModal('add')" class="bg-primary hover:bg-primary_hover text-white px-5 py-2.5 rounded-xl text-sm font-semibold transition flex items-center gap-2 shadow-lg shadow-primary/30">
            <i class="fa-solid fa-user-shield"></i> Tambah Admin Baru
        </button>
    </div>

    <!-- Peringatan Visual -->
    <div class="bg-blue-50 text-blue-700 p-4 rounded-xl text-sm border border-blue-100 flex gap-3">
        <i class="fa-solid fa-circle-info text-xl mt-0.5"></i>
        <div>
            <p class="font-bold">Informasi Alur Sistem</p>
            <p>Untuk mendaftarkan akun <b>Pegawai</b> baru, silakan gunakan menu <a href="{{ route('admin.karyawan.index') }}" class="font-bold underline">Master Karyawan</a> agar data jabatan dan divisinya terintegrasi. Menu ini dikhususkan untuk menambah Super Admin dan me-reset password akun yang sudah ada.</p>
        </div>
    </div>

    <!-- Table Data -->
    <div class="bg-base rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-100 text-sm text-gray-500">
                        <th class="px-6 py-4 font-semibold">Informasi Akun</th>
                        <th class="px-6 py-4 font-semibold">Hak Akses (Role)</th>
                        <th class="px-6 py-4 font-semibold">Terdaftar Pada</th>
                        <th class="px-6 py-4 font-semibold text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($users as $item)
                    <tr class="hover:bg-gray-50/50 transition">
                        <td class="px-6 py-4 flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full bg-gray-100 border border-gray-200 flex items-center justify-center text-gray-500 font-bold uppercase">
                                {{ substr($item->name, 0, 1) }}
                            </div>
                            <div>
                                <p class="font-semibold text-secondary">{{ $item->name }}</p>
                                <p class="text-xs text-gray-500">{{ $item->email }}</p>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            @if($item->role == 'admin')
                                <span class="bg-purple-100 text-purple-700 px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider border border-purple-200"><i class="fa-solid fa-shield-halved mr-1"></i> Administrator</span>
                            @else
                                <span class="bg-gray-100 text-gray-600 px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider border border-gray-200"><i class="fa-solid fa-user mr-1"></i> Pegawai</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600">
                            {{ $item->created_at->translatedFormat('d M Y, H:i') }}
                        </td>
                        <td class="px-6 py-4 text-right">
                            <button @click="openModal('edit', {{ $item->id }})" class="text-primary hover:bg-primary/10 w-8 h-8 rounded-lg transition" title="Edit / Reset Password"><i class="fa-solid fa-pen-to-square"></i></button>
                            
                            <!-- Jangan tampilkan tombol hapus untuk diri sendiri -->
                            @if($item->id !== Auth::id())
                                <button @click="deleteData({{ $item->id }})" class="text-red-500 hover:bg-red-50 w-8 h-8 rounded-lg transition" title="Hapus Akun"><i class="fa-solid fa-trash"></i></button>
                            @else
                                <span class="text-gray-300 w-8 inline-block text-center" title="Akun Anda Saat Ini"><i class="fa-solid fa-ban"></i></span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="px-6 py-8 text-center text-gray-500">Belum ada data user.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal Form -->
    <div x-show="isModalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div x-show="isModalOpen" x-transition.opacity @click="closeModal()" class="fixed inset-0 bg-secondary/50 backdrop-blur-sm"></div>
        <div x-show="isModalOpen" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100" class="bg-base w-full max-w-md rounded-2xl shadow-xl z-10 overflow-hidden relative">
             
            <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50">
                <h3 class="font-bold text-lg" x-text="modalTitle"></h3>
                <button type="button" @click="closeModal()" class="text-gray-400 hover:text-red-500"><i class="fa-solid fa-xmark text-xl"></i></button>
            </div>

            <form id="userForm" @submit.prevent="submitForm">
                <div class="p-6 space-y-4">
                    
                    <!-- Info Khusus Saat Tambah Baru -->
                    <div x-show="!isEdit" class="bg-purple-50 text-purple-700 p-3 rounded-xl border border-purple-100 text-xs mb-2">
                        Form ini didedikasikan untuk menambah akun pengelola (Admin) baru.
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nama Lengkap</label>
                        <input type="text" name="name" x-model="formData.name" required class="w-full px-4 py-2 rounded-xl border border-gray-200 focus:border-primary outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Alamat Email (Untuk Login)</label>
                        <input type="email" name="email" x-model="formData.email" required class="w-full px-4 py-2 rounded-xl border border-gray-200 focus:border-primary outline-none">
                    </div>

                    <!-- Input Role (Tersembunyi jika tambah baru karena otomatis Admin, hanya jadi label saat Edit) -->
                    <div x-show="isEdit">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Hak Akses</label>
                        <input type="text" x-model="formData.role === 'admin' ? 'Administrator' : 'Pegawai'" class="w-full px-4 py-2 rounded-xl border border-gray-200 bg-gray-100 text-gray-500 outline-none" readonly>
                        <input type="hidden" name="role" x-model="formData.role">
                        <p class="text-[10px] text-gray-400 mt-1">Hak akses tidak dapat diubah setelah akun terdaftar.</p>
                    </div>
                    
                    <div x-show="!isEdit">
                        <input type="hidden" name="role" value="admin">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Password <span x-show="isEdit" class="text-xs text-gray-400 font-normal">(Kosongkan jika tidak ingin mereset)</span></label>
                        <input type="password" name="password" :required="!isEdit" minlength="6" placeholder="Minimal 6 karakter" class="w-full px-4 py-2 rounded-xl border border-gray-200 focus:border-primary outline-none">
                    </div>
                </div>
                <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex justify-end gap-3">
                    <button type="button" @click="closeModal()" class="px-5 py-2 text-sm font-medium text-gray-600 hover:bg-gray-200 rounded-xl transition">Batal</button>
                    <button type="submit" class="px-5 py-2 text-sm font-medium text-white bg-primary hover:bg-primary_hover rounded-xl transition shadow-lg flex items-center gap-2">
                        <span x-show="isLoading"><i class="fa-solid fa-spinner fa-spin"></i></span>
                        <span x-text="isEdit ? 'Update Akun' : 'Simpan Admin'"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('userManager', () => ({
            isModalOpen: false, isEdit: false, isLoading: false, editId: null, modalTitle: 'Tambah Admin Baru',
            formData: { name: '', email: '', role: 'admin' },

            openModal(type, id = null) {
                this.isEdit = type === 'edit';
                this.modalTitle = this.isEdit ? 'Edit Akun & Reset Password' : 'Tambah Admin Baru';
                this.editId = id;
                document.getElementById('userForm').reset();
                
                if (this.isEdit) {
                    $.get(`/admin/users/${id}/edit`, (data) => {
                        this.formData.name = data.name;
                        this.formData.email = data.email;
                        this.formData.role = data.role;
                        this.isModalOpen = true;
                    });
                } else {
                    this.formData = { name: '', email: '', role: 'admin' };
                    this.isModalOpen = true;
                }
            },

            closeModal() { this.isModalOpen = false; },

            submitForm(e) {
                this.isLoading = true;
                let form = document.getElementById('userForm');
                let formDataToSend = new FormData(form);
                
                let url = this.isEdit ? `/admin/users/${this.editId}` : `/admin/users`;
                if(this.isEdit) formDataToSend.append('_method', 'PUT'); 
                
                $.ajax({
                    url: url, type: 'POST', data: formDataToSend, processData: false, contentType: false,
                    success: (response) => {
                        this.isLoading = false; this.closeModal();
                        Toast.fire({ icon: 'success', title: response.message });
                        setTimeout(() => location.reload(), 1000);
                    },
                    error: (xhr) => {
                        this.isLoading = false;
                        let errorMsg = xhr.responseJSON.errors ? Object.values(xhr.responseJSON.errors)[0][0] : 'Terjadi kesalahan sistem.';
                        Toast.fire({ icon: 'error', title: errorMsg });
                    }
                });
            },

            deleteData(id) {
                Swal.fire({ title: 'Yakin Hapus User?', text: "Semua data profil dan absensinya juga akan ikut terhapus!", icon: 'warning', showCancelButton: true, confirmButtonColor: '#EF4444', confirmButtonText: 'Ya, Hapus!' })
                .then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({ url: `/admin/users/${id}`, type: 'POST', data: { _method: 'DELETE' },
                            success: (response) => {
                                Toast.fire({ icon: 'success', title: response.message });
                                setTimeout(() => location.reload(), 1000);
                            },
                            error: (xhr) => {
                                let msg = xhr.responseJSON?.errors ? Object.values(xhr.responseJSON.errors)[0][0] : 'Gagal menghapus user.';
                                Toast.fire({ icon: 'error', title: msg });
                            }
                        });
                    }
                });
            }
        }));
    });
</script>
@endpush