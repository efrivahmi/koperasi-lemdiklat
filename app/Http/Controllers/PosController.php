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

        if ($request->has('search') && $request->search) {
            $search = $request->search;
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
        $validated = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric|min:0',
            'payment_method' => 'required|in:cash,balance',
            'student_id' => 'required_if:payment_method,balance|exists:students,id',
            'cash_amount' => 'required_if:payment_method,cash|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();

            // Calculate total
            $total = 0;
            foreach ($validated['items'] as $item) {
                $total += $item['price'] * $item['quantity'];
            }

            // Validate stock and student balance
            foreach ($validated['items'] as $item) {
                $product = Product::find($item['product_id']);

                if ($product->stock < $item['quantity']) {
                    throw new \Exception("Stok {$product->name} tidak mencukupi. Tersedia: {$product->stock}");
                }
            }

            // Validate student balance if payment method is balance
            $student = null;
            if ($validated['payment_method'] === 'balance') {
                $student = Student::find($validated['student_id']);

                if ($student->balance < $total) {
                    throw new \Exception("Saldo siswa tidak mencukupi. Saldo: Rp " . number_format($student->balance, 0, ',', '.'));
                }
            }

            // Create sale record
            $sale = Sale::create([
                'user_id' => auth()->id(), // Kasir
                'student_id' => $validated['payment_method'] === 'balance' ? $validated['student_id'] : null,
                'total' => $total,
                'payment_method' => $validated['payment_method'],
                'cash_amount' => $validated['payment_method'] === 'cash' ? $validated['cash_amount'] : null,
                'change_amount' => $validated['payment_method'] === 'cash' ? ($validated['cash_amount'] - $total) : null,
            ]);

            // Create sale items and update stock
            foreach ($validated['items'] as $item) {
                $product = Product::find($item['product_id']);

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
                $student->decrement('balance', $total);
                $student->refresh();

                // Create transaction record
                Transaction::create([
                    'student_id' => $student->id,
                    'type' => 'pembelian',
                    'amount' => $total,
                    'description' => 'Pembelian di koperasi - Invoice #' . $sale->id,
                    'ending_balance' => $student->balance,
                    'reference_id' => $sale->id,
                    'reference_type' => 'sale',
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Transaksi berhasil',
                'sale' => $sale->load('saleItems.product'),
                'sale_id' => $sale->id,
                'change' => $validated['payment_method'] === 'cash' ? ($validated['cash_amount'] - $total) : 0,
                'receipt_url' => route('pos.receipt', $sale->id),
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
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
}
