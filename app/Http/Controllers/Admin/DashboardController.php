<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Attendance;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $today = Carbon::today()->toDateString();

        // 1. Total Karyawan Aktif
        $totalKaryawan = User::where('role', 'pegawai')->count();

        // 2. Statistik Kehadiran Hari Ini
        $hadir = Attendance::whereDate('tanggal', $today)->where('status_kehadiran', 'hadir')->count();
        $terlambat = Attendance::whereDate('tanggal', $today)->where('status_kehadiran', 'terlambat')->count();
        
        // Asumsi Tidak Hadir (Alfa/Izin/Cuti/Belum Absen) = Total Karyawan - Yang Sudah Absen
        $tidakHadir = $totalKaryawan - ($hadir + $terlambat);
        if ($tidakHadir < 0) {
            $tidakHadir = 0;
        }

        // 3. Hitung Persentase (Cegah pembagian dengan 0 jika belum ada karyawan)
        $persenHadir = $totalKaryawan > 0 ? round(($hadir / $totalKaryawan) * 100) : 0;
        $persenTerlambat = $totalKaryawan > 0 ? round(($terlambat / $totalKaryawan) * 100) : 0;
        $persenTidakHadir = $totalKaryawan > 0 ? round(($tidakHadir / $totalKaryawan) * 100) : 0;

        // 4. Data Grafik Bar Mingguan (Senin sampai Minggu untuk minggu ini)
        $weeklyData = [];
        $startOfWeek = Carbon::now()->startOfWeek(); // Mulai dari hari Senin
        for ($i = 0; $i < 7; $i++) {
            $date = $startOfWeek->copy()->addDays($i)->toDateString();
            
            // Hitung total yg hadir & terlambat di hari tersebut
            $count = Attendance::whereDate('tanggal', $date)
                               ->whereIn('status_kehadiran', ['hadir', 'terlambat'])
                               ->count();
            $weeklyData[] = $count;
        }

        return view('admin.dashboard', compact(
            'totalKaryawan', 'hadir', 'terlambat', 'tidakHadir', 
            'persenHadir', 'persenTerlambat', 'persenTidakHadir', 'weeklyData'
        ));
    }
}