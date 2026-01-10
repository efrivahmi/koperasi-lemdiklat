<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Student;
use Illuminate\Support\Facades\Hash;

class StudentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $students = [
            [
                'name' => 'Andi Prasetyo',
                'email' => 'andi.prasetyo@student.com',
                'nis' => '2021001',
                'nisn' => '0051234567',
                'kelas' => 'X IPA 1',
                'jenjang' => 'SMA Taruna Nusantara Indonesia',
                'balance' => 250000,
            ],
            [
                'name' => 'Siti Aisyah',
                'email' => 'siti.aisyah@student.com',
                'nis' => '2021002',
                'nisn' => '0051234568',
                'kelas' => 'X IPA 2',
                'jenjang' => 'SMA Taruna Nusantara Indonesia',
                'balance' => 300000,
            ],
            [
                'name' => 'Budi Santoso',
                'email' => 'budi.santoso@student.com',
                'nis' => '2021003',
                'nisn' => '0051234569',
                'kelas' => 'XI IPS 1',
                'jenjang' => 'SMA Taruna Nusantara Indonesia',
                'balance' => 150000,
            ],
            [
                'name' => 'Dewi Lestari',
                'email' => 'dewi.lestari@student.com',
                'nis' => '2020001',
                'nisn' => '0041234567',
                'kelas' => 'XII IPA 1',
                'jenjang' => 'SMA Taruna Nusantara Indonesia',
                'balance' => 500000,
            ],
            [
                'name' => 'Rizki Ramadhan',
                'email' => 'rizki.ramadhan@student.com',
                'nis' => '2021004',
                'nisn' => '0051234570',
                'kelas' => 'X TKJ 1',
                'jenjang' => 'SMK Taruna Nusantara Jaya',
                'balance' => 200000,
            ],
            [
                'name' => 'Maya Putri',
                'email' => 'maya.putri@student.com',
                'nis' => '2021005',
                'nisn' => '0051234571',
                'kelas' => 'XI RPL 1',
                'jenjang' => 'SMK Taruna Nusantara Jaya',
                'balance' => 350000,
            ],
            [
                'name' => 'Ahmad Fauzi',
                'email' => 'ahmad.fauzi@student.com',
                'nis' => '2020002',
                'nisn' => '0041234568',
                'kelas' => 'XII TKJ 2',
                'jenjang' => 'SMK Taruna Nusantara Jaya',
                'balance' => 450000,
            ],
            [
                'name' => 'Nur Azizah',
                'email' => 'nur.azizah@student.com',
                'nis' => '2021006',
                'nisn' => '0051234572',
                'kelas' => 'X IPA 3',
                'jenjang' => 'SMA Taruna Nusantara Indonesia',
                'balance' => 175000,
            ],
        ];

        foreach ($students as $studentData) {
            // Create user account
            $user = User::create([
                'name' => $studentData['name'],
                'email' => $studentData['email'],
                'password' => Hash::make('password123'), // Default password
                'role' => 'siswa',
                'email_verified_at' => now(),
            ]);

            // Generate unique RFID UID
            do {
                $rfidUid = strtoupper(bin2hex(random_bytes(4)));
            } while (
                Student::where('rfid_uid', $rfidUid)->exists() ||
                \App\Models\Teacher::where('rfid_uid', $rfidUid)->exists()
            );

            // Create student profile
            Student::create([
                'user_id' => $user->id,
                'nis' => $studentData['nis'],
                'nisn' => $studentData['nisn'],
                'name' => $studentData['name'],
                'kelas' => $studentData['kelas'],
                'jenjang' => $studentData['jenjang'],
                'balance' => $studentData['balance'],
                'rfid_uid' => $rfidUid,
            ]);

            $this->command->info("Student created: {$studentData['name']} (RFID: {$rfidUid})");
        }

        $this->command->info('Student seeder completed successfully!');
    }
}
