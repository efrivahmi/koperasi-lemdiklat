<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
})->name('welcome');

Route::get('/dashboard', [\App\Http\Controllers\DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::post('/profile/photo', [ProfileController::class, 'uploadPhoto'])->name('profile.photo.upload');
    Route::delete('/profile/photo', [ProfileController::class, 'deletePhoto'])->name('profile.photo.delete');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Admin & Master Routes (User Management - CRUD Kasir, Admin, Siswa)
// Kasir can also access if they have users.view / users.manage permission
Route::middleware(['auth', 'role:admin,master,kasir'])->prefix('admin')->group(function () {
    Route::resource('users', \App\Http\Controllers\UserController::class);
});

// Admin, Master & Kasir Routes (Products, Categories, Stock Management)
Route::middleware(['auth', 'role:admin,master,kasir'])->prefix('admin')->group(function () {
    // Master Data - Accessible by Kasir (with permission)
    Route::resource('categories', \App\Http\Controllers\CategoryController::class);

    // Product specific routes (MUST come before resource routes)
    Route::get('products-barcode/generator', [\App\Http\Controllers\ProductController::class, 'barcodeGenerator'])->name('products.barcode-generator');
    Route::get('products-barcode/print', [\App\Http\Controllers\ProductController::class, 'printBarcodes'])->name('products.print-barcodes');
    Route::get('products/{product}/print-barcode', [\App\Http\Controllers\ProductController::class, 'printBarcode'])->name('products.print-barcode');
    Route::get('api/generate-barcode', [\App\Http\Controllers\ProductController::class, 'generateBarcodeApi'])->name('api.generate-barcode');
    Route::get('api/products/search', [\App\Http\Controllers\ProductController::class, 'apiSearch'])->name('api.products.search');
    Route::post('products/{product}/adjust-stock', [\App\Http\Controllers\StockAdjustmentController::class, 'store'])->name('products.adjust-stock');
    Route::get('products/{product}/adjustment-history', [\App\Http\Controllers\StockAdjustmentController::class, 'history'])->name('products.adjustment-history');
    Route::put('stock-adjustments/{stockAdjustment}', [\App\Http\Controllers\StockAdjustmentController::class, 'update'])->name('stock-adjustments.update');
    Route::delete('stock-adjustments/{stockAdjustment}', [\App\Http\Controllers\StockAdjustmentController::class, 'destroy'])->name('stock-adjustments.destroy');

    Route::resource('products', \App\Http\Controllers\ProductController::class);

    // Inventory & Finance - Accessible by Kasir (with permission)
    Route::resource('stock-ins', \App\Http\Controllers\StockInController::class);
    Route::resource('expenses', \App\Http\Controllers\ExpenseController::class);

    // Vouchers (specific routes MUST come before resource routes)
    Route::get('vouchers/search-student', [\App\Http\Controllers\VoucherController::class, 'searchStudent'])->name('vouchers.search.student');
    Route::get('vouchers/print/{voucher}', [\App\Http\Controllers\VoucherController::class, 'printVoucher'])->name('vouchers.print');
    Route::match(['get', 'post'], 'vouchers/print-batch', [\App\Http\Controllers\VoucherController::class, 'printBatch'])->name('vouchers.print.batch');
    Route::get('vouchers-redeem', [\App\Http\Controllers\VoucherController::class, 'redeemForm'])->name('vouchers.redeem.form');
    Route::post('vouchers-redeem', [\App\Http\Controllers\VoucherController::class, 'redeem'])->name('vouchers.redeem');
    Route::resource('vouchers', \App\Http\Controllers\VoucherController::class);

    // Transactions - View only for Kasir (accessed by Admin)
    Route::get('transactions', [\App\Http\Controllers\TransactionController::class, 'index'])->name('transactions.index');
    Route::get('transactions/student/{student}', [\App\Http\Controllers\TransactionController::class, 'studentHistory'])->name('transactions.student');

    // POS for Admin/Master (Alias to keep URL consistent)
    Route::get('/pos', [\App\Http\Controllers\PosController::class, 'index'])->name('pos.index');
    Route::get('/pos/history', [\App\Http\Controllers\PosController::class, 'transactionsHistory'])->name('pos.transactions-history');
});

// Students, Teachers, Savings - Admin, Master & Kasir (with permission)
// RoleMiddleware checks granular permissions for kasir
Route::middleware(['auth', 'role:admin,master,kasir'])->prefix('admin')->group(function () {
    // Students Management
    Route::resource('students', \App\Http\Controllers\StudentController::class);
    Route::get('students/{student}/rfid-register', [\App\Http\Controllers\StudentController::class, 'rfidRegister'])->name('students.rfid.register');
    Route::post('students/{student}/rfid-store', [\App\Http\Controllers\StudentController::class, 'rfidStore'])->name('students.rfid.store');
    Route::get('api/generate-rfid', [\App\Http\Controllers\StudentController::class, 'generateRfidApi'])->name('api.generate-rfid');
    Route::get('students/{student}/generate-card', [\App\Http\Controllers\StudentCardController::class, 'generate'])->name('students.card.generate');
    Route::post('students/generate-cards-batch', [\App\Http\Controllers\StudentCardController::class, 'generateBatch'])->name('students.cards.batch');
    Route::get('students/{student}/export-rfid', [\App\Http\Controllers\StudentCardController::class, 'exportForRfid'])->name('students.rfid.export');

    // Teachers Management
    Route::resource('teachers', \App\Http\Controllers\TeacherController::class);
    Route::get('teachers/{teacher}/rfid-register', [\App\Http\Controllers\TeacherController::class, 'rfidRegister'])->name('teachers.rfid.register');
    Route::post('teachers/{teacher}/rfid-store', [\App\Http\Controllers\TeacherController::class, 'rfidStore'])->name('teachers.rfid.store');
    Route::get('api/generate-teacher-rfid', [\App\Http\Controllers\TeacherController::class, 'generateRfidApi'])->name('api.generate-teacher-rfid');

    // Savings Management
    Route::get('savings', [\App\Http\Controllers\SavingController::class, 'index'])->name('savings.index');
    Route::get('savings/create', [\App\Http\Controllers\SavingController::class, 'create'])->name('savings.create');
    Route::post('savings', [\App\Http\Controllers\SavingController::class, 'store'])->name('savings.store');
    Route::get('savings/{saverType}/{saverId}', [\App\Http\Controllers\SavingController::class, 'show'])->name('savings.show');
    Route::post('savings/deposit', [\App\Http\Controllers\SavingController::class, 'deposit'])->name('savings.deposit');
    Route::post('savings/withdraw', [\App\Http\Controllers\SavingController::class, 'withdraw'])->name('savings.withdraw');
});

// Reports - Accessible by Admin, Master & Kasir
Route::middleware(['auth', 'role:admin,master,kasir'])->prefix('admin')->group(function () {
    Route::get('reports/sales', [\App\Http\Controllers\ReportController::class, 'sales'])->name('reports.sales');
    Route::get('reports/sales/export', [\App\Http\Controllers\ReportController::class, 'salesExport'])->name('reports.sales.export');
    Route::get('reports/inventory', [\App\Http\Controllers\ReportController::class, 'inventory'])->name('reports.inventory');
    Route::get('reports/inventory/export', [\App\Http\Controllers\ReportController::class, 'inventoryExport'])->name('reports.inventory.export');
    Route::get('reports/financial', [\App\Http\Controllers\ReportController::class, 'financial'])->name('reports.financial');
    Route::get('reports/financial/export', [\App\Http\Controllers\ReportController::class, 'financialExport'])->name('reports.financial.export');
    Route::get('reports/student-transactions', [\App\Http\Controllers\ReportController::class, 'studentTransactions'])->name('reports.student-transactions');
    Route::get('reports/student-transactions/export', [\App\Http\Controllers\ReportController::class, 'studentTransactionsExport'])->name('reports.student-transactions.export');
    Route::get('reports/stock-adjustments', [\App\Http\Controllers\ReportController::class, 'stockAdjustments'])->name('reports.stock-adjustments');
    Route::get('reports/stock-adjustments/export', [\App\Http\Controllers\ReportController::class, 'stockAdjustmentsExport'])->name('reports.stock-adjustments.export');
});

// Kasir Routes (Admin, Master & Kasir)
Route::middleware(['auth', 'role:admin,master,kasir'])->group(function () {
    // POS
    // POS & Staf Koperasi Routes
    Route::prefix('stafkoperasi')->as('kasir.')->group(function () {
        Route::get('/dashboard', function () {
            return Inertia::render('Kasir/Dashboard');
        })->name('dashboard');

        Route::get('/pos', [\App\Http\Controllers\PosController::class, 'index'])->name('pos.index');
        Route::get('/transactions-history', [\App\Http\Controllers\PosController::class, 'transactionsHistory'])->name('pos.transactions-history');
        Route::get('/receipt/{sale}', [\App\Http\Controllers\PosController::class, 'printReceipt'])->name('pos.receipt');

        // Master Data
        Route::resource('categories', \App\Http\Controllers\CategoryController::class);
        
        // Products & Barcodes
        Route::get('products-barcode/generator', [\App\Http\Controllers\ProductController::class, 'barcodeGenerator'])->name('products.barcode-generator');
        Route::get('products-barcode/print', [\App\Http\Controllers\ProductController::class, 'printBarcodes'])->name('products.print-barcodes');
        Route::get('products/{product}/print-barcode', [\App\Http\Controllers\ProductController::class, 'printBarcode'])->name('products.print-barcode');
        Route::get('api/generate-barcode', [\App\Http\Controllers\ProductController::class, 'generateBarcodeApi'])->name('api.generate-barcode');
        Route::post('products/{product}/adjust-stock', [\App\Http\Controllers\StockAdjustmentController::class, 'store'])->name('products.adjust-stock');
        Route::get('products/{product}/adjustment-history', [\App\Http\Controllers\StockAdjustmentController::class, 'history'])->name('products.adjustment-history');
        Route::put('stock-adjustments/{stockAdjustment}', [\App\Http\Controllers\StockAdjustmentController::class, 'update'])->name('stock-adjustments.update');
        Route::delete('stock-adjustments/{stockAdjustment}', [\App\Http\Controllers\StockAdjustmentController::class, 'destroy'])->name('stock-adjustments.destroy');
        Route::resource('products', \App\Http\Controllers\ProductController::class);

        // Inventory
        Route::resource('stock-ins', \App\Http\Controllers\StockInController::class);

        // Vouchers
        Route::get('vouchers/search-student', [\App\Http\Controllers\VoucherController::class, 'searchStudent'])->name('vouchers.search.student');
        Route::get('vouchers/print/{voucher}', [\App\Http\Controllers\VoucherController::class, 'printVoucher'])->name('vouchers.print');
        Route::match(['get', 'post'], 'vouchers/print-batch', [\App\Http\Controllers\VoucherController::class, 'printBatch'])->name('vouchers.print.batch');
        Route::get('vouchers-redeem', [\App\Http\Controllers\VoucherController::class, 'redeemForm'])->name('vouchers.redeem.form');
        Route::post('vouchers-redeem', [\App\Http\Controllers\VoucherController::class, 'redeem'])->name('vouchers.redeem');
        Route::resource('vouchers', \App\Http\Controllers\VoucherController::class);

        // Savings
        Route::get('savings', [\App\Http\Controllers\SavingController::class, 'index'])->name('savings.index');
        Route::get('savings/create', [\App\Http\Controllers\SavingController::class, 'create'])->name('savings.create');
        Route::post('savings', [\App\Http\Controllers\SavingController::class, 'store'])->name('savings.store');
        Route::get('savings/{saverType}/{saverId}', [\App\Http\Controllers\SavingController::class, 'show'])->name('savings.show');
        Route::post('savings/deposit', [\App\Http\Controllers\SavingController::class, 'deposit'])->name('savings.deposit');
        Route::post('savings/withdraw', [\App\Http\Controllers\SavingController::class, 'withdraw'])->name('savings.withdraw');

        // Transactions
        Route::get('transactions', [\App\Http\Controllers\TransactionController::class, 'index'])->name('transactions.index');
        Route::get('transactions/student/{student}', [\App\Http\Controllers\TransactionController::class, 'studentHistory'])->name('transactions.student');
        
        // Vouchers
        Route::resource('vouchers', \App\Http\Controllers\VoucherController::class);
        Route::get('vouchers/redeem/form', [\App\Http\Controllers\VoucherController::class, 'redeemForm'])->name('vouchers.redeem.form');
        Route::post('vouchers/redeem', [\App\Http\Controllers\VoucherController::class, 'redeem'])->name('vouchers.redeem');
        Route::get('vouchers/search-student', [\App\Http\Controllers\VoucherController::class, 'searchStudent'])->name('vouchers.search.student');
        Route::get('vouchers/{voucher}/print', [\App\Http\Controllers\VoucherController::class, 'printVoucher'])->name('vouchers.print');
        Route::match(['get', 'post'], 'vouchers/print/batch', [\App\Http\Controllers\VoucherController::class, 'printBatch'])->name('vouchers.print.batch');

        // Top-up (Duplicated for Kasir)
        Route::get('transactions/topup', [\App\Http\Controllers\TransactionController::class, 'topupForm'])->name('transactions.topup.form');
        Route::post('transactions/topup', [\App\Http\Controllers\TransactionController::class, 'topup'])->name('transactions.topup');

        // Students
        Route::resource('students', \App\Http\Controllers\StudentController::class);
        Route::get('students/{student}/rfid-register', [\App\Http\Controllers\StudentController::class, 'rfidRegister'])->name('students.rfid.register');
        Route::post('students/{student}/rfid-store', [\App\Http\Controllers\StudentController::class, 'rfidStore'])->name('students.rfid.store');
        Route::get('api/generate-rfid', [\App\Http\Controllers\StudentController::class, 'generateRfidApi'])->name('api.generate-rfid');
        Route::get('students/{student}/generate-card', [\App\Http\Controllers\StudentCardController::class, 'generate'])->name('students.card.generate');
        Route::post('students/generate-cards-batch', [\App\Http\Controllers\StudentCardController::class, 'generateBatch'])->name('students.cards.batch');
        Route::get('students/{student}/export-rfid', [\App\Http\Controllers\StudentCardController::class, 'exportForRfid'])->name('students.rfid.export');
        
        // Teachers
        Route::resource('teachers', \App\Http\Controllers\TeacherController::class);
        Route::get('teachers/{teacher}/rfid-register', [\App\Http\Controllers\TeacherController::class, 'rfidRegister'])->name('teachers.rfid.register');
        Route::post('teachers/{teacher}/rfid-store', [\App\Http\Controllers\TeacherController::class, 'rfidStore'])->name('teachers.rfid.store');
        Route::get('api/generate-teacher-rfid', [\App\Http\Controllers\TeacherController::class, 'generateRfidApi'])->name('api.generate-teacher-rfid');

        // API Routes for POS - Rate limited to prevent abuse
        Route::middleware('throttle:60,1')->group(function () {
            Route::get('/api/products', [\App\Http\Controllers\PosController::class, 'getProducts'])->name('pos.api.products');
            Route::get('/api/products/barcode/{barcode}', [\App\Http\Controllers\PosController::class, 'getProductByBarcode'])->name('pos.api.barcode');
            Route::get('/api/students/rfid/{rfid_uid}', [\App\Http\Controllers\PosController::class, 'getStudentByRfid'])->name('pos.api.rfid');
            Route::get('/api/students/search', [\App\Http\Controllers\PosController::class, 'searchStudent'])->name('pos.api.search');
            Route::get('/api/teachers/rfid/{rfid_uid}', [\App\Http\Controllers\PosController::class, 'getTeacherByRfid'])->name('pos.api.teacher-rfid');
            Route::get('/api/teachers/search', [\App\Http\Controllers\PosController::class, 'searchTeacher'])->name('pos.api.teacher-search');
            Route::get('/api/recent-sales', [\App\Http\Controllers\PosController::class, 'getRecentSales'])->name('pos.api.recent-sales');
            Route::get('/api/stock-monitor', [\App\Http\Controllers\ProductController::class, 'stockMonitor'])->name('api.stock-monitor');

            // Stricter rate limiting for write operations
            Route::middleware('throttle:30,1')->group(function () {
            Route::post('/api/checkout', [\App\Http\Controllers\PosController::class, 'checkout'])->name('pos.api.checkout');
                Route::post('/api/void/{sale}', [\App\Http\Controllers\PosController::class, 'voidSale'])->name('pos.api.void');
            });
        });

        // Reports (Kasir Context)
        Route::get('reports/sales', [\App\Http\Controllers\ReportController::class, 'sales'])->name('reports.sales');
        Route::get('reports/sales/export', [\App\Http\Controllers\ReportController::class, 'salesExport'])->name('reports.sales.export');
        Route::get('reports/inventory', [\App\Http\Controllers\ReportController::class, 'inventory'])->name('reports.inventory');
        Route::get('reports/inventory/export', [\App\Http\Controllers\ReportController::class, 'inventoryExport'])->name('reports.inventory.export');
        Route::get('reports/stock-adjustments', [\App\Http\Controllers\ReportController::class, 'stockAdjustments'])->name('reports.stock-adjustments');
        Route::get('reports/stock-adjustments/export', [\App\Http\Controllers\ReportController::class, 'stockAdjustmentsExport'])->name('reports.stock-adjustments.export');
        Route::get('reports/financial', [\App\Http\Controllers\ReportController::class, 'financial'])->name('reports.financial');
        Route::get('reports/financial/export', [\App\Http\Controllers\ReportController::class, 'financialExport'])->name('reports.financial.export');
        Route::get('reports/student-transactions', [\App\Http\Controllers\ReportController::class, 'studentTransactions'])->name('reports.student-transactions');
        Route::get('reports/student-transactions/export', [\App\Http\Controllers\ReportController::class, 'studentTransactionsExport'])->name('reports.student-transactions.export');
    });

    // Top-up Saldo (KASIR BISA AKSES)
    Route::get('transactions/topup', [\App\Http\Controllers\TransactionController::class, 'topupForm'])->name('transactions.topup.form');
    Route::post('transactions/topup', [\App\Http\Controllers\TransactionController::class, 'topup'])->name('transactions.topup');
    Route::get('transactions/search-student', [\App\Http\Controllers\TransactionController::class, 'searchStudent'])->name('transactions.search.student');
    Route::get('transactions/search-teacher', [\App\Http\Controllers\TransactionController::class, 'searchTeacher'])->name('transactions.search.teacher');
});

// Student Portal Routes
Route::middleware(['auth', 'role:siswa'])->prefix('student')->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\StudentPortalController::class, 'dashboard'])->name('student.dashboard');
    Route::get('/transactions', [\App\Http\Controllers\StudentPortalController::class, 'transactions'])->name('student.transactions');
    Route::get('/purchase-history', [\App\Http\Controllers\StudentPortalController::class, 'purchaseHistory'])->name('student.purchases');
    Route::get('/savings', [\App\Http\Controllers\StudentPortalController::class, 'savings'])->name('student.savings');
});

// Teacher Portal Routes
Route::middleware(['auth', 'role:guru'])->prefix('teacher')->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\TeacherPortalController::class, 'dashboard'])->name('teacher.dashboard');
    Route::get('/transactions', [\App\Http\Controllers\TeacherPortalController::class, 'transactions'])->name('teacher.transactions');
    Route::get('/purchase-history', [\App\Http\Controllers\TeacherPortalController::class, 'purchaseHistory'])->name('teacher.purchases');
    Route::get('/savings', [\App\Http\Controllers\TeacherPortalController::class, 'savings'])->name('teacher.savings');
});

require __DIR__.'/auth.php';
