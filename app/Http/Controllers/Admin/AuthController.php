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
        // Jika sudah login, arahkan sesuai role masing-masing
        if (Auth::check()) {
            if (Auth::user()->role === 'admin') {
                return redirect()->route('admin.dashboard');
            } elseif (Auth::user()->role === 'pegawai') {
                return redirect()->route('pegawai.beranda');
            }
        }
        
        // Jika belum login, tampilkan form login utama
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
            // Generate session baru untuk keamanan
            $request->session()->regenerate();

            // Pengecekan Role sebagai Pengatur Lalu Lintas
            if (Auth::user()->role === 'admin') {
                return redirect()->intended(route('admin.dashboard'))
                    ->with('success', 'Selamat datang kembali, Administrator!');
            } elseif (Auth::user()->role === 'pegawai') {
                return redirect()->intended(route('pegawai.beranda'))
                    ->with('success', 'Selamat datang, selamat bekerja!');
            }

            // Fallback (Jaga-jaga jika ada user tanpa role yang jelas)
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return back()->with('error', 'Akses ditolak! Role akun tidak valid.');
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