<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\EmployeeController;

// --- ROUTE UTAMA ---
// Arahkan otomatis halaman awal (/) ke halaman login admin
Route::redirect('/', '/admin/login');

// --- ROUTE ADMIN ---
Route::prefix('admin')->name('admin.')->group(function () {
    
    // Route untuk Tamu (Belum Login)
    Route::middleware('guest')->group(function () {
        Route::get('/login', [AuthController::class, 'index'])->name('login');
        Route::post('/login', [AuthController::class, 'authenticate'])->name('authenticate');
    });

    // Route khusus Admin (Sudah Login & Role = Admin)
    Route::middleware(['auth', 'role:admin'])->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
        
        // Master Pegawai (CRUD AJAX)
        Route::resource('karyawan', EmployeeController::class)->except(['create', 'show']);
        Route::get('/departemen', [App\Http\Controllers\Admin\DepartmentController::class, 'index'])->name('departemen.index');
        
        // Route AJAX Divisi
        Route::post('/departemen/divisi', [App\Http\Controllers\Admin\DepartmentController::class, 'storeDivision']);
        Route::delete('/departemen/divisi/{id}', [App\Http\Controllers\Admin\DepartmentController::class, 'destroyDivision']);
        
        // Route AJAX Jabatan
        Route::post('/departemen/jabatan', [App\Http\Controllers\Admin\DepartmentController::class, 'storePosition']);
        Route::get('/departemen/jabatan/{id}/edit', [App\Http\Controllers\Admin\DepartmentController::class, 'editPosition']);
        Route::put('/departemen/jabatan/{id}', [App\Http\Controllers\Admin\DepartmentController::class, 'updatePosition']);
        Route::delete('/departemen/jabatan/{id}', [App\Http\Controllers\Admin\DepartmentController::class, 'destroyPosition']);
        Route::get('/registrasi-wajah', [App\Http\Controllers\Admin\FaceRegistrationController::class, 'index'])->name('registrasi-wajah.index');
        Route::post('/registrasi-wajah', [App\Http\Controllers\Admin\FaceRegistrationController::class, 'store'])->name('registrasi-wajah.store');
        Route::get('/absensi', [App\Http\Controllers\Admin\AttendanceController::class, 'index'])->name('absensi.index');
        Route::get('/absensi/export-excel', [App\Http\Controllers\Admin\AttendanceController::class, 'exportExcel'])->name('absensi.exportExcel');
        
        // Next: Route Master Data (Jadwal, Registrasi Wajah, dll) di bawah sini
    });
});