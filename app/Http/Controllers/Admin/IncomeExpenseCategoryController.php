<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreIncomeExpenseCategoryRequest;
use App\Http\Requests\UpdateIncomeExpenseCategoryRequest;
use App\Models\ExpenseCategory;
use App\Models\IncomeCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class IncomeExpenseCategoryController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorizePermission($request, 'view');

        $search = trim((string) $request->get('search'));
        $status = $request->get('status');

        $incomeCategories = IncomeCategory::withCount('incomes')
            ->when($search, fn ($q) => $q->where(function ($x) use ($search) {
                $x->where('name', 'like', "%{$search}%")
                  ->orWhere('slug', 'like', "%{$search}%");
            }))
            ->when($status, fn ($q) => $q->where('status', $status))
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $expenseCategories = ExpenseCategory::withCount('expenses')
            ->when($search, fn ($q) => $q->where(function ($x) use ($search) {
                $x->where('name', 'like', "%{$search}%")
                  ->orWhere('slug', 'like', "%{$search}%");
            }))
            ->when($status, fn ($q) => $q->where('status', $status))
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return view('admin.income-expenses.categories.index', compact(
            'incomeCategories',
            'expenseCategories',
            'search',
            'status'
        ));
    }

    public function createIncome(Request $request): View
    {
        $this->authorizePermission($request, 'create');

        return view('admin.income-expenses.categories.create', [
            'type' => 'income',
            'title' => 'Add Income Category',
            'category' => null,
        ]);
    }

    public function storeIncome(StoreIncomeExpenseCategoryRequest $request): RedirectResponse
    {
        $this->authorizePermission($request, 'create');

        IncomeCategory::create([
            'name' => $request->validated('name'),
            'slug' => $request->validated('slug'),
            'status' => $request->validated('status'),
            'sort_order' => $request->validated('sort_order', 0),
        ]);

        return redirect()->route('admin.income-expenses.categories.index')
            ->with('success', 'Income category created successfully.');
    }

    public function editIncome(Request $request, IncomeCategory $category): View
    {
        $this->authorizePermission($request, 'edit');

        return view('admin.income-expenses.categories.edit', [
            'type' => 'income',
            'title' => 'Edit Income Category',
            'category' => $category,
        ]);
    }

    public function updateIncome(UpdateIncomeExpenseCategoryRequest $request, IncomeCategory $category): RedirectResponse
    {
        $this->authorizePermission($request, 'edit');

        $category->update([
            'name' => $request->validated('name'),
            'slug' => $request->validated('slug'),
            'status' => $request->validated('status'),
            'sort_order' => $request->validated('sort_order', 0),
        ]);

        return redirect()->route('admin.income-expenses.categories.index')
            ->with('success', 'Income category updated successfully.');
    }

    public function toggleIncome(Request $request, IncomeCategory $category): RedirectResponse
    {
        $this->authorizePermission($request, 'edit');

        $category->update(['status' => $category->status === 'active' ? 'inactive' : 'active']);

        return back()->with('success', 'Income category status updated.');
    }

    public function createExpense(Request $request): View
    {
        $this->authorizePermission($request, 'create');

        return view('admin.income-expenses.categories.create', [
            'type' => 'expense',
            'title' => 'Add Expense Category',
            'category' => null,
        ]);
    }

    public function storeExpense(StoreIncomeExpenseCategoryRequest $request): RedirectResponse
    {
        $this->authorizePermission($request, 'create');

        ExpenseCategory::create([
            'name' => $request->validated('name'),
            'slug' => $request->validated('slug'),
            'status' => $request->validated('status'),
            'sort_order' => $request->validated('sort_order', 0),
        ]);

        return redirect()->route('admin.income-expenses.categories.index')
            ->with('success', 'Expense category created successfully.');
    }

    public function editExpense(Request $request, ExpenseCategory $category): View
    {
        $this->authorizePermission($request, 'edit');

        return view('admin.income-expenses.categories.edit', [
            'type' => 'expense',
            'title' => 'Edit Expense Category',
            'category' => $category,
        ]);
    }

    public function updateExpense(UpdateIncomeExpenseCategoryRequest $request, ExpenseCategory $category): RedirectResponse
    {
        $this->authorizePermission($request, 'edit');

        $category->update([
            'name' => $request->validated('name'),
            'slug' => $request->validated('slug'),
            'status' => $request->validated('status'),
            'sort_order' => $request->validated('sort_order', 0),
        ]);

        return redirect()->route('admin.income-expenses.categories.index')
            ->with('success', 'Expense category updated successfully.');
    }

    public function toggleExpense(Request $request, ExpenseCategory $category): RedirectResponse
    {
        $this->authorizePermission($request, 'edit');

        $category->update(['status' => $category->status === 'active' ? 'inactive' : 'active']);

        return back()->with('success', 'Expense category status updated.');
    }

    private function authorizePermission(Request $request, string $action): void
    {
        abort_unless($request->user() && $request->user()->hasPermission('finance.categories'), 403);
    }
}
