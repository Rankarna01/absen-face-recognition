@php
    // Mengambil data pengaturan secara global
    $setting = \App\Models\Setting::first();
    $appName = $setting->app_name ?? 'Family Market';
    
    // Logika mengambil 2 huruf awal untuk icon text jika logo tidak ada
    $words = explode(' ', $appName);
    $initials = isset($words[1]) ? substr($words[0], 0, 1) . substr($words[1], 0, 1) : substr($words[0], 0, 2);
    $initials = strtoupper($initials);
@endphp

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Login - {{ $appName }}</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: { 
                        primary: '#F5A623', 
                        primary_hover: '#E0961B', 
                        secondary: '#1F2937', 
                        background: '#F4F7FE' // Sedikit lebih gelap dari putih agar card menonjol
                    },
                    fontFamily: { sans: ['Poppins', 'sans-serif'] }
                }
            }
        }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <script src="https://unpkg.com/@lottiefiles/lottie-player@latest/dist/lottie-player.js"></script>
</head>
<body class="bg-background font-sans text-secondary min-h-screen flex items-center justify-center p-4">

    <div class="bg-white rounded-[2rem] shadow-[0_10px_40px_rgba(0,0,0,0.08)] w-full max-w-4xl flex overflow-hidden border border-gray-100">
        
        <div class="hidden md:flex md:w-1/2 bg-primary/5 flex-col items-center justify-center p-12 relative overflow-hidden">
            <div class="absolute top-0 left-0 w-full h-full opacity-50 bg-[radial-gradient(circle_at_top_left,_var(--tw-gradient-stops))] from-primary/20 via-transparent to-transparent"></div>
            
            <div class="relative z-10 flex flex-col items-center">
                <lottie-player 
                    src="{{ asset('Call Center Support Lottie Animation.json') }}" 
                    background="transparent" 
                    speed="1" 
                    style="width: 100%; max-width: 320px; height: auto;" 
                    loop 
                    autoplay>
                </lottie-player>
                
                <h2 class="text-2xl font-bold text-primary mt-6 text-center leading-tight">HRIS Dashboard<br>{{ $appName }}</h2>
                <p class="text-sm text-gray-500 text-center mt-3">Sistem Informasi Manajemen Pegawai & Absensi Terintegrasi.</p>
            </div>
        </div>

        <div class="w-full md:w-1/2 p-8 sm:p-12 lg:p-14 flex flex-col justify-center bg-white">
            
            <div class="flex justify-center md:justify-start mb-8">
                <div class="text-3xl font-bold flex items-center gap-3">
                    @if($setting && $setting->app_logo)
                        <img src="{{ asset('storage/' . $setting->app_logo) }}" alt="Logo" class="h-10 w-auto object-contain rounded-lg">
                        <div class="flex flex-col leading-none">
                            <span class="text-xl text-secondary">{{ $appName }}</span>
                        </div>
                    @else
                        <div class="bg-primary text-white px-3 py-1 rounded-xl shadow-md text-xl">{{ $initials }}</div>
                        <div class="flex flex-col leading-none">
                            <span class="text-xl text-secondary">{{ $appName }}</span>
                        </div>
                    @endif
                </div>
            </div>

            <div class="text-center md:text-left mb-8">
                <h1 class="text-2xl font-bold text-secondary">Selamat Datang 👋</h1>
                <p class="text-gray-400 text-sm mt-1">Silakan masuk menggunakan email terdaftar Anda.</p>
            </div>

            <form action="{{ route('authenticate') }}" method="POST">
                @csrf
                
                <div class="space-y-5">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Email Karyawan</label>
                        <input type="email" name="email" value="{{ old('email') }}" required autofocus
                            class="w-full px-4 py-3.5 rounded-xl border border-gray-200 focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all bg-gray-50/50"
                            placeholder="nama@perusahaan.com">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Kata Sandi</label>
                        <input type="password" name="password" required
                            class="w-full px-4 py-3.5 rounded-xl border border-gray-200 focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all bg-gray-50/50"
                            placeholder="••••••••">
                    </div>

                    <div class="flex items-center justify-between text-sm">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="remember" class="w-4 h-4 text-primary border-gray-300 rounded focus:ring-primary accent-primary">
                            <span class="text-gray-500 font-medium">Ingat Sesi Saya</span>
                        </label>
                    </div>

                    <button type="submit" class="w-full bg-primary hover:bg-primary_hover text-white font-bold text-base py-3.5 px-4 rounded-xl transition-all shadow-lg shadow-primary/30 mt-4 flex items-center justify-center gap-2 group">
                        Masuk Sistem
                        <i class="fa-solid fa-arrow-right transition-transform group-hover:translate-x-1"></i>
                    </button>
                </div>
            </form>
            
            <div class="mt-8 text-center text-xs text-gray-400">
                <p>&copy; {{ date('Y') }} {{ $appName }}. All rights reserved.</p>
            </div>
        </div>

    </div>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

    <script>
        const Toast = Swal.mixin({ toast: true, position: 'top-end', showConfirmButton: false, timer: 3000, timerProgressBar: true });
        @if(session('error')) Toast.fire({ icon: 'error', title: "{{ session('error') }}" }); @endif
        @if(session('success')) Toast.fire({ icon: 'success', title: "{{ session('success') }}" }); @endif
    </script>
</body>
</html>