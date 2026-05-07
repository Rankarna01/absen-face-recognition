@extends('layouts.admin')
@section('title', 'Registrasi Wajah Pegawai')
@section('header_title', 'Registrasi Wajah Pegawai')

@push('styles')
<style>
    /* Styling Kamera dan CSS Overlay Frame yang SANGAT RINGAN */
    .video-container {
        position: relative;
        width: 100%;
        max-width: 400px;
        aspect-ratio: 3/4; /* Dibuat agak lonjong seperti potret wajah */
        margin: 0 auto;
        overflow: hidden;
        border-radius: 1.5rem;
        border: 4px solid #F5A623;
        box-shadow: 0 10px 25px rgba(245, 166, 35, 0.2);
        background-color: #1F2937;
    }
    #video {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transform: scaleX(-1); /* Mirror kamera depan */
    }
    
    /* CSS Frame transparan untuk panduan wajah (Nol memori CPU) */
    .face-guide {
        position: absolute;
        top: 0; left: 0; right: 0; bottom: 0;
        background: radial-gradient(ellipse 60% 70% at 50% 50%, transparent 40%, rgba(0,0,0,0.6) 60%);
        pointer-events: none; /* Agar tidak menghalangi klik */
        display: flex;
        justify-content: center;
        align-items: center;
    }
    .face-guide-box {
        width: 60%;
        height: 70%;
        border: 2px dashed rgba(245, 166, 35, 0.8);
        border-radius: 50% / 60%; /* Berbentuk oval */
    }
</style>
@endpush

@section('content')
<div x-data="faceRegistration()" class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    <div class="lg:col-span-1 space-y-6">
        <div class="bg-base p-6 rounded-2xl border border-gray-100 shadow-sm">
            <h3 class="font-bold text-lg text-secondary mb-4"><i class="fa-solid fa-user-check text-primary mr-2"></i> Pilih Pegawai</h3>
            
            <p class="text-sm text-gray-500 mb-4">Pilih pegawai yang ingin didaftarkan data wajahnya. Pastikan pencahayaan terang saat mendaftar.</p>

            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Daftar Pegawai</label>
                    <select x-model="selectedUser" @change="checkUserStatus()" class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-primary focus:ring-1 focus:ring-primary outline-none bg-white">
                        <option value="">-- Pilih Pegawai --</option>
                        @foreach($employees as $emp)
                            <option value="{{ $emp->id }}" data-status="{{ $emp->employee?->face_descriptor ? 'registered' : 'unregistered' }}">
                                {{ $emp->employee?->nip ?? '-' }} - {{ $emp->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div x-show="selectedUser" x-cloak class="p-4 rounded-xl text-sm border" 
                     :class="userStatus === 'registered' ? 'bg-orange-50 border-orange-200 text-orange-700' : 'bg-red-50 border-red-200 text-red-700'">
                    <div class="flex items-center gap-2 font-semibold mb-1">
                        <i class="fa-solid" :class="userStatus === 'registered' ? 'fa-triangle-exclamation' : 'fa-circle-xmark'"></i>
                        <span x-text="userStatus === 'registered' ? 'Wajah Sudah Terdaftar' : 'Wajah Belum Terdaftar'"></span>
                    </div>
                    <p class="text-xs" x-text="userStatus === 'registered' ? 'Anda dapat menimpa (registrasi ulang) data wajah sebelumnya jika diperlukan.' : 'Silakan hadapkan wajah ke kamera dan klik daftarkan.'"></p>
                </div>
            </div>
        </div>

        <div x-show="isModelLoading" class="bg-blue-50 p-4 rounded-xl border border-blue-200 text-blue-700 text-sm flex items-center gap-3">
            <i class="fa-solid fa-spinner fa-spin text-xl"></i>
            <div>
                <p class="font-bold">Memuat Model AI...</p>
                <p class="text-xs">Tunggu sebentar, menyiapkan sistem ringan.</p>
            </div>
        </div>
    </div>

    <div class="lg:col-span-2 space-y-6">
        <div class="bg-base p-6 rounded-2xl border border-gray-100 shadow-sm text-center">
            
            <div x-show="!selectedUser" class="py-20 flex flex-col items-center justify-center text-gray-400">
                <i class="fa-solid fa-camera text-6xl mb-4 text-gray-200"></i>
                <p>Silakan pilih pegawai di sebelah kiri untuk mengaktifkan kamera.</p>
            </div>

            <div x-show="selectedUser" x-cloak>
                <h3 class="font-bold text-xl text-secondary mb-1">Posisikan Wajah</h3>
                <p class="text-sm text-gray-500 mb-6">Paskan wajah di dalam garis oval (jangan bergerak saat memindai).</p>

                <div class="video-container">
                    <video id="video" autoplay muted playsinline></video>
                    <div class="face-guide">
                        <div class="face-guide-box"></div>
                    </div>
                </div>

                <div class="mt-8">
                    <button type="button" @click="registerFaceConfirm()" :disabled="isScanning || isModelLoading" 
                            class="text-white px-8 py-3 rounded-full font-bold transition shadow-lg shadow-primary/30 flex items-center gap-2 mx-auto text-lg"
                            :class="userStatus === 'registered' ? 'bg-gray-800 hover:bg-gray-900 shadow-gray-500/30' : 'bg-primary hover:bg-primary_hover'">
                        <span x-show="!isScanning">
                            <i class="fa-solid fa-fingerprint"></i> 
                            <span x-text="userStatus === 'registered' ? 'Registrasi Ulang Wajah' : 'Daftarkan Wajah'"></span>
                        </span>
                        <span x-show="isScanning"><i class="fa-solid fa-spinner fa-spin"></i> Memindai Wajah...</span>
                    </button>
                    
                    <p class="text-xs text-gray-400 mt-3">Pemindaian hanya memakan waktu 1 detik saat tombol diklik.</p>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection

@push('scripts-head')
<script src="{{ asset('js/face-api.min.js') }}"></script>
@endpush

@push('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('faceRegistration', () => ({
            selectedUser: '',
            userStatus: '',
            isModelLoading: true,
            isScanning: false,
            videoEl: null,
            stream: null,

            init() {
                this.videoEl = document.getElementById('video');
                this.loadModels();
            },

            async loadModels() {
                try {
                    await Promise.all([
                        faceapi.nets.ssdMobilenetv1.loadFromUri('/models'),
                        faceapi.nets.faceLandmark68Net.loadFromUri('/models'),
                        faceapi.nets.faceRecognitionNet.loadFromUri('/models')
                    ]);
                    this.isModelLoading = false;
                } catch (error) {
                    Toast.fire({ icon: 'error', title: 'Gagal memuat model AI!' });
                    console.error(error);
                }
            },

            checkUserStatus() {
                if(this.selectedUser) {
                    let option = document.querySelector(`select option[value="${this.selectedUser}"]`);
                    this.userStatus = option.getAttribute('data-status');
                    this.startCamera(); 
                } else {
                    this.stopCamera();
                }
            },

            startCamera() {
                if (navigator.mediaDevices.getUserMedia) {
                    navigator.mediaDevices.getUserMedia({ video: true })
                        .then((stream) => {
                            this.stream = stream;
                            this.videoEl.srcObject = stream;
                            // Catatan: Tidak ada lagi event real-time draw agar CPU aman!
                        })
                        .catch((err) => {
                            Toast.fire({ icon: 'error', title: 'Kamera tidak dapat diakses!' });
                        });
                }
            },

            stopCamera() {
                if (this.stream) {
                    this.stream.getTracks().forEach(track => track.stop());
                    this.videoEl.srcObject = null;
                }
            },

            // Konfirmasi sebelum menimpa data
            registerFaceConfirm() {
                if (this.userStatus === 'registered') {
                    Swal.fire({
                        title: 'Data Wajah Sudah Ada!',
                        text: "Pegawai ini sudah memiliki data wajah. Apakah Anda yakin ingin menghapus yang lama dan menimpanya dengan wajah yang baru?",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#F5A623',
                        cancelButtonColor: '#d33',
                        confirmButtonText: 'Ya, Timpa Wajah',
                        cancelButtonText: 'Batal'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            this.executeFaceScan();
                        }
                    });
                } else {
                    // Jika belum terdaftar, langsung scan
                    this.executeFaceScan();
                }
            },

            // Eksekusi Scan Wajah (Hanya berjalan SEKALI saat diklik)
            async executeFaceScan() {
                if (this.isModelLoading) return;
                this.isScanning = true;

                try {
                    // AI hanya bekerja berat di detik ini saja
                    const detection = await faceapi.detectSingleFace(this.videoEl)
                                                   .withFaceLandmarks()
                                                   .withFaceDescriptor();

                    if (!detection) {
                        this.isScanning = false;
                        Swal.fire({
                            icon: 'error',
                            title: 'Wajah Tidak Terdeteksi',
                            text: 'Coba dekatkan wajah ke arah kamera, pastikan cahaya terang dan stabil.'
                        });
                        return;
                    }

                    const faceDescriptorJson = JSON.stringify(Array.from(detection.descriptor));

                    $.ajax({
                        url: '{{ route("admin.registrasi-wajah.store") }}',
                        type: 'POST',
                        data: {
                            user_id: this.selectedUser,
                            face_descriptor: faceDescriptorJson
                        },
                        success: (res) => {
                            this.isScanning = false;
                            
                            // Ubah status ke registered
                            let option = document.querySelector(`select option[value="${this.selectedUser}"]`);
                            option.setAttribute('data-status', 'registered');
                            this.userStatus = 'registered';

                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil!',
                                text: res.message,
                                confirmButtonColor: '#F5A623'
                            });
                        },
                        error: (xhr) => {
                            this.isScanning = false;
                            Swal.fire({ icon: 'error', title: 'Oops', text: 'Gagal menyimpan data ke database.' });
                        }
                    });

                } catch (error) {
                    this.isScanning = false;
                    console.error("Error during face detection:", error);
                    Toast.fire({ icon: 'error', title: 'Terjadi kesalahan sistem AI!' });
                }
            }
        }));
    });
</script>
@endpush