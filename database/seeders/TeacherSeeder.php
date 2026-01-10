<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Teacher;
use Illuminate\Support\Facades\Hash;

class TeacherSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $teachers = [
            [
                'name' => 'Budi Santoso',
                'email' => 'budi.santoso@example.com',
                'nip' => '196801011993031001',
                'nuptk' => '1234567890123456',
                'jabatan' => 'Guru Tetap',
                'mata_pelajaran' => 'Matematika',
                'jenjang' => 'SMA Taruna Nusantara Indonesia',
                'balance' => 500000,
            ],
            [
                'name' => 'Siti Nurhaliza',
                'email' => 'siti.nurhaliza@example.com',
                'nip' => '197205101995122001',
                'nuptk' => '2345678901234567',
                'jabatan' => 'Guru Tetap',
                'mata_pelajaran' => 'Bahasa Indonesia',
                'jenjang' => 'SMA Taruna Nusantara Indonesia',
                'balance' => 750000,
            ],
            [
                'name' => 'Ahmad Dahlan',
                'email' => 'ahmad.dahlan@example.com',
                'nip' => '198003151998031002',
                'nuptk' => '3456789012345678',
                'jabatan' => 'Guru Honorer',
                'mata_pelajaran' => 'Fisika',
                'jenjang' => 'SMK Taruna Nusantara Jaya',
                'balance' => 300000,
            ],
            [
                'name' => 'Dewi Lestari',
                'email' => 'dewi.lestari@example.com',
                'nip' => '198512202006042003',
                'nuptk' => '4567890123456789',
                'jabatan' => 'Guru Tetap',
                'mata_pelajaran' => 'Kimia',
                'jenjang' => 'SMK Taruna Nusantara Jaya',
                'balance' => 450000,
            ],
            [
                'name' => 'Rudi Hermawan',
                'email' => 'rudi.hermawan@example.com',
                'nip' => '199001052010011003',
                'nuptk' => '5678901234567890',
                'jabatan' => 'Guru Tetap',
                'mata_pelajaran' => 'Bahasa Inggris',
                'jenjang' => 'SMA Taruna Nusantara Indonesia',
                'balance' => 600000,
            ],
        ];

        foreach ($teachers as $teacherData) {
            // Create user account
            $user = User::create([
                'name' => $teacherData['name'],
                'email' => $teacherData['email'],
                'password' => Hash::make('password123'), // Default password
                'role' => 'guru',
                'email_verified_at' => now(),
            ]);

            // Generate unique RFID UID
            do {
                $rfidUid = strtoupper(bin2hex(random_bytes(4)));
            } while (
                Teacher::where('rfid_uid', $rfidUid)->exists() ||
                \App\Models\Student::where('rfid_uid', $rfidUid)->exists()
            );

            // Create teacher profile
            Teacher::create([
                'user_id' => $user->id,
                'name' => $teacherData['name'],
                'nip' => $teacherData['nip'],
                'nuptk' => $teacherData['nuptk'],
                'jabatan' => $teacherData['jabatan'],
                'mata_pelajaran' => $teacherData['mata_pelajaran'],
                'jenjang' => $teacherData['jenjang'],
                'balance' => $teacherData['balance'],
                'rfid_uid' => $rfidUid,
            ]);

            $this->command->info("Teacher created: {$teacherData['name']} (RFID: {$rfidUid})");
        }

        $this->command->info('Teacher seeder completed successfully!');
    }
}
