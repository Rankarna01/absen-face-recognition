@extends('layouts.admin')
@section('title', 'Laporan Rekapitulasi')
@section('header_title', 'Laporan Bulanan')

@push('styles')
<style>
    /* CSS Khusus Print (PDF) */
    @media print {
        body * { visibility: hidden; }
        .print-area, .print-area * { visibility: visible; }
        .print-area { position: absolute; left: 0; top: 0; width: 100%; }
        .no-print { display: none !important; }
        .print-title { display: block !important; text-align: center; margin-bottom: 20px; }
    }
</style>
@endpush

@section('content')
<div x-data="{ activeTab: 'absensi' }" class="space-y-6">

    <!-- Header & Filter -->
    <div class="bg-base p-6 rounded-2xl border border-gray-100 shadow-sm flex flex-col md:flex-row justify-between items-start md:items-center gap-4 no-print">
        <div>
            <h3 class="font-bold text-xl text-secondary">Rekapitulasi Laporan</h3>
            <p class="text-sm text-gray-500 mt-1">Laporan komprehensif kehadiran dan tingkat keterlambatan.</p>
        </div>
        
        <div class="flex flex-col sm:flex-row gap-3 w-full md:w-auto">
            <!-- Filter Form -->
            <form action="{{ route('admin.laporan.index') }}" method="GET" class="flex gap-2 w-full sm:w-auto">
                <input type="month" name="periode" value="{{ $periode }}" class="px-4 py-2 rounded-xl border border-gray-200 focus:border-primary outline-none bg-white text-sm w-full sm:w-auto">
                <button type="submit" class="bg-gray-800 hover:bg-gray-900 text-white px-4 py-2 rounded-xl text-sm font-semibold transition">Filter</button>
            </form>

            <!-- Export Buttons -->
            <button onclick="window.print()" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-xl text-sm font-semibold transition flex justify-center items-center gap-2 shadow-lg shadow-red-500/30">
                <i class="fa-solid fa-file-pdf"></i> PDF
            </button>
            
            <!-- Excel akan mengexport berdasarkan Tab mana yang sedang aktif -->
            <a :href="`{{ route('admin.laporan.exportExcel') }}?periode={{ $periode }}&jenis=` + activeTab" class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-xl text-sm font-semibold transition flex justify-center items-center gap-2 shadow-lg shadow-green-500/30">
                <i class="fa-solid fa-file-excel"></i> Excel
            </a>
        </div>
    </div>

    <!-- Custom Tabs -->
    <div class="flex space-x-2 border-b border-gray-200 no-print">
        <button @click="activeTab = 'absensi'" 
                :class="activeTab === 'absensi' ? 'border-primary text-primary border-b-2 font-bold' : 'text-gray-500 hover:text-gray-700'"
                class="pb-3 px-4 text-sm transition flex items-center gap-2">
            <i class="fa-solid fa-clipboard-user"></i> Laporan Absensi
        </button>
        <button @click="activeTab = 'keterlambatan'" 
                :class="activeTab === 'keterlambatan' ? 'border-orange-500 text-orange-500 border-b-2 font-bold' : 'text-gray-500 hover:text-gray-700'"
                class="pb-3 px-4 text-sm transition flex items-center gap-2">
            <i class="fa-solid fa-clock-rotate-left"></i> Laporan Keterlambatan
        </button>
    </div>

    <!-- AREA CETAK PRINT -->
    <div class="print-area">
        
        <!-- Judul Header Print (Hanya muncul saat print) -->
        <div class="hidden print-title mb-6">
            <h2 class="text-2xl font-bold" x-text="activeTab === 'absensi' ? 'Laporan Rekapitulasi Absensi Bulanan' : 'Laporan Analisis Keterlambatan Pegawai'"></h2>
            <p class="text-gray-500">Periode: {{ \Carbon\Carbon::createFromFormat('Y-m', $periode)->translatedFormat('F Y') }}</p>
            <hr class="mt-4 border-gray-300">
        </div>

        <!-- ==================== TAB: LAPORAN ABSENSI ==================== -->
        <div x-show="activeTab === 'absensi'" class="bg-base rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-50 border-b border-gray-100 text-sm text-gray-600">
                            <th class="px-6 py-4 font-semibold">Pegawai</th>
                            <th class="px-6 py-4 font-semibold text-center text-green-600">Hadir</th>
                            <th class="px-6 py-4 font-semibold text-center text-orange-600">Terlambat</th>
                            <th class="px-6 py-4 font-semibold text-center text-purple-600">Izin/Cuti</th>
                            <th class="px-6 py-4 font-semibold text-center text-red-600">Alfa</th>
                            <th class="px-6 py-4 font-semibold text-center">Total Hari</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($laporan as $row)
                        <tr class="hover:bg-gray-50/50 transition">
                            <td class="px-6 py-4">
                                <p class="font-semibold text-secondary">{{ $row['user']->name }}</p>
                                <p class="text-xs text-gray-500">{{ $row['user']->employee->position->nama_jabatan ?? 'Pegawai' }}</p>
                            </td>
                            <td class="px-6 py-4 text-center font-bold text-green-600">{{ $row['hadir'] }}</td>
                            <td class="px-6 py-4 text-center font-bold text-orange-600">{{ $row['terlambat'] }}</td>
                            <td class="px-6 py-4 text-center font-bold text-purple-600">{{ $row['izin_cuti'] }}</td>
                            <td class="px-6 py-4 text-center font-bold text-red-600">{{ $row['alfa'] }}</td>
                            <td class="px-6 py-4 text-center font-bold bg-gray-50">{{ $row['total_hari'] }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="6" class="px-6 py-10 text-center text-gray-500">Belum ada data pegawai.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- ==================== TAB: LAPORAN KETERLAMBATAN ==================== -->
        <div x-show="activeTab === 'keterlambatan'" x-cloak class="bg-base rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-orange-50 border-b border-orange-100 text-sm text-orange-800">
                            <th class="px-6 py-4 font-semibold w-16 text-center">Peringkat</th>
                            <th class="px-6 py-4 font-semibold">Pegawai</th>
                            <th class="px-6 py-4 font-semibold text-center">Total Telat (Hari)</th>
                            <th class="px-6 py-4 font-semibold text-center">Tingkat Kedisiplinan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($laporanKeterlambatan as $index => $row)
                            @if($row['terlambat'] > 0) <!-- Hanya tampilkan yang pernah telat -->
                            <tr class="hover:bg-gray-50/50 transition">
                                <td class="px-6 py-4 text-center">
                                    @if($index == 0)
                                        <span class="text-2xl" title="Paling Sering Telat">🚨</span>
                                    @else
                                        <span class="font-bold text-gray-400">#{{ $index + 1 }}</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    <p class="font-semibold text-secondary">{{ $row['user']->name }}</p>
                                    <p class="text-xs text-gray-500">{{ $row['user']->employee->position->nama_jabatan ?? 'Pegawai' }}</p>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span class="bg-orange-100 text-orange-600 px-4 py-1 rounded-full font-bold border border-orange-200">
                                        {{ $row['terlambat'] }} Kali
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <!-- Logika warna untuk kedisiplinan -->
                                    @php
                                        $persenTelat = $row['total_hari'] > 0 ? ($row['terlambat'] / $row['total_hari']) * 100 : 0;
                                    @endphp
                                    <div class="w-full bg-gray-200 rounded-full h-2.5 mb-1 max-w-[150px] mx-auto">
                                        <div class="{{ $persenTelat > 30 ? 'bg-red-500' : 'bg-orange-400' }} h-2.5 rounded-full" style="width: {{ $persenTelat }}%"></div>
                                    </div>
                                    <p class="text-xs text-gray-500">{{ number_format($persenTelat, 1) }}% hari kerja telat</p>
                                </td>
                            </tr>
                            @endif
                        @empty
                        <tr>
                            <td colspan="4" class="px-6 py-10 text-center text-gray-500">
                                <i class="fa-solid fa-award text-4xl mb-3 text-yellow-400 block"></i>
                                Luar biasa! Bulan ini tidak ada satupun pegawai yang terlambat.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div> <!-- End Print Area -->
</div>
@endsection