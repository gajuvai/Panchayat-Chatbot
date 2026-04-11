<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class AdminExpenseController extends Controller
{
    public function index(Request $request): View
    {
        $query = Expense::with('loggedBy')->latest('expense_date');

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }
        if ($request->filled('month')) {
            [$y, $m] = explode('-', $request->month);
            $query->whereYear('expense_date', $y)->whereMonth('expense_date', $m);
        }
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(fn ($q) => $q->where('title', 'like', "%{$s}%")
                                       ->orWhere('vendor', 'like', "%{$s}%")
                                       ->orWhere('invoice_number', 'like', "%{$s}%"));
        }

        $expenses = $query->paginate(15)->withQueryString();

        $totalFiltered = $query->sum('amount');

        return view('admin.expenses.index', compact('expenses', 'totalFiltered'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'title'          => ['required', 'string', 'max:255'],
            'description'    => ['nullable', 'string'],
            'category'       => ['required', 'in:' . implode(',', Expense::categories())],
            'amount'         => ['required', 'numeric', 'min:0.01'],
            'expense_date'   => ['required', 'date', 'before_or_equal:today'],
            'vendor'         => ['nullable', 'string', 'max:100'],
            'invoice_number' => ['nullable', 'string', 'max:50'],
            'is_recurring'   => ['boolean'],
            'receipt'        => ['nullable', 'file', 'max:10240', 'mimes:jpg,jpeg,png,pdf'],
        ]);

        $data['user_id']      = $request->user()->id;
        $data['is_recurring'] = $request->boolean('is_recurring');

        $expense = Expense::create($data);

        if ($request->hasFile('receipt')) {
            $path = $request->file('receipt')->store("expenses/{$expense->id}", 'public');
            $expense->update(['receipt_path' => $path]);
        }

        return redirect()->route('admin.expenses.index')
            ->with('success', 'Expense logged: Rs. ' . number_format($data['amount'], 2));
    }

    public function update(Request $request, Expense $expense): RedirectResponse
    {
        $data = $request->validate([
            'title'          => ['required', 'string', 'max:255'],
            'description'    => ['nullable', 'string'],
            'category'       => ['required', 'in:' . implode(',', Expense::categories())],
            'amount'         => ['required', 'numeric', 'min:0.01'],
            'expense_date'   => ['required', 'date', 'before_or_equal:today'],
            'vendor'         => ['nullable', 'string', 'max:100'],
            'invoice_number' => ['nullable', 'string', 'max:50'],
            'is_recurring'   => ['boolean'],
            'receipt'        => ['nullable', 'file', 'max:10240', 'mimes:jpg,jpeg,png,pdf'],
        ]);

        $data['is_recurring'] = $request->boolean('is_recurring');

        if ($request->hasFile('receipt')) {
            if ($expense->receipt_path) Storage::disk('public')->delete($expense->receipt_path);
            $data['receipt_path'] = $request->file('receipt')->store("expenses/{$expense->id}", 'public');
        }

        unset($data['receipt']);
        $expense->update($data);

        return redirect()->route('admin.expenses.index')
            ->with('success', 'Expense updated.');
    }

    public function destroy(Expense $expense): RedirectResponse
    {
        if ($expense->receipt_path) Storage::disk('public')->delete($expense->receipt_path);
        $expense->delete();

        return redirect()->route('admin.expenses.index')
            ->with('success', 'Expense deleted.');
    }

    public function export(Request $request): Response
    {
        $year = $request->get('year', now()->year);

        $expenses = Expense::with('loggedBy')
            ->whereYear('expense_date', $year)
            ->orderBy('expense_date')
            ->get();

        $csv = "Date,Title,Category,Amount (Rs.),Vendor,Invoice,Logged By\n";

        foreach ($expenses as $e) {
            $csv .= implode(',', [
                $e->expense_date->format('d M Y'),
                '"' . str_replace('"', '""', $e->title) . '"',
                '"' . $e->category_label . '"',
                number_format($e->amount, 2),
                '"' . ($e->vendor ?? '') . '"',
                '"' . ($e->invoice_number ?? '') . '"',
                '"' . $e->loggedBy->name . '"',
            ]) . "\n";
        }

        return response($csv, 200, [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"expenses-{$year}.csv\"",
        ]);
    }
}
