<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Student;
use App\Models\Category;
use App\Models\Product;
use App\Models\Sale;
use App\Models\Transaction;
use App\Models\Voucher;
use App\Models\Expense;

class DatabaseFixCommand extends Command
{
    protected $signature = 'db:diagnose';
    protected $description = 'Diagnose and display all database issues';

    public function handle()
    {
        $this->info('=== DATABASE DIAGNOSTIC REPORT ===');
        $this->newLine();

        // Check all tables
        $this->checkTables();
        $this->newLine();

        // Check relationships
        $this->checkRelationships();
        $this->newLine();

        // Check enum values
        $this->checkEnumValues();
        $this->newLine();

        // Check for orphaned records
        $this->checkOrphans();
        $this->newLine();

        $this->info('=== DIAGNOSTIC COMPLETE ===');
        $this->newLine();
        $this->info('Run the following commands to test admin functions:');
        $this->line('  - Categories: Visit /admin/categories');
        $this->line('  - Products: Visit /admin/products');
        $this->line('  - Students: Visit /admin/students');
        $this->line('  - Vouchers: Visit /admin/vouchers');
        $this->line('  - Expenses: Visit /admin/expenses');
    }

    private function checkTables()
    {
        $this->info('📊 TABLE COUNTS:');

        $tables = [
            'users' => User::count(),
            'students' => Student::count(),
            'categories' => Category::count(),
            'products' => Product::count(),
            'sales' => Sale::count(),
            'transactions' => Transaction::count(),
            'vouchers' => Voucher::count(),
            'expenses' => Expense::count(),
        ];

        foreach ($tables as $table => $count) {
            $color = $count > 0 ? '<fg=green>' : '<fg=yellow>';
            $this->line("  {$color}{$table}: {$count}</>");
        }
    }

    private function checkRelationships()
    {
        $this->info('🔗 RELATIONSHIP TESTS:');

        try {
            // Test Student -> User relationship
            $student = Student::with('user')->first();
            if ($student && $student->user) {
                $this->line('  <fg=green>✓</> Student->User relationship OK');
            } else {
                $this->line('  <fg=red>✗</> Student->User relationship FAILED');
            }

            // Test Product -> Category relationship
            $product = Product::with('category')->first();
            if ($product && $product->category) {
                $this->line('  <fg=green>✓</> Product->Category relationship OK');
            } else {
                $this->line('  <fg=yellow>!</> Product->Category: No data to test');
            }

            // Test Sale -> Student relationship
            $sale = Sale::with('student')->first();
            if ($sale && $sale->student) {
                $this->line('  <fg=green>✓</> Sale->Student relationship OK');
            } else {
                $this->line('  <fg=yellow>!</> Sale->Student: No data to test');
            }

            // Test Transaction -> Student relationship
            $transaction = Transaction::with('student')->first();
            if ($transaction && $transaction->student) {
                $this->line('  <fg=green>✓</> Transaction->Student relationship OK');
            } else {
                $this->line('  <fg=yellow>!</> Transaction->Student: No data to test');
            }

        } catch (\Exception $e) {
            $this->error('  ✗ Error testing relationships: ' . $e->getMessage());
        }
    }

    private function checkEnumValues()
    {
        $this->info('🔢 ENUM VALUE CHECKS:');

        // Check vouchers status
        try {
            $vouchers = DB::table('vouchers')->select('status')->distinct()->get();
            $this->line('  Voucher status values: ' . $vouchers->pluck('status')->join(', ') ?: 'No vouchers');

            $validVoucherStatus = ['available', 'used'];
            foreach ($vouchers as $v) {
                if (!in_array($v->status, $validVoucherStatus)) {
                    $this->error("  ✗ Invalid voucher status: {$v->status}");
                }
            }
        } catch (\Exception $e) {
            $this->error('  ✗ Voucher enum check failed: ' . $e->getMessage());
        }

        // Check transactions type
        try {
            $transactions = DB::table('transactions')->select('type')->distinct()->get();
            $this->line('  Transaction type values: ' . $transactions->pluck('type')->join(', ') ?: 'No transactions');

            $validTransactionTypes = ['topup', 'debit', 'purchase', 'redeem', 'return'];
            foreach ($transactions as $t) {
                if (!in_array($t->type, $validTransactionTypes)) {
                    $this->error("  ✗ Invalid transaction type: {$t->type}");
                }
            }
        } catch (\Exception $e) {
            $this->error('  ✗ Transaction enum check failed: ' . $e->getMessage());
        }

        // Check sales payment_method
        try {
            $sales = DB::table('sales')->select('payment_method')->distinct()->get();
            $this->line('  Sale payment methods: ' . $sales->pluck('payment_method')->join(', ') ?: 'No sales');

            $validPaymentMethods = ['cash', 'balance'];
            foreach ($sales as $s) {
                if (!in_array($s->payment_method, $validPaymentMethods)) {
                    $this->error("  ✗ Invalid payment method: {$s->payment_method}");
                }
            }
        } catch (\Exception $e) {
            $this->error('  ✗ Sales enum check failed: ' . $e->getMessage());
        }
    }

    private function checkOrphans()
    {
        $this->info('🔍 ORPHANED RECORDS CHECK:');

        // Check products without category
        $orphanedProducts = Product::whereDoesntHave('category')->count();
        if ($orphanedProducts > 0) {
            $this->error("  ✗ {$orphanedProducts} products without category");
        } else {
            $this->line('  <fg=green>✓</> No orphaned products');
        }

        // Check students without user
        $orphanedStudents = Student::whereDoesntHave('user')->count();
        if ($orphanedStudents > 0) {
            $this->error("  ✗ {$orphanedStudents} students without user");
        } else {
            $this->line('  <fg=green>✓</> No orphaned students');
        }

        // Check sales without student (cash sales are OK)
        $salesWithoutStudent = Sale::where('payment_method', 'balance')
            ->whereNull('student_id')
            ->count();
        if ($salesWithoutStudent > 0) {
            $this->error("  ✗ {$salesWithoutStudent} balance sales without student");
        } else {
            $this->line('  <fg=green>✓</> No invalid balance sales');
        }
    }
}
