@extends('layouts.admin')
@section('title', 'Master Divisi & Jabatan')
@section('header_title', 'Master Divisi & Jabatan')

@section('content')
<div x-data="departmentCrud()" class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    
    <!-- KOLOM KIRI: KELOLA DIVISI -->
    <div class="lg:col-span-1 space-y-6">
        <div class="bg-base p-6 rounded-2xl border border-gray-100 shadow-sm">
            <h3 class="font-bold text-lg text-secondary mb-4"><i class="fa-solid fa-layer-group text-primary mr-2"></i> Data Divisi</h3>
            
            <!-- Form Tambah Divisi Cepat -->
            <form @submit.prevent="submitDivisi" class="flex gap-2 mb-6">
                <input type="text" x-model="divisiName" placeholder="Nama Divisi Baru..." required 
                       class="w-full px-4 py-2 rounded-xl border border-gray-200 focus:border-primary focus:ring-1 focus:ring-primary outline-none text-sm">
                
                <!-- Tombol Submit Divisi -->
                <button type="submit" 
                        class="bg-primary hover:bg-primary_hover text-white px-5 py-2 rounded-xl text-sm font-semibold transition z-10 relative flex items-center justify-center min-w-[90px]">
                    <span x-show="!isDivisiLoading">Tambah</span>
                    <span x-show="isDivisiLoading"><i class="fa-solid fa-spinner fa-spin"></i></span>
                </button>
            </form>

            <!-- List Divisi -->
            <ul class="space-y-3">
                @forelse($divisions as $div)
                <li class="flex justify-between items-center bg-gray-50 px-4 py-3 rounded-xl border border-gray-100">
                    <div>
                        <p class="font-medium text-sm text-secondary">{{ $div->nama_divisi }}</p>
                        <p class="text-xs text-gray-400">{{ $div->positions_count }} Jabatan</p>
                    </div>
                    <button type="button" @click="deleteDivisi({{ $div->id }})" class="text-red-400 hover:text-red-600 transition w-8 h-8 flex items-center justify-center rounded-lg hover:bg-red-50 relative z-10">
                        <i class="fa-solid fa-trash-can text-sm"></i>
                    </button>
                </li>
                @empty
                <li class="text-center text-gray-500 text-sm py-4">Belum ada divisi.</li>
                @endforelse
            </ul>
        </div>
    </div>

    <!-- KOLOM KANAN: KELOLA JABATAN -->
    <div class="lg:col-span-2 space-y-6">
        <div class="bg-base p-6 rounded-2xl border border-gray-100 shadow-sm">
            <div class="flex justify-between items-center mb-6">
                <h3 class="font-bold text-lg text-secondary"><i class="fa-solid fa-briefcase text-primary mr-2"></i> Data Jabatan</h3>
                <button type="button" @click="openModal('add')" class="bg-primary hover:bg-primary_hover text-white px-4 py-2 rounded-xl text-sm font-semibold transition flex items-center gap-2 shadow-lg shadow-primary/30 relative z-10">
                    <i class="fa-solid fa-plus"></i> Tambah Jabatan
                </button>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-50 border-b border-gray-100 text-sm text-gray-500">
                            <th class="px-4 py-3 font-semibold">Nama Jabatan</th>
                            <th class="px-4 py-3 font-semibold">Bagian Divisi</th>
                            <th class="px-4 py-3 font-semibold text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($positions as $pos)
                        <tr class="hover:bg-gray-50/50 transition">
                            <td class="px-4 py-3 font-medium text-secondary">{{ $pos->nama_jabatan }}</td>
                            <td class="px-4 py-3 text-sm text-gray-600">
                                <span class="bg-blue-50 text-blue-600 px-2.5 py-1 rounded-md text-xs font-medium">{{ $pos->division->nama_divisi }}</span>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <button type="button" @click="openModal('edit', {{ $pos->id }})" class="text-primary hover:bg-primary/10 w-8 h-8 rounded-lg transition relative z-10"><i class="fa-solid fa-pen-to-square text-sm"></i></button>
                                <button type="button" @click="deleteJabatan({{ $pos->id }})" class="text-red-500 hover:bg-red-50 w-8 h-8 rounded-lg transition relative z-10"><i class="fa-solid fa-trash text-sm"></i></button>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="3" class="px-4 py-6 text-center text-gray-500 text-sm">Belum ada data jabatan.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal Form Jabatan -->
    <div x-show="isModalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div x-show="isModalOpen" x-transition.opacity @click="closeModal()" class="fixed inset-0 bg-secondary/50 backdrop-blur-sm"></div>
        <div x-show="isModalOpen" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100" class="bg-base w-full max-w-md rounded-2xl shadow-xl z-10 overflow-hidden relative">
            <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50">
                <h3 class="font-bold text-lg" x-text="modalTitle"></h3>
                <button type="button" @click="closeModal()" class="text-gray-400 hover:text-red-500"><i class="fa-solid fa-xmark text-xl"></i></button>
            </div>
            <form id="jabatanForm" @submit.prevent="submitJabatan">
                <div class="p-6 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Pilih Divisi</label>
                        <select name="division_id" x-model="formJabatan.division_id" required class="w-full px-4 py-2 rounded-xl border border-gray-200 focus:border-primary focus:ring-1 focus:ring-primary outline-none bg-white">
                            <option value="">-- Pilih Divisi --</option>
                            @foreach($divisions as $div)
                                <option value="{{ $div->id }}">{{ $div->nama_divisi }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nama Jabatan</label>
                        <input type="text" name="nama_jabatan" x-model="formJabatan.nama_jabatan" required class="w-full px-4 py-2 rounded-xl border border-gray-200 focus:border-primary focus:ring-1 focus:ring-primary outline-none">
                    </div>
                </div>
                <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex justify-end gap-3">
                    <button type="button" @click="closeModal()" class="px-5 py-2 text-sm font-medium text-gray-600 hover:bg-gray-200 rounded-xl transition relative z-10">Batal</button>
                    <button type="submit" class="px-5 py-2 text-sm font-medium text-white bg-primary hover:bg-primary_hover rounded-xl transition shadow-lg shadow-primary/30 flex items-center gap-2 relative z-10">
                        <span x-show="isJabatanLoading"><i class="fa-solid fa-spinner fa-spin"></i></span>
                        <span x-text="isEdit ? 'Update' : 'Simpan'"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<!-- Pastikan jQuery diload agar AJAX berfungsi -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('departmentCrud', () => ({
            divisiName: '',
            isDivisiLoading: false,
            
            isModalOpen: false, 
            isEdit: false, 
            isJabatanLoading: false, 
            editId: null,
            modalTitle: 'Tambah Jabatan',
            formJabatan: { division_id: '', nama_jabatan: '' },

            // FUNGSI AJAX DIVISI
            submitDivisi() {
                if(this.divisiName.trim() === '') return;
                
                this.isDivisiLoading = true;
                $.ajax({
                    url: '/admin/departemen/divisi',
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        nama_divisi: this.divisiName
                    },
                    success: (res) => {
                        this.isDivisiLoading = false;
                        this.divisiName = '';
                        Swal.fire({ icon: 'success', title: 'Berhasil', text: res.message, showConfirmButton: false, timer: 1500 })
                        .then(() => location.reload());
                    },
                    error: (xhr) => {
                        this.isDivisiLoading = false;
                        let msg = xhr.responseJSON.message || 'Terjadi kesalahan sistem';
                        Swal.fire({ icon: 'error', title: 'Oops', text: msg });
                    }
                });
            },

            deleteDivisi(id) {
                Swal.fire({ 
                    title: 'Hapus Divisi?', 
                    text: "Semua jabatan di divisi ini akan ikut terhapus!", 
                    icon: 'warning', 
                    showCancelButton: true, 
                    confirmButtonColor: '#EF4444', 
                    cancelButtonColor: '#9CA3AF',
                    confirmButtonText: 'Ya, Hapus!' 
                }).then((res) => {
                    if (res.isConfirmed) {
                        $.ajax({ 
                            url: `/admin/departemen/divisi/${id}`, 
                            type: 'POST', 
                            data: { _method: 'DELETE', _token: '{{ csrf_token() }}' },
                            success: (r) => { 
                                Swal.fire({ icon: 'success', title: 'Terhapus', text: r.message, showConfirmButton: false, timer: 1500 })
                                .then(() => location.reload()); 
                            }
                        });
                    }
                });
            },

            // FUNGSI AJAX JABATAN
            openModal(type, id = null) {
                this.isEdit = type === 'edit';
                this.modalTitle = this.isEdit ? 'Edit Jabatan' : 'Tambah Jabatan';
                this.editId = id;
                this.formJabatan = { division_id: '', nama_jabatan: '' }; // Reset form
                
                if(this.isEdit) {
                    $.get(`/admin/departemen/jabatan/${id}/edit`, (data) => {
                        this.formJabatan.division_id = data.division_id;
                        this.formJabatan.nama_jabatan = data.nama_jabatan;
                        this.isModalOpen = true;
                    });
                } else {
                    this.isModalOpen = true;
                }
            },
            
            closeModal() { 
                this.isModalOpen = false; 
            },
            
            submitJabatan() {
                this.isJabatanLoading = true;
                let url = this.isEdit ? `/admin/departemen/jabatan/${this.editId}` : '/admin/departemen/jabatan';
                let method = this.isEdit ? 'PUT' : 'POST';
                
                $.ajax({
                    url: url, 
                    type: 'POST', 
                    data: { 
                        _method: method, 
                        _token: '{{ csrf_token() }}', 
                        division_id: this.formJabatan.division_id,
                        nama_jabatan: this.formJabatan.nama_jabatan
                    },
                    success: (res) => {
                        this.isJabatanLoading = false; 
                        this.closeModal();
                        Swal.fire({ icon: 'success', title: 'Berhasil', text: res.message, showConfirmButton: false, timer: 1500 })
                        .then(() => location.reload());
                    },
                    error: (xhr) => {
                        this.isJabatanLoading = false;
                        let msg = xhr.responseJSON.errors ? Object.values(xhr.responseJSON.errors)[0][0] : 'Error';
                        Swal.fire({ icon: 'error', title: 'Oops', text: msg });
                    }
                });
            },
            
            deleteJabatan(id) {
                Swal.fire({ 
                    title: 'Hapus Jabatan?', 
                    icon: 'warning', 
                    showCancelButton: true, 
                    confirmButtonColor: '#EF4444', 
                    cancelButtonColor: '#9CA3AF',
                    confirmButtonText: 'Ya, Hapus!' 
                }).then((res) => {
                    if (res.isConfirmed) {
                        $.ajax({ 
                            url: `/admin/departemen/jabatan/${id}`, 
                            type: 'POST', 
                            data: { _method: 'DELETE', _token: '{{ csrf_token() }}' },
                            success: (r) => { 
                                Swal.fire({ icon: 'success', title: 'Terhapus', text: r.message, showConfirmButton: false, timer: 1500 })
                                .then(() => location.reload()); 
                            }
                        });
                    }
                });
            }
        }));
    });
</script>
@endpush