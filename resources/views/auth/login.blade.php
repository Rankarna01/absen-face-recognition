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
    <!-- Judul Tab Browser Dinamis -->
    <title>Login - {{ $appName }}</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: { primary: '#F5A623', primary_hover: '#E0961B', secondary: '#1F2937', background: '#F9FAFB' },
                    fontFamily: { sans: ['Poppins', 'sans-serif'] }
                }
            }
        }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="bg-background font-sans text-secondary min-h-screen flex items-center justify-center p-4">

    <!-- Container dibuat cocok untuk tampilan HP maupun Desktop -->
    <div class="bg-white p-8 sm:p-10 rounded-3xl shadow-[0_10px_40px_rgba(0,0,0,0.08)] w-full max-w-md border border-gray-100">
        
        <!-- Logo Dinamis -->
        <div class="flex justify-center mb-6">
            <div class="text-3xl font-bold flex items-center gap-3">
                @if($setting && $setting->app_logo)
                    <!-- Tampil jika ada file logo -->
                    <img src="{{ asset('storage/' . $setting->app_logo) }}" alt="Logo" class="h-12 w-auto object-contain rounded-lg">
                    <div class="flex flex-col leading-none">
                        <span class="text-2xl text-secondary">{{ $appName }}</span>
                    </div>
                @else
                    <!-- Tampil jika tidak ada logo (Pakai text inisial) -->
                    <div class="bg-primary text-white px-3 py-1 rounded-xl shadow-md">{{ $initials }}</div>
                    <div class="flex flex-col leading-none">
                        <span class="text-2xl text-secondary">{{ $appName }}</span>
                    </div>
                @endif
            </div>
        </div>

        <div class="text-center mb-8">
            <h1 class="text-xl font-bold">Portal Sistem Karyawan</h1>
            <p class="text-gray-400 text-sm mt-1">Silakan masuk menggunakan email terdaftar.</p>
        </div>

        <!-- Perhatikan action mengarah ke rute 'authenticate' global -->
        <form action="{{ route('authenticate') }}" method="POST">
            @csrf
            
            <div class="space-y-5">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                    <input type="email" name="email" value="{{ old('email') }}" required autofocus
                        class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all"
                        placeholder="contoh@email.com">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                    <input type="password" name="password" required
                        class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all"
                        placeholder="••••••••">
                </div>

                <div class="flex items-center justify-between text-sm">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="remember" class="w-4 h-4 text-primary border-gray-300 rounded focus:ring-primary accent-primary">
                        <span class="text-gray-500">Ingat Saya</span>
                    </label>
                </div>

                <button type="submit" class="w-full bg-primary hover:bg-primary_hover text-white font-semibold py-3 px-4 rounded-xl transition-colors shadow-lg shadow-primary/30 mt-2">
                    Masuk
                </button>
            </div>
        </form>
    </div>

    <script>
        const Toast = Swal.mixin({ toast: true, position: 'top-end', showConfirmButton: false, timer: 3000, timerProgressBar: true });
        @if(session('error')) Toast.fire({ icon: 'error', title: "{{ session('error') }}" }); @endif
        @if(session('success')) Toast.fire({ icon: 'success', title: "{{ session('success') }}" }); @endif
    </script>
</body>
</html>