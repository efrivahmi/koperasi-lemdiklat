<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Saving;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;
use Carbon\Carbon;

class SavingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get first admin user as processor
        $adminUser = User::where('role', 'admin')->orWhere('role', 'master')->first();

        if (!$adminUser) {
            $this->command->warn('No admin user found. Skipping savings seeder.');
            return;
        }

        // Get some students
        $students = Student::with('user')->limit(3)->get();
        // Get some teachers
        $teachers = Teacher::with('user')->limit(2)->get();

        $savingsData = [];

        // Create savings for students
        foreach ($students as $index => $student) {
            $balanceBefore = 0;

            // Deposit 1 - 30 days ago
            $balanceBefore = $balanceBefore;
            $depositAmount = 100000;
            $balanceAfter = $balanceBefore + $depositAmount;

            $savingsData[] = [
                'saver_type' => 'App\Models\Student',
                'saver_id' => $student->id,
                'type' => 'deposit',
                'amount' => $depositAmount,
                'balance_before' => $balanceBefore,
                'balance_after' => $balanceAfter,
                'description' => 'Setoran awal tabungan',
                'transaction_date' => Carbon::now()->subDays(30 - ($index * 5)),
                'processed_by' => $adminUser->id,
                'created_at' => Carbon::now()->subDays(30 - ($index * 5)),
                'updated_at' => Carbon::now()->subDays(30 - ($index * 5)),
            ];

            $balanceBefore = $balanceAfter;

            // Deposit 2 - 15 days ago
            $depositAmount = 50000;
            $balanceAfter = $balanceBefore + $depositAmount;

            $savingsData[] = [
                'saver_type' => 'App\Models\Student',
                'saver_id' => $student->id,
                'type' => 'deposit',
                'amount' => $depositAmount,
                'balance_before' => $balanceBefore,
                'balance_after' => $balanceAfter,
                'description' => 'Setoran bulanan',
                'transaction_date' => Carbon::now()->subDays(15 - ($index * 3)),
                'processed_by' => $adminUser->id,
                'created_at' => Carbon::now()->subDays(15 - ($index * 3)),
                'updated_at' => Carbon::now()->subDays(15 - ($index * 3)),
            ];

            $balanceBefore = $balanceAfter;

            // Withdrawal - 7 days ago (for first student only)
            if ($index === 0) {
                $withdrawalAmount = 30000;
                $balanceAfter = $balanceBefore - $withdrawalAmount;

                $savingsData[] = [
                    'saver_type' => 'App\Models\Student',
                    'saver_id' => $student->id,
                    'type' => 'withdrawal',
                    'amount' => $withdrawalAmount,
                    'balance_before' => $balanceBefore,
                    'balance_after' => $balanceAfter,
                    'description' => 'Penarikan untuk keperluan sekolah',
                    'transaction_date' => Carbon::now()->subDays(7),
                    'processed_by' => $adminUser->id,
                    'created_at' => Carbon::now()->subDays(7),
                    'updated_at' => Carbon::now()->subDays(7),
                ];
            }
        }

        // Create savings for teachers
        foreach ($teachers as $index => $teacher) {
            $balanceBefore = 0;

            // Deposit 1 - 25 days ago
            $depositAmount = 200000;
            $balanceAfter = $balanceBefore + $depositAmount;

            $savingsData[] = [
                'saver_type' => 'App\Models\Teacher',
                'saver_id' => $teacher->id,
                'type' => 'deposit',
                'amount' => $depositAmount,
                'balance_before' => $balanceBefore,
                'balance_after' => $balanceAfter,
                'description' => 'Setoran awal tabungan guru',
                'transaction_date' => Carbon::now()->subDays(25 - ($index * 5)),
                'processed_by' => $adminUser->id,
                'created_at' => Carbon::now()->subDays(25 - ($index * 5)),
                'updated_at' => Carbon::now()->subDays(25 - ($index * 5)),
            ];

            $balanceBefore = $balanceAfter;

            // Deposit 2 - 10 days ago
            $depositAmount = 150000;
            $balanceAfter = $balanceBefore + $depositAmount;

            $savingsData[] = [
                'saver_type' => 'App\Models\Teacher',
                'saver_id' => $teacher->id,
                'type' => 'deposit',
                'amount' => $depositAmount,
                'balance_before' => $balanceBefore,
                'balance_after' => $balanceAfter,
                'description' => 'Setoran bulanan guru',
                'transaction_date' => Carbon::now()->subDays(10 - ($index * 2)),
                'processed_by' => $adminUser->id,
                'created_at' => Carbon::now()->subDays(10 - ($index * 2)),
                'updated_at' => Carbon::now()->subDays(10 - ($index * 2)),
            ];
        }

        // Insert all savings data
        foreach ($savingsData as $savingData) {
            Saving::create($savingData);
            $saverName = $savingData['saver_type'] === 'App\Models\Student'
                ? Student::find($savingData['saver_id'])->name
                : Teacher::find($savingData['saver_id'])->name;

            $this->command->info("Saving created: {$saverName} - {$savingData['type']} Rp" . number_format($savingData['amount']));
        }

        $this->command->info('Saving seeder completed successfully!');
    }
}
