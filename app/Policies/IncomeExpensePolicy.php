<?php
namespace App\Policies;
use App\Models\Income;
use App\Models\Expense;
class IncomeExpensePolicy {
    private function ok($user,$p): bool { return $user?->hasPermission($p) ?? false; }
    public function viewIncome($user): bool { return $this->ok($user,'income.view'); }
    public function createIncome($user): bool { return $this->ok($user,'income.create'); }
    public function updateIncome($user): bool { return $this->ok($user,'income.edit'); }
    public function cancelIncome($user): bool { return $this->ok($user,'income.cancel'); }
    public function viewExpense($user): bool { return $this->ok($user,'expenses.view'); }
    public function createExpense($user): bool { return $this->ok($user,'expenses.create'); }
    public function updateExpense($user): bool { return $this->ok($user,'expenses.edit'); }
    public function cancelExpense($user): bool { return $this->ok($user,'expenses.cancel'); }
}
