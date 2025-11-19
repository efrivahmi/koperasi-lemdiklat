<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Student;
use App\Models\Category;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Transaction;
use App\Models\Voucher;
use App\Models\Expense;
use App\Models\StockIn;
use Carbon\Carbon;
use Illuminate\Support\Str;

class ComprehensiveDemoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🌱 Starting comprehensive demo data seeding...');

        // Get existing users
        $adminUser = User::where('role', 'admin')->first();
        $kasirUser = User::where('role', 'kasir')->first();

        // ============================================
        // 1. CREATE MORE STUDENTS (10 students total)
        // ============================================
        $this->command->info('Creating students...');

        $studentData = [
            ['name' => 'Siti Nurhaliza', 'nis' => '2024002', 'kelas' => 'XII IPA 1', 'balance' => 150000],
            ['name' => 'Ahmad Fauzi', 'nis' => '2024003', 'kelas' => 'XII IPA 2', 'balance' => 200000],
            ['name' => 'Dewi Kartika', 'nis' => '2024004', 'kelas' => 'XII IPS 1', 'balance' => 175000],
            ['name' => 'Rizky Pratama', 'nis' => '2024005', 'kelas' => 'XI IPA 1', 'balance' => 120000],
            ['name' => 'Intan Permata', 'nis' => '2024006', 'kelas' => 'XI IPA 2', 'balance' => 180000],
            ['name' => 'Farhan Maulana', 'nis' => '2024007', 'kelas' => 'XI IPS 1', 'balance' => 90000],
            ['name' => 'Nurul Aini', 'nis' => '2024008', 'kelas' => 'X IPA 1', 'balance' => 100000],
            ['name' => 'Andi Setiawan', 'nis' => '2024009', 'kelas' => 'X IPA 2', 'balance' => 130000],
            ['name' => 'Maya Anggraini', 'nis' => '2024010', 'kelas' => 'X IPS 1', 'balance' => 160000],
        ];

        $students = [Student::first()]; // Keep Budi Santoso as first student

        foreach ($studentData as $data) {
            $email = strtolower(str_replace(' ', '.', $data['name'])) . '@student.com';

            // Skip if user already exists
            if (User::where('email', $email)->exists()) {
                $user = User::where('email', $email)->first();
                $student = Student::where('user_id', $user->id)->first();
                if ($student) {
                    $students[] = $student;
                    continue;
                }
            }

            $user = User::create([
                'name' => $data['name'],
                'email' => $email,
                'password' => bcrypt('password'),
                'role' => 'siswa',
            ]);

            $student = Student::create([
                'user_id' => $user->id,
                'nis' => $data['nis'],
                'name' => $data['name'],
                'kelas' => $data['kelas'],
                'balance' => $data['balance'],
            ]);

            $students[] = $student;
        }

        $this->command->info('✓ Created ' . count($students) . ' students');

        // ============================================
        // 2. CREATE MORE CATEGORIES (6 categories)
        // ============================================
        $this->command->info('Creating additional categories...');

        $existingCategories = Category::all();

        $newCategories = [
            ['name' => 'Perlengkapan Seragam', 'description' => 'Atribut dan perlengkapan seragam'],
            ['name' => 'Buku Pelajaran', 'description' => 'Buku mata pelajaran dan referensi'],
            ['name' => 'Elektronik', 'description' => 'Alat elektronik dan aksesori'],
        ];

        foreach ($newCategories as $cat) {
            if (!Category::where('name', $cat['name'])->exists()) {
                $existingCategories->push(Category::create($cat));
            }
        }

        $this->command->info('✓ Total categories: ' . Category::count());

        // ============================================
        // 3. CREATE MORE PRODUCTS (30 products total)
        // ============================================
        $this->command->info('Creating additional products...');

        $allCategories = Category::all();
        $snackCategory = $allCategories->where('name', 'Makanan Ringan')->first();
        $drinkCategory = $allCategories->where('name', 'Minuman')->first();
        $stationeryCategory = $allCategories->where('name', 'Alat Tulis')->first();
        $uniformCategory = $allCategories->where('name', 'Perlengkapan Seragam')->first();
        $bookCategory = $allCategories->where('name', 'Buku Pelajaran')->first();
        $electronicCategory = $allCategories->where('name', 'Elektronik')->first();

        $newProducts = [
            // More snacks
            ['category' => $snackCategory, 'name' => 'Oreo', 'barcode' => '8992388707077', 'stock' => 40, 'harga_beli' => 4000, 'harga_jual' => 6000],
            ['category' => $snackCategory, 'name' => 'Qtela Singkong', 'barcode' => '8992388808088', 'stock' => 35, 'harga_beli' => 3500, 'harga_jual' => 5500],
            ['category' => $snackCategory, 'name' => 'Taro Net', 'barcode' => '8992388909099', 'stock' => 30, 'harga_beli' => 3000, 'harga_jual' => 4500],
            ['category' => $snackCategory, 'name' => 'Biskuat', 'barcode' => '8992389010100', 'stock' => 45, 'harga_beli' => 2500, 'harga_jual' => 4000],

            // More drinks
            ['category' => $drinkCategory, 'name' => 'Coca Cola 250ml', 'barcode' => '8992389111111', 'stock' => 50, 'harga_beli' => 3500, 'harga_jual' => 5000],
            ['category' => $drinkCategory, 'name' => 'Fanta 250ml', 'barcode' => '8992389212222', 'stock' => 45, 'harga_beli' => 3500, 'harga_jual' => 5000],
            ['category' => $drinkCategory, 'name' => 'Pocari Sweat', 'barcode' => '8992389313333', 'stock' => 40, 'harga_beli' => 4000, 'harga_jual' => 6000],
            ['category' => $drinkCategory, 'name' => 'Mizone', 'barcode' => '8992389414444', 'stock' => 35, 'harga_beli' => 4000, 'harga_jual' => 6000],
            ['category' => $drinkCategory, 'name' => 'Good Day Cappuccino', 'barcode' => '8992389515555', 'stock' => 30, 'harga_beli' => 3000, 'harga_jual' => 4500],

            // Stationery
            ['category' => $stationeryCategory, 'name' => 'Pensil 2B Faber Castell', 'barcode' => '8992389616666', 'stock' => 50, 'harga_beli' => 2000, 'harga_jual' => 3500],
            ['category' => $stationeryCategory, 'name' => 'Penghapus Steadtler', 'barcode' => '8992389717777', 'stock' => 40, 'harga_beli' => 1500, 'harga_jual' => 2500],
            ['category' => $stationeryCategory, 'name' => 'Penggaris 30cm', 'barcode' => '8992389818888', 'stock' => 30, 'harga_beli' => 2500, 'harga_jual' => 4000],
            ['category' => $stationeryCategory, 'name' => 'Spidol Snowman', 'barcode' => '8992389919999', 'stock' => 35, 'harga_beli' => 3000, 'harga_jual' => 5000],
            ['category' => $stationeryCategory, 'name' => 'Tip-Ex', 'barcode' => '8992390020000', 'stock' => 25, 'harga_beli' => 3500, 'harga_jual' => 5500],

            // Uniform supplies
            ['category' => $uniformCategory, 'name' => 'Dasi Sekolah', 'barcode' => '8992390121111', 'stock' => 20, 'harga_beli' => 15000, 'harga_jual' => 25000],
            ['category' => $uniformCategory, 'name' => 'Topi Sekolah', 'barcode' => '8992390222222', 'stock' => 15, 'harga_beli' => 20000, 'harga_jual' => 35000],
            ['category' => $uniformCategory, 'name' => 'Kaos Kaki Putih', 'barcode' => '8992390323333', 'stock' => 30, 'harga_beli' => 5000, 'harga_jual' => 10000],
            ['category' => $uniformCategory, 'name' => 'Ikat Pinggang', 'barcode' => '8992390424444', 'stock' => 18, 'harga_beli' => 25000, 'harga_jual' => 40000],

            // Books
            ['category' => $bookCategory, 'name' => 'Buku Matematika XII', 'barcode' => '8992390525555', 'stock' => 10, 'harga_beli' => 50000, 'harga_jual' => 75000],
            ['category' => $bookCategory, 'name' => 'Buku Fisika XII', 'barcode' => '8992390626666', 'stock' => 10, 'harga_beli' => 50000, 'harga_jual' => 75000],
            ['category' => $bookCategory, 'name' => 'Buku Kimia XII', 'barcode' => '8992390727777', 'stock' => 10, 'harga_beli' => 50000, 'harga_jual' => 75000],
            ['category' => $bookCategory, 'name' => 'Buku Biologi XII', 'barcode' => '8992390828888', 'stock' => 10, 'harga_beli' => 50000, 'harga_jual' => 75000],

            // Electronics
            ['category' => $electronicCategory, 'name' => 'Kalkulator Scientific', 'barcode' => '8992390929999', 'stock' => 8, 'harga_beli' => 75000, 'harga_jual' => 120000],
            ['category' => $electronicCategory, 'name' => 'Flash Disk 16GB', 'barcode' => '8992391030000', 'stock' => 12, 'harga_beli' => 50000, 'harga_jual' => 80000],
        ];

        foreach ($newProducts as $prod) {
            if ($prod['category'] && !Product::where('barcode', $prod['barcode'])->exists()) {
                Product::create([
                    'category_id' => $prod['category']->id,
                    'name' => $prod['name'],
                    'description' => $prod['name'],
                    'barcode' => $prod['barcode'],
                    'stock' => $prod['stock'],
                    'harga_beli' => $prod['harga_beli'],
                    'harga_jual' => $prod['harga_jual'],
                    'image_path' => null,
                ]);
            }
        }

        $this->command->info('✓ Total products: ' . Product::count());

        // ============================================
        // 4. CREATE VOUCHERS (10 vouchers)
        // ============================================
        $this->command->info('Creating vouchers...');

        for ($i = 1; $i <= 10; $i++) {
            $status = $i <= 3 ? 'used' : 'available';
            $voucher = Voucher::create([
                'code' => 'VCR-' . strtoupper(Str::random(8)),
                'nominal' => [10000, 20000, 50000][rand(0, 2)],
                'status' => $status,
                'expired_at' => Carbon::now()->addDays(30),
                'created_at' => Carbon::now()->subDays(rand(1, 10)),
            ]);

            // Mark some as used
            if ($status === 'used') {
                $randomStudent = $students[rand(0, count($students) - 1)];
                $voucher->update([
                    'student_id' => $randomStudent->id,
                    'used_by' => $adminUser->id,
                    'used_at' => Carbon::now()->subDays(rand(1, 5)),
                ]);
            }
        }

        $this->command->info('✓ Created 10 vouchers');

        // ============================================
        // 5. CREATE EXPENSES (15 expenses)
        // ============================================
        $this->command->info('Creating expenses...');

        $expenseCategories = ['Listrik', 'Air', 'Internet', 'Gaji', 'Pembelian Stok', 'Perbaikan', 'Transportasi', 'Lain-lain'];

        for ($i = 1; $i <= 15; $i++) {
            Expense::create([
                'category' => $expenseCategories[rand(0, count($expenseCategories) - 1)],
                'description' => 'Pengeluaran ' . $expenseCategories[rand(0, count($expenseCategories) - 1)] . ' bulan ' . Carbon::now()->subDays(rand(1, 30))->format('F'),
                'amount' => rand(50000, 500000),
                'expense_date' => Carbon::now()->subDays(rand(1, 30)),
                'user_id' => $adminUser->id,
                'created_at' => Carbon::now()->subDays(rand(1, 30)),
            ]);
        }

        $this->command->info('✓ Created 15 expenses');

        // ============================================
        // 6. CREATE STOCK INS (20 stock ins)
        // ============================================
        $this->command->info('Creating stock ins...');

        $allProducts = Product::all();

        for ($i = 1; $i <= 20; $i++) {
            $product = $allProducts->random();
            $quantity = rand(10, 50);

            $stockInDate = Carbon::now()->subDays(rand(1, 60));

            StockIn::create([
                'product_id' => $product->id,
                'quantity' => $quantity,
                'harga_beli' => $product->harga_beli,
                'supplier' => ['PT Sumber Makmur', 'CV Berkah Jaya', 'UD Maju Sejahtera', 'Toko Grosir Sentosa'][rand(0, 3)],
                'user_id' => $adminUser->id,
                'created_at' => $stockInDate,
                'updated_at' => $stockInDate,
            ]);

            // Update product stock
            $product->increment('stock', $quantity);
        }

        $this->command->info('✓ Created 20 stock ins');

        // ============================================
        // 7. CREATE MORE SALES & TRANSACTIONS (30 sales)
        // ============================================
        $this->command->info('Creating sales and transactions...');

        for ($i = 1; $i <= 30; $i++) {
            $student = $students[rand(0, count($students) - 1)];
            $paymentMethod = rand(0, 1) === 0 ? 'cash' : 'balance';
            $saleDate = Carbon::now()->subDays(rand(1, 30));

            // Create sale
            $itemCount = rand(1, 4);
            $total = 0;
            $saleItems = [];

            for ($j = 0; $j < $itemCount; $j++) {
                $product = $allProducts->random();
                $quantity = rand(1, 3);
                $subtotal = $product->harga_jual * $quantity;
                $total += $subtotal;

                $saleItems[] = [
                    'product' => $product,
                    'quantity' => $quantity,
                    'subtotal' => $subtotal,
                ];
            }

            $sale = Sale::create([
                'user_id' => $kasirUser->id,
                'student_id' => $paymentMethod === 'balance' ? $student->id : null,
                'total' => $total,
                'payment_method' => $paymentMethod,
                'cash_amount' => $paymentMethod === 'cash' ? $total + rand(0, 50000) : null,
                'change_amount' => $paymentMethod === 'cash' ? rand(0, 50000) : null,
                'created_at' => $saleDate,
                'updated_at' => $saleDate,
            ]);

            // Create sale items
            foreach ($saleItems as $item) {
                SaleItem::create([
                    'sale_id' => $sale->id,
                    'product_id' => $item['product']->id,
                    'quantity' => $item['quantity'],
                    'price' => $item['product']->harga_jual,
                    'subtotal' => $item['subtotal'],
                ]);

                // Decrease stock
                $item['product']->decrement('stock', $item['quantity']);
            }

            // Create transaction for balance payment
            if ($paymentMethod === 'balance') {
                $student->decrement('balance', $total);
                $student->refresh();

                Transaction::create([
                    'student_id' => $student->id,
                    'type' => 'debit',
                    'amount' => $total,
                    'ending_balance' => $student->balance,
                    'description' => 'Pembelian di koperasi - Invoice #' . $sale->id,
                    'reference_type' => 'sale',
                    'reference_id' => $sale->id,
                    'created_at' => $saleDate,
                    'updated_at' => $saleDate,
                ]);
            }
        }

        $this->command->info('✓ Created 30 sales with transactions');

        // ============================================
        // 8. CREATE TOP-UP TRANSACTIONS (20 top-ups)
        // ============================================
        $this->command->info('Creating top-up transactions...');

        for ($i = 1; $i <= 20; $i++) {
            $student = $students[rand(0, count($students) - 1)];
            $amount = [50000, 100000, 150000, 200000][rand(0, 3)];
            $topupDate = Carbon::now()->subDays(rand(1, 30));

            $student->increment('balance', $amount);
            $student->refresh();

            Transaction::create([
                'student_id' => $student->id,
                'type' => 'topup',
                'amount' => $amount,
                'ending_balance' => $student->balance,
                'description' => 'Top-up saldo via transfer',
                'reference_type' => null,
                'reference_id' => null,
                'created_at' => $topupDate,
                'updated_at' => $topupDate,
            ]);
        }

        $this->command->info('✓ Created 20 top-up transactions');

        // ============================================
        // SUMMARY
        // ============================================
        $this->command->newLine();
        $this->command->info('========================================');
        $this->command->info('✅ COMPREHENSIVE DEMO DATA CREATED!');
        $this->command->info('========================================');
        $this->command->info('Students: ' . Student::count());
        $this->command->info('Categories: ' . Category::count());
        $this->command->info('Products: ' . Product::count());
        $this->command->info('Vouchers: ' . Voucher::count());
        $this->command->info('Expenses: ' . Expense::count());
        $this->command->info('Stock Ins: ' . StockIn::count());
        $this->command->info('Sales: ' . Sale::count());
        $this->command->info('Transactions: ' . Transaction::count());
        $this->command->info('========================================');
    }
}
