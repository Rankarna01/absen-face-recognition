@extends('layouts.admin')
@section('title', 'Data Absensi Pegawai')
@section('header_title', 'Data Absensi Pegawai')

@push('styles')
<style>
    /* CSS Khusus Print (Merapikan tampilan saat di PDF-kan) */
    @media print {
        body * { visibility: hidden; }
        .print-area, .print-area * { visibility: visible; }
        .print-area { position: absolute; left: 0; top: 0; width: 100%; }
        .no-print { display: none !important; }
    }
</style>
@endpush

@section('content')
<div class="space-y-6" x-data="{ filterType: '{{ request('filter_waktu', 'hari_ini') }}' }">
    
    <!-- Header & Action Buttons -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <h2 class="text-2xl font-bold text-secondary">Monitoring Absensi</h2>
            <p class="text-sm text-gray-500 mt-1">Pantau kehadiran, keterlambatan, dan histori pegawai.</p>
        </div>
        
        <div class="flex gap-2 no-print">
            <!-- Tombol Cetak PDF (Gunakan fungsi print browser) -->
            <button onclick="window.print()" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2.5 rounded-xl text-sm font-semibold transition flex items-center gap-2 shadow-lg shadow-red-500/30">
                <i class="fa-solid fa-file-pdf"></i> Cetak PDF
            </button>
            
            <!-- Tombol Export Excel -->
            <!-- Menggunakan request()->all() untuk membawa semua parameter filter saat ini ke controller export -->
            <a href="{{ route('admin.absensi.exportExcel', request()->all()) }}" class="bg-green-500 hover:bg-green-600 text-white px-4 py-2.5 rounded-xl text-sm font-semibold transition flex items-center gap-2 shadow-lg shadow-green-500/30">
                <i class="fa-solid fa-file-excel"></i> Export Excel
            </a>
        </div>
    </div>

    <!-- Filter Card -->
    <div class="bg-base p-6 rounded-2xl border border-gray-100 shadow-sm no-print">
        <form action="{{ route('admin.absensi.index') }}" method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
            
            <!-- Filter Tipe Waktu -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Periode</label>
                <select name="filter_waktu" x-model="filterType" class="w-full px-4 py-2 rounded-xl border border-gray-200 focus:border-primary focus:ring-1 focus:ring-primary outline-none bg-white">
                    <option value="hari_ini">Hari Ini</option>
                    <option value="minggu_ini">Minggu Ini</option>
                    <option value="bulan_ini">Bulan Ini</option>
                    <option value="tahun_ini">Tahun Ini</option>
                    <option value="custom">Kustom Tanggal</option>
                </select>
            </div>

            <!-- Custom Tanggal (Hanya muncul jika filterType == 'custom') -->
            <div x-show="filterType === 'custom'" class="flex gap-2 items-center col-span-1 md:col-span-2">
                <div class="w-full">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Dari Tanggal</label>
                    <input type="date" name="tanggal_awal" value="{{ request('tanggal_awal') }}" class="w-full px-4 py-2 rounded-xl border border-gray-200 focus:border-primary outline-none">
                </div>
                <span class="mt-6 text-gray-400">-</span>
                <div class="w-full">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Sampai Tanggal</label>
                    <input type="date" name="tanggal_akhir" value="{{ request('tanggal_akhir') }}" class="w-full px-4 py-2 rounded-xl border border-gray-200 focus:border-primary outline-none">
                </div>
            </div>

            <!-- Filter Pegawai -->
            <div :class="filterType === 'custom' ? 'col-span-1' : 'col-span-2'">
                <label class="block text-sm font-medium text-gray-700 mb-1">Pegawai (Opsional)</label>
                <select name="user_id" class="w-full px-4 py-2 rounded-xl border border-gray-200 focus:border-primary outline-none bg-white">
                    <option value="">-- Semua Pegawai --</option>
                    @foreach($employees as $emp)
                        <option value="{{ $emp->id }}" {{ request('user_id') == $emp->id ? 'selected' : '' }}>
                            {{ $emp->nip }} - {{ $emp->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Tombol Filter -->
            <div>
                <button type="submit" class="w-full bg-primary hover:bg-primary_hover text-white px-4 py-2.5 rounded-xl text-sm font-semibold transition shadow-md shadow-primary/30">
                    <i class="fa-solid fa-filter mr-1"></i> Terapkan Filter
                </button>
            </div>
        </form>
    </div>

    <!-- Area yang akan di-print (Table) -->
    <div class="bg-base rounded-2xl border border-gray-100 shadow-sm overflow-hidden print-area">
        <!-- Header Khusus Print (Tersembunyi di web) -->
        <div class="hidden print:block p-6 text-center border-b border-gray-200">
            <h2 class="text-2xl font-bold">Laporan Absensi Family Market</h2>
            <p class="text-gray-500">Tanggal Cetak: {{ \Carbon\Carbon::now()->format('d/m/Y H:i') }}</p>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-100 text-sm text-gray-500">
                        <th class="px-6 py-4 font-semibold">Tanggal</th>
                        <th class="px-6 py-4 font-semibold">Pegawai</th>
                        <th class="px-6 py-4 font-semibold text-center">Jam Masuk</th>
                        <th class="px-6 py-4 font-semibold text-center">Jam Pulang</th>
                        <th class="px-6 py-4 font-semibold">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($attendances as $absen)
                    <tr class="hover:bg-gray-50/50 transition">
                        <td class="px-6 py-4 text-secondary font-medium">
                            {{ \Carbon\Carbon::parse($absen->tanggal)->translatedFormat('l, d M Y') }}
                        </td>
                        <td class="px-6 py-4">
                            <p class="font-semibold text-secondary">{{ $absen->user->name }}</p>
                            <p class="text-xs text-gray-500">{{ $absen->user->employee->position->nama_jabatan ?? 'Pegawai' }}</p>
                        </td>
                        <td class="px-6 py-4 text-center">
                            @if($absen->jam_masuk)
                                <span class="bg-green-50 text-green-600 px-3 py-1 rounded-full text-sm font-medium border border-green-100">{{ $absen->jam_masuk }}</span>
                            @else
                                <span class="text-gray-400">-</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-center">
                            @if($absen->jam_pulang)
                                <span class="bg-blue-50 text-blue-600 px-3 py-1 rounded-full text-sm font-medium border border-blue-100">{{ $absen->jam_pulang }}</span>
                            @else
                                <span class="text-gray-400">-</span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            @php
                                $color = match($absen->status_kehadiran) {
                                    'hadir' => 'bg-green-100 text-green-600',
                                    'terlambat' => 'bg-orange-100 text-orange-600',
                                    'alfa' => 'bg-red-100 text-red-600',
                                    'izin', 'cuti' => 'bg-purple-100 text-purple-600',
                                    default => 'bg-gray-100 text-gray-600'
                                };
                            @endphp
                            <span class="{{ $color }} px-3 py-1 rounded-md text-xs font-bold uppercase tracking-wider">
                                {{ $absen->status_kehadiran }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-10 text-center text-gray-500">
                            <i class="fa-regular fa-folder-open text-4xl mb-3 text-gray-300 block"></i>
                            Tidak ada data absensi untuk periode/filter ini.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection