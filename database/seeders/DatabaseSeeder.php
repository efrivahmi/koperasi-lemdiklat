<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->command->info('🌱 Starting database seeding...');

        // Urutan seeding sangat penting!
        // 1. Users (admin, kasir) harus pertama
        $this->call([
            UserSeeder::class,
        ]);

        // 2. Master data
        $this->call([
            CategorySeeder::class,
            ProductSeeder::class,
        ]);

        // 3. Students & Teachers (butuh users sudah ada)
        $this->call([
            StudentSeeder::class,
            TeacherSeeder::class,
        ]);

        // 4. Permissions (jika kasir butuh permissions)
        if (class_exists(PermissionSeeder::class)) {
            $this->call([
                PermissionSeeder::class,
            ]);
        }

        // 5. Savings (butuh students & teachers sudah ada)
        $this->call([
            SavingSeeder::class,
        ]);

        $this->command->info('✅ Database seeding completed successfully!');
        $this->command->newLine();
        $this->command->info('Default credentials:');
        $this->command->info('📧 Students: *.student.com | Password: password123');
        $this->command->info('👨‍🏫 Teachers: *.example.com | Password: password123');
        $this->command->info('👤 Check UserSeeder for admin credentials');
    }
}
