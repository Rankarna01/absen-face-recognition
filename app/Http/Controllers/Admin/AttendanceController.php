<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\User;
use Illuminate\Http\Request;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    public function index(Request $request)
    {
        $query = Attendance::with(['user.employee.division', 'user.employee.position']);

        // 1. Filter Rentang Waktu (Harian, Mingguan, Bulanan, Tahunan)
        if ($request->filled('filter_waktu')) {
            $now = Carbon::now();
            switch ($request->filter_waktu) {
                case 'hari_ini':
                    $query->whereDate('tanggal', $now->toDateString());
                    break;
                case 'minggu_ini':
                    $query->whereBetween('tanggal', [$now->startOfWeek()->toDateString(), $now->endOfWeek()->toDateString()]);
                    break;
                case 'bulan_ini':
                    $query->whereMonth('tanggal', $now->month)->whereYear('tanggal', $now->year);
                    break;
                case 'tahun_ini':
                    $query->whereYear('tanggal', $now->year);
                    break;
            }
        } elseif ($request->filled('tanggal_awal') && $request->filled('tanggal_akhir')) {
            // Filter Custom Tanggal
            $query->whereBetween('tanggal', [$request->tanggal_awal, $request->tanggal_akhir]);
        } else {
            // Default: Tampilkan Hari Ini agar tidak berat
            $query->whereDate('tanggal', Carbon::today()->toDateString());
        }

        // 2. Filter Pegawai
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        $attendances = $query->orderBy('tanggal', 'desc')->orderBy('jam_masuk', 'desc')->get();
        $employees = User::where('role', 'pegawai')->orderBy('name', 'asc')->get();

        return view('admin.attendance.index', compact('attendances', 'employees'));
    }

    // Fungsi Export ke CSV (Excel Compatible) tanpa perlu library tambahan
    public function exportExcel(Request $request)
    {
        $query = Attendance::with(['user.employee.position']);
        
        if ($request->filled('filter_waktu')) {
            // Logic sama persis dengan index
            $now = Carbon::now();
            if($request->filter_waktu == 'hari_ini') $query->whereDate('tanggal', $now->toDateString());
            if($request->filter_waktu == 'minggu_ini') $query->whereBetween('tanggal', [$now->startOfWeek()->toDateString(), $now->endOfWeek()->toDateString()]);
            if($request->filter_waktu == 'bulan_ini') $query->whereMonth('tanggal', $now->month)->whereYear('tanggal', $now->year);
            if($request->filter_waktu == 'tahun_ini') $query->whereYear('tanggal', $now->year);
        } elseif ($request->filled('tanggal_awal') && $request->filled('tanggal_akhir')) {
            $query->whereBetween('tanggal', [$request->tanggal_awal, $request->tanggal_akhir]);
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        $attendances = $query->orderBy('tanggal', 'desc')->get();

        $fileName = 'Laporan_Absensi_' . date('Y-m-d_H-i-s') . '.csv';
        
        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $columns = ['Tanggal', 'NIP', 'Nama Pegawai', 'Jabatan', 'Jam Masuk', 'Jam Pulang', 'Status'];

        $callback = function() use($attendances, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach ($attendances as $absen) {
                $row['Tanggal']  = Carbon::parse($absen->tanggal)->format('d/m/Y');
                $row['NIP']    = $absen->user->nip;
                $row['Nama Pegawai']  = $absen->user->name;
                $row['Jabatan']  = $absen->user->employee->position->nama_jabatan ?? '-';
                $row['Jam Masuk']  = $absen->jam_masuk ?? '-';
                $row['Jam Pulang']  = $absen->jam_pulang ?? '-';
                $row['Status']  = strtoupper($absen->status_kehadiran);

                fputcsv($file, array($row['Tanggal'], $row['NIP'], $row['Nama Pegawai'], $row['Jabatan'], $row['Jam Masuk'], $row['Jam Pulang'], $row['Status']));
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}