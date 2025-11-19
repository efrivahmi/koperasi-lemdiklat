<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Category;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Transaction;
use App\Models\Student;
use App\Models\User;
use Carbon\Carbon;

class DemoDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get the student user (created by UserSeeder)
        $siswaUser = User::where('role', 'siswa')->first();
        $kasirUser = User::where('role', 'kasir')->first();

        if (!$siswaUser || !$kasirUser) {
            $this->command->error('Please run UserSeeder first!');
            return;
        }

        $student = Student::where('user_id', $siswaUser->id)->first();

        if (!$student) {
            $this->command->error('No student found!');
            return;
        }

        // Create categories
        $snackCategory = Category::create(['name' => 'Makanan Ringan', 'description' => 'Snack dan cemilan']);
        $drinkCategory = Category::create(['name' => 'Minuman', 'description' => 'Minuman dingin dan panas']);
        $stationeryCategory = Category::create(['name' => 'Alat Tulis', 'description' => 'Perlengkapan sekolah']);

        // Create products
        $products = [];

        // Snacks
        $products[] = Product::create([
            'category_id' => $snackCategory->id,
            'name' => 'Indomie Goreng',
            'description' => 'Mie instan rasa goreng',
            'barcode' => '8992388101015',
            'stock' => 50,
            'harga_beli' => 2500,
            'harga_jual' => 3500,
            'image_path' => null,
        ]);

        $products[] = Product::create([
            'category_id' => $snackCategory->id,
            'name' => 'Chitato',
            'description' => 'Keripik kentang',
            'barcode' => '8992388202022',
            'stock' => 30,
            'harga_beli' => 5000,
            'harga_jual' => 7000,
            'image_path' => null,
        ]);

        // Drinks
        $products[] = Product::create([
            'category_id' => $drinkCategory->id,
            'name' => 'Teh Pucuk',
            'description' => 'Teh botol rasa melati',
            'barcode' => '8992388303033',
            'stock' => 40,
            'harga_beli' => 3000,
            'harga_jual' => 4500,
            'image_path' => null,
        ]);

        $products[] = Product::create([
            'category_id' => $drinkCategory->id,
            'name' => 'Aqua 600ml',
            'description' => 'Air mineral',
            'barcode' => '8992388404044',
            'stock' => 60,
            'harga_beli' => 2000,
            'harga_jual' => 3000,
            'image_path' => null,
        ]);

        // Stationery
        $products[] = Product::create([
            'category_id' => $stationeryCategory->id,
            'name' => 'Pulpen Standard AE7',
            'description' => 'Pulpen tinta hitam',
            'barcode' => '8992388505055',
            'stock' => 25,
            'harga_beli' => 1500,
            'harga_jual' => 2500,
            'image_path' => null,
        ]);

        $products[] = Product::create([
            'category_id' => $stationeryCategory->id,
            'name' => 'Buku Tulis 38 Lembar',
            'description' => 'Buku tulis kotak-kotak',
            'barcode' => '8992388606066',
            'stock' => 20,
            'harga_beli' => 3000,
            'harga_jual' => 5000,
            'image_path' => null,
        ]);

        // Create some demo transactions for the student
        $this->command->info('Creating demo transactions...');

        // Transaction 1: Top-up balance (3 days ago)
        $topupDate = Carbon::now()->subDays(3);
        $student->update(['balance' => $student->balance + 100000]);

        Transaction::create([
            'student_id' => $student->id,
            'type' => 'topup',
            'amount' => 100000,
            'ending_balance' => $student->balance,
            'description' => 'Top-up saldo via transfer',
            'reference_type' => null,
            'reference_id' => null,
            'created_at' => $topupDate,
        ]);

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
        ]);

        SaleItem::create([
            'sale_id' => $sale1->id,
            'product_id' => $products[0]->id, // Indomie
            'quantity' => 2,
            'price' => 3500,
            'subtotal' => 7000,
        ]);

        SaleItem::create([
            'sale_id' => $sale1->id,
            'product_id' => $products[2]->id, // Teh Pucuk
            'quantity' => 1,
            'price' => 4500,
            'subtotal' => 4500,
        ]);

        SaleItem::create([
            'sale_id' => $sale1->id,
            'product_id' => $products[3]->id, // Aqua
            'quantity' => 1,
            'price' => 3000,
            'subtotal' => 3000,
        ]);

        // Update student balance
        $student->decrement('balance', 14500);
        $student->refresh();

        // Update product stocks
        $products[0]->decrement('stock', 2);
        $products[2]->decrement('stock', 1);
        $products[3]->decrement('stock', 1);

        Transaction::create([
            'student_id' => $student->id,
            'type' => 'debit',
            'amount' => 14500,
            'ending_balance' => $student->balance,
            'description' => 'Pembelian di koperasi - Invoice #' . $sale1->id,
            'reference_type' => 'sale',
            'reference_id' => $sale1->id,
            'created_at' => $purchaseDate1,
        ]);

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
        ]);

        SaleItem::create([
            'sale_id' => $sale2->id,
            'product_id' => $products[1]->id, // Chitato
            'quantity' => 1,
            'price' => 7000,
            'subtotal' => 7000,
        ]);

        SaleItem::create([
            'sale_id' => $sale2->id,
            'product_id' => $products[4]->id, // Pulpen
            'quantity' => 1,
            'price' => 2500,
            'subtotal' => 2500,
        ]);

        // Update student balance
        $student->decrement('balance', 9500);
        $student->refresh();

        // Update product stocks
        $products[1]->decrement('stock', 1);
        $products[4]->decrement('stock', 1);

        Transaction::create([
            'student_id' => $student->id,
            'type' => 'debit',
            'amount' => 9500,
            'ending_balance' => $student->balance,
            'description' => 'Pembelian di koperasi - Invoice #' . $sale2->id,
            'reference_type' => 'sale',
            'reference_id' => $sale2->id,
            'created_at' => $purchaseDate2,
        ]);

        $this->command->info('Demo data created successfully!');
        $this->command->info('Student balance: Rp ' . number_format($student->balance, 0, ',', '.'));
        $this->command->info('Total transactions: 3 (1 top-up, 2 purchases)');
    }
}
