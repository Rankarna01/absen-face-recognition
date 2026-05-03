@extends('layouts.admin')
@section('title', 'Master Karyawan')
@section('header_title', 'Master Karyawan')

@section('content')
<!-- Inject data divisi & posisi ke AlpineJS agar bisa difilter di sisi client -->
<div x-data="employeeCrud({{ $divisions->toJson() }}, {{ $positions->toJson() }})" class="space-y-6">
    
    <!-- Header Card -->
    <div class="bg-base p-6 rounded-2xl border border-gray-100 shadow-sm flex justify-between items-center">
        <div>
            <h3 class="font-bold text-lg text-secondary">Data Karyawan</h3>
            <p class="text-sm text-gray-500">Kelola informasi, jabatan, dan akses pegawai.</p>
        </div>
        <button @click="openModal('add')" class="bg-primary hover:bg-primary_hover text-white px-5 py-2.5 rounded-xl text-sm font-semibold transition flex items-center gap-2 shadow-lg shadow-primary/30">
            <i class="fa-solid fa-plus"></i> Tambah Karyawan
        </button>
    </div>

    <!-- Table Data -->
    <div class="bg-base rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-100 text-sm text-gray-500">
                        <th class="px-6 py-4 font-semibold">Profil</th>
                        <th class="px-6 py-4 font-semibold">NIP / Jabatan</th>
                        <th class="px-6 py-4 font-semibold">Divisi</th>
                        <th class="px-6 py-4 font-semibold text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($karyawan as $item)
                    <tr class="hover:bg-gray-50/50 transition">
                        <td class="px-6 py-4 flex items-center gap-3">
                            <img src="{{ $item->employee && $item->employee->foto ? asset('storage/'.$item->employee->foto) : 'https://ui-avatars.com/api/?name='.$item->name.'&background=F5A623&color=fff' }}" class="w-10 h-10 rounded-full object-cover border border-gray-200">
                            <div>
                                <p class="font-semibold text-secondary">{{ $item->name }}</p>
                                <p class="text-xs text-gray-500">{{ $item->email }}</p>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <p class="font-medium text-secondary">{{ $item->nip }}</p>
                            <!-- Tampilkan relasi Jabatan -->
                            <p class="text-xs text-gray-500 font-medium bg-gray-100 inline-block px-2 py-0.5 rounded mt-1">
                                {{ $item->employee->position->nama_jabatan ?? 'Belum diatur' }}
                            </p>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600">
                            <!-- Tampilkan relasi Divisi -->
                            {{ $item->employee->division->nama_divisi ?? 'Belum diatur' }}
                        </td>
                        <td class="px-6 py-4 text-right">
                            <button @click="openModal('edit', {{ $item->id }})" class="text-primary hover:bg-primary/10 w-8 h-8 rounded-lg transition"><i class="fa-solid fa-pen-to-square"></i></button>
                            <button @click="deleteData({{ $item->id }})" class="text-red-500 hover:bg-red-50 w-8 h-8 rounded-lg transition"><i class="fa-solid fa-trash"></i></button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="px-6 py-8 text-center text-gray-500">Belum ada data karyawan.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal Form -->
    <div x-show="isModalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div x-show="isModalOpen" x-transition.opacity @click="closeModal()" class="fixed inset-0 bg-secondary/50 backdrop-blur-sm"></div>
        
        <div x-show="isModalOpen" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100" class="bg-base w-full max-w-2xl rounded-2xl shadow-xl z-10 overflow-hidden">
             
            <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50">
                <h3 class="font-bold text-lg" x-text="modalTitle"></h3>
                <button @click="closeModal()" class="text-gray-400 hover:text-red-500"><i class="fa-solid fa-xmark text-xl"></i></button>
            </div>

            <form id="employeeForm" @submit.prevent="submitForm">
                <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-5 max-h-[70vh] overflow-y-auto">
                    <div class="col-span-2 md:col-span-1">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nama Lengkap</label>
                        <input type="text" name="name" x-model="formData.name" required class="w-full px-4 py-2 rounded-xl border border-gray-200 focus:border-primary focus:ring-1 focus:ring-primary outline-none">
                    </div>
                    <div class="col-span-2 md:col-span-1">
                        <label class="block text-sm font-medium text-gray-700 mb-1">NIP</label>
                        <input type="text" name="nip" x-model="formData.nip" required class="w-full px-4 py-2 rounded-xl border border-gray-200 focus:border-primary focus:ring-1 focus:ring-primary outline-none">
                    </div>
                    <div class="col-span-2 md:col-span-1">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                        <input type="email" name="email" x-model="formData.email" required class="w-full px-4 py-2 rounded-xl border border-gray-200 focus:border-primary focus:ring-1 focus:ring-primary outline-none">
                    </div>
                    <div class="col-span-2 md:col-span-1">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Password <span x-show="isEdit" class="text-xs text-gray-400 font-normal">(Opsional)</span></label>
                        <input type="password" name="password" :required="!isEdit" class="w-full px-4 py-2 rounded-xl border border-gray-200 focus:border-primary focus:ring-1 focus:ring-primary outline-none">
                    </div>
                    
                    <!-- Select Divisi (Dinamis dari Database) -->
                    <div class="col-span-2 md:col-span-1">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Divisi</label>
                        <select name="division_id" x-model="formData.division_id" required class="w-full px-4 py-2 rounded-xl border border-gray-200 focus:border-primary focus:ring-1 focus:ring-primary outline-none bg-white">
                            <option value="">-- Pilih Divisi --</option>
                            <template x-for="div in divisionsList" :key="div.id">
                                <option :value="div.id" x-text="div.nama_divisi"></option>
                            </template>
                        </select>
                    </div>

                    <!-- Select Jabatan (Terfilter berdasarkan Divisi yg dipilih) -->
                    <div class="col-span-2 md:col-span-1">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Jabatan</label>
                        <select name="position_id" x-model="formData.position_id" required class="w-full px-4 py-2 rounded-xl border border-gray-200 focus:border-primary focus:ring-1 focus:ring-primary outline-none bg-white">
                            <option value="">-- Pilih Jabatan --</option>
                            <template x-for="pos in filteredPositions" :key="pos.id">
                                <option :value="pos.id" x-text="pos.nama_jabatan"></option>
                            </template>
                        </select>
                    </div>

                    <div class="col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Upload Foto Profile</label>
                        <input type="file" name="foto" accept="image/*" class="w-full px-4 py-2 rounded-xl border border-gray-200 focus:border-primary focus:ring-1 focus:ring-primary outline-none">
                    </div>
                </div>
                <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex justify-end gap-3">
                    <button type="button" @click="closeModal()" class="px-5 py-2 text-sm font-medium text-gray-600 hover:bg-gray-200 rounded-xl transition">Batal</button>
                    <button type="submit" class="px-5 py-2 text-sm font-medium text-white bg-primary hover:bg-primary_hover rounded-xl transition shadow-lg shadow-primary/30 flex items-center gap-2">
                        <span x-show="isLoading"><i class="fa-solid fa-spinner fa-spin"></i></span>
                        <span x-text="isEdit ? 'Simpan Perubahan' : 'Simpan Karyawan'"></span>
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
        // Menerima data master dari Blade
        Alpine.data('employeeCrud', (divisionsData, positionsData) => ({
            divisionsList: divisionsData,
            allPositions: positionsData,
            
            isModalOpen: false, isEdit: false, isLoading: false, editId: null, modalTitle: 'Tambah Karyawan',
            formData: { name: '', nip: '', email: '', division_id: '', position_id: '' },

            // Auto-filter Jabatan saat Divisi diganti
            get filteredPositions() {
                if (!this.formData.division_id) return [];
                return this.allPositions.filter(p => p.division_id == this.formData.division_id);
            },

            // Jika Division diganti secara manual, reset pilihan Jabatan agar tidak error
            init() {
                this.$watch('formData.division_id', (value, oldValue) => {
                    // Hanya reset jika di-trigger oleh interaksi user, bukan saat load edit data
                    if (oldValue !== '' && this.isModalOpen) {
                        this.formData.position_id = '';
                    }
                });
            },

            openModal(type, id = null) {
                this.isEdit = type === 'edit';
                this.modalTitle = this.isEdit ? 'Edit Data Karyawan' : 'Tambah Karyawan';
                this.editId = id;
                document.getElementById('employeeForm').reset();
                
                if (this.isEdit) {
                    $.get(`/admin/karyawan/${id}/edit`, (data) => {
                        this.formData.name = data.name;
                        this.formData.nip = data.nip;
                        this.formData.email = data.email;
                        this.formData.division_id = data.employee?.division_id || '';
                        
                        // Set timeout kecil agar Alpine merender opsi jabatan dulu, baru pilih valuenya
                        setTimeout(() => {
                            this.formData.position_id = data.employee?.position_id || '';
                        }, 50);

                        this.isModalOpen = true;
                    });
                } else {
                    this.formData = { name: '', nip: '', email: '', division_id: '', position_id: '' };
                    this.isModalOpen = true;
                }
            },

            closeModal() { this.isModalOpen = false; },

            submitForm(e) {
                this.isLoading = true;
                let form = document.getElementById('employeeForm');
                let formDataToSend = new FormData(form);
                
                let url = this.isEdit ? `/admin/karyawan/${this.editId}` : `/admin/karyawan`;
                if(this.isEdit) formDataToSend.append('_method', 'PUT'); 
                formDataToSend.append('_token', '{{ csrf_token() }}');

                $.ajax({
                    url: url, type: 'POST', data: formDataToSend, processData: false, contentType: false,
                    success: (response) => {
                        this.isLoading = false; this.closeModal();
                        Swal.fire({ icon: 'success', title: 'Berhasil!', text: response.message, showConfirmButton: false, timer: 1500 })
                        .then(() => location.reload());
                    },
                    error: (xhr) => {
                        this.isLoading = false;
                        let errorMsg = xhr.responseJSON.errors ? Object.values(xhr.responseJSON.errors)[0][0] : 'Terjadi kesalahan sistem.';
                        Swal.fire({ icon: 'error', title: 'Oops...', text: errorMsg });
                    }
                });
            },

            deleteData(id) {
                Swal.fire({ title: 'Yakin Hapus Data?', text: "Semua data karyawan ini akan hilang!", icon: 'warning', showCancelButton: true, confirmButtonColor: '#EF4444', confirmButtonText: 'Ya, Hapus!' })
                .then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({ url: `/admin/karyawan/${id}`, type: 'POST', data: { _method: 'DELETE', _token: '{{ csrf_token() }}' },
                            success: (response) => {
                                Swal.fire({ icon: 'success', title: 'Terhapus!', text: response.message, showConfirmButton: false, timer: 1500 })
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