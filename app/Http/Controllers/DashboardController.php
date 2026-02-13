<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Sale;
use App\Models\Student;
use App\Models\Transaction;
use App\Models\Expense;
use App\Models\Voucher;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        // Redirect students to student portal
        if ($user->role === 'siswa') {
            return redirect()->route('student.dashboard');
        }

        // Redirect guru to teacher portal
        if ($user->role === 'guru') {
            return redirect()->route('teacher.dashboard');
        }

        // Kasir Dashboard - Shows kasir-specific view
        if ($user->role === 'kasir') {
            $today = Carbon::today();
            $todaySales = Sale::whereDate('created_at', $today)->get();

            // Sales by this kasir
            $mySales = Sale::where('created_by', $user->id)
                ->whereDate('created_at', $today)
                ->get();

            $lowStockList = Product::with('category')
                ->where('stock', '<=', config('business.inventory.low_stock_threshold', 10))
                ->orderBy('stock', 'asc')
                ->limit(10)
                ->get();

            $recentSales = Sale::with(['items', 'creator'])
                ->whereDate('created_at', $today)
                ->latest()
                ->limit(10)
                ->get();

            return Inertia::render('Kasir/Dashboard', [
                'stats' => [
                    'todayRevenue' => $todaySales->sum('total'),
                    'todayTransactions' => $todaySales->count(),
                    'myRevenue' => $mySales->sum('total'),
                    'myTransactions' => $mySales->count(),
                    'totalProducts' => Product::count(),
                    'totalCategories' => \App\Models\Category::count(),
                    'lowStockCount' => Product::where('stock', '<=', config('business.inventory.low_stock_threshold', 10))->count(),
                    'outOfStockProducts' => Product::where('stock', '<=', config('business.inventory.out_of_stock_threshold', 0))->count(),
                ],
                'recentSales' => $recentSales,
                'lowStockList' => $lowStockList,
            ]);
        }

        // Admin/Master Dashboard - Full unified view

        // Today's sales statistics
        $today = Carbon::today();
        $todaySales = Sale::whereDate('created_at', $today)->get();
        $todayRevenue = $todaySales->sum('total');
        $todayTransactions = $todaySales->count();

        // This month statistics
        $monthStart = Carbon::now()->startOfMonth();
        $monthSales = Sale::where('created_at', '>=', $monthStart)->get();
        $monthRevenue = $monthSales->sum('total');

        // This month expenses
        $monthExpenses = Expense::where('expense_date', '>=', $monthStart)->sum('amount');

        // Products statistics
        $totalProducts = Product::count();
        $lowStockProducts = Product::where('stock', '<=', config('business.inventory.low_stock_threshold', 10))->count();
        $outOfStockProducts = Product::where('stock', '<=', config('business.inventory.out_of_stock_threshold', 0))->count();

        // Students statistics
        $totalStudents = Student::count();
        $totalStudentBalance = Student::sum('balance');
        $activeStudents = Student::where('balance', '>', 0)->count();

        // Vouchers statistics
        $availableVouchers = Voucher::where('status', 'available')
            ->where(function($query) {
                $query->whereNull('expired_at')
                      ->orWhere('expired_at', '>', now());
            })
            ->count();
        $usedVouchers = Voucher::where('status', 'used')->count();

        // Recent transactions (last 10)
        $recentTransactions = Transaction::with(['student.user'])
            ->latest()
            ->limit(config('business.transaction.recent_transactions_limit', 10))
            ->get();

        // Top selling products this month
        $topProducts = DB::table('sale_items')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->select('products.name', DB::raw('SUM(sale_items.quantity) as total_sold'), DB::raw('SUM(sale_items.subtotal) as total_revenue'))
            ->where('sales.created_at', '>=', $monthStart)
            ->groupBy('products.id', 'products.name')
            ->orderBy('total_sold', 'desc')
            ->limit(config('business.transaction.top_products_limit', 5))
            ->get();

        // Low stock products (need restock)
        $lowStockList = Product::with('category')
            ->where('stock', '<=', config('business.inventory.low_stock_threshold', 10))
            ->orderBy('stock', 'asc')
            ->limit(config('business.transaction.recent_transactions_limit', 10))
            ->get();

        // Sales chart data (last 7 days)
        $salesChart = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $dayName = $date->isoFormat('ddd, DD MMM');
            $dayRevenue = Sale::whereDate('created_at', $date)->sum('total');
            $salesChart[] = [
                'date' => $dayName,
                'revenue' => (float) $dayRevenue,
            ];
        }

        // Calculate profit margin (estimate)
        $totalCOGS = DB::table('sale_items')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->where('sales.created_at', '>=', $monthStart)
            ->sum(DB::raw('sale_items.quantity * products.harga_beli'));

        $grossProfit = $monthRevenue - $totalCOGS;
        $netProfit = $grossProfit - $monthExpenses;

        return Inertia::render('Dashboard', [
            'stats' => [
                // Today
                'todayRevenue' => $todayRevenue,
                'todayTransactions' => $todayTransactions,

                // This Month
                'monthRevenue' => $monthRevenue,
                'monthExpenses' => $monthExpenses,
                'grossProfit' => $grossProfit,
                'netProfit' => $netProfit,

                // Products
                'totalProducts' => $totalProducts,
                'lowStockProducts' => $lowStockProducts,
                'outOfStockProducts' => $outOfStockProducts,

                // Students
                'totalStudents' => $totalStudents,
                'totalStudentBalance' => $totalStudentBalance,
                'activeStudents' => $activeStudents,

                // Vouchers
                'availableVouchers' => $availableVouchers,
                'usedVouchers' => $usedVouchers,
            ],
            'recentTransactions' => $recentTransactions,
            'topProducts' => $topProducts,
            'lowStockList' => $lowStockList,
            'salesChart' => $salesChart,
        ]);
    }
}
