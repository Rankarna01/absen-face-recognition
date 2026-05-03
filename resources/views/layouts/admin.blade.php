<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title') - Admin Panel FM</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: { primary: '#F5A623', secondary: '#1F2937', base: '#FFFFFF', background: '#F4F7FE' },
                    fontFamily: { sans: ['Poppins', 'sans-serif'] }
                }
            }
        }
    </script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <!-- Chart.js untuk Grafik Dashboard -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    @stack('scripts-head')
</head>
<body class="bg-background font-sans text-secondary antialiased" x-data="{ sidebarOpen: true }">

    <div class="flex h-screen overflow-hidden">
        
        <!-- Sidebar -->
        <aside class="bg-base w-64 border-r border-gray-100 flex flex-col transition-all duration-300" :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full absolute'">
            <!-- Logo -->
            <div class="h-20 flex items-center justify-center border-b border-gray-50">
                <div class="text-2xl font-bold flex items-center gap-2">
                    <div class="bg-primary text-base px-2 py-1 rounded-md">FM</div>
                    <div class="flex flex-col leading-none">
                        <span class="text-lg">Family <span class="text-primary font-normal">market</span></span>
                    </div>
                </div>
            </div>

            <!-- Menu Navigation -->
            <nav class="flex-1 overflow-y-auto py-4 px-3 space-y-1">
                <!-- Gunakan request()->routeIs('admin.dashboard') untuk mengaktifkan warna kuning -->
                <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl {{ request()->routeIs('admin.dashboard') ? 'bg-primary/10 text-primary font-semibold' : 'text-gray-500 hover:bg-gray-50' }}">
                    <i class="fa-solid fa-house w-5"></i> Dashboard
                </a>
                
                <a href="{{ route('admin.karyawan.index') }}" 
   class="flex items-center gap-3 px-4 py-3 rounded-xl {{ request()->routeIs('admin.karyawan.*') ? 'bg-primary/10 text-primary font-semibold' : 'text-gray-500 hover:bg-gray-50' }}">
    <i class="fa-solid fa-users w-5"></i> Karyawan
</a>
<a href="{{ route('admin.departemen.index') }}" 
   class="flex items-center gap-3 px-4 py-3 rounded-xl {{ request()->routeIs('admin.departemen.*') ? 'bg-primary/10 text-primary font-semibold' : 'text-gray-500 hover:bg-gray-50' }}">
    <i class="fa-solid fa-sitemap w-5"></i> Divisi & Jabatan
</a>
<a href="{{ route('admin.registrasi-wajah.index') }}" 
   class="flex items-center gap-3 px-4 py-3 rounded-xl {{ request()->routeIs('admin.registrasi-wajah.*') ? 'bg-primary/10 text-primary font-semibold' : 'text-gray-500 hover:bg-gray-50' }}">
    <i class="fa-solid fa fa-camera w-5"></i> Registrasi Wajah
</a>

               <a href="{{ route('admin.absensi.index') }}" 
   class="flex items-center gap-3 px-4 py-3 rounded-xl {{ request()->routeIs('admin.absensi.*') ? 'bg-primary/10 text-primary font-semibold' : 'text-gray-500 hover:bg-gray-50' }}">
    <i class="fa-solid fa-calendar-check w-5"></i> Data Absensi
</a>

                <a href="#" class="flex items-center gap-3 px-4 py-3 rounded-xl text-gray-500 hover:bg-gray-50">
                    <i class="fa-regular fa-calendar-days w-5"></i> Jadwal Kerja
                </a>
            </nav>

            <!-- Menu Bawah (Keluar) -->
            <div class="p-4 border-t border-gray-50">
                <a href="{{ route('admin.logout') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl text-gray-500 hover:bg-red-50 hover:text-red-500 transition">
                    <i class="fa-solid fa-right-from-bracket w-5"></i> Keluar
                </a>
            </div>
        </aside>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <!-- Topbar -->
            <header class="h-20 bg-base flex items-center justify-between px-8 border-b border-gray-100">
                <div class="flex items-center gap-4">
                    <button @click="sidebarOpen = !sidebarOpen" class="text-gray-500 hover:text-primary md:hidden">
                        <i class="fa-solid fa-bars text-xl"></i>
                    </button>
                    <h1 class="text-xl font-semibold text-secondary">@yield('header_title', 'Dashboard')</h1>
                </div>
                
                <div class="flex items-center gap-6">
                    <button class="relative text-gray-400 hover:text-primary">
                        <i class="fa-regular fa-bell text-xl"></i>
                        <span class="absolute -top-1 -right-1 flex h-3 w-3 relative">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-primary opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-3 w-3 bg-primary border-2 border-base"></span>
                        </span>
                    </button>
                    <div class="flex items-center gap-3 border-l pl-6">
                        <img src="https://ui-avatars.com/api/?name=Admin&background=1F2937&color=fff" class="w-10 h-10 rounded-full">
                        <div class="hidden md:block text-sm">
                            <p class="font-semibold text-secondary">Administrator</p>
                            <p class="text-xs text-gray-400">Super Admin</p>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Content Area -->
            <main class="flex-1 overflow-x-hidden overflow-y-auto bg-background p-6">
                @yield('content')
            </main>
        </div>
    </div>

    <script>
        // Setup CSRF Token otomatis untuk semua request AJAX
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        // Setup SweetAlert Toast ala Family Market
        const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 2500,
            timerProgressBar: true,
            didOpen: (toast) => {
                toast.addEventListener('mouseenter', Swal.stopTimer)
                toast.addEventListener('mouseleave', Swal.resumeTimer)
            }
        });

        // Menangkap Session Flash (Jika menggunakan controller biasa, bukan AJAX)
        @if(session('success'))
            Toast.fire({ icon: 'success', title: "{{ session('success') }}" });
        @endif
        @if(session('error'))
            Toast.fire({ icon: 'error', title: "{{ session('error') }}" });
        @endif
    </script>

    @stack('scripts')
</body>
</html>