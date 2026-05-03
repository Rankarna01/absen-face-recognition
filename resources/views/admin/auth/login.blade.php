<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin - Family Market</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: { primary: '#F5A623', primary_hover: '#E0961B', secondary: '#1F2937', background: '#F4F7FE' },
                    fontFamily: { sans: ['Poppins', 'sans-serif'] }
                }
            }
        }
    </script>
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="bg-background font-sans text-secondary min-h-screen flex items-center justify-center p-4">

    <div class="bg-white p-8 sm:p-10 rounded-3xl shadow-xl w-full max-w-md border border-gray-100">
        <!-- Logo -->
        <div class="flex justify-center mb-8">
            <div class="text-3xl font-bold flex items-center gap-2">
                <div class="bg-primary text-white px-3 py-1 rounded-lg">FM</div>
                <div class="flex flex-col leading-none">
                    <span class="text-2xl">Family</span>
                    <span class="text-primary font-normal text-xl">market</span>
                </div>
            </div>
        </div>

        <div class="text-center mb-8">
            <h1 class="text-2xl font-bold">Portal Admin</h1>
            <p class="text-gray-500 text-sm mt-1">Silakan masuk untuk mengelola sistem absensi.</p>
        </div>

        <form action="{{ route('admin.authenticate') }}" method="POST">
            @csrf
            
            <div class="space-y-5">
                <!-- Input Email -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email Administrator</label>
                    <input type="email" name="email" value="{{ old('email') }}" required autofocus
                        class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition-all @error('email') border-red-500 @enderror"
                        placeholder="admin@familymarket.com">
                    @error('email')
                        <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Input Password -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                    <input type="password" name="password" required
                        class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition-all @error('password') border-red-500 @enderror"
                        placeholder="••••••••">
                    @error('password')
                        <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Remember Me -->
                <div class="flex items-center justify-between text-sm">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="remember" class="w-4 h-4 text-primary border-gray-300 rounded focus:ring-primary">
                        <span class="text-gray-600">Ingat Saya</span>
                    </label>
                </div>

                <!-- Submit Button -->
                <button type="submit" class="w-full bg-primary hover:bg-primary_hover text-white font-semibold py-3 px-4 rounded-xl transition-colors shadow-lg shadow-primary/30 mt-4">
                    Masuk Sekarang
                </button>
            </div>
        </form>
    </div>

    <!-- Alert Notifikasi dengan SweetAlert -->
    <script>
        const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true,
        });

        @if(session('error'))
            Toast.fire({ icon: 'error', title: "{{ session('error') }}" });
        @endif

        @if(session('success'))
            Toast.fire({ icon: 'success', title: "{{ session('success') }}" });
        @endif
    </script>
</body>
</html>