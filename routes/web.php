<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\EmployeeController;

// ==========================================
// --- ROUTE UTAMA & AUTENTIKASI GLOBAL ---
// ==========================================

// Arahkan otomatis halaman awal (/) ke halaman login
// Arahkan otomatis halaman awal (/) ke halaman login
Route::redirect('/', '/login');

// Route Login (Tanpa middleware guest, biar AuthController yang atur!)
Route::get('/login', [AuthController::class, 'index'])->name('login');
Route::post('/login', [AuthController::class, 'authenticate'])->name('authenticate');

// Route Logout Global (Untuk Admin & Pegawai)
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});


// ==========================================
// --- ROUTE ADMIN ---
// ==========================================
Route::prefix('admin')->name('admin.')->middleware(['auth', 'role:admin'])->group(function () {
    
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // Master Pegawai
    Route::resource('karyawan', EmployeeController::class)->except(['create', 'show']);
    
    // Master Departemen (Divisi & Jabatan)
    Route::get('/departemen', [App\Http\Controllers\Admin\DepartmentController::class, 'index'])->name('departemen.index');
    Route::post('/departemen/divisi', [App\Http\Controllers\Admin\DepartmentController::class, 'storeDivision']);
    Route::delete('/departemen/divisi/{id}', [App\Http\Controllers\Admin\DepartmentController::class, 'destroyDivision']);
    Route::post('/departemen/jabatan', [App\Http\Controllers\Admin\DepartmentController::class, 'storePosition']);
    Route::get('/departemen/jabatan/{id}/edit', [App\Http\Controllers\Admin\DepartmentController::class, 'editPosition']);
    Route::put('/departemen/jabatan/{id}', [App\Http\Controllers\Admin\DepartmentController::class, 'updatePosition']);
    Route::delete('/departemen/jabatan/{id}', [App\Http\Controllers\Admin\DepartmentController::class, 'destroyPosition']);
    
    // Registrasi Wajah
    Route::get('/registrasi-wajah', [App\Http\Controllers\Admin\FaceRegistrationController::class, 'index'])->name('registrasi-wajah.index');
    Route::post('/registrasi-wajah', [App\Http\Controllers\Admin\FaceRegistrationController::class, 'store'])->name('registrasi-wajah.store');
    
    // Absensi
    Route::get('/absensi', [App\Http\Controllers\Admin\AttendanceController::class, 'index'])->name('absensi.index');
    Route::get('/absensi/export-excel', [App\Http\Controllers\Admin\AttendanceController::class, 'exportExcel'])->name('absensi.exportExcel');
    
    // Jadwal Kerja
    Route::get('/jadwal', [App\Http\Controllers\Admin\ScheduleController::class, 'index'])->name('jadwal.index');
    Route::post('/jadwal/shift', [App\Http\Controllers\Admin\ScheduleController::class, 'storeShift']);
    Route::post('/jadwal/assign', [App\Http\Controllers\Admin\ScheduleController::class, 'assignSchedule']);
    Route::delete('/jadwal/{id}', [App\Http\Controllers\Admin\ScheduleController::class, 'destroySchedule']);
    
    // Pengajuan Izin & Cuti
    Route::get('/pengajuan', [App\Http\Controllers\Admin\LeaveController::class, 'index'])->name('pengajuan.index');
    Route::post('/pengajuan/{id}/status', [App\Http\Controllers\Admin\LeaveController::class, 'updateStatus']);
    
    // Payroll & Slip Gaji
    Route::get('/payroll', [App\Http\Controllers\Admin\PayrollController::class, 'index'])->name('payroll.index');
    Route::post('/payroll/generate', [App\Http\Controllers\Admin\PayrollController::class, 'generate']);
    Route::post('/payroll/{id}/bayar', [App\Http\Controllers\Admin\PayrollController::class, 'markPaid']);
    Route::delete('/payroll/{id}', [App\Http\Controllers\Admin\PayrollController::class, 'destroy']);
    Route::get('/payroll/{id}/print', [App\Http\Controllers\Admin\PayrollController::class, 'printSlip'])->name('payroll.print');
    
    // Laporan
    Route::get('/laporan', [App\Http\Controllers\Admin\ReportController::class, 'index'])->name('laporan.index');
    Route::get('/laporan/export-excel', [App\Http\Controllers\Admin\ReportController::class, 'exportExcel'])->name('laporan.exportExcel');
    
    // Pengaturan Sistem
    Route::get('/pengaturan', [App\Http\Controllers\Admin\SettingController::class, 'index'])->name('pengaturan.index');
    Route::post('/pengaturan', [App\Http\Controllers\Admin\SettingController::class, 'update'])->name('pengaturan.update');
    
    // Manajemen User
    Route::resource('users', App\Http\Controllers\Admin\UserController::class)->except(['create', 'show']);
});


// ==========================================
// --- ROUTE PEGAWAI (Mobile PWA) ---
// ==========================================
Route::prefix('pegawai')->name('pegawai.')->middleware(['auth', 'role:pegawai'])->group(function () {
    
    // Beranda Pegawai
    Route::get('/beranda', [App\Http\Controllers\Pegawai\HomeController::class, 'index'])->name('beranda');
    Route::get('/absen', [App\Http\Controllers\Pegawai\AttendanceController::class, 'index'])->name('absen.index');
    Route::post('/absen', [App\Http\Controllers\Pegawai\AttendanceController::class, 'store'])->name('absen.store');
    Route::get('/riwayat', [App\Http\Controllers\Pegawai\HistoryController::class, 'index'])->name('riwayat');
    Route::get('/izin', [App\Http\Controllers\Pegawai\LeaveController::class, 'index'])->name('izin.index');
    Route::post('/izin', [App\Http\Controllers\Pegawai\LeaveController::class, 'store'])->name('izin.store');
    // Jadwal Kerja
    Route::get('/jadwal', [App\Http\Controllers\Pegawai\ScheduleController::class, 'index'])->name('jadwal');
    Route::get('/slip-gaji', [App\Http\Controllers\Pegawai\PayrollController::class, 'index'])->name('slip.index');
    Route::get('/slip-gaji/{id}/print', [App\Http\Controllers\Pegawai\PayrollController::class, 'print'])->name('slip.print');
    // Profil & Pengaturan Akun
    Route::get('/profil', [App\Http\Controllers\Pegawai\ProfileController::class, 'index'])->name('profil.index');
    Route::post('/profil/update', [App\Http\Controllers\Pegawai\ProfileController::class, 'updateProfile'])->name('profil.update');
    Route::post('/profil/password', [App\Http\Controllers\Pegawai\ProfileController::class, 'updatePassword'])->name('profil.password');
    Route::get('/laporan', [App\Http\Controllers\Pegawai\ReportController::class, 'index'])->name('laporan.index');
    Route::get('/laporan/export', [App\Http\Controllers\Pegawai\ReportController::class, 'export'])->name('laporan.export');
    
});