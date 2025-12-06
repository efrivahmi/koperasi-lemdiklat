<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\Transaction;
use App\Models\Sale;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;

class StudentPortalController extends Controller
{
    public function dashboard()
    {
        $user = auth()->user();

        // Get student record
        $student = Student::where('user_id', $user->id)->first();

        if (!$student) {
            return redirect()->route('dashboard')->with('error', 'Student profile not found.');
        }

        // Get student statistics
        $totalTransactions = Transaction::where('student_id', $student->id)->count();
        $totalSpent = Sale::where('student_id', $student->id)->sum('total');

        // Recent transactions (last 10)
        $recentTransactions = Transaction::with(['student.user'])
            ->where('student_id', $student->id)
            ->latest()
            ->limit(10)
            ->get();

        // Monthly spending (last 30 days)
        $monthlySpending = Sale::where('student_id', $student->id)
            ->where('created_at', '>=', Carbon::now()->subDays(30))
            ->sum('total');

        // Transaction chart data (last 7 days)
        $transactionChart = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $dayName = $date->isoFormat('ddd, DD MMM');
            $daySpent = Sale::where('student_id', $student->id)
                ->whereDate('created_at', $date)
                ->sum('total');
            $transactionChart[] = [
                'date' => $dayName,
                'spent' => (float) $daySpent,
            ];
        }

        return Inertia::render('Student/Dashboard', [
            'student' => $student->load('user'),
            'stats' => [
                'balance' => $student->balance,
                'totalTransactions' => $totalTransactions,
                'totalSpent' => $totalSpent,
                'monthlySpending' => $monthlySpending,
            ],
            'recentTransactions' => $recentTransactions,
            'transactionChart' => $transactionChart,
        ]);
    }

    public function transactions(Request $request)
    {
        $user = auth()->user();
        $student = Student::where('user_id', $user->id)->first();

        if (!$student) {
            return redirect()->route('dashboard')->with('error', 'Student profile not found.');
        }

        $query = Transaction::with(['student.user'])
            ->where('student_id', $student->id);

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

        return Inertia::render('Student/Transactions', [
            'student' => $student->load('user'),
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
        $student = Student::where('user_id', $user->id)->first();

        if (!$student) {
            return redirect()->route('dashboard')->with('error', 'Student profile not found.');
        }

        $query = Sale::with(['student.user', 'saleItems.product'])
            ->where('student_id', $student->id);

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

        return Inertia::render('Student/PurchaseHistory', [
            'student' => $student->load('user'),
            'purchases' => $purchases,
            'filters' => [
                'date_from' => $request->date_from,
                'date_to' => $request->date_to,
                'payment_method' => $request->payment_method,
            ],
        ]);
    }
}
