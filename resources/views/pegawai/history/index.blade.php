@extends('layouts.pegawai')
@section('title', 'Riwayat Absensi')

@section('content')
<div class="p-5 flex flex-col h-full" x-data="{ autoSubmit() { $refs.filterForm.submit() } }">
    
    <!-- Header Back -->
    <div class="flex items-center gap-4 mb-6 relative">
        <a href="{{ route('pegawai.beranda') }}" class="absolute left-0 w-10 h-10 bg-gray-50 rounded-full flex items-center justify-center text-gray-600 hover:bg-gray-100 transition z-10">
            <i class="fa-solid fa-chevron-left"></i>
        </a>
        <h2 class="text-xl font-bold text-secondary text-center w-full">Riwayat Absensi</h2>
    </div>

    <!-- Filter Bulan (Auto Submit saat dipilih) -->
    <div class="mb-6 z-10">
        <form x-ref="filterForm" action="{{ route('pegawai.riwayat') }}" method="GET" class="relative">
            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                <i class="fa-regular fa-calendar text-primary"></i>
            </div>
            <!-- Input Month akan memanggil native date picker di HP -->
            <input type="month" name="periode" value="{{ $periode }}" @change="autoSubmit()"
                   class="w-full pl-11 pr-4 py-3 bg-white border border-gray-200 rounded-2xl text-sm font-semibold text-secondary focus:border-primary focus:ring-1 focus:ring-primary outline-none shadow-sm transition appearance-none">
            <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none">
                <i class="fa-solid fa-chevron-down text-gray-400 text-xs"></i>
            </div>
        </form>
    </div>

    <!-- List Riwayat (Bisa di-scroll) -->
    <div class="space-y-3 pb-8">
        @forelse($riwayat as $item)
            <div class="bg-white p-4 rounded-2xl border border-gray-100 shadow-[0_2px_10px_rgba(0,0,0,0.02)] relative overflow-hidden">
                
                <!-- Garis warna di sebelah kiri kartu untuk penanda visual -->
                @php
                    $borderColor = match($item->status_kehadiran) {
                        'hadir' => 'bg-green-500',
                        'terlambat' => 'bg-orange-500',
                        'izin', 'cuti' => 'bg-purple-500',
                        'alfa' => 'bg-red-500',
                        default => 'bg-gray-400'
                    };
                @endphp
                <div class="absolute left-0 top-0 bottom-0 w-1.5 {{ $borderColor }}"></div>
                
                <div class="flex justify-between items-start mb-3 pl-2">
                    <div>
                        <h4 class="font-bold text-secondary text-sm">{{ \Carbon\Carbon::parse($item->tanggal)->translatedFormat('l, d M Y') }}</h4>
                    </div>
                    
                    <!-- Badge Status -->
                    @php
                        $badgeStyle = match($item->status_kehadiran) {
                            'hadir' => 'bg-green-50 text-green-600',
                            'terlambat' => 'bg-orange-50 text-orange-600',
                            'izin', 'cuti' => 'bg-purple-50 text-purple-600',
                            'alfa' => 'bg-red-50 text-red-600',
                            default => 'bg-gray-50 text-gray-600'
                        };
                    @endphp
                    <span class="{{ $badgeStyle }} px-2.5 py-1 rounded-md text-[10px] font-bold uppercase tracking-wider">
                        {{ $item->status_kehadiran }}
                    </span>
                </div>

                <div class="flex justify-between items-center bg-gray-50 rounded-xl p-3 pl-4">
                    <!-- Jam Masuk -->
                    <div>
                        <p class="text-[10px] text-gray-400 font-medium uppercase mb-0.5"><i class="fa-solid fa-arrow-right-to-bracket mr-1"></i> Masuk</p>
                        <p class="text-sm font-bold text-secondary">
                            {{ $item->jam_masuk ? substr($item->jam_masuk, 0, 5) . ' WIB' : '--:--' }}
                        </p>
                    </div>

                    <!-- Divider -->
                    <div class="h-8 w-px bg-gray-200"></div>

                    <!-- Jam Pulang -->
                    <div class="text-right">
                        <p class="text-[10px] text-gray-400 font-medium uppercase mb-0.5">Pulang <i class="fa-solid fa-arrow-right-from-bracket ml-1"></i></p>
                        <p class="text-sm font-bold text-secondary">
                            {{ $item->jam_pulang ? substr($item->jam_pulang, 0, 5) . ' WIB' : '--:--' }}
                        </p>
                    </div>
                </div>
            </div>
        @empty
            <!-- State jika data kosong -->
            <div class="flex flex-col items-center justify-center py-12 px-4 text-center">
                <div class="w-20 h-20 bg-gray-50 rounded-full flex items-center justify-center mb-4">
                    <i class="fa-regular fa-calendar-xmark text-3xl text-gray-300"></i>
                </div>
                <h3 class="font-bold text-gray-700">Tidak ada riwayat</h3>
                <p class="text-sm text-gray-400 mt-1">Anda belum memiliki riwayat absensi pada bulan ini.</p>
            </div>
        @endforelse
    </div>

</div>
@endsection