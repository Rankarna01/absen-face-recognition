@extends('layouts.pegawai')
@section('title', 'Beranda')

@section('content')
<div class="p-5" x-data="realtimeClock()">
    
    <!-- Top Bar: Logo & Notifikasi -->
    <div class="flex justify-between items-center mb-6">
        <div class="flex items-center gap-2">
            <div class="w-8 h-8 bg-primary rounded-md flex items-center justify-center font-bold text-white text-sm">FM</div>
            <div class="font-bold text-lg leading-tight">Family<br><span class="text-primary font-normal text-sm">market</span></div>
        </div>
        <button class="relative text-gray-500 hover:text-primary">
            <i class="fa-regular fa-bell text-xl"></i>
            <span class="absolute top-0 right-0 w-2 h-2 bg-red-500 rounded-full border-2 border-white"></span>
        </button>
    </div>

    <!-- Greeting & Profile -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <p class="text-gray-500 text-sm">Selamat pagi,</p>
            <h2 class="text-2xl font-bold text-secondary">{{ $user->name }}</h2>
            <div class="inline-block bg-primary text-white text-xs font-bold px-3 py-1 rounded-full mt-1">
                NIP: {{ $user->nip }}
            </div>
        </div>
        <div class="w-14 h-14 rounded-full border-2 border-primary/20 p-0.5">
            <img src="{{ $user->employee && $user->employee->foto ? asset('storage/'.$user->employee->foto) : 'https://ui-avatars.com/api/?name='.$user->name.'&background=F5A623&color=fff' }}" 
                 class="w-full h-full rounded-full object-cover">
        </div>
    </div>

    <!-- Realtime Server Time Card (Warna Kuning) -->
    <div class="bg-primary rounded-3xl p-6 text-white text-center shadow-lg shadow-primary/30 mb-6 relative overflow-hidden">
        <div class="absolute -right-8 -top-8 w-32 h-32 bg-white/10 rounded-full blur-2xl"></div>
        <div class="absolute -left-8 -bottom-8 w-24 h-24 bg-white/10 rounded-full blur-xl"></div>
        
        <div class="relative z-10">
            <div class="flex items-center justify-center gap-2 text-white/90 text-xs font-medium mb-1">
                <i class="fa-regular fa-clock"></i> WAKTU SERVER (WIB)
            </div>
            <h1 class="text-5xl font-bold tracking-wider" x-text="time">--:--:--</h1>
            <p class="text-sm font-medium mt-1 text-white/90" x-text="date">Memuat tanggal...</p>
        </div>
    </div>

    <!-- Status Absen Card -->
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 mb-6 grid grid-cols-2 gap-4 divide-x divide-gray-100">
        <!-- Masuk -->
        <div class="text-center">
            <div class="inline-flex items-center gap-1 px-3 py-1 rounded-full bg-green-50 text-green-600 text-xs font-bold mb-2">
                <i class="fa-solid fa-arrow-right-to-bracket"></i> Masuk
            </div>
            @if($absenHariIni && $absenHariIni->jam_masuk)
                <p class="font-bold text-secondary">{{ substr($absenHariIni->jam_masuk, 0, 5) }} WIB</p>
            @else
                <p class="text-sm text-gray-400 font-medium">Belum absen</p>
            @endif
        </div>
        <!-- Pulang -->
        <div class="text-center">
            <div class="inline-flex items-center gap-1 px-3 py-1 rounded-full bg-red-50 text-red-600 text-xs font-bold mb-2">
                <i class="fa-solid fa-arrow-right-from-bracket"></i> Pulang
            </div>
            @if($absenHariIni && $absenHariIni->jam_pulang)
                <p class="font-bold text-secondary">{{ substr($absenHariIni->jam_pulang, 0, 5) }} WIB</p>
            @else
                <p class="text-sm text-gray-400 font-medium">Belum absen</p>
            @endif
        </div>
    </div>

    <!-- Ringkasan Bulan Ini -->
    <div class="mb-8">
        <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-3 pl-1 border-l-4 border-primary">Ringkasan Bulan Ini</h3>
        <div class="grid grid-cols-3 gap-3">
            <div class="bg-white border border-gray-100 shadow-sm rounded-2xl p-4 text-center">
                <h4 class="text-2xl font-bold text-secondary">{{ $summary['hadir'] }}</h4>
                <p class="text-xs font-medium text-green-500 mt-1">Hadir</p>
            </div>
            <div class="bg-white border border-gray-100 shadow-sm rounded-2xl p-4 text-center">
                <h4 class="text-2xl font-bold text-secondary">{{ $summary['izin'] }}</h4>
                <p class="text-xs font-medium text-primary mt-1">Izin</p>
            </div>
            <div class="bg-white border border-gray-100 shadow-sm rounded-2xl p-4 text-center">
                <h4 class="text-2xl font-bold text-secondary">{{ $summary['terlambat'] }}</h4>
                <p class="text-xs font-medium text-red-500 mt-1">Terlambat</p>
            </div>
        </div>
    </div>

    <!-- ========================================== -->
    <!-- MENU UTAMA (Grid 3x3 seperti gambar)       -->
    <!-- ========================================== -->
    <div class="mb-4">
        <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-3 pl-1 border-l-4 border-primary">Menu</h3>
        <div class="grid grid-cols-3 gap-4">
            
            <a href="{{ route('pegawai.riwayat') }}" class="bg-white p-4 rounded-2xl border border-gray-100 shadow-sm flex flex-col items-center justify-center gap-2 transition transform hover:scale-105 active:scale-95">
                <i class="fa-solid fa-calendar-check text-2xl text-primary"></i>
                <span class="text-[10px] font-bold text-secondary text-center leading-tight mt-1">Riwayat<br>Absensi</span>
            </a>
            
            <a href="{{ route('pegawai.jadwal') }}" class="bg-white p-4 rounded-2xl border border-gray-100 shadow-sm flex flex-col items-center justify-center gap-2 transition transform hover:scale-105 active:scale-95">
                <i class="fa-solid fa-calendar-days text-2xl text-primary"></i>
                <span class="text-[10px] font-bold text-secondary text-center leading-tight mt-1">Jadwal<br>Kerja</span>
            </a>
            
            <a href="{{ route('pegawai.izin.index') }}" class="bg-white p-4 rounded-2xl border border-gray-100 shadow-sm flex flex-col items-center justify-center gap-2 transition transform hover:scale-105 active:scale-95">
                <i class="fa-solid fa-file-signature text-2xl text-primary"></i>
                <span class="text-[10px] font-bold text-secondary text-center leading-tight mt-1">Pengajuan<br>Izin</span>
            </a>

            <a href="#" class="bg-white p-4 rounded-2xl border border-gray-100 shadow-sm flex flex-col items-center justify-center gap-2 transition transform hover:scale-105 active:scale-95">
                <i class="fa-solid fa-user-clock text-2xl text-primary"></i>
                <span class="text-[10px] font-bold text-secondary text-center leading-tight mt-1">Pengajuan<br>Cuti</span>
            </a>

           <a href="{{ route('pegawai.slip.index') }}" class="bg-white p-4 rounded-2xl border border-gray-100 shadow-sm flex flex-col items-center justify-center gap-2 transition transform hover:scale-105 active:scale-95">
                <i class="fa-solid fa-money-check-dollar text-2xl text-primary"></i>
                <span class="text-[10px] font-bold text-secondary text-center leading-tight mt-1">Slip<br>Gaji</span>
            </a>

           <a href="{{ route('pegawai.laporan.index') }}" class="bg-white p-4 rounded-2xl border border-gray-100 shadow-sm flex flex-col items-center justify-center gap-2 transition transform hover:scale-105 active:scale-95">
                <i class="fa-solid fa-chart-pie text-2xl text-primary"></i>
                <span class="text-[10px] font-bold text-secondary text-center leading-tight mt-1">Laporan<br>Data</span>
            </a>
            <a href="#" class="bg-white p-4 rounded-2xl border border-gray-100 shadow-sm flex flex-col items-center justify-center gap-2 transition transform hover:scale-105 active:scale-95">
                <i class="fa-solid fa-bullhorn text-2xl text-primary"></i>
                <span class="text-[10px] font-bold text-secondary text-center leading-tight mt-1">Info &<br>Pengumuman</span>
            </a>

            <a href="{{ route('pegawai.profil.index') }}" class="bg-white p-4 rounded-2xl border border-gray-100 shadow-sm flex flex-col items-center justify-center gap-2 transition transform hover:scale-105 active:scale-95">
                <i class="fa-solid fa-user text-2xl text-primary"></i>
                <span class="text-[10px] font-bold text-secondary text-center leading-tight mt-1">Profil<br>Saya</span>
            </a>

            <a href="#" class="bg-white p-4 rounded-2xl border border-gray-100 shadow-sm flex flex-col items-center justify-center gap-2 transition transform hover:scale-105 active:scale-95">
                <i class="fa-solid fa-gear text-2xl text-primary"></i>
                <span class="text-[10px] font-bold text-secondary text-center leading-tight mt-1">Pengaturan<br>Akun</span>
            </a>
            
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('realtimeClock', () => ({
            time: '00:00:00',
            date: 'Memuat...',
            
            init() {
                this.updateClock();
                setInterval(() => { this.updateClock(); }, 1000);
            },
            
            updateClock() {
                const now = new Date();
                this.time = now.toLocaleTimeString('id-ID', { hour12: false, hour: '2-digit', minute:'2-digit', second:'2-digit' });
                this.date = now.toLocaleDateString('id-ID', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' });
            }
        }));
    });
</script>
@endpush