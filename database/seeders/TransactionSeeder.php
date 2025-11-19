<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Transaction;
use App\Models\Student;
use App\Models\Product;
use App\Models\User;
use Carbon\Carbon;

class TransactionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $student = Student::first();
        $kasirUser = User::where('role', 'kasir')->first();
        $products = Product::all();

        if (!$student || !$kasirUser || $products->count() == 0) {
            $this->command->error('Missing required data! Please run UserSeeder and DemoDataSeeder first.');
            return;
        }

        $this->command->info('Creating demo transactions...');

        // Transaction 1: Top-up balance (3 days ago)
        $topupDate = Carbon::now()->subDays(3);
        $student->increment('balance', 100000);
        $student->refresh();

        Transaction::create([
            'student_id' => $student->id,
            'type' => 'topup',
            'amount' => 100000,
            'ending_balance' => $student->balance,
            'description' => 'Top-up saldo via transfer',
            'reference_type' => null,
            'reference_id' => null,
            'created_at' => $topupDate,
            'updated_at' => $topupDate,
        ]);

        $this->command->info('Created top-up transaction');

        // Transaction 2: Purchase (2 days ago)
        $purchaseDate1 = Carbon::now()->subDays(2);
        $sale1 = Sale::create([
            'user_id' => $kasirUser->id,
            'student_id' => $student->id,
            'total' => 14500,
            'payment_method' => 'balance',
            'cash_amount' => null,
            'change_amount' => null,
            'created_at' => $purchaseDate1,
            'updated_at' => $purchaseDate1,
        ]);

        SaleItem::create([
            'sale_id' => $sale1->id,
            'product_id' => $products[0]->id,
            'quantity' => 2,
            'price' => 3500,
            'subtotal' => 7000,
        ]);

        SaleItem::create([
            'sale_id' => $sale1->id,
            'product_id' => $products[1]->id,
            'quantity' => 1,
            'price' => 4500,
            'subtotal' => 4500,
        ]);

        SaleItem::create([
            'sale_id' => $sale1->id,
            'product_id' => $products[2]->id,
            'quantity' => 1,
            'price' => 3000,
            'subtotal' => 3000,
        ]);

        $student->decrement('balance', 14500);
        $student->refresh();

        Transaction::create([
            'student_id' => $student->id,
            'type' => 'debit',
            'amount' => 14500,
            'ending_balance' => $student->balance,
            'description' => 'Pembelian di koperasi - Invoice #' . $sale1->id,
            'reference_type' => 'sale',
            'reference_id' => $sale1->id,
            'created_at' => $purchaseDate1,
            'updated_at' => $purchaseDate1,
        ]);

        $this->command->info('Created purchase transaction 1');

        // Transaction 3: Purchase (yesterday)
        $purchaseDate2 = Carbon::now()->subDay();
        $sale2 = Sale::create([
            'user_id' => $kasirUser->id,
            'student_id' => $student->id,
            'total' => 9500,
            'payment_method' => 'balance',
            'cash_amount' => null,
            'change_amount' => null,
            'created_at' => $purchaseDate2,
            'updated_at' => $purchaseDate2,
        ]);

        SaleItem::create([
            'sale_id' => $sale2->id,
            'product_id' => $products[0]->id,
            'quantity' => 1,
            'price' => 7000,
            'subtotal' => 7000,
        ]);

        SaleItem::create([
            'sale_id' => $sale2->id,
            'product_id' => $products[3]->id,
            'quantity' => 1,
            'price' => 2500,
            'subtotal' => 2500,
        ]);

        $student->decrement('balance', 9500);
        $student->refresh();

        Transaction::create([
            'student_id' => $student->id,
            'type' => 'debit',
            'amount' => 9500,
            'ending_balance' => $student->balance,
            'description' => 'Pembelian di koperasi - Invoice #' . $sale2->id,
            'reference_type' => 'sale',
            'reference_id' => $sale2->id,
            'created_at' => $purchaseDate2,
            'updated_at' => $purchaseDate2,
        ]);

        $this->command->info('Created purchase transaction 2');

        $this->command->info('Demo transactions created successfully!');
        $this->command->info('Student balance: Rp ' . number_format($student->balance, 0, ',', '.'));
        $this->command->info('Total transactions: ' . Transaction::count());
    }
}
