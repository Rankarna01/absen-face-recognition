<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Holiday;
use Illuminate\Http\Request;

class HolidayController extends Controller
{
    public function index()
    {
        $holidays = Holiday::orderBy('tanggal', 'desc')->get();
        return view('admin.holiday.index', compact('holidays'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'tanggal' => 'required|date|unique:holidays,tanggal',
            'keterangan' => 'required|string|max:255'
        ], [
            'tanggal.unique' => 'Tanggal ini sudah didaftarkan sebagai hari libur.'
        ]);

        Holiday::create($request->all());
        return response()->json(['status' => 'success', 'message' => 'Hari libur berhasil ditambahkan!']);
    }

    public function destroy($id)
    {
        Holiday::findOrFail($id)->delete();
        return response()->json(['status' => 'success', 'message' => 'Hari libur dihapus!']);
    }
}