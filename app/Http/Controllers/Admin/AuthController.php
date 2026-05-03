<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    // Tampilkan halaman login
    public function index()
    {
        // Jika sudah login sebagai admin, langsung arahkan ke dashboard
        if (Auth::check() && Auth::user()->role === 'admin') {
            return redirect()->route('admin.dashboard');
        }
        return view('admin.auth.login');
    }

    // Proses login
    public function authenticate(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ], [
            'email.required' => 'Email wajib diisi',
            'password.required' => 'Password wajib diisi'
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            // Cek apakah yang login benar-benar admin
            if (Auth::user()->role === 'admin') {
                $request->session()->regenerate();
                return redirect()->intended(route('admin.dashboard'))
                    ->with('success', 'Selamat datang kembali, Admin!');
            }

            // Jika bukan admin (misal pegawai mencoba login di portal admin)
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return back()->with('error', 'Akses ditolak! Portal ini khusus Administrator.');
        }

        return back()->with('error', 'Email atau Password yang Anda masukkan salah.');
    }

    // Proses Logout
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login')->with('success', 'Berhasil keluar dari sistem.');
    }
}