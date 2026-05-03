<?php

namespace App\Http\Controllers\Pegawai;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class HomeController extends Controller
{
    public function index()
    {
        $user = Auth::user()->load('employee.position');
        $today = Carbon::today()->toDateString();
        $currentMonth = Carbon::now()->month;
        $currentYear = Carbon::now()->year;

        // 1. Cek status absen hari ini
        $absenHariIni = Attendance::where('user_id', $user->id)
                                  ->whereDate('tanggal', $today)
                                  ->first();

        // 2. Rekapitulasi bulan ini
        $rekapBulanIni = Attendance::where('user_id', $user->id)
                                   ->whereMonth('tanggal', $currentMonth)
                                   ->whereYear('tanggal', $currentYear)
                                   ->get();

        $summary = [
            'hadir' => $rekapBulanIni->where('status_kehadiran', 'hadir')->count(),
            'izin' => $rekapBulanIni->whereIn('status_kehadiran', ['izin', 'cuti'])->count(),
            'terlambat' => $rekapBulanIni->where('status_kehadiran', 'terlambat')->count(),
        ];

        return view('pegawai.home', compact('user', 'absenHariIni', 'summary'));
    }
}