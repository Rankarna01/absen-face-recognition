<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Leave;
use Illuminate\Http\Request;

class LeaveController extends Controller
{
    public function index()
    {
        // Ambil data yang statusnya masih 'menunggu'
        $pending = Leave::with(['user.employee.position'])
                        ->where('status', 'menunggu')
                        ->orderBy('created_at', 'asc')
                        ->get();

        // Ambil data riwayat (yang sudah disetujui atau ditolak)
        $history = Leave::with(['user.employee.position'])
                        ->whereIn('status', ['disetujui', 'ditolak'])
                        ->orderBy('updated_at', 'desc')
                        ->get();

        return view('admin.leave.index', compact('pending', 'history'));
    }

    // Fungsi untuk memproses approval (Disetujui / Ditolak) via AJAX
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:disetujui,ditolak',
        ]);

        try {
            $leave = Leave::findOrFail($id);
            
            $leave->update([
                'status' => $request->status,
                'keterangan_admin' => $request->keterangan_admin ?? null
            ]);

            return response()->json([
                'status' => 'success', 
                'message' => 'Pengajuan berhasil ' . $request->status . '!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error', 
                'message' => 'Gagal memproses data: ' . $e->getMessage()
            ], 500);
        }
    }
}