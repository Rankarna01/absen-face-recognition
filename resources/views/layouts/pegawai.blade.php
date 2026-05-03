<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title') - Family Market</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: { primary: '#F5A623', primary_hover: '#E0961B', secondary: '#1F2937', base: '#FFFFFF', background: '#F9FAFB' },
                    fontFamily: { sans: ['Poppins', 'sans-serif'] }
                }
            }
        }
    </script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <style>
        /* Mencegah scroll pada body luar (khusus desktop view) */
        body { background-color: #f3f4f6; }
        /* Hide scrollbar for Chrome, Safari and Opera */
        .no-scrollbar::-webkit-scrollbar { display: none; }
        /* Hide scrollbar for IE, Edge and Firefox */
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
    </style>
    @stack('styles')
    @stack('scripts-head')
</head>
<body class="flex justify-center h-screen overflow-hidden antialiased text-secondary">

    <!-- Mobile Container -->
    <div class="w-full max-w-md bg-white h-full flex flex-col relative shadow-2xl overflow-hidden">
        
        <!-- Area Konten (Bisa di-scroll) -->
        <main class="flex-1 overflow-y-auto no-scrollbar pb-6 bg-white">
            @yield('content')
        </main>

        <!-- Bottom Navigation Bar -->
        <nav class="bg-white border-t border-gray-100 flex justify-between items-center px-6 py-2 pb-safe z-50 relative rounded-t-3xl shadow-[0_-4px_15px_rgba(0,0,0,0.05)]">
            
            <!-- Beranda -->
            <a href="{{ route('pegawai.beranda') }}" class="flex flex-col items-center gap-1 w-12 transition {{ request()->routeIs('pegawai.beranda') ? 'text-primary' : 'text-gray-400 hover:text-primary' }}">
                <i class="fa-solid fa-house text-xl mb-0.5"></i>
                <span class="text-[10px] font-semibold">Beranda</span>
            </a>
            
            <!-- Riwayat -->
          <!-- Riwayat -->
            <a href="{{ route('pegawai.riwayat') }}" class="flex flex-col items-center gap-1 w-12 transition {{ request()->routeIs('pegawai.riwayat') ? 'text-primary' : 'text-gray-400 hover:text-primary' }}">
                <i class="fa-regular fa-calendar-check text-xl mb-0.5"></i>
                <span class="text-[10px] font-semibold">Riwayat</span>
            </a>

            <!-- Tombol Floating Absen (Tengah) -->
           <div class="relative w-14 flex justify-center">
                <a href="{{ route('pegawai.absen.index') }}" class="absolute -top-8 w-14 h-14 bg-primary text-white rounded-full flex items-center justify-center shadow-lg shadow-primary/40 border-4 border-white transition transform hover:scale-105">
                    <i class="fa-solid fa-fingerprint text-3xl"></i>
                </a>
            </div>

           <a href="{{ route('pegawai.izin.index') }}" class="flex flex-col items-center gap-1 w-12 transition {{ request()->routeIs('pegawai.izin.*') ? 'text-primary' : 'text-gray-400 hover:text-primary' }}">
                <i class="fa-solid fa-file-signature text-xl mb-0.5"></i>
                <span class="text-[10px] font-semibold">Izin</span>
            </a>

            <!-- Profil -->
           <a href="{{ route('pegawai.profil.index') }}" class="flex flex-col items-center gap-1 w-12 transition {{ request()->routeIs('pegawai.profil.*') ? 'text-primary' : 'text-gray-400 hover:text-primary' }}">
                <i class="fa-regular fa-user text-xl mb-0.5"></i>
                <span class="text-[10px] font-semibold">Profil</span>
            </a>

        </nav>
    </div>

    <!-- Global Toast Notifikasi -->
    <script>
        const Toast = Swal.mixin({
            toast: true, position: 'top-end', showConfirmButton: false, timer: 2500, timerProgressBar: true
        });
        @if(session('success')) Toast.fire({ icon: 'success', title: "{{ session('success') }}" }); @endif
        @if(session('error')) Toast.fire({ icon: 'error', title: "{{ session('error') }}" }); @endif
    </script>
    @stack('scripts')
</body>
</html>