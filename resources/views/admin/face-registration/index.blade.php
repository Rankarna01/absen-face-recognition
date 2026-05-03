@extends('layouts.admin')
@section('title', 'Registrasi Wajah Pegawai')
@section('header_title', 'Registrasi Wajah Pegawai')

@push('styles')
<style>
    /* Membuat video camera pas di tengah dan terpotong rapi (mirip HP) */
    .video-container {
        position: relative;
        width: 100%;
        max-width: 400px;
        height: 400px;
        margin: 0 auto;
        overflow: hidden;
        border-radius: 50%; /* Dibuat bulat seperti desain referensi */
        border: 8px solid #F5A623; /* Border primary */
        box-shadow: 0 10px 25px rgba(245, 166, 35, 0.2);
    }
    #video {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transform: scaleX(-1); /* Mirror effect supaya kamera depan tidak terbalik */
    }
    #canvas {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        transform: scaleX(-1); /* Samakan dengan video */
    }
</style>
@endpush

@section('content')
<div x-data="faceRegistration()" class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    <!-- Kolom Kiri: Pilih Pegawai -->
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
                            <option value="{{ $emp->id }}" data-status="{{ $emp->employee->face_descriptor ? 'registered' : 'unregistered' }}">
                                {{ $emp->nip }} - {{ $emp->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Status Info -->
                <div x-show="selectedUser" x-cloak class="p-4 rounded-xl text-sm border" 
                     :class="userStatus === 'registered' ? 'bg-green-50 border-green-200 text-green-700' : 'bg-red-50 border-red-200 text-red-700'">
                    <div class="flex items-center gap-2 font-semibold mb-1">
                        <i class="fa-solid" :class="userStatus === 'registered' ? 'fa-circle-check' : 'fa-circle-xmark'"></i>
                        <span x-text="userStatus === 'registered' ? 'Wajah Sudah Terdaftar' : 'Wajah Belum Terdaftar'"></span>
                    </div>
                    <p class="text-xs" x-text="userStatus === 'registered' ? 'Anda dapat meregistrasi ulang wajah jika diperlukan.' : 'Silakan hadapkan wajah ke kamera dan klik daftarkan.'"></p>
                </div>
            </div>
        </div>

        <!-- Indikator Loading Model AI -->
        <div x-show="isModelLoading" class="bg-blue-50 p-4 rounded-xl border border-blue-200 text-blue-700 text-sm flex items-center gap-3">
            <i class="fa-solid fa-spinner fa-spin text-xl"></i>
            <div>
                <p class="font-bold">Memuat Model AI...</p>
                <p class="text-xs">Tunggu sebentar, sedang menyiapkan sistem pengenalan wajah.</p>
            </div>
        </div>
    </div>

    <!-- Kolom Kanan: Live Camera & Scanner -->
    <div class="lg:col-span-2 space-y-6">
        <div class="bg-base p-6 rounded-2xl border border-gray-100 shadow-sm text-center">
            
            <div x-show="!selectedUser" class="py-20 flex flex-col items-center justify-center text-gray-400">
                <i class="fa-solid fa-camera text-6xl mb-4 text-gray-200"></i>
                <p>Silakan pilih pegawai di sebelah kiri untuk mengaktifkan kamera.</p>
            </div>

            <div x-show="selectedUser" x-cloak>
                <h3 class="font-bold text-xl text-secondary mb-1">Posisikan Wajah</h3>
                <p class="text-sm text-gray-500 mb-6">Pastikan wajah berada di dalam lingkaran dan pencahayaan cukup.</p>

                <!-- Video Camera Section -->
                <div class="video-container relative">
                    <video id="video" autoplay muted playsinline></video>
                    <!-- Canvas untuk menggambar kotak deteksi -->
                    <canvas id="canvas"></canvas>
                </div>

                <div class="mt-8">
                    <button type="button" @click="registerFace()" :disabled="isScanning || isModelLoading" 
                            class="bg-primary hover:bg-primary_hover disabled:bg-gray-300 disabled:cursor-not-allowed text-white px-8 py-3 rounded-full font-bold transition shadow-lg shadow-primary/30 flex items-center gap-2 mx-auto text-lg">
                        <span x-show="!isScanning"><i class="fa-solid fa-fingerprint"></i> Daftarkan Wajah</span>
                        <span x-show="isScanning"><i class="fa-solid fa-spinner fa-spin"></i> Memindai Wajah...</span>
                    </button>
                    <p class="text-xs text-gray-400 mt-3">Sistem akan mengambil 128 titik metrik wajah untuk keamanan tinggi.</p>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection

@push('scripts-head')
<!-- Load Face API JS -->
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
            canvasEl: null,
            stream: null,

            init() {
                this.videoEl = document.getElementById('video');
                this.canvasEl = document.getElementById('canvas');
                this.loadModels();
            },

            // 1. Load Model AI dari public/models
            async loadModels() {
                try {
                    await Promise.all([
                        faceapi.nets.ssdMobilenetv1.loadFromUri('/models'),
                        faceapi.nets.faceLandmark68Net.loadFromUri('/models'),
                        faceapi.nets.faceRecognitionNet.loadFromUri('/models')
                    ]);
                    this.isModelLoading = false;
                    console.log('Model Face API berhasil dimuat');
                } catch (error) {
                    Toast.fire({ icon: 'error', title: 'Gagal memuat model AI!' });
                    console.error(error);
                }
            },

            // Cek status saat select box diganti
            checkUserStatus() {
                if(this.selectedUser) {
                    let option = document.querySelector(`select option[value="${this.selectedUser}"]`);
                    this.userStatus = option.getAttribute('data-status');
                    this.startCamera(); // Hidupkan kamera
                } else {
                    this.stopCamera(); // Matikan jika tidak ada yg dipilih
                }
            },

            // 2. Hidupkan Kamera
            startCamera() {
                if (navigator.mediaDevices.getUserMedia) {
                    navigator.mediaDevices.getUserMedia({ video: true })
                        .then((stream) => {
                            this.stream = stream;
                            this.videoEl.srcObject = stream;
                            // Event listener untuk menggambar overlay deteksi secara real-time (opsional, tapi keren)
                            this.videoEl.addEventListener('play', this.realTimeDetection.bind(this));
                        })
                        .catch((err) => {
                            Toast.fire({ icon: 'error', title: 'Kamera tidak dapat diakses!' });
                            console.error(err);
                        });
                }
            },

            // Matikan Kamera
            stopCamera() {
                if (this.stream) {
                    this.stream.getTracks().forEach(track => track.stop());
                    this.videoEl.srcObject = null;
                }
            },

            // 3. (Opsional) Tampilkan Box Deteksi secara Realtime (Efek Keren)
            realTimeDetection() {
                const displaySize = { width: this.videoEl.clientWidth, height: this.videoEl.clientHeight };
                faceapi.matchDimensions(this.canvasEl, displaySize);

                setInterval(async () => {
                    if(!this.videoEl || this.videoEl.paused || this.videoEl.ended) return;
                    
                    const detections = await faceapi.detectAllFaces(this.videoEl).withFaceLandmarks();
                    const resizedDetections = faceapi.resizeResults(detections, displaySize);
                    
                    this.canvasEl.getContext('2d').clearRect(0, 0, this.canvasEl.width, this.canvasEl.height);
                    // Gambar box hijau
                    faceapi.draw.drawDetections(this.canvasEl, resizedDetections);
                }, 100);
            },

            // 4. Eksekusi Registrasi Wajah
            async registerFace() {
                if (this.isModelLoading) {
                    Toast.fire({ icon: 'warning', title: 'Model AI belum selesai dimuat!' });
                    return;
                }

                this.isScanning = true;

                try {
                    // Deteksi wajah (Mengambil Descriptor 128 array)
                    const detection = await faceapi.detectSingleFace(this.videoEl)
                                                   .withFaceLandmarks()
                                                   .withFaceDescriptor();

                    if (!detection) {
                        this.isScanning = false;
                        Swal.fire({
                            icon: 'error',
                            title: 'Wajah Tidak Terdeteksi',
                            text: 'Pastikan wajah terlihat jelas di kamera, tidak terhalang masker, dan cahaya cukup.'
                        });
                        return;
                    }

                    // Wajah ketemu, ambil descriptor dan jadikan JSON string
                    const faceDescriptorJson = JSON.stringify(Array.from(detection.descriptor));

                    // Simpan ke Database via AJAX
                    $.ajax({
                        url: '{{ route("admin.registrasi-wajah.store") }}',
                        type: 'POST',
                        data: {
                            user_id: this.selectedUser,
                            face_descriptor: faceDescriptorJson
                        },
                        success: (res) => {
                            this.isScanning = false;
                            
                            // Update UI select box (agar statusnya berubah jadi 'registered')
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
                    Toast.fire({ icon: 'error', title: 'Terjadi kesalahan sistem!' });
                }
            }
        }));
    });
</script>
@endpush