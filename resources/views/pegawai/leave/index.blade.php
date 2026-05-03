@extends('layouts.pegawai')
@section('title', 'Izin & Cuti')

@section('content')
<!-- Alpine.js mengatur tab default: 'form' atau 'status' -->
<div class="p-5 flex flex-col h-full" x-data="leaveManager()">
    
    <!-- Header Back -->
    <div class="flex items-center gap-4 mb-6 relative">
        <a href="{{ route('pegawai.beranda') }}" class="absolute left-0 w-10 h-10 bg-gray-50 rounded-full flex items-center justify-center text-gray-600 hover:bg-gray-100 transition z-10">
            <i class="fa-solid fa-chevron-left"></i>
        </a>
        <h2 class="text-xl font-bold text-secondary text-center w-full">Izin & Cuti</h2>
    </div>

    <!-- Toggle Kapsul (Pilihan Menu Atas) -->
    <div class="bg-gray-100 p-1 rounded-full flex mb-6">
        <button @click="activeTab = 'form'" 
                :class="activeTab === 'form' ? 'bg-white shadow-sm text-secondary font-bold' : 'text-gray-500 hover:text-gray-700'"
                class="flex-1 py-2 rounded-full text-sm transition transition-all duration-300">
            Pengajuan
        </button>
        <button @click="activeTab = 'status'" 
                :class="activeTab === 'status' ? 'bg-white shadow-sm text-secondary font-bold' : 'text-gray-500 hover:text-gray-700'"
                class="flex-1 py-2 rounded-full text-sm transition transition-all duration-300 relative">
            Status Izin
            <!-- Notification Dot (Opsional jika ada yang sedang menunggu) -->
            @if($riwayat->where('status', 'menunggu')->count() > 0)
                <span class="absolute top-2 right-4 w-2 h-2 bg-primary rounded-full"></span>
            @endif
        </button>
    </div>

    <!-- ============================================== -->
    <!-- TAB 1: FORM PENGAJUAN                          -->
    <!-- ============================================== -->
    <div x-show="activeTab === 'form'" x-transition.opacity class="pb-8">
        
        <form id="formLeave" @submit.prevent="submitLeave">
            <div class="space-y-4">
                
                <!-- Jenis Izin -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Jenis Izin</label>
                    <div class="relative">
                        <select name="jenis_izin" required class="w-full px-4 py-3.5 bg-gray-50 border border-gray-200 rounded-2xl text-sm text-secondary focus:border-primary focus:ring-1 focus:ring-primary outline-none appearance-none transition">
                            <option value="">Pilih jenis izin...</option>
                            <option value="Izin Sakit">Izin Sakit (Butuh Surat Dokter)</option>
                            <option value="Izin Keperluan Pribadi">Izin Keperluan Pribadi</option>
                            <option value="Cuti Tahunan">Cuti Tahunan</option>
                            <option value="Cuti Melahirkan">Cuti Melahirkan</option>
                        </select>
                        <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none">
                            <i class="fa-solid fa-chevron-down text-gray-400 text-xs"></i>
                        </div>
                    </div>
                </div>

                <!-- Rentang Tanggal -->
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Tanggal Mulai</label>
                        <input type="date" name="tanggal_mulai" required class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-2xl text-sm text-secondary focus:border-primary focus:ring-1 focus:ring-primary outline-none transition">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Tanggal Selesai</label>
                        <input type="date" name="tanggal_selesai" required class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-2xl text-sm text-secondary focus:border-primary focus:ring-1 focus:ring-primary outline-none transition">
                    </div>
                </div>

                <!-- Alasan -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Alasan</label>
                    <textarea name="alasan" rows="3" required placeholder="Tuliskan alasan izin dengan jelas..." class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-2xl text-sm text-secondary focus:border-primary focus:ring-1 focus:ring-primary outline-none transition"></textarea>
                </div>

                <!-- Lampiran (Surat Dokter/Bukti) -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Lampiran (Opsional)</label>
                    <div class="relative">
                        <input type="file" name="lampiran" accept="image/*" class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-2xl text-sm text-gray-500 file:mr-4 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-primary/10 file:text-primary hover:file:bg-primary/20 focus:border-primary outline-none transition">
                    </div>
                    <p class="text-[10px] text-gray-400 mt-1">* Wajib diisi jika mengajukan Izin Sakit.</p>
                </div>

            </div>

            <!-- Tombol Submit -->
            <button type="submit" :disabled="isLoading" class="w-full bg-primary hover:bg-primary_hover disabled:bg-primary/50 text-white font-bold py-4 rounded-2xl transition shadow-lg shadow-primary/30 mt-8 flex justify-center items-center gap-2">
                <span x-show="!isLoading">Ajukan Izin</span>
                <span x-show="isLoading"><i class="fa-solid fa-spinner fa-spin"></i> Memproses...</span>
            </button>
        </form>
    </div>


    <!-- ============================================== -->
    <!-- TAB 2: STATUS & RIWAYAT PENGAJUAN              -->
    <!-- ============================================== -->
    <div x-show="activeTab === 'status'" x-cloak x-transition.opacity class="pb-8 space-y-4">
        
        @forelse($riwayat as $item)
            <div class="bg-white p-4 rounded-2xl border border-gray-100 shadow-[0_2px_10px_rgba(0,0,0,0.02)]">
                
                <div class="flex justify-between items-start mb-3">
                    <div>
                        <h4 class="font-bold text-secondary">{{ $item->jenis_izin }}</h4>
                        <p class="text-[11px] text-gray-400 mt-0.5">Diajukan: {{ \Carbon\Carbon::parse($item->created_at)->translatedFormat('d M Y') }}</p>
                    </div>
                    
                    <!-- Dynamic Badge Status -->
                    @php
                        $badgeStyle = match($item->status) {
                            'disetujui' => 'bg-green-50 text-green-600 border-green-100',
                            'ditolak' => 'bg-red-50 text-red-600 border-red-100',
                            default => 'bg-orange-50 text-orange-600 border-orange-100' // menunggu
                        };
                        $iconStyle = match($item->status) {
                            'disetujui' => 'fa-check',
                            'ditolak' => 'fa-xmark',
                            default => 'fa-clock' // menunggu
                        };
                    @endphp
                    <span class="{{ $badgeStyle }} border px-2.5 py-1 rounded-lg text-[10px] font-bold uppercase tracking-wider flex items-center gap-1">
                        <i class="fa-solid {{ $iconStyle }}"></i> {{ $item->status }}
                    </span>
                </div>

                <!-- Info Tanggal & Alasan -->
                <div class="bg-gray-50 rounded-xl p-3 mb-2">
                    <p class="text-xs text-gray-500 mb-1"><i class="fa-regular fa-calendar text-gray-400 mr-1"></i> {{ \Carbon\Carbon::parse($item->tanggal_mulai)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($item->tanggal_selesai)->format('d/m/Y') }}</p>
                    <p class="text-sm font-medium text-secondary line-clamp-2">"{{ $item->alasan }}"</p>
                </div>

                <!-- Catatan Admin (Jika ada balasan dari HRD) -->
                @if($item->keterangan_admin)
                    <div class="mt-2 text-xs p-2.5 rounded-xl {{ $item->status == 'ditolak' ? 'bg-red-50 text-red-600' : 'bg-green-50 text-green-600' }}">
                        <strong>Pesan Admin:</strong> {{ $item->keterangan_admin }}
                    </div>
                @endif
            </div>
        @empty
            <div class="flex flex-col items-center justify-center py-12 px-4 text-center">
                <div class="w-20 h-20 bg-gray-50 rounded-full flex items-center justify-center mb-4">
                    <i class="fa-solid fa-file-circle-check text-3xl text-gray-300"></i>
                </div>
                <h3 class="font-bold text-gray-700">Belum ada pengajuan</h3>
                <p class="text-sm text-gray-400 mt-1">Anda belum pernah mengajukan izin atau cuti.</p>
            </div>
        @endforelse

    </div>

</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('leaveManager', () => ({
            activeTab: 'form', // Tab default
            isLoading: false,

            submitLeave(e) {
                this.isLoading = true;
                let form = document.getElementById('formLeave');
                let formData = new FormData(form);
                formData.append('_token', '{{ csrf_token() }}');

                $.ajax({
                    url: '{{ route("pegawai.izin.store") }}',
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: (res) => {
                        this.isLoading = false;
                        form.reset(); // Kosongkan form
                        
                        Swal.fire({
                            icon: 'success', title: 'Berhasil!', text: res.message, confirmButtonColor: '#F5A623'
                        }).then(() => {
                            // Pindahkan otomatis ke tab status dan reload untuk melihat data baru
                            location.reload(); 
                        });
                    },
                    error: (xhr) => {
                        this.isLoading = false;
                        let msg = xhr.responseJSON?.message || 'Terjadi kesalahan sistem.';
                        Toast.fire({ icon: 'error', title: msg });
                    }
                });
            }
        }));
    });
</script>
@endpush