<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Student;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Transaction;
use App\Models\Category;
use App\Services\ReceiptPrinter;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class PosController extends Controller
{
    /**
     * Display the POS interface
     */
    public function index()
    {
        $categories = Category::all();

        return Inertia::render('Kasir/Pos/Index', [
            'categories' => $categories,
        ]);
    }

    /**
     * Get products for POS (with search and filter)
     */
    public function getProducts(Request $request)
    {
        $query = Product::with('category')
            ->where('stock', '>', 0); // Only show products with stock

        // Search by name
        if ($request->has('search') && $request->search) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        // Filter by category
        if ($request->has('category_id') && $request->category_id) {
            $query->where('category_id', $request->category_id);
        }

        $products = $query->get();

        return response()->json([
            'products' => $products
        ]);
    }

    /**
     * Get product by barcode
     */
    public function getProductByBarcode($barcode)
    {
        $product = Product::with('category')
            ->where('barcode', $barcode)
            ->where('stock', '>', 0)
            ->first();

        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Produk tidak ditemukan atau stok habis'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'product' => $product
        ]);
    }

    /**
     * Get student by RFID
     */
    public function getStudentByRfid($rfid_uid)
    {
        $student = Student::with('user')
            ->where('rfid_uid', $rfid_uid)
            ->first();

        if (!$student) {
            return response()->json([
                'success' => false,
                'message' => 'Kartu RFID tidak terdaftar'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'student' => $student
        ]);
    }

    /**
     * Search student by NIS or name
     */
    public function searchStudent(Request $request)
    {
        $query = Student::with('user');

        if ($request->has('q') && $request->q) {
            $search = $request->q;
            $query->where('nis', 'like', '%' . $search . '%')
                  ->orWhereHas('user', function($q) use ($search) {
                      $q->where('name', 'like', '%' . $search . '%');
                  });
        }

        $students = $query->limit(10)->get();

        return response()->json([
            'students' => $students
        ]);
    }

    /**
     * Process checkout
     */
    public function checkout(Request $request)
    {
        \Log::info('Checkout request received', [
            'payment_method' => $request->payment_method,
            'student_id' => $request->student_id,
            'cash_amount' => $request->cash_amount,
            'cash_amount_type' => gettype($request->cash_amount),
            'items_count' => count($request->items ?? []),
            'user_id' => auth()->id()
        ]);

        $validated = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric|min:0',
            'payment_method' => 'required|in:cash,balance',
            'student_id' => 'nullable|required_if:payment_method,balance|exists:students,id',
            'cash_amount' => 'nullable|required_if:payment_method,cash|numeric|min:0',
            'used_rfid' => 'nullable|boolean',
            'used_barcode' => 'nullable|boolean',
        ]);

        \Log::info('Checkout validation passed', [
            'validated_payment_method' => $validated['payment_method'],
            'validated_student_id' => $validated['student_id'] ?? 'null',
            'validated_cash_amount' => $validated['cash_amount'] ?? 'null',
        ]);

        try {
            return DB::transaction(function () use ($validated) {
                // Calculate total
                $total = 0;
                foreach ($validated['items'] as $item) {
                    $total += $item['price'] * $item['quantity'];
                }

                // Validate total is positive
                if ($total <= 0) {
                    throw new \Exception("Total transaksi tidak valid");
                }

                // Validate stock with row locking to prevent race conditions
                $productIds = array_column($validated['items'], 'product_id');
                $products = Product::whereIn('id', $productIds)
                    ->lockForUpdate()
                    ->get()
                    ->keyBy('id');

                foreach ($validated['items'] as $item) {
                    $product = $products->get($item['product_id']);

                    if (!$product) {
                        throw new \Exception("Produk dengan ID {$item['product_id']} tidak ditemukan");
                    }

                    if ($product->stock < $item['quantity']) {
                        throw new \Exception("Stok {$product->name} tidak mencukupi. Tersedia: {$product->stock}, Diminta: {$item['quantity']}");
                    }

                    // Validate price matches current selling price (prevent price manipulation)
                    if (abs($item['price'] - $product->harga_jual) > 0.01) {
                        throw new \Exception("Harga produk {$product->name} tidak sesuai");
                    }
                }

                // Validate student balance if payment method is balance
                $student = null;
                if ($validated['payment_method'] === 'balance') {
                    $student = Student::where('id', $validated['student_id'])
                        ->lockForUpdate()
                        ->first();

                    if (!$student) {
                        throw new \Exception("Data siswa tidak ditemukan");
                    }

                    if ($student->balance < $total) {
                        throw new \Exception("Saldo tidak mencukupi. Saldo saat ini: Rp " . number_format($student->balance, 0, ',', '.') . ", Total belanja: Rp " . number_format($total, 0, ',', '.'));
                    }
                }

                // Validate cash amount for cash payment
                if ($validated['payment_method'] === 'cash') {
                    if ($validated['cash_amount'] < $total) {
                        throw new \Exception("Jumlah uang tunai tidak mencukupi. Total: Rp " . number_format($total, 0, ',', '.') . ", Dibayar: Rp " . number_format($validated['cash_amount'], 0, ',', '.'));
                    }
                }

                // Determine transaction method
                $transactionMethod = 'manual'; // default
                if (!empty($validated['used_rfid']) && !empty($validated['used_barcode'])) {
                    $transactionMethod = 'mixed'; // both RFID and barcode used
                } elseif (!empty($validated['used_rfid'])) {
                    $transactionMethod = 'rfid'; // RFID scanner used
                } elseif (!empty($validated['used_barcode'])) {
                    $transactionMethod = 'barcode'; // Barcode scanner used
                }

                // Create sale record
                $sale = Sale::create([
                    'user_id' => auth()->id(), // Kasir
                    'student_id' => $validated['payment_method'] === 'balance' ? $validated['student_id'] : null,
                    'total' => $total,
                    'payment_method' => $validated['payment_method'],
                    'transaction_method' => $transactionMethod,
                    'cash_amount' => $validated['payment_method'] === 'cash' ? $validated['cash_amount'] : null,
                    'change_amount' => $validated['payment_method'] === 'cash' ? ($validated['cash_amount'] - $total) : null,
                    'status' => 'completed',
                ]);

                // Create sale items and update stock
                foreach ($validated['items'] as $item) {
                    $product = $products->get($item['product_id']);

                    SaleItem::create([
                        'sale_id' => $sale->id,
                        'product_id' => $product->id,
                        'quantity' => $item['quantity'],
                        'price' => $item['price'],
                        'subtotal' => $item['price'] * $item['quantity'],
                    ]);

                    // Reduce stock
                    $product->decrement('stock', $item['quantity']);
                }

                // If payment method is balance, deduct from student balance
                if ($validated['payment_method'] === 'balance') {
                    \Log::info('Before balance deduction', [
                        'student_id' => $student->id,
                        'student_name' => $student->user->name,
                        'balance_before' => $student->balance,
                        'total_to_deduct' => $total,
                        'sale_id' => $sale->id
                    ]);

                    $student->decrement('balance', $total);
                    $student->refresh();

                    \Log::info('After balance deduction', [
                        'student_id' => $student->id,
                        'balance_after' => $student->balance,
                        'expected_balance' => $student->balance,
                    ]);

                    // Create transaction record
                    $transaction = Transaction::create([
                        'student_id' => $student->id,
                        'type' => 'purchase',
                        'transaction_method' => $transactionMethod,
                        'amount' => $total,
                        'description' => 'Pembelian di koperasi - Invoice #' . $sale->id,
                        'ending_balance' => $student->balance,
                        'reference_id' => $sale->id,
                        'reference_type' => 'sale',
                    ]);

                    \Log::info('Transaction record created', [
                        'transaction_id' => $transaction->id,
                        'type' => $transaction->type,
                        'amount' => $transaction->amount,
                        'ending_balance' => $transaction->ending_balance
                    ]);
                }

                return response()->json([
                    'success' => true,
                    'message' => 'Transaksi berhasil diproses',
                    'sale' => $sale->load('saleItems.product'),
                    'sale_id' => $sale->id,
                    'change' => $validated['payment_method'] === 'cash' ? ($validated['cash_amount'] - $total) : 0,
                    'receipt_url' => route('pos.receipt', $sale->id),
                ]);
            });

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak valid: ' . implode(', ', $e->validator->errors()->all())
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Checkout failed: ' . $e->getMessage(), [
                'user_id' => auth()->id(),
                'items' => $validated['items'] ?? null,
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Get recent sales for void/cancel feature
     */
    public function getRecentSales()
    {
        $sales = Sale::with(['student.user', 'saleItems.product', 'user'])
            ->where('status', 'completed')
            ->where('created_at', '>=', now()->subHours(24)) // Last 24 hours only
            ->latest()
            ->limit(10)
            ->get();

        return response()->json([
            'sales' => $sales
        ]);
    }

    /**
     * Void/Cancel a sale transaction
     */
    public function voidSale(Sale $sale)
    {
        try {
            // Check if sale is already voided
            if ($sale->status === 'voided') {
                return response()->json([
                    'success' => false,
                    'message' => 'Transaksi ini sudah dibatalkan sebelumnya'
                ], 400);
            }

            DB::beginTransaction();

            // Return stock to each product
            foreach ($sale->saleItems as $item) {
                $item->product->increment('stock', $item->quantity);
            }

            // Return balance to student if payment was using balance
            if ($sale->payment_method === 'balance' && $sale->student) {
                $sale->student->increment('balance', $sale->total);
                $sale->student->refresh();

                // Create reversal transaction record
                Transaction::create([
                    'student_id' => $sale->student_id,
                    'type' => 'return',
                    'transaction_method' => 'system', // system reversal
                    'amount' => $sale->total,
                    'description' => 'Pembatalan transaksi - Invoice #' . $sale->id,
                    'ending_balance' => $sale->student->balance,
                    'reference_id' => $sale->id,
                    'reference_type' => 'void_sale',
                ]);
            }

            // Mark sale as voided
            $sale->update(['status' => 'voided']);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Transaksi berhasil dibatalkan. Stok dan saldo telah dikembalikan.'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Gagal membatalkan transaksi: ' . $e->getMessage()
            ], 400);
        }
    }

    /**
     * Print receipt (Thermal Printer Format)
     */
    public function printReceipt(Sale $sale)
    {
        // Load relationships
        $sale->load(['user', 'student.user', 'saleItems.product']);

        // Return thermal printer view
        return view('receipt', [
            'sale' => $sale
        ]);
    }

    /**
     * Show all transactions history with advanced search
     */
    public function transactionsHistory(Request $request)
    {
        try {
            // OPTIMIZED: Only load necessary relationships with specific columns
            $query = Sale::with([
                'student:id,user_id,nis,kelas',
                'student.user:id,name',
                'user:id,name,role'
            ])->select('id', 'student_id', 'user_id', 'payment_method', 'status', 'total', 'created_at');

            // Filter by date range
            if ($request->filled('date_from')) {
                $query->whereDate('created_at', '>=', $request->date_from);
            }

            if ($request->filled('date_to')) {
                $query->whereDate('created_at', '<=', $request->date_to);
            }

            // Filter by payment method
            if ($request->filled('payment_method')) {
                $query->where('payment_method', $request->payment_method);
            }

            // Filter by status
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            // Filter by student
            if ($request->filled('student_id')) {
                $query->where('student_id', $request->student_id);
            }

            // Filter by cashier/user
            if ($request->filled('user_id')) {
                $query->where('user_id', $request->user_id);
            }

            // Search by invoice ID, student name, or NIS
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('id', 'like', '%' . $search . '%')
                      ->orWhereHas('student.user', function($q) use ($search) {
                          $q->where('name', 'like', '%' . $search . '%');
                      })
                      ->orWhereHas('student', function($q) use ($search) {
                          $q->where('nis', 'like', '%' . $search . '%');
                      });
                });
            }

            $sales = $query->latest()->paginate(20);

            // OPTIMIZED: Use aggregate queries instead of loading all records
            $summaryBase = Sale::query();
            if ($request->filled('date_from')) {
                $summaryBase->whereDate('created_at', '>=', $request->date_from);
            }
            if ($request->filled('date_to')) {
                $summaryBase->whereDate('created_at', '<=', $request->date_to);
            }
            if ($request->filled('payment_method')) {
                $summaryBase->where('payment_method', $request->payment_method);
            }
            if ($request->filled('status')) {
                $summaryBase->where('status', $request->status);
            }

            // Use separate optimized queries for each metric
            $summary = [
                'total_transactions' => (clone $summaryBase)->count(),
                'total_revenue' => (clone $summaryBase)->where('status', 'completed')->sum('total'),
                'cash_transactions' => (clone $summaryBase)->where('payment_method', 'cash')->count(),
                'balance_transactions' => (clone $summaryBase)->where('payment_method', 'balance')->count(),
                'voided_transactions' => (clone $summaryBase)->where('status', 'voided')->count(),
                'cash_revenue' => (clone $summaryBase)->where('payment_method', 'cash')->where('status', 'completed')->sum('total'),
                'balance_revenue' => (clone $summaryBase)->where('payment_method', 'balance')->where('status', 'completed')->sum('total'),
            ];

            return Inertia::render('Kasir/TransactionsHistory', [
                'sales' => $sales,
                'summary' => $summary,
                'filters' => [
                    'date_from' => $request->date_from ?? '',
                    'date_to' => $request->date_to ?? '',
                    'payment_method' => $request->payment_method ?? '',
                    'status' => $request->status ?? '',
                    'student_id' => $request->student_id ?? '',
                    'user_id' => $request->user_id ?? '',
                    'search' => $request->search ?? '',
                ],
            ]);

        } catch (\Exception $e) {
            \Log::error('Transactions history error: ' . $e->getMessage());

            return Inertia::render('Kasir/TransactionsHistory', [
                'sales' => new \Illuminate\Pagination\LengthAwarePaginator([], 0, 20),
                'summary' => [
                    'total_transactions' => 0,
                    'total_revenue' => 0,
                    'cash_transactions' => 0,
                    'balance_transactions' => 0,
                    'voided_transactions' => 0,
                    'cash_revenue' => 0,
                    'balance_revenue' => 0,
                ],
                'filters' => [
                    'date_from' => '',
                    'date_to' => '',
                    'payment_method' => '',
                    'status' => '',
                    'student_id' => '',
                    'user_id' => '',
                    'search' => '',
                ],
                'error' => 'Gagal memuat riwayat transaksi: ' . $e->getMessage()
            ]);
        }
    }
}
