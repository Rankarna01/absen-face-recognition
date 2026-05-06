@php
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title') - Admin Panel</title>

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
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    @stack('scripts-head')
</head>

<!-- Alpine JS: Sidebar mendeteksi lebar layar otomatis -->
<body class="bg-background font-sans text-secondary antialiased overflow-hidden" 
      x-data="{ sidebarOpen: window.innerWidth >= 1024 }" 
      @resize.window="sidebarOpen = window.innerWidth >= 1024">

    <div class="flex h-screen w-full">

        <!-- Overlay Gelap Khusus Layar HP saat Sidebar Terbuka -->
        <div x-show="sidebarOpen" x-cloak @click="sidebarOpen = false" 
             x-transition.opacity 
             class="fixed inset-0 bg-secondary/60 backdrop-blur-sm z-40 lg:hidden">
        </div>

        <!-- Sidebar -->
        <aside class="fixed inset-y-0 left-0 z-50 w-64 bg-base border-r border-gray-100 flex flex-col transition-transform duration-300 lg:static lg:translate-x-0"
            :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'">
            
            <!-- Logo -->
            <div class="h-20 flex items-center justify-center border-b border-gray-50 shrink-0">
                <div class="text-2xl font-bold flex items-center gap-2">
                    @if ($setting && $setting->app_logo)
                        <img src="{{ asset('storage/' . $setting->app_logo) }}" alt="Logo" class="h-10 w-auto object-contain">
                        <div class="flex flex-col leading-none">
                            <span class="text-lg text-secondary">{{ $appName }}</span>
                        </div>
                    @else
                        <div class="bg-primary text-white text-base px-2 py-1 rounded-md">{{ $initials }}</div>
                        <div class="flex flex-col leading-none">
                            <span class="text-lg text-secondary">{{ $appName }}</span>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Menu Navigation -->
            <nav class="flex-1 overflow-y-auto py-4 px-3 space-y-1">
                <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl {{ request()->routeIs('admin.dashboard') ? 'bg-primary/10 text-primary font-semibold' : 'text-gray-500 hover:bg-gray-50' }}">
                    <i class="fa-solid fa-house w-5"></i> Dashboard
                </a>
                <a href="{{ route('admin.karyawan.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl {{ request()->routeIs('admin.karyawan.*') ? 'bg-primary/10 text-primary font-semibold' : 'text-gray-500 hover:bg-gray-50' }}">
                    <i class="fa-solid fa-users w-5"></i> Karyawan
                </a>
                <a href="{{ route('admin.departemen.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl {{ request()->routeIs('admin.departemen.*') ? 'bg-primary/10 text-primary font-semibold' : 'text-gray-500 hover:bg-gray-50' }}">
                    <i class="fa-solid fa-sitemap w-5"></i> Divisi & Jabatan
                </a>
                <a href="{{ route('admin.registrasi-wajah.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl {{ request()->routeIs('admin.registrasi-wajah.*') ? 'bg-primary/10 text-primary font-semibold' : 'text-gray-500 hover:bg-gray-50' }}">
                    <i class="fa-solid fa-camera w-5"></i> Registrasi Wajah
                </a>
                <a href="{{ route('admin.absensi.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl {{ request()->routeIs('admin.absensi.*') ? 'bg-primary/10 text-primary font-semibold' : 'text-gray-500 hover:bg-gray-50' }}">
                    <i class="fa-solid fa-calendar-check w-5"></i> Data Absensi
                </a>
                <a href="{{ route('admin.jadwal.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl {{ request()->routeIs('admin.jadwal.*') ? 'bg-primary/10 text-primary font-semibold' : 'text-gray-500 hover:bg-gray-50' }}">
                    <i class="fa-regular fa-calendar-days w-5"></i> Jadwal Kerja
                </a>
                <a href="{{ route('admin.holiday.index') }}"
    class="flex items-center gap-3 px-4 py-3 rounded-xl {{ request()->routeIs('admin.holiday.*') ? 'bg-primary/10 text-primary font-semibold' : 'text-gray-500 hover:bg-gray-50' }}">
    <i class="fa-solid fa-calendar-day w-5"></i> Master Hari Libur
</a>
                <a href="{{ route('admin.pengajuan.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl {{ request()->routeIs('admin.pengajuan.*') ? 'bg-primary/10 text-primary font-semibold' : 'text-gray-500 hover:bg-gray-50' }}">
                    <i class="fa-solid fa-envelope-open-text w-5"></i> Pengajuan Izin
                </a>
                <a href="{{ route('admin.payroll.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl {{ request()->routeIs('admin.payroll.*') ? 'bg-primary/10 text-primary font-semibold' : 'text-gray-500 hover:bg-gray-50' }}">
                    <i class="fa-solid fa-money-check-dollar w-5"></i> Payroll / Gaji
                </a>
                <a href="{{ route('admin.laporan.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl {{ request()->routeIs('admin.laporan.*') ? 'bg-primary/10 text-primary font-semibold' : 'text-gray-500 hover:bg-gray-50' }}">
                    <i class="fa-solid fa-chart-pie w-5"></i> Laporan
                </a>
                
                <div class="my-4 border-t border-gray-100 mx-4"></div>

                <a href="{{ route('admin.pengaturan.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl {{ request()->routeIs('admin.pengaturan.*') ? 'bg-primary/10 text-primary font-semibold' : 'text-gray-500 hover:bg-gray-50' }}">
                    <i class="fa-solid fa-gear w-5"></i> Pengaturan Sistem
                </a>
                <a href="{{ route('admin.users.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl {{ request()->routeIs('admin.users.*') ? 'bg-primary/10 text-primary font-semibold' : 'text-gray-500 hover:bg-gray-50' }}">
                    <i class="fa-solid fa-user-shield w-5"></i> Manajemen User
                </a>
            </nav>

            <!-- Menu Bawah (Keluar) -->
            <div class="p-3 border-t border-gray-100 shrink-0">
                <form action="{{ route('logout') }}" method="POST" class="w-full">
                    @csrf
                    <button type="submit" class="flex items-center gap-3 px-4 py-3 rounded-xl w-full text-left text-red-500 hover:bg-red-50 font-semibold transition">
                        <i class="fa-solid fa-right-from-bracket w-5"></i> Keluar Sistem
                    </button>
                </form>
            </div>
        </aside>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col h-screen w-full relative overflow-hidden">
            
            <!-- Topbar -->
            <header class="h-20 bg-base flex items-center justify-between px-4 md:px-8 border-b border-gray-100 shrink-0">
                <div class="flex items-center gap-4">
                    <!-- Tombol Hamburger (Hanya muncul di Layar HP) -->
                    <button @click="sidebarOpen = !sidebarOpen" class="text-gray-500 hover:text-primary lg:hidden focus:outline-none">
                        <i class="fa-solid fa-bars text-xl"></i>
                    </button>
                    <h1 class="text-lg md:text-xl font-semibold text-secondary truncate max-w-[150px] md:max-w-full">@yield('header_title', 'Dashboard')</h1>
                </div>

                <div class="flex items-center gap-4 md:gap-6">
                    <button class="relative text-gray-400 hover:text-primary">
                        <i class="fa-regular fa-bell text-xl"></i>
                        <span class="absolute -top-1 -right-1 flex h-3 w-3 relative">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-primary opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-3 w-3 bg-primary border-2 border-base"></span>
                        </span>
                    </button>
                    <div class="flex items-center gap-3 border-l border-gray-200 pl-4 md:pl-6">
                        <img src="https://ui-avatars.com/api/?name=Admin&background=1F2937&color=fff" class="w-8 h-8 md:w-10 md:h-10 rounded-full">
                        <div class="hidden md:block text-sm">
                            <p class="font-semibold text-secondary">Administrator</p>
                            <p class="text-[11px] text-gray-400">Super Admin</p>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Content Area -->
            <main class="flex-1 overflow-x-hidden overflow-y-auto bg-background p-4 md:p-6 w-full">
                @yield('content')
            </main>
        </div>
    </div>

    <script>
        $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });
        const Toast = Swal.mixin({
            toast: true, position: 'top-end', showConfirmButton: false, timer: 2500, timerProgressBar: true,
            didOpen: (toast) => { toast.addEventListener('mouseenter', Swal.stopTimer); toast.addEventListener('mouseleave', Swal.resumeTimer); }
        });
        @if (session('success')) Toast.fire({ icon: 'success', title: "{{ session('success') }}" }); @endif
        @if (session('error')) Toast.fire({ icon: 'error', title: "{{ session('error') }}" }); @endif
    </script>
    @stack('scripts')
</body>
</html>