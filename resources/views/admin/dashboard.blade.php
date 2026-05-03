@extends('layouts.admin')
@section('title', 'Dashboard Admin')
@section('header_title', 'Dashboard')

@section('content')
<!-- Welcome Section -->
<div class="flex justify-between items-end mb-8">
    <div>
        <p class="text-gray-500">Selamat datang kembali,</p>
        <h2 class="text-2xl font-bold text-secondary">Administrator</h2>
        <p class="text-sm text-gray-400 mt-1">Berikut ringkasan aktivitas hari ini.</p>
    </div>
    <div class="bg-base px-4 py-2 rounded-lg border border-gray-100 flex items-center gap-2 text-sm text-gray-600 shadow-sm">
        <i class="fa-regular fa-calendar"></i>
        {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}
    </div>
</div>

<!-- Stats Cards -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <!-- Card Total -->
    <div class="bg-primary/5 rounded-2xl p-6 border border-primary/20 relative overflow-hidden">
        <div class="flex items-center gap-4 mb-4">
            <div class="w-12 h-12 bg-primary/20 text-primary rounded-xl flex items-center justify-center text-xl">
                <i class="fa-solid fa-users"></i>
            </div>
            <div>
                <p class="text-sm font-medium text-gray-500">Total Karyawan</p>
                <h3 class="text-2xl font-bold text-secondary">128</h3>
            </div>
        </div>
        <p class="text-xs text-gray-500">Semua Karyawan</p>
    </div>

    <!-- Card Hadir -->
    <div class="bg-base rounded-2xl p-6 border border-gray-100 shadow-sm">
        <div class="flex items-center gap-4 mb-4">
            <div class="w-12 h-12 bg-green-100 text-green-500 rounded-xl flex items-center justify-center text-xl">
                <i class="fa-solid fa-user-check"></i>
            </div>
            <div>
                <p class="text-sm font-medium text-gray-500">Hadir Hari Ini</p>
                <h3 class="text-2xl font-bold text-secondary">92</h3>
            </div>
        </div>
        <p class="text-xs text-gray-400">72% dari total karyawan</p>
    </div>

    <!-- Card Terlambat -->
    <div class="bg-base rounded-2xl p-6 border border-gray-100 shadow-sm">
        <div class="flex items-center gap-4 mb-4">
            <div class="w-12 h-12 bg-orange-100 text-orange-500 rounded-xl flex items-center justify-center text-xl">
                <i class="fa-solid fa-clock-rotate-left"></i>
            </div>
            <div>
                <p class="text-sm font-medium text-gray-500">Terlambat</p>
                <h3 class="text-2xl font-bold text-secondary">18</h3>
            </div>
        </div>
        <p class="text-xs text-gray-400">14% dari total karyawan</p>
    </div>

    <!-- Card Tidak Hadir -->
    <div class="bg-base rounded-2xl p-6 border border-gray-100 shadow-sm">
        <div class="flex items-center gap-4 mb-4">
            <div class="w-12 h-12 bg-red-100 text-red-500 rounded-xl flex items-center justify-center text-xl">
                <i class="fa-solid fa-user-xmark"></i>
            </div>
            <div>
                <p class="text-sm font-medium text-gray-500">Tidak Hadir</p>
                <h3 class="text-2xl font-bold text-secondary">18</h3>
            </div>
        </div>
        <p class="text-xs text-gray-400">14% dari total karyawan</p>
    </div>
</div>

<!-- Charts Section -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
    <div class="lg:col-span-2 bg-base p-6 rounded-2xl border border-gray-100 shadow-sm">
        <div class="flex justify-between items-center mb-4">
            <h3 class="font-semibold text-secondary">Grafik Kehadiran Mingguan</h3>
            <select class="text-sm border-gray-200 rounded-lg text-gray-500 focus:ring-primary focus:border-primary">
                <option>Minggu Ini</option>
            </select>
        </div>
        <!-- Canvas Chart.js -->
        <canvas id="barChart" height="100"></canvas>
    </div>
    
    <div class="bg-base p-6 rounded-2xl border border-gray-100 shadow-sm flex flex-col justify-center items-center">
        <h3 class="font-semibold text-secondary w-full text-left mb-4">Ringkasan Kehadiran</h3>
        <div class="w-48 h-48 relative">
            <canvas id="doughnutChart"></canvas>
            <div class="absolute inset-0 flex flex-col items-center justify-center mt-2">
                <span class="text-2xl font-bold">128</span>
                <span class="text-xs text-gray-400">Karyawan</span>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Dummy Data untuk Chart.js (Bisa kamu dinamiskan dari Controller nantinya)
    const ctxBar = document.getElementById('barChart').getContext('2d');
    new Chart(ctxBar, {
        type: 'bar',
        data: {
            labels: ['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'],
            datasets: [{
                label: 'Kehadiran',
                data: [85, 70, 80, 90, 75, 40, 20],
                backgroundColor: '#60A5FA', // Warna biru muda sesuai desain
                borderRadius: 4,
                barThickness: 12
            }]
        },
        options: { responsive: true, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, grid: { display: false } }, x: { grid: { display: false } } } }
    });

    const ctxDoughnut = document.getElementById('doughnutChart').getContext('2d');
    new Chart(ctxDoughnut, {
        type: 'doughnut',
        data: {
            labels: ['Hadir', 'Terlambat', 'Tidak Hadir'],
            datasets: [{
                data: [92, 18, 18],
                backgroundColor: ['#22C55E', '#F5A623', '#EF4444'],
                borderWidth: 0,
                cutout: '80%'
            }]
        },
        options: { responsive: true, plugins: { legend: { display: false } } }
    });
</script>
@endpush