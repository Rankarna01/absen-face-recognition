<?php

namespace App\Http\Controllers\Pegawai;

use App\Http\Controllers\Controller;
use App\Models\Schedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class ScheduleController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        
        // Ambil filter bulan/tahun (default ke bulan ini)
        $periode = $request->periode ?? Carbon::now()->format('Y-m');
        $tahun = substr($periode, 0, 4);
        $bulan = substr($periode, 5, 2);

        // Ambil data jadwal berserta relasi Shift-nya
        $jadwal = Schedule::with('shift')
                          ->where('user_id', $user->id)
                          ->whereMonth('tanggal', $bulan)
                          ->whereYear('tanggal', $tahun)
                          ->orderBy('tanggal', 'asc')
                          ->get();

        return view('pegawai.schedule.index', compact('jadwal', 'periode'));
    }
}