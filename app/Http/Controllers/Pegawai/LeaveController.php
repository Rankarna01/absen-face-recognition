<?php

namespace App\Http\Controllers\Pegawai;

use App\Http\Controllers\Controller;
use App\Models\Leave;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LeaveController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        
        // Ambil semua riwayat pengajuan user ini
        $riwayat = Leave::where('user_id', $user->id)
                        ->orderBy('created_at', 'desc')
                        ->get();

        return view('pegawai.leave.index', compact('riwayat'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'jenis_izin' => 'required|string',
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
            'alasan' => 'required|string',
            'lampiran' => 'nullable|image|mimes:jpeg,png,jpg|max:2048', // Maksimal 2MB
        ]);

        try {
            $data = [
                'user_id' => Auth::id(),
                'jenis_izin' => $request->jenis_izin,
                'tanggal_mulai' => $request->tanggal_mulai,
                'tanggal_selesai' => $request->tanggal_selesai,
                'alasan' => $request->alasan,
                'status' => 'menunggu' // Status default saat baru diajukan
            ];

            // Jika ada lampiran surat dokter / bukti
            if ($request->hasFile('lampiran')) {
                $data['lampiran'] = $request->file('lampiran')->store('pengajuan_izin', 'public');
            }

            Leave::create($data);

            return response()->json([
                'status' => 'success', 
                'message' => 'Pengajuan berhasil dikirim! Silakan tunggu konfirmasi Admin.'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error', 
                'message' => 'Gagal mengirim pengajuan: ' . $e->getMessage()
            ], 500);
        }
    }
}