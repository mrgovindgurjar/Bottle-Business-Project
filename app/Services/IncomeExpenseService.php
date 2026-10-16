<?php

namespace App\Services;

use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Income;
use App\Models\IncomeCategory;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class IncomeExpenseService
{
    public function createIncome(array $data, $user): Income
    {
        return DB::transaction(function () use ($data, $user) {
            $this->validateIncomeLinks($data);
            $this->ensureActiveIncomeCategory((int) $data['category_id']);
            $data['income_number'] = $this->nextNumber('income');
            $data['created_by'] = $user?->id;
            $data['status'] = 'posted';
            return Income::create($data);
        });
    }

    public function updateIncome(Income $income, array $data, $user): Income
    {
        return DB::transaction(function () use ($income, $data, $user) {
            $income = Income::lockForUpdate()->findOrFail($income->id);
            if ($income->status === 'cancelled') {
                throw ValidationException::withMessages(['income' => 'Cancelled income cannot be edited.']);
            }
            $this->validateIncomeLinks($data, $income->id);
            $this->ensureActiveIncomeCategory((int) $data['category_id']);
            $data['updated_by'] = $user?->id;
            unset($data['income_number'], $data['created_by'], $data['status']);
            $income->update($data);
            return $income->refresh();
        });
    }

    public function createExpense(array $data, $user): Expense
    {
        return DB::transaction(function () use ($data, $user) {
            $this->validateExpenseLinks($data);
            $this->ensureActiveExpenseCategory((int) $data['category_id']);
            $data['expense_number'] = $this->nextNumber('expense');
            $data['created_by'] = $user?->id;
            $data['status'] = 'posted';
            return Expense::create($data);
        });
    }

    public function updateExpense(Expense $expense, array $data, $user): Expense
    {
        return DB::transaction(function () use ($expense, $data, $user) {
            $expense = Expense::lockForUpdate()->findOrFail($expense->id);
            if ($expense->status === 'cancelled') {
                throw ValidationException::withMessages(['expense' => 'Cancelled expense cannot be edited.']);
            }
            $this->validateExpenseLinks($data, $expense->id);
            $this->ensureActiveExpenseCategory((int) $data['category_id']);
            $data['updated_by'] = $user?->id;
            unset($data['expense_number'], $data['created_by'], $data['status']);
            $expense->update($data);
            return $expense->refresh();
        });
    }

    public function cancelIncome(Income $income, string $reason, $user): Income
    {
        return DB::transaction(function () use ($income, $reason, $user) {
            $income = Income::lockForUpdate()->findOrFail($income->id);
            if ($income->status === 'cancelled') return $income;
            $income->update(['status' => 'cancelled', 'cancelled_by' => $user?->id, 'cancelled_at' => now(), 'cancellation_reason' => $reason]);
            return $income->refresh();
        });
    }

    public function cancelExpense(Expense $expense, string $reason, $user): Expense
    {
        return DB::transaction(function () use ($expense, $reason, $user) {
            $expense = Expense::lockForUpdate()->findOrFail($expense->id);
            if ($expense->status === 'cancelled') return $expense;
            $expense->update(['status' => 'cancelled', 'cancelled_by' => $user?->id, 'cancelled_at' => now(), 'cancellation_reason' => $reason]);
            return $expense->refresh();
        });
    }

    private function validateIncomeLinks(array $data, ?int $ignoreIncomeId = null): void
    {
        if (!empty($data['payment_id'])) {
            $payment = Payment::lockForUpdate()->findOrFail((int) $data['payment_id']);
            if ($payment->status !== 'completed' || $payment->direction !== 'received') {
                throw ValidationException::withMessages(['payment_id' => 'Only completed received payments can be linked to income.']);
            }
            if ((float) $data['amount'] > (float) $payment->amount + 0.0001) {
                throw ValidationException::withMessages(['amount' => 'Income amount cannot exceed the linked payment amount.']);
            }
            $existing = Income::where('payment_id', $payment->id)
                ->where('status', '!=', 'cancelled')
                ->when($ignoreIncomeId, fn ($q) => $q->where('id', '!=', $ignoreIncomeId))
                ->exists();
            if ($existing) {
                throw ValidationException::withMessages(['payment_id' => 'This payment is already linked to an active income record.']);
            }
        }
    }

    private function validateExpenseLinks(array $data, ?int $ignoreExpenseId = null): void
    {
        if (!empty($data['payment_id'])) {
            $payment = Payment::lockForUpdate()->findOrFail((int) $data['payment_id']);
            if ($payment->status !== 'completed' || $payment->direction !== 'paid') {
                throw ValidationException::withMessages(['payment_id' => 'Only completed paid payments can be linked to expense.']);
            }
            if ((float) $data['amount'] > (float) $payment->amount + 0.0001) {
                throw ValidationException::withMessages(['amount' => 'Expense amount cannot exceed the linked payment amount.']);
            }
            $existing = Expense::where('payment_id', $payment->id)
                ->where('status', '!=', 'cancelled')
                ->when($ignoreExpenseId, fn ($q) => $q->where('id', '!=', $ignoreExpenseId))
                ->exists();
            if ($existing) {
                throw ValidationException::withMessages(['payment_id' => 'This payment is already linked to an active expense record.']);
            }
        }
    }

    private function ensureActiveIncomeCategory(int $id): void
    {
        if (!IncomeCategory::whereKey($id)->where('status', 'active')->exists()) {
            throw ValidationException::withMessages(['category_id' => 'Selected income category is inactive.']);
        }
    }

    private function ensureActiveExpenseCategory(int $id): void
    {
        if (!ExpenseCategory::whereKey($id)->where('status', 'active')->exists()) {
            throw ValidationException::withMessages(['category_id' => 'Selected expense category is inactive.']);
        }
    }

    public function nextNumber(string $type): string
    {
        return DB::transaction(function () use ($type) {
            $key = $type.'_'.now()->format('Y');
            $seq = DB::table('finance_sequences')->where('sequence_key', $key)->lockForUpdate()->first();
            if (!$seq) {
                DB::table('finance_sequences')->insert(['sequence_key' => $key, 'current_number' => 1, 'created_at' => now(), 'updated_at' => now()]);
                $n = 1;
            } else {
                $n = $seq->current_number + 1;
                DB::table('finance_sequences')->where('id', $seq->id)->update(['current_number' => $n, 'updated_at' => now()]);
            }
            return strtoupper($type === 'income' ? 'INC' : 'EXP').'-'.now()->format('Y').'-'.str_pad((string) $n, 6, '0', STR_PAD_LEFT);
        });
    }

    public function dashboard(): array
    {
        $start = now()->startOfMonth();
        $end = now()->endOfMonth();
        $income = (float) Income::where('status', 'posted')->whereBetween('income_date', [$start->toDateString(), $end->toDateString()])->sum('amount');
        $expense = (float) Expense::where('status', 'posted')->whereBetween('expense_date', [$start->toDateString(), $end->toDateString()])->sum('amount');
        return ['income' => $income, 'expense' => $expense, 'net' => $income - $expense];
    }
}
