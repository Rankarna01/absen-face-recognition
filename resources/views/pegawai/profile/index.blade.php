@extends('layouts.pegawai')
@section('title', 'Profil & Pengaturan')

@section('content')
<div class="p-5 flex flex-col h-full" x-data="profileManager()">
    
    <!-- Header Back -->
    <div class="flex items-center gap-4 mb-6 relative">
        <a href="{{ route('pegawai.beranda') }}" class="absolute left-0 w-10 h-10 bg-gray-50 rounded-full flex items-center justify-center text-gray-600 hover:bg-gray-100 transition z-10">
            <i class="fa-solid fa-chevron-left"></i>
        </a>
        <h2 class="text-xl font-bold text-secondary text-center w-full">Pengaturan Akun</h2>
    </div>

    <!-- Foto Profil & Nama (Global Header) -->
    <div class="flex flex-col items-center justify-center mb-6">
        <div class="relative w-24 h-24 rounded-full border-4 border-white shadow-lg mb-3">
            <img :src="photoPreview || '{{ $user->employee && $user->employee->foto ? asset('storage/'.$user->employee->foto) : 'https://ui-avatars.com/api/?name='.$user->name.'&background=F5A623&color=fff' }}'" 
                 class="w-full h-full rounded-full object-cover">
            
            <!-- Tombol Kamera Cepat untuk memicu input file di bawah -->
            <button @click="$refs.fotoInput.click()" class="absolute bottom-0 right-0 w-8 h-8 bg-primary text-white rounded-full flex items-center justify-center border-2 border-white shadow-sm hover:bg-primary_hover transition">
                <i class="fa-solid fa-camera text-xs"></i>
            </button>
        </div>
        <h2 class="text-xl font-bold text-secondary text-center leading-tight">{{ $user->name }}</h2>
        <p class="text-xs text-primary font-bold mt-1 bg-primary/10 px-3 py-1 rounded-full">{{ $user->employee->position->nama_jabatan ?? 'Pegawai' }}</p>
    </div>

    <!-- Toggle Kapsul Tab -->
    <div class="bg-gray-100 p-1 rounded-full flex mb-6">
        <button @click="activeTab = 'profil'" :class="activeTab === 'profil' ? 'bg-white shadow-sm text-secondary font-bold' : 'text-gray-500 hover:text-gray-700'" class="flex-1 py-2 rounded-full text-sm transition transition-all duration-300">
            Data Profil
        </button>
        <button @click="activeTab = 'password'" :class="activeTab === 'password' ? 'bg-white shadow-sm text-secondary font-bold' : 'text-gray-500 hover:text-gray-700'" class="flex-1 py-2 rounded-full text-sm transition transition-all duration-300">
            Keamanan
        </button>
    </div>

    <!-- ============================================== -->
    <!-- TAB 1: DATA PROFIL                             -->
    <!-- ============================================== -->
    <div x-show="activeTab === 'profil'" x-transition.opacity class="pb-8 space-y-5">
        <form id="formProfile" @submit.prevent="submitProfile">
            <!-- Hidden input file yang dipicu oleh tombol kamera di atas -->
            <input type="file" name="foto" x-ref="fotoInput" @change="previewImage" accept="image/*" class="hidden">
            
            <div class="space-y-4 bg-white p-5 rounded-3xl border border-gray-100 shadow-[0_2px_10px_rgba(0,0,0,0.02)]">
                <!-- Data Read-only (Dikunci HRD) -->
                <div>
                    <label class="block text-xs font-bold text-gray-400 mb-1 uppercase tracking-wider">NIP / ID Karyawan</label>
                    <div class="w-full px-4 py-3 bg-gray-50 border border-gray-100 rounded-xl text-sm text-gray-500 font-semibold cursor-not-allowed flex items-center gap-2">
                        <i class="fa-solid fa-lock text-gray-400"></i> {{ $user->employee->nip ?? '-' }}
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-400 mb-1 uppercase tracking-wider">Divisi Bagian</label>
                    <div class="w-full px-4 py-3 bg-gray-50 border border-gray-100 rounded-xl text-sm text-gray-500 font-semibold cursor-not-allowed flex items-center gap-2">
                        <i class="fa-solid fa-lock text-gray-400"></i> {{ $user->employee->division->nama_divisi ?? '-' }}
                    </div>
                </div>

                <!-- Data Editable -->
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1 uppercase tracking-wider">Email Akun</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <i class="fa-regular fa-envelope text-gray-400"></i>
                        </div>
                        <input type="email" name="email" value="{{ $user->email }}" required class="w-full pl-11 pr-4 py-3 bg-white border border-gray-200 rounded-xl text-sm text-secondary focus:border-primary focus:ring-1 focus:ring-primary outline-none transition">
                    </div>
                </div>
                
            </div>

            <button type="submit" :disabled="isLoading" class="w-full bg-primary hover:bg-primary_hover disabled:bg-primary/50 text-white font-bold py-4 rounded-2xl transition shadow-lg shadow-primary/30 mt-6 flex justify-center items-center gap-2">
                <span x-show="!isLoading"><i class="fa-solid fa-save"></i> Simpan Profil</span>
                <span x-show="isLoading"><i class="fa-solid fa-spinner fa-spin"></i> Menyimpan...</span>
            </button>
        </form>

        <!-- Garis Pemisah & Tombol Logout -->
        <div class="pt-4 border-t border-gray-100 mt-6">
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="w-full bg-red-50 hover:bg-red-100 text-red-600 font-bold py-4 rounded-2xl transition flex justify-center items-center gap-2 border border-red-100">
                    <i class="fa-solid fa-right-from-bracket"></i> Keluar dari Aplikasi
                </button>
            </form>
        </div>
    </div>


    <!-- ============================================== -->
    <!-- TAB 2: KEAMANAN / UBAH PASSWORD                -->
    <!-- ============================================== -->
    <div x-show="activeTab === 'password'" x-cloak x-transition.opacity class="pb-8">
        <form id="formPassword" @submit.prevent="submitPassword">
            <div class="space-y-4 bg-white p-5 rounded-3xl border border-gray-100 shadow-[0_2px_10px_rgba(0,0,0,0.02)]">
                
                <div class="bg-blue-50 text-blue-700 p-3 rounded-xl text-xs mb-2 border border-blue-100 flex gap-2">
                    <i class="fa-solid fa-shield-halved mt-0.5"></i>
                    <p>Gunakan kombinasi huruf dan angka minimal 6 karakter agar akun Anda tetap aman.</p>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1 uppercase tracking-wider">Password Saat Ini</label>
                    <input type="password" name="current_password" required placeholder="••••••••" class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl text-sm text-secondary focus:border-primary focus:ring-1 focus:ring-primary outline-none transition">
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1 uppercase tracking-wider">Password Baru</label>
                    <input type="password" name="new_password" required minlength="6" placeholder="••••••••" class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl text-sm text-secondary focus:border-primary focus:ring-1 focus:ring-primary outline-none transition">
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1 uppercase tracking-wider">Konfirmasi Password Baru</label>
                    <input type="password" name="new_password_confirmation" required minlength="6" placeholder="••••••••" class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl text-sm text-secondary focus:border-primary focus:ring-1 focus:ring-primary outline-none transition">
                </div>
            </div>

            <button type="submit" :disabled="isLoadingPwd" class="w-full bg-secondary hover:bg-gray-800 disabled:bg-gray-400 text-white font-bold py-4 rounded-2xl transition shadow-lg shadow-gray-300 mt-6 flex justify-center items-center gap-2">
                <span x-show="!isLoadingPwd"><i class="fa-solid fa-key"></i> Perbarui Password</span>
                <span x-show="isLoadingPwd"><i class="fa-solid fa-spinner fa-spin"></i> Memproses...</span>
            </button>
        </form>
    </div>

</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('profileManager', () => ({
            activeTab: 'profil',
            isLoading: false,
            isLoadingPwd: false,
            photoPreview: null,

            // Fitur untuk mengganti gambar profile secara visual sebelum disave
            previewImage(event) {
                const file = event.target.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = (e) => { this.photoPreview = e.target.result; };
                    reader.readAsDataURL(file);
                }
            },

            // Eksekusi Update Profil (Email & Foto)
            submitProfile() {
                this.isLoading = true;
                let form = document.getElementById('formProfile');
                let formData = new FormData(form);
                formData.append('_token', '{{ csrf_token() }}');

                $.ajax({
                    url: '{{ route("pegawai.profil.update") }}', type: 'POST', data: formData, processData: false, contentType: false,
                    success: (res) => {
                        this.isLoading = false;
                        Toast.fire({ icon: 'success', title: res.message });
                        setTimeout(() => location.reload(), 1000); // Reload agar navbar berubah fotonya
                    },
                    error: (xhr) => {
                        this.isLoading = false;
                        let msg = xhr.responseJSON?.errors ? Object.values(xhr.responseJSON.errors)[0][0] : 'Gagal menyimpan profil.';
                        Toast.fire({ icon: 'error', title: msg });
                    }
                });
            },

            // Eksekusi Update Password
            submitPassword() {
                this.isLoadingPwd = true;
                let form = document.getElementById('formPassword');
                let formData = new FormData(form);
                formData.append('_token', '{{ csrf_token() }}');

                $.ajax({
                    url: '{{ route("pegawai.profil.password") }}', type: 'POST', data: formData, processData: false, contentType: false,
                    success: (res) => {
                        this.isLoadingPwd = false;
                        form.reset(); // Kosongkan form password
                        Swal.fire({ icon: 'success', title: 'Berhasil!', text: res.message, confirmButtonColor: '#F5A623' });
                    },
                    error: (xhr) => {
                        this.isLoadingPwd = false;
                        let msg = xhr.responseJSON?.errors ? Object.values(xhr.responseJSON.errors)[0][0] : 'Terjadi kesalahan sistem.';
                        Toast.fire({ icon: 'error', title: msg });
                    }
                });
            }
        }));
    });
</script>
@endpush