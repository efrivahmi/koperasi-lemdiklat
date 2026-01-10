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
            'name' => 'Master Administrator',
            'email' => 'master@koperasi.com',
            'password' => Hash::make('Master123'), // Memenuhi requirement: huruf besar, kecil, angka
            'role' => 'master',
            'email_verified_at' => now(),
        ]);

        $this->command->info('✅ Master user created: master@koperasi.com | Password: Master123');

        // Admin User
        User::create([
            'name' => 'Administrator',
            'email' => 'admin@koperasi.com',
            'password' => Hash::make('Admin123'), // Memenuhi requirement
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        $this->command->info('✅ Admin user created: admin@koperasi.com | Password: Admin123');

        // Kasir User 1
        User::create([
            'name' => 'Kasir Utama',
            'email' => 'kasir@koperasi.com',
            'password' => Hash::make('Kasir123'), // Memenuhi requirement
            'role' => 'kasir',
            'email_verified_at' => now(),
        ]);

        $this->command->info('✅ Kasir user created: kasir@koperasi.com | Password: Kasir123');

        // Kasir User 2
        User::create([
            'name' => 'Kasir 2',
            'email' => 'kasir2@koperasi.com',
            'password' => Hash::make('Kasir123'), // Memenuhi requirement
            'role' => 'kasir',
            'email_verified_at' => now(),
        ]);

        $this->command->info('✅ Kasir 2 user created: kasir2@koperasi.com | Password: Kasir123');

        $this->command->info('User seeder completed successfully!');
        $this->command->newLine();
        $this->command->warn('IMPORTANT: Please change these default passwords in production!');
    }
}
