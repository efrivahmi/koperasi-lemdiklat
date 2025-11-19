<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Student;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Admin User
        User::create([
            'name' => 'Administrator',
            'email' => 'admin@koperasi.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        // Kasir User
        User::create([
            'name' => 'Kasir 1',
            'email' => 'kasir@koperasi.com',
            'password' => Hash::make('password'),
            'role' => 'kasir',
        ]);

        // Siswa User (contoh)
        $siswaUser = User::create([
            'name' => 'Budi Santoso',
            'email' => 'budi@student.com',
            'password' => Hash::make('password'),
            'role' => 'siswa',
        ]);

        // Buat profil siswa
        Student::create([
            'user_id' => $siswaUser->id,
            'nis' => '2024001',
            'name' => 'Budi Santoso',
            'kelas' => 'XII IPA 1',
            'balance' => 50000,
            'rfid_uid' => null,
            'foto' => null,
        ]);
    }
}
