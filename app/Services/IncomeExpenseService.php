<?php
namespace App\Services;

use App\Models\Income;
use App\Models\Expense;
use App\Models\IncomeCategory;
use App\Models\ExpenseCategory;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class IncomeExpenseService
{
    public function createIncome(array $data, $user): Income {
        return DB::transaction(function() use ($data,$user){
            $data['income_number'] = $this->nextNumber('income'); $data['created_by']=$user?->id; $data['status']='posted';
            return Income::create($data);
        });
    }
    public function updateIncome(Income $income, array $data, $user): Income {
        if ($income->status === 'cancelled') throw ValidationException::withMessages(['income'=>'Cancelled income cannot be edited.']);
        return DB::transaction(function() use ($income,$data,$user){ $data['updated_by']=$user?->id; $income->update($data); return $income->refresh(); });
    }
    public function createExpense(array $data, $user): Expense {
        return DB::transaction(function() use ($data,$user){
            $data['expense_number'] = $this->nextNumber('expense'); $data['created_by']=$user?->id; $data['status']='posted';
            return Expense::create($data);
        });
    }
    public function updateExpense(Expense $expense, array $data, $user): Expense {
        if ($expense->status === 'cancelled') throw ValidationException::withMessages(['expense'=>'Cancelled expense cannot be edited.']);
        return DB::transaction(function() use ($expense,$data,$user){ $data['updated_by']=$user?->id; $expense->update($data); return $expense->refresh(); });
    }
    public function cancelIncome(Income $income, string $reason, $user): Income {
        return DB::transaction(function() use($income,$reason,$user){ if($income->status==='cancelled') return $income; $income->update(['status'=>'cancelled','cancelled_by'=>$user?->id,'cancelled_at'=>now(),'cancellation_reason'=>$reason]); return $income->refresh(); });
    }
    public function cancelExpense(Expense $expense, string $reason, $user): Expense {
        return DB::transaction(function() use($expense,$reason,$user){ if($expense->status==='cancelled') return $expense; $expense->update(['status'=>'cancelled','cancelled_by'=>$user?->id,'cancelled_at'=>now(),'cancellation_reason'=>$reason]); return $expense->refresh(); });
    }
    public function nextNumber(string $type): string {
        return DB::transaction(function() use($type){
            $key=$type.'_'.now()->format('Y');
            $seq=DB::table('finance_sequences')->where('sequence_key',$key)->lockForUpdate()->first();
            if(!$seq){ DB::table('finance_sequences')->insert(['sequence_key'=>$key,'current_number'=>1,'created_at'=>now(),'updated_at'=>now()]); $n=1; }
            else { $n=$seq->current_number+1; DB::table('finance_sequences')->where('id',$seq->id)->update(['current_number'=>$n,'updated_at'=>now()]); }
            return strtoupper($type==='income'?'INC':'EXP').'-'.now()->format('Y').'-'.str_pad((string)$n,6,'0',STR_PAD_LEFT);
        });
    }
    public function dashboard(): array {
        $start=now()->startOfMonth(); $end=now()->endOfMonth();
        $income=(float)Income::where('status','posted')->whereBetween('income_date',[$start->toDateString(),$end->toDateString()])->sum('amount');
        $expense=(float)Expense::where('status','posted')->whereBetween('expense_date',[$start->toDateString(),$end->toDateString()])->sum('amount');
        return ['income'=>$income,'expense'=>$expense,'net'=>$income-$expense];
    }
}
