@extends('layouts.admin')
@section('title', 'Pengajuan Izin & Cuti')
@section('header_title', 'Pengajuan Izin & Cuti')

@section('content')
<!-- Alpine.js untuk mengatur sistem Tab -->
<div x-data="leaveManager()" class="space-y-6">

    <!-- Header & Tabs -->
    <div class="bg-base p-6 rounded-2xl border border-gray-100 shadow-sm">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
            <div>
                <h3 class="font-bold text-xl text-secondary">Manajemen Izin & Cuti</h3>
                <p class="text-sm text-gray-500 mt-1">Kelola persetujuan izin, sakit, dan cuti tahunan pegawai.</p>
            </div>
        </div>

        <!-- Custom Tabs -->
        <div class="flex space-x-2 border-b border-gray-100">
            <button @click="activeTab = 'pending'" 
                    :class="activeTab === 'pending' ? 'border-primary text-primary border-b-2 font-semibold' : 'text-gray-500 hover:text-gray-700'"
                    class="pb-3 px-4 text-sm transition relative">
                Menunggu Approval
                @if($pending->count() > 0)
                    <span class="absolute -top-1 -right-2 bg-red-500 text-white text-[10px] font-bold px-1.5 py-0.5 rounded-full">{{ $pending->count() }}</span>
                @endif
            </button>
            <button @click="activeTab = 'history'" 
                    :class="activeTab === 'history' ? 'border-primary text-primary border-b-2 font-semibold' : 'text-gray-500 hover:text-gray-700'"
                    class="pb-3 px-4 text-sm transition">
                Riwayat Pengajuan
            </button>
        </div>
    </div>

    <!-- ==================== TAB PENDING (MENUNGGU) ==================== -->
    <div x-show="activeTab === 'pending'" x-transition.opacity class="bg-base rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-orange-50/50 border-b border-gray-100 text-sm text-gray-600">
                        <th class="px-6 py-4 font-semibold">Pegawai</th>
                        <th class="px-6 py-4 font-semibold">Detail Izin</th>
                        <th class="px-6 py-4 font-semibold">Alasan</th>
                        <th class="px-6 py-4 font-semibold text-center">Lampiran</th>
                        <th class="px-6 py-4 font-semibold text-right">Aksi Approval</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($pending as $item)
                    <tr class="hover:bg-gray-50/50 transition">
                        <td class="px-6 py-4">
                            <p class="font-semibold text-secondary">{{ $item->user->name }}</p>
                            <p class="text-xs text-gray-500">{{ $item->user->employee->position->nama_jabatan ?? 'Staff' }}</p>
                        </td>
                        <td class="px-6 py-4">
                            <span class="inline-block bg-primary/10 text-primary_hover font-semibold px-2 py-1 rounded text-xs mb-1">{{ $item->jenis_izin }}</span>
                            <p class="text-sm font-medium text-secondary">
                                {{ \Carbon\Carbon::parse($item->tanggal_mulai)->format('d M') }} - {{ \Carbon\Carbon::parse($item->tanggal_selesai)->format('d M Y') }}
                            </p>
                            @php
                                $hari = \Carbon\Carbon::parse($item->tanggal_mulai)->diffInDays(\Carbon\Carbon::parse($item->tanggal_selesai)) + 1;
                            @endphp
                            <p class="text-xs text-gray-500">({{ $hari }} Hari)</p>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600 max-w-xs truncate" title="{{ $item->alasan }}">
                            {{ $item->alasan }}
                        </td>
                        <td class="px-6 py-4 text-center">
                            @if($item->lampiran)
                                <a href="{{ asset('storage/'.$item->lampiran) }}" target="_blank" class="text-blue-500 hover:text-blue-700 bg-blue-50 p-2 rounded-lg inline-block transition" title="Lihat Lampiran">
                                    <i class="fa-solid fa-paperclip"></i>
                                </a>
                            @else
                                <span class="text-gray-300">-</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex justify-end gap-2">
                                <button @click="processLeave({{ $item->id }}, 'disetujui')" class="bg-green-100 hover:bg-green-500 text-green-600 hover:text-white px-3 py-1.5 rounded-lg text-xs font-semibold transition">
                                    <i class="fa-solid fa-check mr-1"></i> Setujui
                                </button>
                                <button @click="processLeave({{ $item->id }}, 'ditolak')" class="bg-red-100 hover:bg-red-500 text-red-600 hover:text-white px-3 py-1.5 rounded-lg text-xs font-semibold transition">
                                    <i class="fa-solid fa-xmark mr-1"></i> Tolak
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center text-gray-500">
                            <div class="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-3">
                                <i class="fa-regular fa-face-smile text-2xl text-gray-400"></i>
                            </div>
                            <p>Belum ada pengajuan izin/cuti yang menunggu.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- ==================== TAB RIWAYAT ==================== -->
    <div x-show="activeTab === 'history'" x-cloak x-transition.opacity class="bg-base rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-100 text-sm text-gray-600">
                        <th class="px-6 py-4 font-semibold">Pegawai</th>
                        <th class="px-6 py-4 font-semibold">Tgl Pengajuan</th>
                        <th class="px-6 py-4 font-semibold">Detail Izin</th>
                        <th class="px-6 py-4 font-semibold">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($history as $item)
                    <tr class="hover:bg-gray-50/50 transition">
                        <td class="px-6 py-4">
                            <p class="font-semibold text-secondary">{{ $item->user->name }}</p>
                            <p class="text-xs text-gray-500">{{ $item->jenis_izin }}</p>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600">
                            {{ \Carbon\Carbon::parse($item->created_at)->format('d M Y') }}
                        </td>
                        <td class="px-6 py-4">
                            <p class="text-sm font-medium text-secondary">
                                {{ \Carbon\Carbon::parse($item->tanggal_mulai)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($item->tanggal_selesai)->format('d/m/Y') }}
                            </p>
                            @if($item->status == 'ditolak' && $item->keterangan_admin)
                                <p class="text-xs text-red-500 mt-1">Alasan tolak: {{ $item->keterangan_admin }}</p>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            @if($item->status == 'disetujui')
                                <span class="bg-green-100 text-green-600 px-3 py-1 rounded-full text-xs font-bold uppercase"><i class="fa-solid fa-check mr-1"></i> Disetujui</span>
                            @else
                                <span class="bg-red-100 text-red-600 px-3 py-1 rounded-full text-xs font-bold uppercase"><i class="fa-solid fa-xmark mr-1"></i> Ditolak</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="px-6 py-8 text-center text-gray-500">Belum ada riwayat pengajuan.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('leaveManager', () => ({
            // Set tab awal ke 'pending'
            activeTab: 'pending',

            // Fungsi eksekusi approval (Setuju/Tolak)
            processLeave(id, actionStatus) {
                // Konfigurasi SweetAlert berdasarkan aksi
                let titleMsg = actionStatus === 'disetujui' ? 'Setujui Pengajuan?' : 'Tolak Pengajuan?';
                let btnColor = actionStatus === 'disetujui' ? '#22C55E' : '#EF4444';
                let btnText  = actionStatus === 'disetujui' ? 'Ya, Setujui' : 'Ya, Tolak';
                
                // Jika Ditolak, kita butuh input teks untuk alasan
                let swalConfig = {
                    title: titleMsg,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: btnColor,
                    cancelButtonColor: '#9CA3AF',
                    confirmButtonText: btnText,
                    cancelButtonText: 'Batal'
                };

                if (actionStatus === 'ditolak') {
                    swalConfig.input = 'text';
                    swalConfig.inputPlaceholder = 'Masukkan alasan penolakan...';
                    swalConfig.inputValidator = (value) => {
                        if (!value) {
                            return 'Alasan penolakan wajib diisi!'
                        }
                    };
                }

                Swal.fire(swalConfig).then((result) => {
                    if (result.isConfirmed) {
                        // Data yang akan dikirim via AJAX
                        let payload = {
                            _token: '{{ csrf_token() }}',
                            status: actionStatus
                        };

                        // Jika ditolak, masukkan alasannya
                        if (actionStatus === 'ditolak') {
                            payload.keterangan_admin = result.value;
                        }

                        // Eksekusi AJAX POST
                        $.ajax({
                            url: `/admin/pengajuan/${id}/status`,
                            type: 'POST',
                            data: payload,
                            success: (res) => {
                                Toast.fire({ icon: 'success', title: res.message });
                                setTimeout(() => location.reload(), 1000);
                            },
                            error: (xhr) => {
                                Toast.fire({ icon: 'error', title: 'Terjadi kesalahan sistem' });
                            }
                        });
                    }
                });
            }
        }));
    });
</script>
@endpush