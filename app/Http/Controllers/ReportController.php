<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\Product;
use App\Models\Expense;
use App\Models\Transaction;
use App\Models\StockAdjustment;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\SalesReportExport;
use App\Exports\InventoryReportExport;
use App\Exports\FinancialReportExport;
use App\Exports\StudentTransactionsExport;
use App\Exports\StockAdjustmentsExport;

class ReportController extends Controller
{
    /**
     * Sales Report
     */
    public function sales(Request $request)
    {
        try {
            \Log::info('Sales report accessed', [
                'user_id' => auth()->id(),
                'filters' => $request->all()
            ]);

            $query = Sale::with(['student.user', 'saleItems.product', 'creator', 'updater']);

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

            $sales = $query->oldest()->paginate(20);

            // Calculate summary
            $allSales = Sale::whereBetween('created_at', [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59'])->get();
            $summary = [
                'total_sales' => $allSales->count(),
                'total_revenue' => $allSales->sum('total'),
                'cash_sales' => $allSales->where('payment_method', 'cash')->sum('total'),
                'balance_sales' => $allSales->where('payment_method', 'balance')->sum('total'),
            ];

            \Log::info('Sales report loaded successfully', [
                'total_records' => $sales->total(),
                'summary' => $summary
            ]);

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

        } catch (\Exception $e) {
            \Log::error('Sales report error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'user_id' => auth()->id()
            ]);

            // Return Inertia response dengan empty data dan error message
            return Inertia::render('Admin/Reports/Sales', [
                'sales' => new \Illuminate\Pagination\LengthAwarePaginator([], 0, 20),
                'summary' => [
                    'total_sales' => 0,
                    'total_revenue' => 0,
                    'cash_sales' => 0,
                    'balance_sales' => 0,
                ],
                'filters' => [
                    'date_from' => Carbon::now()->startOfMonth()->format('Y-m-d'),
                    'date_to' => Carbon::now()->format('Y-m-d'),
                    'payment_method' => '',
                    'search' => '',
                ],
                'error' => 'Gagal memuat laporan: ' . $e->getMessage()
            ]);
        }
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
        try {
            \Log::info('Inventory report accessed', [
                'user_id' => auth()->id(),
                'filters' => $request->all()
            ]);

            $query = Product::with(['category', 'creator', 'updater']);

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

            \Log::info('Inventory report loaded successfully', [
                'total_products' => $products->total()
            ]);

            return Inertia::render('Admin/Reports/Inventory', [
                'products' => $products,
                'summary' => $summary,
                'categories' => $categories,
                'filters' => $request->only(['category_id', 'stock_status', 'search']),
            ]);

        } catch (\Exception $e) {
            \Log::error('Inventory report error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'user_id' => auth()->id()
            ]);

            return Inertia::render('Admin/Reports/Inventory', [
                'products' => new \Illuminate\Pagination\LengthAwarePaginator([], 0, 20),
                'summary' => [
                    'total_products' => 0,
                    'total_stock_value' => 0,
                    'total_potential_revenue' => 0,
                    'out_of_stock' => 0,
                    'low_stock' => 0,
                ],
                'categories' => [],
                'filters' => ['category_id' => '', 'stock_status' => '', 'search' => ''],
                'error' => 'Gagal memuat laporan: ' . $e->getMessage()
            ]);
        }
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
        try {
            \Log::info('Financial report accessed', [
                'user_id' => auth()->id(),
                'filters' => $request->all()
            ]);

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
            $expenses = Expense::with(['creator', 'updater'])->whereBetween('expense_date', [$dateFrom, $dateTo])->get();
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

            \Log::info('Financial report loaded successfully', [
                'total_revenue' => $totalRevenue,
                'net_profit' => $netProfit
            ]);

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

        } catch (\Exception $e) {
            \Log::error('Financial report error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'user_id' => auth()->id()
            ]);

            return Inertia::render('Admin/Reports/Financial', [
                'data' => [
                    'total_revenue' => 0,
                    'total_cogs' => 0,
                    'gross_profit' => 0,
                    'total_expenses' => 0,
                    'net_profit' => 0,
                    'profit_margin' => 0,
                    'total_transactions' => 0,
                ],
                'expenses' => [],
                'expensesByCategory' => [],
                'filters' => [
                    'date_from' => Carbon::now()->startOfMonth()->format('Y-m-d'),
                    'date_to' => Carbon::now()->format('Y-m-d'),
                ],
                'error' => 'Gagal memuat laporan: ' . $e->getMessage()
            ]);
        }
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
        try {
            \Log::info('Student transactions report accessed', [
                'user_id' => auth()->id(),
                'filters' => $request->all()
            ]);

            $query = Sale::with(['student.user', 'saleItems.product', 'creator', 'updater']);

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

            $transactions = $query->oldest()->paginate(50);

            // Get unique classes for filter
            $classes = \App\Models\Student::select('kelas')->distinct()->orderBy('kelas')->pluck('kelas');

            // Get students for filter
            $students = \App\Models\Student::with('user')
                ->whereHas('user')
                ->get()
                ->map(function($student) {
                    return [
                        'id' => $student->id,
                        'name' => $student->user->name,
                        'nis' => $student->nis,
                        'class' => $student->kelas,
                    ];
                });

            \Log::info('Student transactions report loaded successfully', [
                'total_transactions' => $transactions->total()
            ]);

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

        } catch (\Exception $e) {
            \Log::error('Student transactions report error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'user_id' => auth()->id()
            ]);

            return Inertia::render('Admin/Reports/StudentTransactions', [
                'transactions' => new \Illuminate\Pagination\LengthAwarePaginator([], 0, 50),
                'classes' => [],
                'students' => [],
                'filters' => [
                    'date_from' => Carbon::now()->startOfMonth()->format('Y-m-d'),
                    'date_to' => Carbon::now()->format('Y-m-d'),
                    'class' => '',
                    'student_id' => '',
                    'search' => '',
                ],
                'error' => 'Gagal memuat laporan: ' . $e->getMessage()
            ]);
        }
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

    /**
     * Stock Adjustments Report
     */
    public function stockAdjustments(Request $request)
    {
        try {
            \Log::info('Stock adjustments report accessed', [
                'user_id' => auth()->id(),
                'filters' => $request->all()
            ]);

            $query = StockAdjustment::with(['product', 'adjustedBy']);

            // Default date range (this month)
            $dateFrom = $request->date_from ?? Carbon::now()->startOfMonth()->format('Y-m-d');
            $dateTo = $request->date_to ?? Carbon::now()->format('Y-m-d');

            $query->whereBetween('created_at', [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59']);

            // Filter by product
            if ($request->has('product_id') && $request->product_id) {
                $query->where('product_id', $request->product_id);
            }

            // Filter by adjustment type
            if ($request->has('type') && $request->type) {
                $query->where('type', $request->type);
            }

            // Filter by adjusted_by user
            if ($request->has('adjusted_by') && $request->adjusted_by) {
                $query->where('adjusted_by', $request->adjusted_by);
            }

            // Search by product name
            if ($request->has('search') && $request->search) {
                $search = $request->search;
                $query->whereHas('product', function($q) use ($search) {
                    $q->where('name', 'like', '%' . $search . '%')
                      ->orWhere('barcode', 'like', '%' . $search . '%');
                });
            }

            $adjustments = $query->oldest()->paginate(20);

            // Add financial calculations to each adjustment based on purpose
            $adjustments->getCollection()->transform(function ($adjustment) {
                if ($adjustment->product) {
                    $hargaBeli = $adjustment->product->harga_beli;
                    $hargaJual = $adjustment->product->harga_jual;
                    $quantity = $adjustment->quantity_adjusted;

                    $adjustment->cost_impact = $quantity * $hargaBeli;
                    $adjustment->profit_margin_per_unit = $hargaJual - $hargaBeli;

                    // Calculate profit/loss based on purpose using international accounting standards
                    if ($adjustment->purpose === 'sale') {
                        // For sales: Revenue - COGS = Gross Profit
                        $revenue = $quantity * $hargaJual;
                        $cogs = $quantity * $hargaBeli;
                        $adjustment->revenue = $revenue;
                        $adjustment->profit_loss_impact = $revenue - $cogs; // Gross profit
                    } else if (in_array($adjustment->purpose, ['internal_use', 'personal_use', 'damage', 'expired'])) {
                        // For non-revenue purposes: Pure loss (COGS with no revenue)
                        $adjustment->revenue = 0;
                        $adjustment->profit_loss_impact = -($quantity * $hargaBeli); // Negative = Loss
                    } else if ($adjustment->purpose === 'return_to_supplier') {
                        // For returns: Typically get refund at purchase price
                        $adjustment->revenue = $quantity * $hargaBeli; // Refund
                        $adjustment->profit_loss_impact = 0; // Break-even
                    } else {
                        // For 'other' or unspecified: Calculate margin potential
                        $adjustment->revenue = 0;
                        $adjustment->profit_loss_impact = $quantity * $adjustment->profit_margin_per_unit;
                    }
                } else {
                    $adjustment->cost_impact = 0;
                    $adjustment->profit_margin_per_unit = 0;
                    $adjustment->profit_loss_impact = 0;
                    $adjustment->revenue = 0;
                }
                return $adjustment;
            });

            // Calculate summary with profit/loss impact
            $allAdjustments = StockAdjustment::with('product')
                ->whereBetween('created_at', [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59'])
                ->get();

            // Calculate cost and profit/loss impact based on purpose
            $totalCostImpact = 0;
            $totalProfitLossImpact = 0;
            $totalRevenue = 0;
            $additionsCostImpact = 0;
            $deductionsCostImpact = 0;
            $additionsProfitImpact = 0;
            $deductionsLossImpact = 0;

            // Group by purpose for detailed analysis
            $salesRevenue = 0;
            $salesProfit = 0;
            $nonRevenueLoss = 0;
            $returnRefund = 0;

            foreach ($allAdjustments as $adjustment) {
                if ($adjustment->product) {
                    $hargaBeli = $adjustment->product->harga_beli;
                    $hargaJual = $adjustment->product->harga_jual;
                    $quantity = $adjustment->quantity_adjusted;
                    $costImpact = $quantity * $hargaBeli;

                    // Calculate impact based on purpose
                    $profitLossImpact = 0;
                    $revenue = 0;

                    if ($adjustment->purpose === 'sale') {
                        // Sales transaction: Revenue - COGS
                        $revenue = $quantity * $hargaJual;
                        $profitLossImpact = $revenue - $costImpact;
                        $salesRevenue += $revenue;
                        $salesProfit += $profitLossImpact;
                    } else if (in_array($adjustment->purpose, ['internal_use', 'personal_use', 'damage', 'expired'])) {
                        // Non-revenue purposes: Pure loss
                        $profitLossImpact = -$costImpact;
                        $nonRevenueLoss += $costImpact;
                    } else if ($adjustment->purpose === 'return_to_supplier') {
                        // Returns: Refund at purchase price (break-even)
                        $revenue = $costImpact;
                        $profitLossImpact = 0;
                        $returnRefund += $revenue;
                    } else {
                        // Other: Potential margin calculation
                        $profitMargin = $hargaJual - $hargaBeli;
                        $profitLossImpact = $quantity * $profitMargin;
                    }

                    if ($adjustment->type === 'addition') {
                        $additionsCostImpact += $costImpact;
                        $additionsProfitImpact += max(0, $profitLossImpact);
                        $totalCostImpact += $costImpact;
                    } else {
                        $deductionsCostImpact += $costImpact;
                        $deductionsLossImpact += $profitLossImpact;
                        $totalCostImpact -= $costImpact;
                    }

                    $totalProfitLossImpact += ($adjustment->type === 'addition' ? $profitLossImpact : -abs($profitLossImpact));
                    $totalRevenue += $revenue;
                }
            }

            $summary = [
                'total_adjustments' => $allAdjustments->count(),
                'total_additions' => $allAdjustments->where('type', 'addition')->sum('quantity_adjusted'),
                'total_deductions' => $allAdjustments->where('type', 'deduction')->sum('quantity_adjusted'),
                'additions_count' => $allAdjustments->where('type', 'addition')->count(),
                'deductions_count' => $allAdjustments->where('type', 'deduction')->count(),
                // Financial impact
                'total_cost_impact' => $totalCostImpact,
                'total_profit_loss_impact' => $totalProfitLossImpact,
                'total_revenue' => $totalRevenue,
                'additions_cost_impact' => $additionsCostImpact,
                'deductions_cost_impact' => $deductionsCostImpact,
                'additions_profit_impact' => $additionsProfitImpact,
                'deductions_loss_impact' => $deductionsLossImpact,
                // Purpose-based breakdown
                'sales_revenue' => $salesRevenue,
                'sales_profit' => $salesProfit,
                'non_revenue_loss' => $nonRevenueLoss,
                'return_refund' => $returnRefund,
            ];

            // Get products for filter
            $products = Product::select('id', 'name', 'barcode')->orderBy('name')->get();

            // Get users who have made adjustments for filter
            $adjusters = \App\Models\User::whereIn('id', StockAdjustment::select('adjusted_by')->distinct()->pluck('adjusted_by'))
                ->select('id', 'name')
                ->orderBy('name')
                ->get();

            \Log::info('Stock adjustments report loaded successfully', [
                'total_records' => $adjustments->total(),
                'summary' => $summary
            ]);

            return Inertia::render('Admin/Reports/StockAdjustments', [
                'adjustments' => $adjustments,
                'summary' => $summary,
                'products' => $products,
                'adjusters' => $adjusters,
                'filters' => [
                    'date_from' => $dateFrom,
                    'date_to' => $dateTo,
                    'product_id' => $request->product_id ?? '',
                    'type' => $request->type ?? '',
                    'adjusted_by' => $request->adjusted_by ?? '',
                    'search' => $request->search ?? '',
                ],
            ]);

        } catch (\Exception $e) {
            \Log::error('Stock adjustments report error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'user_id' => auth()->id()
            ]);

            return Inertia::render('Admin/Reports/StockAdjustments', [
                'adjustments' => new \Illuminate\Pagination\LengthAwarePaginator([], 0, 20),
                'summary' => [
                    'total_adjustments' => 0,
                    'total_additions' => 0,
                    'total_deductions' => 0,
                    'additions_count' => 0,
                    'deductions_count' => 0,
                    'total_cost_impact' => 0,
                    'total_profit_loss_impact' => 0,
                    'total_revenue' => 0,
                    'additions_cost_impact' => 0,
                    'deductions_cost_impact' => 0,
                    'additions_profit_impact' => 0,
                    'deductions_loss_impact' => 0,
                    'sales_revenue' => 0,
                    'sales_profit' => 0,
                    'non_revenue_loss' => 0,
                    'return_refund' => 0,
                ],
                'products' => [],
                'adjusters' => [],
                'filters' => [
                    'date_from' => Carbon::now()->startOfMonth()->format('Y-m-d'),
                    'date_to' => Carbon::now()->format('Y-m-d'),
                    'product_id' => '',
                    'type' => '',
                    'adjusted_by' => '',
                    'search' => '',
                ],
                'error' => 'Gagal memuat laporan: ' . $e->getMessage()
            ]);
        }
    }

    public function stockAdjustmentsExport(Request $request)
    {
        $dateFrom = $request->date_from ?? Carbon::now()->startOfMonth()->format('Y-m-d');
        $dateTo = $request->date_to ?? Carbon::now()->format('Y-m-d');

        $filename = 'laporan-penyesuaian-stok-' . $dateFrom . '-to-' . $dateTo . '.xlsx';

        return Excel::download(
            new StockAdjustmentsExport($dateFrom, $dateTo, $request->product_id, $request->type, $request->adjusted_by, $request->search),
            $filename
        );
    }
}
