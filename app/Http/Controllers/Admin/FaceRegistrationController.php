<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Employee;
use Illuminate\Http\Request;

class FaceRegistrationController extends Controller
{
    public function index()
    {
        // Ambil semua data pegawai beserta relasi employee-nya
        $employees = User::where('role', 'pegawai')->with('employee')->latest()->get();
        return view('admin.face-registration.index', compact('employees'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'face_descriptor' => 'required' // Ini berupa string JSON array dari Face API
        ]);

        try {
            // Gunakan updateOrCreate untuk menghindari error jika relasi employee belum ada
            Employee::updateOrCreate(
                ['user_id' => $request->user_id],
                ['face_descriptor' => $request->face_descriptor]
            );

            return response()->json([
                'status' => 'success', 
                'message' => 'Data wajah berhasil didaftarkan ke sistem!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error', 
                'message' => 'Gagal mendaftarkan wajah: ' . $e->getMessage()
            ], 500);
        }
    }
}