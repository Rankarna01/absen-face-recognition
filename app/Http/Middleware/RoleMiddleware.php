<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, $role): Response
    {
        // Cek apakah user sudah login dan role-nya sesuai parameter
        if (!Auth::check() || Auth::user()->role !== $role) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            
            return redirect()->route('admin.login')
                ->with('error', 'Akses ditolak! Anda tidak memiliki izin ke halaman ini.');
        }

        return $next($request);
    }
}