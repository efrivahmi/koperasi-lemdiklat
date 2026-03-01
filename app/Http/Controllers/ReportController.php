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
use App\Traits\HandlesReportErrors;

class ReportController extends Controller
{
    use HandlesReportErrors;
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

            // Remove default date range
            $dateFrom = $request->date_from;
            $dateTo = $request->date_to;

            if ($dateFrom && $dateTo) {
                $query->whereBetween('created_at', [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59']);
            } elseif ($dateFrom) {
                $query->where('created_at', '>=', $dateFrom . ' 00:00:00');
            } elseif ($dateTo) {
                $query->where('created_at', '<=', $dateTo . ' 23:59:59');
            }

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
            $allSalesQuery = Sale::query();
            if ($dateFrom && $dateTo) {
                $allSalesQuery->whereBetween('created_at', [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59']);
            } elseif ($dateFrom) {
                $allSalesQuery->where('created_at', '>=', $dateFrom . ' 00:00:00');
            } elseif ($dateTo) {
                $allSalesQuery->where('created_at', '<=', $dateTo . ' 23:59:59');
            }
            $allSales = $allSalesQuery->get();
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

            $viewPath = auth()->user()->role === 'kasir' ? 'Kasir' : 'Admin';
            return Inertia::render("{$viewPath}/Reports/Sales", [
                'sales' => $sales,
                'summary' => $summary,
                'filters' => [
                    'date_from' => $dateFrom ?? '',
                    'date_to' => $dateTo ?? '',
                    'payment_method' => $request->payment_method ?? '',
                    'search' => $request->search ?? '',
                ],
            ]);

        } catch (\Exception $e) {
            return $this->handleReportError(
                $e,
                auth()->user()->role === 'kasir' ? 'Kasir/Reports/Sales' : 'Admin/Reports/Sales',
                [
                    'sales' => new \Illuminate\Pagination\LengthAwarePaginator([], 0, 20),
                    'summary' => [
                        'total_sales' => 0,
                        'total_revenue' => 0,
                        'cash_sales' => 0,
                        'balance_sales' => 0,
                    ],
                ],
                [
                    'date_from' => '',
                    'date_to' => '',
                    'payment_method' => '',
                    'search' => '',
                ],
                'Sales report error'
            );
        }
    }

    public function salesExport(Request $request)
    {
        $dateFrom = $request->date_from;
        $dateTo = $request->date_to;

        $filename = 'laporan-penjualan-' . ($dateFrom ? $dateFrom : 'all') . '-to-' . ($dateTo ? $dateTo : 'all') . '.xlsx';

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
                $lowStockThreshold = config('business.inventory.low_stock_threshold');
                if ($request->stock_status === 'out') {
                    $query->where('stock', 0);
                } elseif ($request->stock_status === 'low') {
                    $query->where('stock', '>', 0)->where('stock', '<=', $lowStockThreshold);
                } elseif ($request->stock_status === 'available') {
                    $query->where('stock', '>', $lowStockThreshold);
                }
            }

            // Search
            if ($request->has('search') && $request->search) {
                $search = $request->search;
                $query->where('name', 'like', '%' . $search . '%');
            }

            $products = $query->orderBy('stock', 'asc')->paginate(20);

            // Re-implement eager loading of stock movements for inventory report
            foreach ($products as $product) {
                // Get adjustments
                $adjustments = \Illuminate\Support\Facades\DB::table('stock_adjustments')
                    ->join('users', 'stock_adjustments.adjusted_by', '=', 'users.id')
                    ->where('product_id', $product->id)
                    ->select('stock_adjustments.*', 'users.name as adjusted_by_name')
                    ->orderBy('created_at', 'desc')
                    ->limit(10)
                    ->get()
                    ->map(function ($adj) {
                        return (object) [
                            'id' => $adj->id,
                            'created_at' => $adj->created_at,
                            'type' => $adj->type,
                            'purpose' => $adj->purpose,
                            'quantity_before' => $adj->quantity_before,
                            'quantity_after' => $adj->quantity_after,
                            'quantity_adjusted' => $adj->quantity_adjusted,
                            'notes' => $adj->notes,
                            'adjusted_by' => (object) ['name' => $adj->adjusted_by_name]
                        ];
                    });

                // Get sales
                $sales = \Illuminate\Support\Facades\DB::table('sale_items')
                    ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
                    ->leftJoin('students', 'sales.student_id', '=', 'students.id')
                    ->leftJoin('users as students_user', 'students.user_id', '=', 'students_user.id')
                    ->leftJoin('users as cashiers', 'sales.created_by', '=', 'cashiers.id')
                    ->where('sale_items.product_id', $product->id)
                    ->select(
                        'sale_items.quantity',
                        'sale_items.price',
                        'sales.created_at',
                        'sales.payment_method',
                        'students_user.name as student_name',
                        'cashiers.name as cashier_name'
                    )
                    ->orderBy('sales.created_at', 'desc')
                    ->limit(10)
                    ->get();

                $product->stock_movements = [
                    'adjustments' => $adjustments,
                    'sales' => $sales
                ];
            }

            // Calculate summary using optimized aggregate queries
            $lowStockThreshold = config('business.inventory.low_stock_threshold');
            $summary = [
                'total_products' => DB::table('products')->count(),
                'total_stock_value' => DB::table('products')->sum(DB::raw('stock * harga_beli')),
                'total_potential_revenue' => DB::table('products')->sum(DB::raw('stock * harga_jual')),
                'out_of_stock' => DB::table('products')->where('stock', 0)->count(),
                'low_stock' => DB::table('products')->where('stock', '>', 0)->where('stock', '<=', $lowStockThreshold)->count(),
            ];

            $categories = \App\Models\Category::all();

            \Log::info('Inventory report loaded successfully', [
                'total_products' => $products->total()
            ]);

            $viewPath = auth()->user()->role === 'kasir' ? 'Kasir' : 'Admin';
            return Inertia::render("{$viewPath}/Reports/Inventory", [
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

            $viewPath = auth()->user()->role === 'kasir' ? 'Kasir' : 'Admin';
            return Inertia::render("{$viewPath}/Reports/Inventory", [
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

            $dateFrom = $request->date_from;
            $dateTo = $request->date_to;

            // Calculate Revenue
            $salesQuery = Sale::query();
            
            // Calculate COGS (Cost of Goods Sold)
            $cogsQuery = DB::table('sale_items')
                ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
                ->join('products', 'sale_items.product_id', '=', 'products.id');

            // Calculate Expenses
            $expensesQuery = Expense::with(['creator', 'updater']);
            
            // Expenses by category
            $expensesByCategoryQuery = Expense::select('category', DB::raw('SUM(amount) as total'))
                ->groupBy('category');

            if ($dateFrom && $dateTo) {
                $salesQuery->whereBetween('created_at', [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59']);
                $cogsQuery->whereBetween('sales.created_at', [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59']);
                $expensesQuery->whereBetween('expense_date', [$dateFrom, $dateTo]);
                $expensesByCategoryQuery->whereBetween('expense_date', [$dateFrom, $dateTo]);
            } elseif ($dateFrom) {
                $salesQuery->where('created_at', '>=', $dateFrom . ' 00:00:00');
                $cogsQuery->where('sales.created_at', '>=', $dateFrom . ' 00:00:00');
                $expensesQuery->where('expense_date', '>=', $dateFrom);
                $expensesByCategoryQuery->where('expense_date', '>=', $dateFrom);
            } elseif ($dateTo) {
                $salesQuery->where('created_at', '<=', $dateTo . ' 23:59:59');
                $cogsQuery->where('sales.created_at', '<=', $dateTo . ' 23:59:59');
                $expensesQuery->where('expense_date', '<=', $dateTo);
                $expensesByCategoryQuery->where('expense_date', '<=', $dateTo);
            }

            $sales = $salesQuery->get();
            $totalRevenue = $sales->sum('total');
            $totalCOGS = $cogsQuery->sum(DB::raw('sale_items.quantity * products.harga_beli'));
            $expenses = $expensesQuery->get();
            $totalExpenses = $expenses->sum('amount');
            $expensesByCategory = $expensesByCategoryQuery->get();

            // Calculate Profit
            $grossProfit = $totalRevenue - $totalCOGS;
            $netProfit = $grossProfit - $totalExpenses;
            $profitMargin = $totalRevenue > 0 ? ($netProfit / $totalRevenue) * 100 : 0;

            \Log::info('Financial report loaded successfully', [
                'total_revenue' => $totalRevenue,
                'net_profit' => $netProfit
            ]);

            $viewPath = auth()->user()->role === 'kasir' ? 'Kasir' : 'Admin';
            return Inertia::render("{$viewPath}/Reports/Financial", [
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
                    'date_from' => $dateFrom ?? '',
                    'date_to' => $dateTo ?? '',
                ],
            ]);

        } catch (\Exception $e) {
            \Log::error('Financial report error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'user_id' => auth()->id()
            ]);

            $viewPath = auth()->user()->role === 'kasir' ? 'Kasir' : 'Admin';
            return Inertia::render("{$viewPath}/Reports/Financial", [
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
                    'date_from' => '',
                    'date_to' => '',
                ],
                'error' => 'Gagal memuat laporan: ' . $e->getMessage()
            ]);
        }
    }

    public function financialExport(Request $request)
    {
        $dateFrom = $request->date_from;
        $dateTo = $request->date_to;

        $filename = 'laporan-keuangan-' . ($dateFrom ? $dateFrom : 'all') . '-to-' . ($dateTo ? $dateTo : 'all') . '.xlsx';
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

            // Remove default date range
            $dateFrom = $request->date_from;
            $dateTo = $request->date_to;

            if ($dateFrom && $dateTo) {
                $query->whereBetween('created_at', [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59']);
            } elseif ($dateFrom) {
                $query->where('created_at', '>=', $dateFrom . ' 00:00:00');
            } elseif ($dateTo) {
                $query->where('created_at', '<=', $dateTo . ' 23:59:59');
            }

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

            $viewPath = auth()->user()->role === 'kasir' ? 'Kasir' : 'Admin';
            return Inertia::render("{$viewPath}/Reports/StudentTransactions", [
                'transactions' => $transactions,
                'classes' => $classes,
                'students' => $students,
                'filters' => [
                    'date_from' => $dateFrom ?? '',
                    'date_to' => $dateTo ?? '',
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

            $viewPath = auth()->user()->role === 'kasir' ? 'Kasir' : 'Admin';
            return Inertia::render("{$viewPath}/Reports/StudentTransactions", [
                'transactions' => new \Illuminate\Pagination\LengthAwarePaginator([], 0, 50),
                'classes' => [],
                'students' => [],
                'filters' => [
                    'date_from' => '',
                    'date_to' => '',
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
        $dateFrom = $request->date_from;
        $dateTo = $request->date_to;

        $filename = 'laporan-transaksi-siswa-' . ($dateFrom ? $dateFrom : 'all') . '-to-' . ($dateTo ? $dateTo : 'all') . '.xlsx';

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

            $query = StockAdjustment::with(['product.category', 'adjustedBy', 'creator', 'updater']);

            // Remove default date range
            $dateFrom = $request->date_from;
            $dateTo = $request->date_to;

            if ($dateFrom && $dateTo) {
                $query->whereBetween('created_at', [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59']);
            } elseif ($dateFrom) {
                $query->where('created_at', '>=', $dateFrom . ' 00:00:00');
            } elseif ($dateTo) {
                $query->where('created_at', '<=', $dateTo . ' 23:59:59');
            }

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

            // Filter by client name
            if ($request->has('client_name') && $request->client_name) {
                $query->where('client_name', 'like', '%' . $request->client_name . '%');
            }

            $adjustments = $query->latest()->paginate(20);

            // Add financial calculations to each adjustment based on purpose
            $adjustments->getCollection()->transform(function ($adjustment) {
                // Append photo_url for adjustedBy user (only when needed)
                if ($adjustment->adjustedBy) {
                    $adjustment->adjustedBy->append('photo_url');
                }

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
            $allAdjustmentsQuery = StockAdjustment::with('product');

            if ($dateFrom && $dateTo) {
                $allAdjustmentsQuery->whereBetween('created_at', [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59']);
            } elseif ($dateFrom) {
                $allAdjustmentsQuery->where('created_at', '>=', $dateFrom . ' 00:00:00');
            } elseif ($dateTo) {
                $allAdjustmentsQuery->where('created_at', '<=', $dateTo . ' 23:59:59');
            }

            $allAdjustments = $allAdjustmentsQuery->get();

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

                    switch ($adjustment->purpose) {
                        case 'sale':
                            $revenue = $quantity * $hargaJual;
                            $profitLossImpact = ($quantity * $hargaJual) - ($quantity * $hargaBeli);
                            $salesRevenue += $revenue;
                            $salesProfit += $profitLossImpact;
                            break;
                        
                        case 'return_to_supplier':
                            $revenue = $quantity * $hargaBeli;
                            $profitLossImpact = 0;
                            $returnRefund += $revenue;
                            break;

                        case 'damage':
                        case 'expired':
                        case 'loss':
                        case 'internal_use':
                        case 'personal_use':
                            $revenue = 0;
                            $profitLossImpact = -($quantity * $hargaBeli);
                            $nonRevenueLoss += $costImpact;
                            break;

                        case 'return': // Customer return
                            $revenue = -($quantity * $hargaJual);
                            $profitLossImpact = -(($quantity * $hargaJual) - ($quantity * $hargaBeli));
                            break;
                        
                        case 'correction':
                            if ($adjustment->type === 'addition') {
                                $revenue = 0;
                                $profitLossImpact = $quantity * $hargaBeli; // Gain
                            } else {
                                $revenue = 0;
                                $profitLossImpact = -($quantity * $hargaBeli); // Loss
                            }
                            break;

                        case 'restock':
                            $revenue = 0;
                            $profitLossImpact = 0;
                            break;

                        default:
                            if ($adjustment->type === 'deduction') {
                                $revenue = 0;
                                $profitLossImpact = -($quantity * $hargaBeli);
                            } else {
                                $revenue = 0;
                                $profitLossImpact = 0;
                            }
                            break;
                    }

                    if ($adjustment->type === 'addition') {
                        $additionsCostImpact += $costImpact;
                        $additionsProfitImpact += max(0, $profitLossImpact);
                        $totalCostImpact += $costImpact;
                    } else {
                        $deductionsCostImpact += $costImpact;
                        // For losses, profitLossImpact is negative. We want to track the magnitude of loss.
                        if ($profitLossImpact < 0) {
                            $deductionsLossImpact += $profitLossImpact;
                        }
                        $totalCostImpact -= $costImpact;
                    }

                    $totalProfitLossImpact += $profitLossImpact;
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

            $viewPath = auth()->user()->role === 'kasir' ? 'Kasir' : 'Admin';
            return Inertia::render("{$viewPath}/Reports/StockAdjustments", [
                'adjustments' => $adjustments,
                'summary' => $summary,
                'products' => $products,
                'adjusters' => $adjusters,
                'filters' => [
                    'date_from' => $dateFrom ?? '',
                    'date_to' => $dateTo ?? '',
                    'product_id' => $request->product_id ?? '',
                    'type' => $request->type ?? '',
                    'adjusted_by' => $request->adjusted_by ?? '',
                    'search' => $request->search ?? '',
                    'client_name' => $request->client_name ?? '',
                ],
            ]);

        } catch (\Exception $e) {
            \Log::error('Stock adjustments report error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'user_id' => auth()->id()
            ]);

            $viewPath = auth()->user()->role === 'kasir' ? 'Kasir' : 'Admin';
            return Inertia::render("{$viewPath}/Reports/StockAdjustments", [
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
                    'date_from' => '',
                    'date_to' => '',
                    'product_id' => '',
                    'type' => '',
                    'adjusted_by' => '',
                    'search' => '',
                    'client_name' => '',
                ],
                'error' => 'Gagal memuat laporan: ' . $e->getMessage()
            ]);
        }
    }

    public function stockAdjustmentsExport(Request $request)
    {
        $dateFrom = $request->date_from;
        $dateTo = $request->date_to;

        $filename = 'laporan-penyesuaian-stok-' . ($dateFrom ? $dateFrom : 'all') . '-to-' . ($dateTo ? $dateTo : 'all') . '.xlsx';

        return Excel::download(
            new StockAdjustmentsExport($dateFrom, $dateTo, $request->product_id, $request->type, $request->adjusted_by, $request->search, $request->client_name),
            $filename
        );
    }
}
