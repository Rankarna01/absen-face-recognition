@extends('layouts.pegawai')
@section('title', 'Laporan & Analitik')

@push('scripts-head')
<!-- Load Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
@endpush

@section('content')
<div class="p-5 flex flex-col h-full" x-data="{ autoSubmit() { $refs.filterForm.submit() } }">
    
    <!-- Header Back -->
    <div class="flex items-center gap-4 mb-6 relative">
        <a href="{{ route('pegawai.beranda') }}" class="absolute left-0 w-10 h-10 bg-gray-50 rounded-full flex items-center justify-center text-gray-600 hover:bg-gray-100 transition z-10">
            <i class="fa-solid fa-chevron-left"></i>
        </a>
        <h2 class="text-xl font-bold text-secondary text-center w-full">Laporan Kehadiran</h2>
    </div>

    <!-- Filter Tahun -->
    <div class="mb-6 z-10">
        <form x-ref="filterForm" action="{{ route('pegawai.laporan.index') }}" method="GET" class="relative">
            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                <i class="fa-regular fa-calendar-days text-primary"></i>
            </div>
            <select name="tahun" @change="autoSubmit()" class="w-full pl-11 pr-4 py-3 bg-white border border-gray-200 rounded-2xl text-sm font-semibold text-secondary focus:border-primary focus:ring-1 focus:ring-primary outline-none shadow-sm transition appearance-none">
                @php $currentYear = date('Y'); @endphp
                @for($i = $currentYear; $i >= $currentYear - 2; $i--)
                    <option value="{{ $i }}" {{ $year == $i ? 'selected' : '' }}>Statistik Tahun {{ $i }}</option>
                @endfor
            </select>
            <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none">
                <i class="fa-solid fa-chevron-down text-gray-400 text-xs"></i>
            </div>
        </form>
    </div>

    <!-- Ringkasan Angka (Summary) -->
    <div class="grid grid-cols-2 gap-4 mb-6">
        <div class="bg-white p-4 rounded-2xl border border-gray-100 shadow-sm text-center">
            <p class="text-xs text-gray-400 font-bold uppercase tracking-wider mb-1">Total Hadir</p>
            <h3 class="text-2xl font-bold text-green-500">{{ $summary['total_hadir'] }} <span class="text-xs font-medium text-gray-400">Hari</span></h3>
        </div>
        <div class="bg-white p-4 rounded-2xl border border-gray-100 shadow-sm text-center">
            <p class="text-xs text-gray-400 font-bold uppercase tracking-wider mb-1">Total Telat</p>
            <h3 class="text-2xl font-bold text-orange-500">{{ $summary['total_terlambat'] }} <span class="text-xs font-medium text-gray-400">Hari</span></h3>
        </div>
        <div class="bg-white p-4 rounded-2xl border border-gray-100 shadow-sm text-center">
            <p class="text-xs text-gray-400 font-bold uppercase tracking-wider mb-1">Izin / Cuti</p>
            <h3 class="text-2xl font-bold text-purple-500">{{ $summary['total_izin'] }} <span class="text-xs font-medium text-gray-400">Hari</span></h3>
        </div>
        <div class="bg-white p-4 rounded-2xl border border-gray-100 shadow-sm text-center bg-gray-50">
            <p class="text-xs text-gray-400 font-bold uppercase tracking-wider mb-1">Total Data</p>
            <h3 class="text-2xl font-bold text-secondary">{{ $summary['total_hari'] }} <span class="text-xs font-medium text-gray-400">Rekor</span></h3>
        </div>
    </div>

    <!-- Area Grafik (Chart.js) -->
    <div class="bg-white p-5 rounded-3xl border border-gray-100 shadow-[0_2px_10px_rgba(0,0,0,0.02)] mb-8">
        <h3 class="text-sm font-bold text-secondary mb-4 pl-1 border-l-4 border-primary">Grafik Tren Bulanan</h3>
        <div class="relative w-full h-64">
            <canvas id="attendanceChart"></canvas>
        </div>
    </div>

    <!-- Tombol Download / Export -->
    <a href="{{ route('pegawai.laporan.export', ['tahun' => $year]) }}" class="w-full bg-primary hover:bg-primary_hover text-white font-bold py-4 rounded-2xl transition shadow-[0_8px_20px_rgba(245,166,35,0.3)] flex justify-center items-center gap-2 mb-8">
        <i class="fa-solid fa-file-excel text-lg"></i> Unduh Riwayat {{ $year }} (Excel)
    </a>

</div>
@endsection

@push('scripts')
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const ctx = document.getElementById('attendanceChart').getContext('2d');
        
        // Terima data dari backend PHP ke JS
        const dataHadir = {!! json_encode(array_values($chartData['hadir'])) !!};
        const dataTerlambat = {!! json_encode(array_values($chartData['terlambat'])) !!};
        const dataIzin = {!! json_encode(array_values($chartData['izin'])) !!};

        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Ags', 'Sep', 'Okt', 'Nov', 'Des'],
                datasets: [
                    {
                        label: 'Hadir',
                        data: dataHadir,
                        backgroundColor: '#22C55E', // Tailwind Green-500
                        borderRadius: 4,
                    },
                    {
                        label: 'Terlambat',
                        data: dataTerlambat,
                        backgroundColor: '#F97316', // Tailwind Orange-500
                        borderRadius: 4,
                    },
                    {
                        label: 'Izin/Cuti',
                        data: dataIzin,
                        backgroundColor: '#A855F7', // Tailwind Purple-500
                        borderRadius: 4,
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { usePointStyle: true, boxWidth: 8, font: { size: 10, family: "'Poppins', sans-serif" } }
                    }
                },
                scales: {
                    x: {
                        stacked: true, // Membuat bar menumpuk agar hemat tempat di layar HP
                        grid: { display: false }
                    },
                    y: {
                        stacked: true,
                        beginAtZero: true,
                        ticks: { stepSize: 5 },
                        grid: { borderDash: [5, 5] }
                    }
                }
            }
        });
    });
</script>
@endpush