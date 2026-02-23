<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Password requirement: minimal 8 karakter, mengandung huruf besar, huruf kecil, dan angka
     */
    public function run(): void
    {
        // Master User (Super Admin)
        User::create([
            'name' => 'Efri Vahmi',
            'email' => 'efri.crb@gmail.com',
            'password' => Hash::make('Master123'), // Memenuhi requirement: huruf besar, kecil, angka
            'role' => 'master',
            'email_verified_at' => now(),
        ]);

        $this->command->info('✅ Master user created: efri.crb@gmail.com | Password: Master123');

        $this->command->warn('IMPORTANT: Please change these default passwords in production!');
    }
}
