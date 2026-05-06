@extends('layouts.admin')
@section('title', 'Jadwal Kerja & Shift')
@section('header_title', 'Jadwal Kerja Pegawai')

@push('scripts-head')
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js'></script>
<style>
    .fc .fc-toolbar-title { font-size: 1.25rem; font-weight: 700; color: #1F2937; }
    .fc .fc-button-primary { background-color: #F5A623 !important; border-color: #F5A623 !important; border-radius: 0.5rem; text-transform: capitalize; }
    .fc .fc-button-primary:hover, .fc .fc-button-primary:not(:disabled).fc-button-active { background-color: #E0961B !important; border-color: #E0961B !important; }
    .fc-event { cursor: pointer; padding: 4px 6px; border-radius: 6px; font-size: 0.75rem; border: none; color: #fff !important; font-weight: 500; margin-bottom: 3px; }
    .fc-toolbar { flex-wrap: wrap; gap: 10px; }
    .fc td, .fc th { border-color: #f3f4f6; }
</style>
@endpush

@section('content')
<div x-data="scheduleManager()" class="grid grid-cols-1 lg:grid-cols-4 gap-6">

    <!-- KOLOM KIRI: Form Shift & Assignment -->
    <div class="lg:col-span-1 space-y-6">
        
        <!-- Form Assign Jadwal -->
        <div class="bg-base p-6 rounded-2xl border border-gray-100 shadow-sm">
            <h3 class="font-bold text-lg text-secondary mb-4"><i class="fa-solid fa-calendar-plus text-primary mr-2"></i> Assign Jadwal</h3>
            
            <form @submit.prevent="assignSchedule" class="space-y-4">
                
                <!-- Info API Libur Nasional -->
                <div id="holidayApiBox" class="hidden bg-red-50 border border-red-200 p-3 rounded-xl text-xs text-red-700 mb-2">
                    <strong><i class="fa-solid fa-bullhorn"></i> Libur Nasional Bulan Ini:</strong>
                    <ul id="holidayApiList" class="mt-1 ml-4 list-disc text-red-600 font-medium"></ul>
                    <p class="text-[10px] mt-2 italic text-red-500">*Daftarkan tanggal ini di menu Master Hari Libur jika kantor/toko libur.</p>
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Pilih Pegawai</label>
                    <select x-model="assignForm.user_id" required class="w-full px-3 py-2 rounded-xl border border-gray-200 focus:border-primary outline-none text-sm bg-white">
                        <option value="">-- Pegawai --</option>
                        @foreach($employees as $emp)
                            <option value="{{ $emp->id }}">{{ $emp->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Pilih Shift -->
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Pilih Shift</label>
                    <select x-model="assignForm.shift_id" required class="w-full px-3 py-2 rounded-xl border border-gray-200 focus:border-primary outline-none text-sm bg-white">
                        <option value="">-- Shift --</option>
                        @foreach($shifts as $shift)
                            <option value="{{ $shift->id }}">{{ $shift->nama_shift }} ({{ substr($shift->jam_masuk,0,5) }} - {{ substr($shift->jam_pulang,0,5) }})</option>
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Mulai Tgl</label>
                        <input type="date" x-model="assignForm.tanggal_mulai" required class="w-full px-3 py-2 rounded-xl border border-gray-200 focus:border-primary outline-none text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Sampai Tgl</label>
                        <input type="date" x-model="assignForm.tanggal_selesai" required class="w-full px-3 py-2 rounded-xl border border-gray-200 focus:border-primary outline-none text-sm">
                    </div>
                </div>
                
                <button type="submit" class="w-full bg-primary hover:bg-primary_hover text-white px-4 py-2 rounded-xl text-sm font-semibold transition shadow-md flex justify-center items-center">
                    <span x-show="!isLoadingAssign">Terapkan Jadwal</span>
                    <span x-show="isLoadingAssign"><i class="fa-solid fa-spinner fa-spin"></i></span>
                </button>
            </form>
        </div>

        <!-- Form Buat Shift Master -->
        <div class="bg-base p-6 rounded-2xl border border-gray-100 shadow-sm">
            <h3 class="font-bold text-lg text-secondary mb-4"><i class="fa-solid fa-clock text-primary mr-2"></i> Master Shift Baru</h3>
            <form @submit.prevent="createShift" class="space-y-4">
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Nama Shift</label>
                    <input type="text" x-model="shiftForm.nama_shift" placeholder="Cth: Shift Pagi" required class="w-full px-3 py-2 rounded-xl border border-gray-200 focus:border-primary outline-none text-sm">
                </div>
                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Jam Masuk</label>
                        <input type="time" x-model="shiftForm.jam_masuk" class="w-full px-3 py-2 rounded-xl border border-gray-200 focus:border-primary outline-none text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Jam Pulang</label>
                        <input type="time" x-model="shiftForm.jam_pulang" class="w-full px-3 py-2 rounded-xl border border-gray-200 focus:border-primary outline-none text-sm">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Warna Label</label>
                    <input type="color" x-model="shiftForm.warna" class="w-full h-10 p-1 rounded-xl border border-gray-200 outline-none cursor-pointer">
                </div>
                <button type="submit" class="w-full bg-gray-800 hover:bg-gray-900 text-white px-4 py-2 rounded-xl text-sm font-semibold transition flex justify-center items-center">
                    <span x-show="!isLoadingShift">Simpan Shift</span>
                    <span x-show="isLoadingShift"><i class="fa-solid fa-spinner fa-spin"></i></span>
                </button>
            </form>
        </div>
    </div>

    <!-- KOLOM KANAN: Kalender -->
    <div class="lg:col-span-3">
        <div class="bg-base p-6 rounded-2xl border border-gray-100 shadow-sm">
            <div class="flex justify-between items-center mb-4">
                <h3 class="font-bold text-lg text-secondary">Visualisasi Kalender</h3>
            </div>
            <div id='calendar' x-ignore class="min-h-[600px] w-full"></div>
            <p class="text-xs text-gray-400 mt-4"><i class="fa-solid fa-circle-info mr-1"></i> Kotak merah adalah Hari Libur Global. Klik pada kotak jadwal pegawai untuk melihat detail atau menghapusnya.</p>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('scheduleManager', () => ({
            isLoadingAssign: false,
            isLoadingShift: false,
            assignForm: { 
                user_id: '', shift_id: '', tanggal_mulai: '', tanggal_selesai: ''
            },
            shiftForm: { nama_shift: '', jam_masuk: '', jam_pulang: '', warna: '#0ea5e9' },

            createShift() {
                this.isLoadingShift = true;
                $.ajax({
                    url: '/admin/jadwal/shift', type: 'POST',
                    data: { _token: '{{ csrf_token() }}', ...this.shiftForm },
                    success: (res) => {
                        Toast.fire({ icon: 'success', title: res.message });
                        setTimeout(() => location.reload(), 1000);
                    },
                    error: (xhr) => {
                        this.isLoadingShift = false;
                        Toast.fire({ icon: 'error', title: 'Gagal membuat shift' });
                    }
                });
            },

            assignSchedule() {
                this.isLoadingAssign = true;
                $.ajax({
                    url: '/admin/jadwal/assign', type: 'POST', 
                    data: { _token: '{{ csrf_token() }}', ...this.assignForm },
                    success: (res) => {
                        Toast.fire({ icon: 'success', title: res.message });
                        setTimeout(() => location.reload(), 1000);
                    },
                    error: (xhr) => {
                        this.isLoadingAssign = false;
                        Toast.fire({ icon: 'error', title: xhr.responseJSON.message || 'Gagal assign jadwal' });
                    }
                });
            }
        }));
    });

    document.addEventListener('DOMContentLoaded', function() {
        var calendarEl = document.getElementById('calendar');
        var rawScheduleData = @json($schedules ?? []);
        var rawHolidays = @json($holidays ?? []);

        // 1. Mapping Jadwal Shift dari Database dengan PENGAMAN EKSTRA
        var formattedEvents = rawScheduleData.map(function(item) {
            let userName = item.user ? item.user.name : 'Pegawai (Dihapus)';
            
            // Jaga-jaga jika ini adalah data jadwal "Libur Manual" dari versi tabel database sebelumnya
            if(item.is_libur) {
                return {
                    id: item.id, title: userName + ' (Libur Manual)', start: item.tanggal,
                    backgroundColor: '#EF4444', borderColor: '#DC2626',
                    extendedProps: { nama: userName, shift: 'Libur Manual', jam: '-', is_global_holiday: false }
                };
            }

            let shiftName = item.shift ? item.shift.nama_shift : 'Tanpa Shift';
            let shiftColor = item.shift ? item.shift.warna : '#0ea5e9';
            
            // PENGAMAN SUBSTRING: Pastikan jam_masuk & jam_pulang tidak NULL
            let shiftTime = '-';
            if (item.shift && item.shift.jam_masuk && item.shift.jam_pulang) {
                shiftTime = item.shift.jam_masuk.substring(0,5) + ' - ' + item.shift.jam_pulang.substring(0,5);
            }

            return {
                id: item.id,
                title: userName + ' (' + shiftName + ')',
                start: item.tanggal,
                backgroundColor: shiftColor,
                borderColor: shiftColor,
                extendedProps: {
                    nama: userName,
                    shift: shiftName,
                    jam: shiftTime,
                    is_global_holiday: false
                }
            };
        });

        // 2. Tambahkan Master Libur ke Kalender
        rawHolidays.forEach(function(libur) {
            // Kotak Merah Background untuk Hari Libur Master
            formattedEvents.push({
                title: libur.keterangan,
                start: libur.tanggal,
                display: 'background',
                backgroundColor: '#FECACA'
            });
            // Teks Label Libur
            formattedEvents.push({
                title: 'LIBUR: ' + libur.keterangan,
                start: libur.tanggal,
                backgroundColor: '#DC2626',
                borderColor: '#DC2626',
                textColor: '#ffffff',
                extendedProps: {
                    is_global_holiday: true,
                    keterangan: libur.keterangan
                }
            });
        });

        // 3. Inisialisasi Kalender
        var calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            locale: 'id',
            height: 'auto',
            headerToolbar: { left: 'prev,next today', center: 'title', right: 'dayGridMonth,timeGridWeek,listWeek' },
            events: formattedEvents,
            
            eventClick: function(info) {
                // Abaikan klik jika yang diklik adalah Libur Global
                if(info.event.extendedProps.is_global_holiday) {
                    Swal.fire({
                        title: 'Hari Libur / Toko Tutup',
                        text: info.event.extendedProps.keterangan,
                        icon: 'info'
                    });
                    return;
                }

                let id = info.event.id;
                let nama = info.event.extendedProps.nama;
                let shift = info.event.extendedProps.shift;
                let jam = info.event.extendedProps.jam;
                let tanggal = info.event.start.toLocaleDateString('id-ID', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });

                Swal.fire({
                    title: 'Detail Jadwal',
                    html: `
                        <div class="text-left mt-4 mb-4 bg-gray-50 p-4 rounded-xl border border-gray-100">
                            <p class="mb-2 text-sm"><span class="text-gray-500 w-24 inline-block">Pegawai</span> <strong class="text-secondary">${nama}</strong></p>
                            <p class="mb-2 text-sm"><span class="text-gray-500 w-24 inline-block">Shift</span> <strong class="text-secondary">${shift}</strong></p>
                            <p class="mb-2 text-sm"><span class="text-gray-500 w-24 inline-block">Jam Kerja</span> <strong class="text-secondary">${jam}</strong></p>
                            <p class="mb-2 text-sm"><span class="text-gray-500 w-24 inline-block">Tanggal</span> <strong class="text-secondary">${tanggal}</strong></p>
                        </div>
                        <p class="text-sm text-red-500 font-medium">Apakah Anda ingin menghapus jadwal ini?</p>
                    `,
                    icon: 'info',
                    showCancelButton: true,
                    confirmButtonColor: '#EF4444',
                    cancelButtonColor: '#9CA3AF',
                    confirmButtonText: '<i class="fa-solid fa-trash"></i> Ya, Hapus',
                    cancelButtonText: 'Tutup'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: `/admin/jadwal/${id}`, type: 'POST',
                            data: { _method: 'DELETE', _token: '{{ csrf_token() }}' },
                            success: (res) => {
                                Toast.fire({ icon: 'success', title: res.message });
                                info.event.remove(); 
                            }
                        });
                    }
                });
            }
        });
        
        calendar.render();
    });
</script>
@endpush