<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        // Data dummy sementara agar dashboard tidak error
        $data = [
            'total_karyawan' => 128,
            'hadir_hari_ini' => 92,
            'terlambat' => 18,
            'tidak_hadir' => 18,
        ];

        // Me-return view resources/views/admin/dashboard.blade.php beserta data dummy
        return view('admin.dashboard', compact('data'));
    }
}