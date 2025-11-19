<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\Product;
use App\Models\Expense;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\SalesReportExport;
use App\Exports\InventoryReportExport;
use App\Exports\FinancialReportExport;
use App\Exports\StudentTransactionsExport;

class ReportController extends Controller
{
    /**
     * Sales Report
     */
    public function sales(Request $request)
    {
        $query = Sale::with(['student.user', 'saleItems.product']);

        // Default date range (this month)
        $dateFrom = $request->date_from ?? Carbon::now()->startOfMonth()->format('Y-m-d');
        $dateTo = $request->date_to ?? Carbon::now()->format('Y-m-d');

        $query->whereBetween('created_at', [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59']);

        // Filter by payment method
        if ($request->has('payment_method') && $request->payment_method) {
            $query->where('payment_method', $request->payment_method);
        }

        // Search by student
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->whereHas('student.user', function($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%');
            })->orWhereHas('student', function($q) use ($search) {
                $q->where('nis', 'like', '%' . $search . '%');
            });
        }

        $sales = $query->latest()->paginate(20);

        // Calculate summary
        $allSales = Sale::whereBetween('created_at', [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59'])->get();
        $summary = [
            'total_sales' => $allSales->count(),
            'total_revenue' => $allSales->sum('total'),
            'cash_sales' => $allSales->where('payment_method', 'cash')->sum('total'),
            'balance_sales' => $allSales->where('payment_method', 'balance')->sum('total'),
        ];

        return Inertia::render('Admin/Reports/Sales', [
            'sales' => $sales,
            'summary' => $summary,
            'filters' => [
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
                'payment_method' => $request->payment_method ?? '',
                'search' => $request->search ?? '',
            ],
        ]);
    }

    public function salesExport(Request $request)
    {
        $dateFrom = $request->date_from ?? Carbon::now()->startOfMonth()->format('Y-m-d');
        $dateTo = $request->date_to ?? Carbon::now()->format('Y-m-d');

        $filename = 'laporan-penjualan-' . $dateFrom . '-to-' . $dateTo . '.xlsx';

        return Excel::download(new SalesReportExport($dateFrom, $dateTo, $request->payment_method, $request->search), $filename);
    }

    /**
     * Inventory Report
     */
    public function inventory(Request $request)
    {
        $query = Product::with('category');

        // Filter by category
        if ($request->has('category_id') && $request->category_id) {
            $query->where('category_id', $request->category_id);
        }

        // Filter by stock status
        if ($request->has('stock_status') && $request->stock_status) {
            if ($request->stock_status === 'out') {
                $query->where('stock', 0);
            } elseif ($request->stock_status === 'low') {
                $query->where('stock', '>', 0)->where('stock', '<=', 10);
            } elseif ($request->stock_status === 'available') {
                $query->where('stock', '>', 10);
            }
        }

        // Search
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where('name', 'like', '%' . $search . '%');
        }

        $products = $query->orderBy('stock', 'asc')->paginate(20);

        // Calculate summary
        $allProducts = Product::all();
        $summary = [
            'total_products' => $allProducts->count(),
            'total_stock_value' => $allProducts->sum(function($p) { return $p->stock * $p->harga_beli; }),
            'total_potential_revenue' => $allProducts->sum(function($p) { return $p->stock * $p->harga_jual; }),
            'out_of_stock' => $allProducts->where('stock', 0)->count(),
            'low_stock' => $allProducts->where('stock', '>', 0)->where('stock', '<=', 10)->count(),
        ];

        $categories = \App\Models\Category::all();

        return Inertia::render('Admin/Reports/Inventory', [
            'products' => $products,
            'summary' => $summary,
            'categories' => $categories,
            'filters' => $request->only(['category_id', 'stock_status', 'search']),
        ]);
    }

    public function inventoryExport(Request $request)
    {
        $filename = 'laporan-inventaris-' . date('Y-m-d') . '.xlsx';
        return Excel::download(new InventoryReportExport($request->category_id, $request->stock_status, $request->search), $filename);
    }

    /**
     * Financial Report (Profit/Loss)
     */
    public function financial(Request $request)
    {
        $dateFrom = $request->date_from ?? Carbon::now()->startOfMonth()->format('Y-m-d');
        $dateTo = $request->date_to ?? Carbon::now()->format('Y-m-d');

        // Calculate Revenue
        $sales = Sale::whereBetween('created_at', [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59'])->get();
        $totalRevenue = $sales->sum('total');

        // Calculate COGS (Cost of Goods Sold)
        $totalCOGS = DB::table('sale_items')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->whereBetween('sales.created_at', [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59'])
            ->sum(DB::raw('sale_items.quantity * products.harga_beli'));

        // Calculate Expenses
        $expenses = Expense::whereBetween('expense_date', [$dateFrom, $dateTo])->get();
        $totalExpenses = $expenses->sum('amount');

        // Expenses by category
        $expensesByCategory = Expense::whereBetween('expense_date', [$dateFrom, $dateTo])
            ->select('category', DB::raw('SUM(amount) as total'))
            ->groupBy('category')
            ->get();

        // Calculate Profit
        $grossProfit = $totalRevenue - $totalCOGS;
        $netProfit = $grossProfit - $totalExpenses;
        $profitMargin = $totalRevenue > 0 ? ($netProfit / $totalRevenue) * 100 : 0;

        return Inertia::render('Admin/Reports/Financial', [
            'data' => [
                'total_revenue' => $totalRevenue,
                'total_cogs' => $totalCOGS,
                'gross_profit' => $grossProfit,
                'total_expenses' => $totalExpenses,
                'net_profit' => $netProfit,
                'profit_margin' => $profitMargin,
                'total_transactions' => $sales->count(),
            ],
            'expenses' => $expenses,
            'expensesByCategory' => $expensesByCategory,
            'filters' => [
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
            ],
        ]);
    }

    public function financialExport(Request $request)
    {
        $dateFrom = $request->date_from ?? Carbon::now()->startOfMonth()->format('Y-m-d');
        $dateTo = $request->date_to ?? Carbon::now()->format('Y-m-d');

        $filename = 'laporan-keuangan-' . $dateFrom . '-to-' . $dateTo . '.xlsx';
        return Excel::download(new FinancialReportExport($dateFrom, $dateTo), $filename);
    }

    /**
     * Student Transactions Report (Detailed)
     */
    public function studentTransactions(Request $request)
    {
        $query = Sale::with(['student.user', 'saleItems.product']);

        // Default date range
        $dateFrom = $request->date_from ?? Carbon::now()->startOfMonth()->format('Y-m-d');
        $dateTo = $request->date_to ?? Carbon::now()->format('Y-m-d');

        $query->whereBetween('created_at', [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59']);

        // Filter by class
        if ($request->has('class') && $request->class) {
            $query->whereHas('student', function($q) use ($request) {
                $q->where('kelas', $request->class);
            });
        }

        // Filter by student
        if ($request->has('student_id') && $request->student_id) {
            $query->where('student_id', $request->student_id);
        }

        // Search
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->whereHas('student.user', function($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%');
            })->orWhereHas('student', function($q) use ($search) {
                $q->where('nis', 'like', '%' . $search . '%');
            });
        }

        $transactions = $query->latest()->paginate(50);

        // Get unique classes for filter
        $classes = \App\Models\Student::select('kelas')->distinct()->orderBy('kelas')->pluck('kelas');

        // Get students for filter
        $students = \App\Models\Student::with('user')->get()->map(function($student) {
            return [
                'id' => $student->id,
                'name' => $student->user->name,
                'nis' => $student->nis,
                'class' => $student->kelas,
            ];
        });

        return Inertia::render('Admin/Reports/StudentTransactions', [
            'transactions' => $transactions,
            'classes' => $classes,
            'students' => $students,
            'filters' => [
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
                'class' => $request->class ?? '',
                'student_id' => $request->student_id ?? '',
                'search' => $request->search ?? '',
            ],
        ]);
    }

    public function studentTransactionsExport(Request $request)
    {
        $dateFrom = $request->date_from ?? Carbon::now()->startOfMonth()->format('Y-m-d');
        $dateTo = $request->date_to ?? Carbon::now()->format('Y-m-d');

        $filename = 'laporan-transaksi-siswa-' . $dateFrom . '-to-' . $dateTo . '.xlsx';

        return Excel::download(
            new StudentTransactionsExport($dateFrom, $dateTo, $request->class, $request->student_id, $request->search),
            $filename
        );
    }
}
