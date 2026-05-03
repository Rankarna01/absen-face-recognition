<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'FM Absensi - Face Recognition')</title>

    <!-- Google Fonts: Poppins -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

    <!-- Tailwind CSS via CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Konfigurasi Tema Tailwind -->
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#F5A623',   // Kuning (Family Market)
                        primary_hover: '#E0961B', // Kuning gelap untuk hover
                        secondary: '#1F2937', // Hitam/Dark Gray untuk teks
                        base: '#FFFFFF',      // Putih murni
                        background: '#F9FAFB', // Off-white untuk background luar
                        success: '#22C55E',   // Hijau
                        danger: '#EF4444',    // Merah
                    },
                    fontFamily: {
                        sans: ['Poppins', 'sans-serif'], // Set Poppins sebagai font utama
                    }
                }
            }
        }
    </script>

    <!-- Custom CSS Tambahan -->
    <style>
        body { -webkit-font-smoothing: antialiased; }
        
        /* Mencegah elemen berkedip sebelum Alpine.js ter-load */
        [x-cloak] { display: none !important; }

        /* Animasi Custom untuk Loading Screen */
        .loader-spinner {
            border: 4px solid rgba(245, 166, 35, 0.2);
            border-left-color: #F5A623; 
            border-radius: 50%;
            width: 48px;
            height: 48px;
            animation: spin 1s linear infinite;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>

    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    @stack('styles')
    @stack('scripts-head')
</head>

<!-- Alpine setup: isLoading true saat awal, dan false ketika seluruh DOM & resource selesai dimuat -->
<body 
    class="bg-background text-secondary font-sans min-h-screen flex flex-col"
    x-data="{ isLoading: true }" 
    x-init="window.addEventListener('load', () => isLoading = false)"
>

    <!-- Global Loading Screen -->
    <div x-show="isLoading" x-cloak 
         x-transition.opacity.duration.500ms
         class="fixed inset-0 z-[9999] bg-base flex flex-col items-center justify-center">
        <div class="loader-spinner mb-4"></div>
        <p class="text-secondary font-medium animate-pulse">Memuat sistem...</p>
    </div>

    <!-- Main Content (Tempat halaman module dirender) -->
    <main class="flex-1 w-full max-w-7xl mx-auto relative">
        @yield('content')
    </main>

    <!-- jQuery (Disediakan sebagai pelengkap jika butuh ajax request manual) -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

    <!-- Setup Global SweetAlert untuk Flash Message Laravel -->
    <script>
        // Konfigurasi dasar untuk Toast Notifikasi
        const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true,
            didOpen: (toast) => {
                toast.addEventListener('mouseenter', Swal.stopTimer)
                toast.addEventListener('mouseleave', Swal.resumeTimer)
            }
        });

        // Menangkap Session 'success' atau 'error' dari Laravel Controller nanti
        @if(session('success'))
            Toast.fire({
                icon: 'success',
                title: "{{ session('success') }}"
            });
        @endif

        @if(session('error'))
            Toast.fire({
                icon: 'error',
                title: "{{ session('error') }}"
            });
        @endif
    </script>

    @stack('scripts-bottom')
</body>
</html>