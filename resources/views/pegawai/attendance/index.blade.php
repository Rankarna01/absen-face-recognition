@extends('layouts.pegawai')
@section('title', 'Absensi Wajah')

@push('styles')
<style>
    /* Styling Kamera Bulat (Mirip UI Referensi) */
    .camera-wrapper {
        position: relative; width: 260px; height: 260px; margin: 0 auto;
        border-radius: 50%; overflow: hidden;
        border: 6px solid #22C55E; /* Warna hijau default */
        box-shadow: 0 10px 25px rgba(34, 197, 94, 0.3);
        transition: border-color 0.3s;
    }
    .camera-wrapper.scanning { border-color: #F5A623; box-shadow: 0 10px 25px rgba(245, 166, 35, 0.3); }
    .camera-wrapper.error { border-color: #EF4444; box-shadow: 0 10px 25px rgba(239, 68, 68, 0.3); }
    
    #video {
        width: 100%; height: 100%; object-fit: cover;
        transform: scaleX(-1); /* Mirror camera */
    }
    #overlay {
        position: absolute; top: 0; left: 0; width: 100%; height: 100%;
        transform: scaleX(-1);
    }
</style>
<!-- Load Face API -->
<script src="{{ asset('js/face-api.min.js') }}"></script>
@endpush

@section('content')
<div class="p-5" x-data="faceAttendance()">
    
    <!-- Header Back -->
    <div class="flex items-center gap-4 mb-8">
        <a href="{{ route('pegawai.beranda') }}" class="w-10 h-10 bg-gray-50 rounded-full flex items-center justify-center text-gray-600 hover:bg-gray-100 transition">
            <i class="fa-solid fa-chevron-left"></i>
        </a>
        <h2 class="text-xl font-bold text-secondary text-center flex-1 pr-10">Absen {{ ucfirst($jenisAbsen) }}</h2>
    </div>

    <!-- Info Box -->
    <div class="bg-orange-50 text-orange-700 p-4 rounded-2xl text-sm text-center mb-8 border border-orange-100">
        Pastikan wajah Anda terlihat jelas di dalam lingkaran dan pencahayaan cukup.
    </div>

    <!-- Kamera Area -->
    <div class="text-center mb-10">
        
        <!-- Loading Model Indicator -->
        <div x-show="isModelLoading" class="mb-4 text-primary font-semibold flex items-center justify-center gap-2">
            <i class="fa-solid fa-spinner fa-spin"></i> Memuat AI Engine...
        </div>

        <!-- Video Wrapper -->
        <div class="camera-wrapper" :class="{'scanning': isScanning, 'error': status === 'error'}">
            <video id="video" autoplay muted playsinline></video>
            <canvas id="overlay"></canvas>
        </div>
        
        <h3 class="text-lg font-bold text-secondary mt-6 mb-1">Posisikan wajah Anda</h3>
        <p class="text-sm text-gray-400 px-4">Sistem akan mencocokkan wajah Anda dengan data biometrik server.</p>
    </div>

    <!-- Tombol Absen -->
    <button type="button" @click="startScan()" :disabled="isModelLoading || isScanning" 
            class="w-full bg-primary hover:bg-primary_hover disabled:bg-gray-300 disabled:shadow-none text-white font-bold py-4 rounded-2xl transition shadow-[0_8px_20px_rgba(245,166,35,0.3)] flex justify-center items-center gap-2 text-lg">
        <span x-show="!isScanning"><i class="fa-solid fa-fingerprint"></i> Mulai Absen</span>
        <span x-show="isScanning"><i class="fa-solid fa-arrows-rotate fa-spin"></i> Memindai Wajah...</span>
    </button>

    <!-- Hidden Canvas (Untuk ambil snapshot foto Base64) -->
    <canvas id="snapshot" style="display: none;"></canvas>

</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('faceAttendance', () => ({
            isModelLoading: true,
            isScanning: false,
            status: 'idle', // idle, scanning, error
            videoEl: null,
            stream: null,
            savedDescriptor: null,

            init() {
                this.videoEl = document.getElementById('video');
                
                // Parse Face Descriptor yang didapat dari database (string JSON ke Float32Array)
                let descriptorStr = {!! json_encode($user->employee->face_descriptor) !!};
                if(descriptorStr) {
                    this.savedDescriptor = new Float32Array(JSON.parse(descriptorStr));
                }

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
                    this.startCamera();
                } catch (error) {
                    Swal.fire({ icon: 'error', title: 'Gagal Memuat Model', text: 'Pastikan file AI berada di direktori /public/models.' });
                }
            },

            startCamera() {
                if (navigator.mediaDevices.getUserMedia) {
                    navigator.mediaDevices.getUserMedia({ video: { facingMode: "user" } })
                        .then((stream) => {
                            this.stream = stream;
                            this.videoEl.srcObject = stream;
                        })
                        .catch((err) => {
                            Swal.fire({ icon: 'error', title: 'Kamera Error', text: 'Mohon izinkan akses kamera di browser Anda.' });
                        });
                }
            },

            async startScan() {
                if (!this.savedDescriptor) {
                    Swal.fire({ icon: 'error', title: 'Data Wajah Kosong', text: 'Anda belum mendaftarkan wajah di HRD.' });
                    return;
                }

                this.isScanning = true;
                this.status = 'scanning';

                try {
                    // 1. Deteksi wajah dari video
                    const detection = await faceapi.detectSingleFace(this.videoEl)
                                                   .withFaceLandmarks()
                                                   .withFaceDescriptor();

                    if (!detection) {
                        this.triggerError('Wajah tidak terdeteksi. Posisikan wajah tepat di kamera dan pastikan cahaya terang.');
                        return;
                    }

                    // 2. Bandingkan dengan data dari DB (Euclidean Distance)
                    // Jika jarak < 0.45, artinya cocok (semakin kecil semakin mirip)
                    const distance = faceapi.euclideanDistance(detection.descriptor, this.savedDescriptor);
                    
                    if (distance > 0.45) {
                        this.triggerError('Wajah tidak dikenali! Tingkat kemiripan rendah ('+distance.toFixed(2)+').');
                        return;
                    }

                    // 3. Wajah Cocok! Ambil Snapshot untuk disimpan sebagai bukti.
                    const canvas = document.getElementById('snapshot');
                    canvas.width = this.videoEl.videoWidth;
                    canvas.height = this.videoEl.videoHeight;
                    const ctx = canvas.getContext('2d');
                    
                    // Mirroring gambar saat digambar ke canvas agar tidak terbalik
                    ctx.translate(canvas.width, 0);
                    ctx.scale(-1, 1);
                    ctx.drawImage(this.videoEl, 0, 0, canvas.width, canvas.height);
                    
                    const base64Image = canvas.toDataURL('image/png');

                    // 4. Kirim Data Absen ke Server via AJAX
                    this.submitAttendance(base64Image);

                } catch (error) {
                    console.error(error);
                    this.triggerError('Terjadi kesalahan saat memindai wajah.');
                }
            },

            triggerError(msg) {
                this.isScanning = false;
                this.status = 'error';
                Swal.fire({ icon: 'error', title: 'Gagal', text: msg, confirmButtonColor: '#F5A623' });
                setTimeout(() => this.status = 'idle', 3000);
            },

            submitAttendance(imageBase64) {
                $.ajax({
                    url: '{{ route("pegawai.absen.store") }}',
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        image: imageBase64
                    },
                    success: (res) => {
                        // Matikan kamera
                        if (this.stream) this.stream.getTracks().forEach(track => track.stop());
                        
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil!',
                            text: res.message,
                            confirmButtonColor: '#22C55E',
                            allowOutsideClick: false
                        }).then(() => {
                            window.location.href = '{{ route("pegawai.beranda") }}';
                        });
                    },
                    error: (xhr) => {
                        this.triggerError(xhr.responseJSON?.message || 'Gagal menyimpan absensi ke server.');
                    }
                });
            }
        }));
    });
</script>
@endpush