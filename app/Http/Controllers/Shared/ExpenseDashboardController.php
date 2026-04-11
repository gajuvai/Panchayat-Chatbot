<?php

namespace App\Http\Controllers\Shared;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ExpenseDashboardController extends Controller
{
    public function index(Request $request): View
    {
        $year = (int) $request->get('year', now()->year);

        $monthlyTotals   = Expense::monthlyTotals($year);
        $categoryTotals  = Expense::categoryTotals($year);
        $yearTotal       = array_sum($monthlyTotals);

        // Recent 10 expenses for the activity feed
        $recentExpenses = Expense::with('loggedBy')
            ->forYear($year)
            ->latest('expense_date')
            ->limit(10)
            ->get();

        // Available years that have data
        $availableYears = Expense::selectRaw('YEAR(expense_date) as year')
            ->distinct()
            ->orderByDesc('year')
            ->pluck('year')
            ->toArray();

        if (!in_array(now()->year, $availableYears)) {
            array_unshift($availableYears, now()->year);
        }

        return view('shared.expenses.index', compact(
            'year', 'monthlyTotals', 'categoryTotals', 'yearTotal',
            'recentExpenses', 'availableYears'
        ));
    }
}
