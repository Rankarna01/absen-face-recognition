<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Seeder untuk Administrator
        User::create([
            'name' => 'Administrator',
            'email' => 'admin@familymarket.com',
            'password' => Hash::make('password123'),
            'role' => 'admin',
        ]);

        // 2. Seeder untuk Pegawai (Muhammad)
        $pegawai = User::create([
            'name' => 'Muhammad',
            'nip' => '12345',
            'email' => 'muhammad@familymarket.com',
            'password' => Hash::make('password123'),
            'role' => 'pegawai',
        ]);

        // Kita juga buatkan data relasi Employee-nya (akan kita buat model/migrasinya di bawah)
        $pegawai->employee()->create([
            'jabatan' => 'Kasir',
            'divisi' => 'Operasional',
            // foto dan face_descriptor dibiarkan null dulu untuk nanti diupdate di menu Registrasi Wajah
        ]);
    }
}