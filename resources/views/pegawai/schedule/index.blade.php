@extends('layouts.pegawai')
@section('title', 'Jadwal Kerja')

@section('content')
<div class="p-5 flex flex-col h-full" x-data="{ autoSubmit() { $refs.filterForm.submit() } }">
    
    <!-- Header Back -->
    <div class="flex items-center gap-4 mb-6 relative">
        <a href="{{ route('pegawai.beranda') }}" class="absolute left-0 w-10 h-10 bg-gray-50 rounded-full flex items-center justify-center text-gray-600 hover:bg-gray-100 transition z-10">
            <i class="fa-solid fa-chevron-left"></i>
        </a>
        <h2 class="text-xl font-bold text-secondary text-center w-full">Jadwal Kerja</h2>
    </div>

    <!-- Filter Bulan (Auto Submit) -->
    <div class="mb-6 z-10">
        <form x-ref="filterForm" action="{{ route('pegawai.jadwal') }}" method="GET" class="relative">
            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                <i class="fa-regular fa-calendar text-primary"></i>
            </div>
            <input type="month" name="periode" value="{{ $periode }}" @change="autoSubmit()"
                   class="w-full pl-11 pr-4 py-3 bg-white border border-gray-200 rounded-2xl text-sm font-semibold text-secondary focus:border-primary focus:ring-1 focus:ring-primary outline-none shadow-sm transition appearance-none">
            <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none">
                <i class="fa-solid fa-chevron-down text-gray-400 text-xs"></i>
            </div>
        </form>
    </div>

    <!-- List Jadwal -->
    <div class="space-y-3 pb-8">
        @forelse($jadwal as $item)
            @php
                // Kita gunakan warna hex dari database, jika kosong pakai abu-abu
                $warnaShift = $item->shift->warna ?? '#9CA3AF';
            @endphp
            
            <div class="bg-white p-4 rounded-2xl border border-gray-100 shadow-[0_2px_10px_rgba(0,0,0,0.02)] flex items-center gap-4 relative overflow-hidden">
                <!-- Garis Warna Shift di Kiri -->
                <div class="absolute left-0 top-0 bottom-0 w-1.5" style="background-color: {{ $warnaShift }};"></div>
                
                <!-- Tanggal Kotak Kiri -->
                <div class="w-14 h-14 rounded-xl flex flex-col items-center justify-center flex-shrink-0" style="background-color: {{ $warnaShift }}15; color: {{ $warnaShift }};">
                    <span class="text-xs font-bold uppercase">{{ \Carbon\Carbon::parse($item->tanggal)->translatedFormat('D') }}</span>
                    <span class="text-xl font-bold leading-none">{{ \Carbon\Carbon::parse($item->tanggal)->format('d') }}</span>
                </div>
                
                <!-- Detail Shift Kanan -->
                <div class="flex-1">
                    <h4 class="font-bold text-secondary text-sm mb-1">{{ $item->shift->nama_shift }}</h4>
                    
                    @if($item->shift->jam_masuk && $item->shift->jam_pulang)
                        <div class="flex items-center text-xs text-gray-500 font-medium">
                            <i class="fa-regular fa-clock mr-1.5"></i>
                            {{ substr($item->shift->jam_masuk, 0, 5) }} - {{ substr($item->shift->jam_pulang, 0, 5) }} WIB
                        </div>
                    @else
                        <div class="flex items-center text-xs text-gray-400 italic">
                            <i class="fa-solid fa-bed mr-1.5"></i> Libur / Off
                        </div>
                    @endif
                </div>
                
            </div>
        @empty
            <!-- State jika data kosong -->
            <div class="flex flex-col items-center justify-center py-12 px-4 text-center">
                <div class="w-20 h-20 bg-gray-50 rounded-full flex items-center justify-center mb-4">
                    <i class="fa-solid fa-mug-hot text-3xl text-gray-300"></i>
                </div>
                <h3 class="font-bold text-gray-700">Jadwal Belum Tersedia</h3>
                <p class="text-sm text-gray-400 mt-1">HRD belum menetapkan jadwal kerja untuk Anda di bulan ini.</p>
            </div>
        @endforelse
    </div>

</div>
@endsection