@extends('layouts.admin')
@section('title', 'Pengaturan Sistem')
@section('header_title', 'Pengaturan Sistem')

@section('content')
<div x-data="settingManager()" class="space-y-6">

    <!-- Header -->
    <div class="bg-base p-6 rounded-2xl border border-gray-100 shadow-sm flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <h3 class="font-bold text-xl text-secondary">Pengaturan Utama</h3>
            <p class="text-sm text-gray-500 mt-1">Konfigurasi logo, jam kerja, batas toleransi, dan GPS lokasi absen.</p>
        </div>
        <button @click="submitForm()" class="bg-primary hover:bg-primary_hover text-white px-6 py-2.5 rounded-xl text-sm font-semibold transition flex items-center gap-2 shadow-lg shadow-primary/30">
            <span x-show="isLoading"><i class="fa-solid fa-spinner fa-spin"></i></span>
            <span x-show="!isLoading"><i class="fa-solid fa-save"></i> Simpan Perubahan</span>
        </button>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <!-- Sidebar Tabs -->
        <div class="md:col-span-1 space-y-2">
            <button @click="activeTab = 'umum'" :class="activeTab === 'umum' ? 'bg-primary/10 text-primary font-bold border-r-4 border-primary' : 'text-gray-600 hover:bg-gray-50'" class="w-full text-left px-4 py-3 rounded-l-xl text-sm transition flex items-center gap-3">
                <i class="fa-solid fa-building"></i> Profil Perusahaan
            </button>
            <button @click="activeTab = 'absensi'" :class="activeTab === 'absensi' ? 'bg-primary/10 text-primary font-bold border-r-4 border-primary' : 'text-gray-600 hover:bg-gray-50'" class="w-full text-left px-4 py-3 rounded-l-xl text-sm transition flex items-center gap-3">
                <i class="fa-solid fa-clock"></i> Aturan Jam Kerja
            </button>
            <button @click="activeTab = 'gps'" :class="activeTab === 'gps' ? 'bg-primary/10 text-primary font-bold border-r-4 border-primary' : 'text-gray-600 hover:bg-gray-50'" class="w-full text-left px-4 py-3 rounded-l-xl text-sm transition flex items-center gap-3">
                <i class="fa-solid fa-map-location-dot"></i> Lokasi Kantor (GPS)
            </button>
            <button @click="activeTab = 'api'" :class="activeTab === 'api' ? 'bg-primary/10 text-primary font-bold border-r-4 border-primary' : 'text-gray-600 hover:bg-gray-50'" class="w-full text-left px-4 py-3 rounded-l-xl text-sm transition flex items-center gap-3">
                <i class="fa-solid fa-code"></i> Integrasi API
            </button>
        </div>

        <!-- Form Content Area -->
        <div class="md:col-span-3">
            <form id="settingForm" class="bg-base p-6 md:p-8 rounded-2xl border border-gray-100 shadow-sm min-h-[400px]">
                
                <!-- TAB 1: UMUM -->
                <div x-show="activeTab === 'umum'" x-transition.opacity class="space-y-6">
                    <h4 class="font-bold text-lg border-b border-gray-100 pb-2 mb-4">Profil & Identitas</h4>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nama Aplikasi / Perusahaan</label>
                        <input type="text" name="app_name" value="{{ $setting->app_name }}" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:border-primary focus:ring-1 focus:ring-primary outline-none">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Alamat Kantor</label>
                        <textarea name="app_address" rows="3" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:border-primary focus:ring-1 focus:ring-primary outline-none">{{ $setting->app_address }}</textarea>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Logo Sistem (Kosongkan jika tidak ingin mengubah)</label>
                        <div class="flex items-center gap-4">
                            @if($setting->app_logo)
                                <img src="{{ asset('storage/'.$setting->app_logo) }}" class="w-16 h-16 rounded-lg object-contain bg-gray-50 border border-gray-200">
                            @else
                                <div class="w-16 h-16 bg-gray-100 rounded-lg flex items-center justify-center text-gray-400 font-bold">LOGO</div>
                            @endif
                            <input type="file" name="app_logo" accept="image/*" class="w-full px-4 py-2 rounded-xl border border-gray-200 focus:border-primary outline-none">
                        </div>
                    </div>
                </div>

                <!-- TAB 2: ABSENSI -->
                <div x-show="activeTab === 'absensi'" x-cloak x-transition.opacity class="space-y-6">
                    <h4 class="font-bold text-lg border-b border-gray-100 pb-2 mb-4">Aturan Jam Kerja Default</h4>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Jam Masuk</label>
                            <input type="time" name="default_jam_masuk" value="{{ substr($setting->default_jam_masuk, 0, 5) }}" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:border-primary outline-none text-lg font-mono">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Jam Pulang</label>
                            <input type="time" name="default_jam_pulang" value="{{ substr($setting->default_jam_pulang, 0, 5) }}" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:border-primary outline-none text-lg font-mono">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Toleransi Keterlambatan (Menit)</label>
                        <div class="flex items-center gap-3">
                            <input type="number" name="toleransi_keterlambatan" value="{{ $setting->toleransi_keterlambatan }}" class="w-32 px-4 py-2.5 rounded-xl border border-gray-200 focus:border-primary outline-none text-lg text-center font-bold">
                            <span class="text-gray-500">Menit setelah jam masuk</span>
                        </div>
                        <p class="text-xs text-gray-400 mt-2">Contoh: Jika jam masuk 08:00 dan toleransi 15 menit, maka absen di atas 08:15 akan dihitung Terlambat.</p>
                    </div>
                </div>

                <!-- TAB 3: GPS LOKASI -->
                <div x-show="activeTab === 'gps'" x-cloak x-transition.opacity class="space-y-6">
                    <h4 class="font-bold text-lg border-b border-gray-100 pb-2 mb-4">Penguncian Lokasi (Geo-Fencing)</h4>
                    
                    <div class="bg-blue-50 text-blue-700 p-4 rounded-xl text-sm border border-blue-100 flex gap-3 mb-4">
                        <i class="fa-solid fa-location-crosshairs text-xl mt-0.5"></i>
                        <div>
                            <p class="font-bold">Dapatkan Koordinat Cepat</p>
                            <p>Berada di kantor sekarang? Klik tombol di bawah ini untuk mengambil titik kordinat secara otomatis.</p>
                            <button type="button" @click="getGPSLocation()" class="mt-2 bg-blue-600 hover:bg-blue-700 text-white px-3 py-1.5 rounded-lg text-xs font-semibold transition">
                                <i class="fa-solid fa-satellite-dish mr-1"></i> Gunakan Lokasi Saat Ini
                            </button>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Garis Lintang (Latitude)</label>
                            <input type="text" id="inputLat" name="office_latitude" value="{{ $setting->office_latitude }}" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:border-primary outline-none font-mono">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Garis Bujur (Longitude)</label>
                            <input type="text" id="inputLng" name="office_longitude" value="{{ $setting->office_longitude }}" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:border-primary outline-none font-mono">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Radius Toleransi (Meter)</label>
                        <div class="flex items-center gap-3">
                            <input type="number" name="office_radius" value="{{ $setting->office_radius }}" class="w-32 px-4 py-2.5 rounded-xl border border-gray-200 focus:border-primary outline-none text-lg text-center font-bold">
                            <span class="text-gray-500">Meter dari titik koordinat</span>
                        </div>
                        <p class="text-xs text-gray-400 mt-2">Jarak maksimal pegawai dari titik koordinat kantor agar tombol absensi muncul di HP mereka.</p>
                    </div>
                </div>

                <!-- TAB 4: API -->
                <div x-show="activeTab === 'api'" x-cloak x-transition.opacity class="space-y-6">
                    <h4 class="font-bold text-lg border-b border-gray-100 pb-2 mb-4">Integrasi API (Opsional)</h4>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">API Key / Token</label>
                        <input type="text" name="api_key" value="{{ $setting->api_key }}" placeholder="Kosongkan jika tidak dipakai" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:border-primary outline-none font-mono text-gray-500">
                        <p class="text-xs text-gray-400 mt-2">Dapat digunakan jika Anda ingin menyambungkan sistem ini dengan aplikasi WhatsApp Gateway atau HRIS pusat.</p>
                    </div>
                </div>

            </form>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('settingManager', () => ({
            activeTab: 'umum',
            isLoading: false,

            // Eksekusi AJAX Simpan Pengaturan
            submitForm() {
                this.isLoading = true;
                let form = document.getElementById('settingForm');
                let formData = new FormData(form);
                formData.append('_token', '{{ csrf_token() }}');

                $.ajax({
                    url: '{{ route("admin.pengaturan.update") }}',
                    type: 'POST', // Walaupun update, FormData wajib POST (Laravel akan handle)
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: (res) => {
                        this.isLoading = false;
                        Toast.fire({ icon: 'success', title: res.message });
                        setTimeout(() => location.reload(), 1500); // Reload untuk melihat logo baru (jika ada)
                    },
                    error: (xhr) => {
                        this.isLoading = false;
                        let msg = xhr.responseJSON?.errors ? Object.values(xhr.responseJSON.errors)[0][0] : 'Terjadi kesalahan sistem';
                        Toast.fire({ icon: 'error', title: msg });
                    }
                });
            },

            // Fitur Keren: Ambil GPS Otomatis
            getGPSLocation() {
                if (navigator.geolocation) {
                    Toast.fire({ icon: 'info', title: 'Mendapatkan koordinat GPS...', timer: 2000 });
                    navigator.geolocation.getCurrentPosition(
                        (position) => {
                            document.getElementById('inputLat').value = position.coords.latitude;
                            document.getElementById('inputLng').value = position.coords.longitude;
                            Toast.fire({ icon: 'success', title: 'Koordinat berhasil diperbarui!' });
                        },
                        (error) => {
                            let errorMsg = 'Gagal mengakses GPS.';
                            if(error.code == error.PERMISSION_DENIED) errorMsg = 'Izin akses lokasi (GPS) ditolak browser.';
                            Toast.fire({ icon: 'error', title: errorMsg });
                        },
                        { enableHighAccuracy: true } // Paksa akurasi tinggi
                    );
                } else {
                    Toast.fire({ icon: 'error', title: 'Browser Anda tidak mendukung GPS!' });
                }
            }
        }));
    });
</script>
@endpush