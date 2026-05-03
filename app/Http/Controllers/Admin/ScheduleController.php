<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Shift;
use App\Models\Schedule;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ScheduleController extends Controller
{
    public function index()
    {
        $employees = User::where('role', 'pegawai')->orderBy('name')->get();
        $shifts = Shift::all();
        
        // Format data untuk FullCalendar.js
        $schedules = Schedule::with(['user', 'shift'])->get()->map(function($sched) {
            return [
                'id' => $sched->id,
                'title' => $sched->user->name . ' (' . $sched->shift->nama_shift . ')',
                'start' => $sched->tanggal,
                'color' => $sched->shift->warna ?? '#F5A623',
                'extendedProps' => [
                    'jam' => substr($sched->shift->jam_masuk, 0, 5) . ' - ' . substr($sched->shift->jam_pulang, 0, 5),
                    'nama' => $sched->user->name,
                    'shift' => $sched->shift->nama_shift
                ]
            ];
        });

        return view('admin.schedule.index', compact('employees', 'shifts', 'schedules'));
    }

    // Fungsi Simpan Master Shift
    public function storeShift(Request $request)
    {
        $request->validate([
            'nama_shift' => 'required',
            'warna' => 'required'
        ]);

        Shift::create($request->all());
        return response()->json(['status' => 'success', 'message' => 'Shift berhasil ditambahkan!']);
    }

    // Fungsi Assign Jadwal ke Pegawai (Bisa lebih dari 1 hari)
    public function assignSchedule(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'shift_id' => 'required|exists:shifts,id',
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
        ]);

        $startDate = Carbon::parse($request->tanggal_mulai);
        $endDate = Carbon::parse($request->tanggal_selesai);

        // Looping untuk assign jadwal dari tanggal mulai ke selesai
        for ($date = $startDate; $date->lte($endDate); $date->addDay()) {
            Schedule::updateOrCreate(
                ['user_id' => $request->user_id, 'tanggal' => $date->toDateString()],
                ['shift_id' => $request->shift_id]
            );
        }

        return response()->json(['status' => 'success', 'message' => 'Jadwal berhasil di-assign!']);
    }

    // Fungsi Hapus Jadwal (Saat event di kalender diklik)
    public function destroySchedule($id)
    {
        Schedule::findOrFail($id)->delete();
        return response()->json(['status' => 'success', 'message' => 'Jadwal berhasil dihapus!']);
    }
}