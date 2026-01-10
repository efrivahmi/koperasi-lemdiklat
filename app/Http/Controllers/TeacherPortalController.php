<?php

namespace App\Http\Controllers;

use App\Models\Teacher;
use App\Models\Transaction;
use App\Models\Sale;
use App\Models\Saving;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;

class TeacherPortalController extends Controller
{
    public function dashboard()
    {
        $user = auth()->user();

        // Get teacher record
        $teacher = Teacher::where('user_id', $user->id)->first();

        if (!$teacher) {
            return redirect()->route('dashboard')->with('error', 'Teacher profile not found.');
        }

        // Get teacher statistics using polymorphic relations
        $totalTransactions = Transaction::where('buyer_type', 'App\Models\Teacher')
            ->where('buyer_id', $teacher->id)
            ->count();

        $totalSpent = Sale::where('buyer_type', 'App\Models\Teacher')
            ->where('buyer_id', $teacher->id)
            ->sum('total');

        $totalSavings = Saving::where('saver_type', 'App\Models\Teacher')
            ->where('saver_id', $teacher->id)
            ->where('type', 'deposit')
            ->sum('amount');

        // Recent transactions (last 10)
        $recentTransactions = Transaction::with(['buyer'])
            ->where('buyer_type', 'App\Models\Teacher')
            ->where('buyer_id', $teacher->id)
            ->latest()
            ->limit(10)
            ->get();

        // Monthly spending (last 30 days)
        $monthlySpending = Sale::where('buyer_type', 'App\Models\Teacher')
            ->where('buyer_id', $teacher->id)
            ->where('created_at', '>=', Carbon::now()->subDays(30))
            ->sum('total');

        // Transaction chart data (last 7 days)
        $transactionChart = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $dayName = $date->isoFormat('ddd, DD MMM');
            $daySpent = Sale::where('buyer_type', 'App\Models\Teacher')
                ->where('buyer_id', $teacher->id)
                ->whereDate('created_at', $date)
                ->sum('total');
            $transactionChart[] = [
                'date' => $dayName,
                'spent' => (float) $daySpent,
            ];
        }

        return Inertia::render('Teacher/Dashboard', [
            'teacher' => $teacher->load('user'),
            'stats' => [
                'balance' => $teacher->balance,
                'totalTransactions' => $totalTransactions,
                'totalSpent' => $totalSpent,
                'totalSavings' => $totalSavings,
                'monthlySpending' => $monthlySpending,
            ],
            'recentTransactions' => $recentTransactions,
            'transactionChart' => $transactionChart,
        ]);
    }

    public function transactions(Request $request)
    {
        $user = auth()->user();
        $teacher = Teacher::where('user_id', $user->id)->first();

        if (!$teacher) {
            return redirect()->route('dashboard')->with('error', 'Teacher profile not found.');
        }

        $query = Transaction::with(['buyer'])
            ->where('buyer_type', 'App\Models\Teacher')
            ->where('buyer_id', $teacher->id);

        // Filter by date range if provided
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Filter by transaction type
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        $transactions = $query->latest()->paginate(20);

        return Inertia::render('Teacher/Transactions', [
            'teacher' => $teacher->load('user'),
            'transactions' => $transactions,
            'filters' => [
                'date_from' => $request->date_from,
                'date_to' => $request->date_to,
                'type' => $request->type,
            ],
        ]);
    }

    public function purchaseHistory(Request $request)
    {
        $user = auth()->user();
        $teacher = Teacher::where('user_id', $user->id)->first();

        if (!$teacher) {
            return redirect()->route('dashboard')->with('error', 'Teacher profile not found.');
        }

        $query = Sale::with(['buyer', 'saleItems.product'])
            ->where('buyer_type', 'App\Models\Teacher')
            ->where('buyer_id', $teacher->id);

        // Filter by date range if provided
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

        $purchases = $query->latest()->paginate(20);

        return Inertia::render('Teacher/PurchaseHistory', [
            'teacher' => $teacher->load('user'),
            'purchases' => $purchases,
            'filters' => [
                'date_from' => $request->date_from,
                'date_to' => $request->date_to,
                'payment_method' => $request->payment_method,
            ],
        ]);
    }

    public function savings(Request $request)
    {
        $user = auth()->user();
        $teacher = Teacher::where('user_id', $user->id)->first();

        if (!$teacher) {
            return redirect()->route('dashboard')->with('error', 'Teacher profile not found.');
        }

        $query = Saving::with(['saver', 'processedBy'])
            ->where('saver_type', 'App\Models\Teacher')
            ->where('saver_id', $teacher->id);

        // Filter by date range if provided
        if ($request->filled('date_from')) {
            $query->whereDate('transaction_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('transaction_date', '<=', $request->date_to);
        }

        // Filter by type (deposit/withdrawal)
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        $savings = $query->latest('transaction_date')->paginate(20);

        // Calculate totals
        $totalDeposits = Saving::where('saver_type', 'App\Models\Teacher')
            ->where('saver_id', $teacher->id)
            ->where('type', 'deposit')
            ->sum('amount');

        $totalWithdrawals = Saving::where('saver_type', 'App\Models\Teacher')
            ->where('saver_id', $teacher->id)
            ->where('type', 'withdrawal')
            ->sum('amount');

        return Inertia::render('Teacher/Savings', [
            'teacher' => $teacher->load('user'),
            'savings' => $savings,
            'stats' => [
                'totalDeposits' => $totalDeposits,
                'totalWithdrawals' => $totalWithdrawals,
                'netSavings' => $totalDeposits - $totalWithdrawals,
            ],
            'filters' => [
                'date_from' => $request->date_from,
                'date_to' => $request->date_to,
                'type' => $request->type,
            ],
        ]);
    }
}
