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
Route::middleware(['auth', 'role:admin,master'])->prefix('admin')->group(function () {
    Route::resource('users', \App\Http\Controllers\UserController::class);
});

// Admin, Master & Kasir Routes (Products, Categories, Stock Management)
Route::middleware(['auth', 'role:admin,master,kasir'])->prefix('admin')->group(function () {
    // Master Data - Accessible by Kasir
    Route::resource('categories', \App\Http\Controllers\CategoryController::class);

    // Product specific routes (MUST come before resource routes)
    Route::get('products-barcode/generator', [\App\Http\Controllers\ProductController::class, 'barcodeGenerator'])->name('products.barcode-generator');
    Route::get('products-barcode/print', [\App\Http\Controllers\ProductController::class, 'printBarcodes'])->name('products.print-barcodes');
    Route::get('products/{product}/print-barcode', [\App\Http\Controllers\ProductController::class, 'printBarcode'])->name('products.print-barcode');
    Route::get('api/generate-barcode', [\App\Http\Controllers\ProductController::class, 'generateBarcodeApi'])->name('api.generate-barcode');
    Route::post('products/{product}/adjust-stock', [\App\Http\Controllers\StockAdjustmentController::class, 'store'])->name('products.adjust-stock');
    Route::get('products/{product}/adjustment-history', [\App\Http\Controllers\StockAdjustmentController::class, 'history'])->name('products.adjustment-history');

    Route::resource('products', \App\Http\Controllers\ProductController::class);

    // Inventory & Finance - Accessible by Kasir (with permission)
    Route::resource('stock-ins', \App\Http\Controllers\StockInController::class);
    Route::resource('expenses', \App\Http\Controllers\ExpenseController::class);

    // Vouchers (specific routes MUST come before resource routes)
    Route::get('vouchers/search-student', [\App\Http\Controllers\VoucherController::class, 'searchStudent'])->name('vouchers.search.student');
    Route::get('vouchers/print/{voucher}', [\App\Http\Controllers\VoucherController::class, 'printVoucher'])->name('vouchers.print');
    Route::post('vouchers/print-batch', [\App\Http\Controllers\VoucherController::class, 'printBatch'])->name('vouchers.print.batch');
    Route::get('vouchers-redeem', [\App\Http\Controllers\VoucherController::class, 'redeemForm'])->name('vouchers.redeem.form');
    Route::post('vouchers-redeem', [\App\Http\Controllers\VoucherController::class, 'redeem'])->name('vouchers.redeem');
    Route::resource('vouchers', \App\Http\Controllers\VoucherController::class);

    // Transactions - View only for Kasir
    Route::get('transactions', [\App\Http\Controllers\TransactionController::class, 'index'])->name('transactions.index');
    Route::get('transactions/student/{student}', [\App\Http\Controllers\TransactionController::class, 'studentHistory'])->name('transactions.student');
});

// Admin & Master Only Routes
Route::middleware(['auth', 'role:admin,master'])->prefix('admin')->group(function () {
    // Students Management - Only for Admin & Master
    Route::resource('students', \App\Http\Controllers\StudentController::class);
    Route::get('students/{student}/rfid-register', [\App\Http\Controllers\StudentController::class, 'rfidRegister'])->name('students.rfid.register');
    Route::post('students/{student}/rfid-store', [\App\Http\Controllers\StudentController::class, 'rfidStore'])->name('students.rfid.store');
    Route::get('api/generate-rfid', [\App\Http\Controllers\StudentController::class, 'generateRfidApi'])->name('api.generate-rfid');
    Route::get('students/{student}/generate-card', [\App\Http\Controllers\StudentCardController::class, 'generate'])->name('students.card.generate');
    Route::post('students/generate-cards-batch', [\App\Http\Controllers\StudentCardController::class, 'generateBatch'])->name('students.cards.batch');
    Route::get('students/{student}/export-rfid', [\App\Http\Controllers\StudentCardController::class, 'exportForRfid'])->name('students.rfid.export');
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
    Route::prefix('kasir')->group(function () {
        Route::get('/pos', [\App\Http\Controllers\PosController::class, 'index'])->name('pos.index');
        Route::get('/transactions-history', [\App\Http\Controllers\PosController::class, 'transactionsHistory'])->name('pos.transactions-history');

        // API Routes for POS
        Route::get('/api/products', [\App\Http\Controllers\PosController::class, 'getProducts'])->name('pos.api.products');
        Route::get('/api/products/barcode/{barcode}', [\App\Http\Controllers\PosController::class, 'getProductByBarcode'])->name('pos.api.barcode');
        Route::get('/api/students/rfid/{rfid_uid}', [\App\Http\Controllers\PosController::class, 'getStudentByRfid'])->name('pos.api.rfid');
        Route::get('/api/students/search', [\App\Http\Controllers\PosController::class, 'searchStudent'])->name('pos.api.search');
        Route::post('/api/checkout', [\App\Http\Controllers\PosController::class, 'checkout'])->name('pos.api.checkout');
        Route::get('/api/recent-sales', [\App\Http\Controllers\PosController::class, 'getRecentSales'])->name('pos.api.recent-sales');
        Route::post('/api/void/{sale}', [\App\Http\Controllers\PosController::class, 'voidSale'])->name('pos.api.void');
        Route::get('/receipt/{sale}', [\App\Http\Controllers\PosController::class, 'printReceipt'])->name('pos.receipt');

        // Stock Monitor API
        Route::get('/api/stock-monitor', [\App\Http\Controllers\ProductController::class, 'stockMonitor'])->name('api.stock-monitor');
    });

    // Top-up Saldo (KASIR BISA AKSES)
    Route::get('transactions/topup', [\App\Http\Controllers\TransactionController::class, 'topupForm'])->name('transactions.topup.form');
    Route::post('transactions/topup', [\App\Http\Controllers\TransactionController::class, 'topup'])->name('transactions.topup');
    Route::get('transactions/search-student', [\App\Http\Controllers\TransactionController::class, 'searchStudent'])->name('transactions.search.student');
});

// Student Portal Routes
Route::middleware(['auth', 'role:siswa'])->prefix('student')->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\StudentPortalController::class, 'dashboard'])->name('student.dashboard');
    Route::get('/transactions', [\App\Http\Controllers\StudentPortalController::class, 'transactions'])->name('student.transactions');
    Route::get('/purchase-history', [\App\Http\Controllers\StudentPortalController::class, 'purchaseHistory'])->name('student.purchases');
});

require __DIR__.'/auth.php';
