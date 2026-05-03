<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Slip Gaji - {{ $payroll->user->name }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { font-family: 'Inter', sans-serif; background: #e5e7eb; padding: 20px; }
        .slip-container { max-width: 800px; margin: 0 auto; background: #fff; padding: 40px; box-shadow: 0 10px 25px rgba(0,0,0,0.1); border-radius: 8px; }
        
        /* Set Print Settings */
        @media print {
            body { background: #fff; padding: 0; }
            .slip-container { box-shadow: none; padding: 0; width: 100%; max-width: 100%; border-radius: 0; }
            .no-print { display: none !important; }
        }
        .dotted-line { border-bottom: 2px dashed #e5e7eb; margin: 24px 0; }
    </style>
</head>
<body>

    @php
        // Mengambil data pengaturan secara global
        $setting = \App\Models\Setting::first();
        $appName = $setting->app_name ?? 'Family Market';
        $appAddress = $setting->app_address ?? 'Alamat perusahaan belum diatur dalam sistem.';
        
        // Logika inisial logo (Maksimal 2 huruf)
        $words = explode(' ', $appName);
        $initials = isset($words[1]) ? substr($words[0], 0, 1) . substr($words[1], 0, 1) : substr($words[0], 0, 2);
        $initials = strtoupper($initials);
    @endphp

    <!-- Tombol Action (Akan disembunyikan saat di print) -->
    <div class="max-w-[800px] mx-auto mb-4 flex justify-between no-print">
        <button onclick="window.close()" class="px-4 py-2 bg-gray-500 text-white rounded-lg text-sm font-semibold shadow hover:bg-gray-600 transition">&larr; Tutup</button>
        <button onclick="window.print()" class="px-4 py-2 bg-[#F5A623] text-white rounded-lg text-sm font-semibold shadow hover:bg-[#E0961B] transition flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg> Cetak PDF / Print
        </button>
    </div>

    <!-- Konten Slip Gaji -->
    <div class="slip-container">
        <!-- Header (Kop Surat Dinamis) -->
        <div class="flex justify-between items-center mb-8">
            <div class="flex items-center gap-4">
                
                @if($setting && $setting->app_logo)
                    <!-- Jika ada logo asli -->
                    <img src="{{ asset('storage/' . $setting->app_logo) }}" class="h-14 w-auto rounded-lg object-contain" alt="Logo">
                @else
                    <!-- Jika tidak ada logo, pakai kotak inisial -->
                    <div class="w-14 h-14 bg-[#F5A623] rounded-lg flex items-center justify-center font-bold text-white text-xl">{{ $initials }}</div>
                @endif

                <div>
                    <h1 class="text-2xl font-bold text-gray-800 leading-tight">{{ $appName }}</h1>
                    <p class="text-sm text-gray-500 mt-0.5 max-w-sm leading-relaxed">{{ $appAddress }}</p>
                </div>
            </div>
            <div class="text-right">
                <h2 class="text-3xl font-bold text-[#F5A623] uppercase tracking-wider">Slip Gaji</h2>
                <p class="text-gray-500 font-medium">Periode: {{ \Carbon\Carbon::createFromFormat('Y-m', $payroll->periode)->translatedFormat('F Y') }}</p>
            </div>
        </div>

        <!-- Info Karyawan -->
        <div class="bg-gray-50 border border-gray-100 p-4 rounded-xl flex flex-wrap justify-between text-sm mb-6">
            <div class="w-full md:w-1/2 space-y-2">
                <p><span class="text-gray-500 inline-block w-24">ID Pegawai</span> : <strong class="text-gray-800">{{ $payroll->user->employee->nip ?? '-' }}</strong></p>
                <p><span class="text-gray-500 inline-block w-24">Nama</span> : <strong class="text-gray-800 uppercase">{{ $payroll->user->name }}</strong></p>
            </div>
            <div class="w-full md:w-1/2 space-y-2 mt-2 md:mt-0">
                <p><span class="text-gray-500 inline-block w-24">Jabatan</span> : <strong class="text-gray-800">{{ $payroll->user->employee->position->nama_jabatan ?? '-' }}</strong></p>
                <p><span class="text-gray-500 inline-block w-24">Divisi</span> : <strong class="text-gray-800">{{ $payroll->user->employee->division->nama_divisi ?? '-' }}</strong></p>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            <!-- Kolom Pemasukan -->
            <div>
                <h3 class="font-bold text-gray-800 mb-4 border-b-2 border-green-500 pb-2 inline-block">Pemasukan</h3>
                <div class="space-y-3 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-600">Gaji Pokok</span>
                        <span class="font-semibold text-gray-800">Rp {{ number_format($payroll->gaji_pokok, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Tunjangan Jabatan</span>
                        <span class="font-semibold text-gray-800">Rp {{ number_format($payroll->tunjangan, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Bonus Khusus</span>
                        <span class="font-semibold text-gray-800">Rp {{ number_format($payroll->bonus, 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>

            <!-- Kolom Potongan -->
            <div>
                <h3 class="font-bold text-gray-800 mb-4 border-b-2 border-red-500 pb-2 inline-block">Potongan</h3>
                <div class="space-y-3 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-600">Keterlambatan ({{ $payroll->jumlah_telat }}x)</span>
                        <span class="font-semibold text-red-500">- Rp {{ number_format($payroll->potongan_telat, 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="dotted-line"></div>

        <!-- Total Penerimaan -->
        <div class="flex justify-between items-center bg-[#F5A623]/10 p-5 rounded-xl border border-[#F5A623]/30">
            <div>
                <p class="text-gray-600 text-sm font-medium">TOTAL PENERIMAAN BERSIH</p>
                <p class="text-xs text-gray-400 mt-1">Status: <span class="uppercase font-bold {{ $payroll->status == 'dibayar' ? 'text-green-600' : 'text-red-500' }}">{{ $payroll->status }}</span></p>
            </div>
            <div class="text-right">
                <h2 class="text-3xl font-bold text-gray-800">Rp {{ number_format($payroll->total_bersih, 0, ',', '.') }}</h2>
            </div>
        </div>

        <!-- Tanda Tangan -->
        <div class="mt-12 flex justify-between text-sm text-center">
            <div>
                <p class="mb-20 text-gray-600">Penerima,</p>
                <p class="font-bold text-gray-800 uppercase underline">{{ $payroll->user->name }}</p>
            </div>
            <div>
                <p class="mb-20 text-gray-600">Disetujui Oleh,</p>
                <p class="font-bold text-gray-800 uppercase underline">{{ $appName }}</p>
            </div>
        </div>
        
        <p class="text-center text-xs text-gray-400 mt-12 italic">Dokumen ini diterbitkan oleh sistem <strong>{{ $appName }}</strong> dan sah tanpa cap basah perusahaan.</p>
    </div>

</body>
</html>