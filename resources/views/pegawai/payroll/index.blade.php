@extends('layouts.pegawai')
@section('title', 'Slip Gaji')

@section('content')
<div class="p-5 flex flex-col h-full" x-data="{ autoSubmit() { $refs.filterForm.submit() } }">
    
    <!-- Header Back -->
    <div class="flex items-center gap-4 mb-6 relative">
        <a href="{{ route('pegawai.beranda') }}" class="absolute left-0 w-10 h-10 bg-gray-50 rounded-full flex items-center justify-center text-gray-600 hover:bg-gray-100 transition z-10">
            <i class="fa-solid fa-chevron-left"></i>
        </a>
        <h2 class="text-xl font-bold text-secondary text-center w-full">Slip Gaji</h2>
    </div>

    <!-- Filter Bulan (Auto Submit) -->
    <div class="mb-6 z-10">
        <form x-ref="filterForm" action="{{ route('pegawai.slip.index') }}" method="GET" class="relative">
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

    @if($payroll)
        <!-- Total Gaji Card -->
        <div class="bg-white rounded-3xl border border-gray-100 shadow-[0_8px_30px_rgba(0,0,0,0.04)] p-6 mb-6 text-center relative overflow-hidden">
            <!-- Hiasan Latar -->
            <div class="absolute -right-6 -top-6 w-24 h-24 bg-primary/5 rounded-full"></div>
            <div class="absolute -left-6 -bottom-6 w-20 h-20 bg-primary/5 rounded-full"></div>
            
            <p class="text-sm font-semibold text-gray-500 mb-2 relative z-10">Total Gaji Bersih</p>
            <h2 class="text-3xl font-bold text-secondary relative z-10 mb-2">Rp {{ number_format($payroll->total_bersih, 0, ',', '.') }}</h2>
            
            @if($payroll->status == 'dibayar')
                <p class="text-xs font-bold text-green-500 relative z-10 flex items-center justify-center gap-1">
                    <i class="fa-solid fa-circle-check"></i> Dibayar pada {{ \Carbon\Carbon::parse($payroll->updated_at)->translatedFormat('d M Y') }}
                </p>
            @else
                <p class="text-xs font-bold text-orange-500 relative z-10 flex items-center justify-center gap-1">
                    <i class="fa-solid fa-clock"></i> Draft (Menunggu Pembayaran)
                </p>
            @endif
        </div>

        <!-- Rincian Gaji -->
        <div class="mb-8">
            <h3 class="text-sm font-bold text-secondary mb-4 pl-1 border-l-4 border-primary">Rincian Gaji</h3>
            
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 space-y-3">
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-500">Gaji Pokok</span>
                    <span class="text-sm font-semibold text-secondary">Rp {{ number_format($payroll->gaji_pokok, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-500">Tunjangan</span>
                    <span class="text-sm font-semibold text-secondary">Rp {{ number_format($payroll->tunjangan, 0, ',', '.') }}</span>
                </div>
                
                @if($payroll->bonus > 0)
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-500">Bonus</span>
                    <span class="text-sm font-semibold text-green-500">+ Rp {{ number_format($payroll->bonus, 0, ',', '.') }}</span>
                </div>
                @endif
                
                <div class="flex justify-between items-center pt-2 border-t border-gray-100">
                    <span class="text-sm text-gray-500">Potongan Telat ({{ $payroll->jumlah_telat }}x)</span>
                    <span class="text-sm font-semibold text-red-500">- Rp {{ number_format($payroll->potongan_telat, 0, ',', '.') }}</span>
                </div>

                <div class="flex justify-between items-center pt-3 mt-1 border-t-2 border-dashed border-gray-200">
                    <span class="text-sm font-bold text-secondary">Total Penerimaan</span>
                    <span class="text-sm font-bold text-secondary">Rp {{ number_format($payroll->total_bersih, 0, ',', '.') }}</span>
                </div>
            </div>
        </div>

        <!-- Tombol Download Slip -->
        <a href="{{ route('pegawai.slip.print', $payroll->id) }}" target="_blank" class="w-full bg-primary/10 text-primary_hover hover:bg-primary hover:text-white font-bold py-4 rounded-2xl transition flex justify-center items-center gap-2 border border-primary/20">
            <i class="fa-solid fa-download"></i> Download Slip PDF
        </a>

    @else
        <!-- State Jika Slip Belum Dibuat -->
        <div class="flex flex-col items-center justify-center py-16 px-4 text-center mt-4">
            <div class="w-24 h-24 bg-gray-50 rounded-full flex items-center justify-center mb-5 border border-gray-100">
                <i class="fa-solid fa-envelope-open-text text-4xl text-gray-300"></i>
            </div>
            <h3 class="text-lg font-bold text-gray-700">Slip Belum Tersedia</h3>
            <p class="text-sm text-gray-400 mt-2 leading-relaxed">HRD belum men-generate atau menerbitkan slip gaji Anda untuk periode bulan ini.</p>
        </div>
    @endif

</div>
@endsection