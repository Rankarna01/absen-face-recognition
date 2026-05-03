<?php

namespace App\Http\Controllers\Pegawai;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        
        // Ambil filter tahun (default ke tahun ini)
        $year = $request->tahun ?? Carbon::now()->year;

        // Ambil data absensi selama setahun penuh
        $attendances = Attendance::where('user_id', $user->id)
                                 ->whereYear('tanggal', $year)
                                 ->get();

        // Siapkan array kosong untuk 12 bulan (1-12)
        $chartData = [
            'hadir' => array_fill(1, 12, 0),
            'terlambat' => array_fill(1, 12, 0),
            'izin' => array_fill(1, 12, 0),
        ];

        // Kelompokkan data berdasarkan bulan
        foreach ($attendances as $att) {
            $month = (int) Carbon::parse($att->tanggal)->format('n');
            
            if ($att->status_kehadiran == 'hadir') {
                $chartData['hadir'][$month]++;
            } elseif ($att->status_kehadiran == 'terlambat') {
                $chartData['terlambat'][$month]++;
            } elseif (in_array($att->status_kehadiran, ['izin', 'cuti'])) {
                $chartData['izin'][$month]++;
            }
        }

        // Hitung Total Setahun
        $summary = [
            'total_hadir' => array_sum($chartData['hadir']),
            'total_terlambat' => array_sum($chartData['terlambat']),
            'total_izin' => array_sum($chartData['izin']),
            'total_hari' => $attendances->count()
        ];

        return view('pegawai.report.index', compact('year', 'chartData', 'summary'));
    }

    // Fungsi Export ke CSV (Excel)
    public function export(Request $request)
    {
        $user = Auth::user();
        $year = $request->tahun ?? Carbon::now()->year;
        
        $attendances = Attendance::where('user_id', $user->id)
                                 ->whereYear('tanggal', $year)
                                 ->orderBy('tanggal', 'asc')
                                 ->get();

        $fileName = 'Riwayat_Absensi_' . str_replace(' ', '_', $user->name) . '_' . $year . '.csv';

        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $callback = function() use ($attendances) {
            $file = fopen('php://output', 'w');
            
            // Header Kolom Excel
            fputcsv($file, ['Tanggal', 'Jam Masuk', 'Jam Pulang', 'Status Kehadiran']);
            
            // Isi Data
            foreach ($attendances as $row) {
                fputcsv($file, [
                    $row->tanggal, 
                    $row->jam_masuk ?? '-', 
                    $row->jam_pulang ?? '-', 
                    strtoupper($row->status_kehadiran)
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}