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
        // Redirect students to student portal
        if (auth()->user()->role === 'siswa') {
            return redirect()->route('student.dashboard');
        }

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
        $lowStockProducts = Product::where('stock', '<=', 10)->count();
        $outOfStockProducts = Product::where('stock', 0)->count();

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
            ->limit(10)
            ->get();

        // Top selling products this month
        $topProducts = DB::table('sale_items')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->select('products.name', DB::raw('SUM(sale_items.quantity) as total_sold'), DB::raw('SUM(sale_items.subtotal) as total_revenue'))
            ->where('sales.created_at', '>=', $monthStart)
            ->groupBy('products.id', 'products.name')
            ->orderBy('total_sold', 'desc')
            ->limit(5)
            ->get();

        // Low stock products (need restock)
        $lowStockList = Product::with('category')
            ->where('stock', '<=', 10)
            ->orderBy('stock', 'asc')
            ->limit(10)
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
