<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreIncomeRequest;
use App\Http\Requests\UpdateIncomeRequest;
use App\Http\Requests\StoreExpenseRequest;
use App\Http\Requests\UpdateExpenseRequest;
use App\Http\Requests\CancelIncomeExpenseRequest;
use App\Models\Income;
use App\Models\Expense;
use App\Models\IncomeCategory;
use App\Models\ExpenseCategory;
use App\Models\Customer;
use App\Models\Supplier;
use App\Services\IncomeExpenseService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class IncomeExpenseController extends Controller
{
    public function __construct(private IncomeExpenseService $service) {}
    private function data(): array { return ['incomeCategories'=>IncomeCategory::where('status','active')->orderBy('sort_order')->orderBy('name')->get(),'expenseCategories'=>ExpenseCategory::where('status','active')->orderBy('sort_order')->orderBy('name')->get(),'customers'=>Customer::orderBy('business_name')->get(['id','business_name']),'suppliers'=>Supplier::orderBy('business_name')->get(['id','business_name'])]; }
    public function index(Request $request): View {
        $tab=$request->get('tab','all'); $search=trim((string)$request->get('search')); $from=$request->get('from'); $to=$request->get('to');
        $in=Income::with(['category','customer'])->when($search,fn($q)=>$q->where(fn($x)=>$x->where('income_number','like',"%$search%")->orWhere('description','like',"%$search%")))->when($from,fn($q)=>$q->whereDate('income_date','>=',$from))->when($to,fn($q)=>$q->whereDate('income_date','<=',$to))->latest('income_date')->get();
        $ex=Expense::with(['category','supplier'])->when($search,fn($q)=>$q->where(fn($x)=>$x->where('expense_number','like',"%$search%")->orWhere('description','like',"%$search%")))->when($from,fn($q)=>$q->whereDate('expense_date','>=',$from))->when($to,fn($q)=>$q->whereDate('expense_date','<=',$to))->latest('expense_date')->get();
        $kpi=$this->service->dashboard(); return view('admin.income-expenses.index',compact('in','ex','kpi','tab','search','from','to'));
    }
    public function createIncome(): View { return view('admin.income-expenses.create-income', $this->data()); }
    public function storeIncome(StoreIncomeRequest $request) { $income=$this->service->createIncome($request->validated(),$request->user()); return redirect()->route('admin.income-expenses.show-income',$income)->with('success','Income recorded successfully.'); }
    public function showIncome(Income $income): View { $income->load(['category','customer','invoice','order','payment','creator']); return view('admin.income-expenses.show-income',compact('income')); }
    public function editIncome(Income $income): View { $income->load('category'); return view('admin.income-expenses.edit-income',array_merge($this->data(),compact('income'))); }
    public function updateIncome(UpdateIncomeRequest $request, Income $income) { $this->service->updateIncome($income,$request->validated(),$request->user()); return redirect()->route('admin.income-expenses.show-income',$income)->with('success','Income updated.'); }
    public function cancelIncome(CancelIncomeExpenseRequest $request, Income $income) { abort_unless($request->user()->hasPermission('income.cancel'),403); $this->service->cancelIncome($income,$request->validated()['reason'],$request->user()); return back()->with('success','Income cancelled.'); }
    public function createExpense(): View { return view('admin.income-expenses.create-expense',$this->data()); }
    public function storeExpense(StoreExpenseRequest $request) { $expense=$this->service->createExpense($request->validated(),$request->user()); return redirect()->route('admin.income-expenses.show-expense',$expense)->with('success','Expense recorded successfully.'); }
    public function showExpense(Expense $expense): View { $expense->load(['category','supplier','purchase','payment','creator']); return view('admin.income-expenses.show-expense',compact('expense')); }
    public function editExpense(Expense $expense): View { $expense->load('category'); return view('admin.income-expenses.edit-expense',array_merge($this->data(),compact('expense'))); }
    public function updateExpense(UpdateExpenseRequest $request, Expense $expense) { $this->service->updateExpense($expense,$request->validated(),$request->user()); return redirect()->route('admin.income-expenses.show-expense',$expense)->with('success','Expense updated.'); }
    public function cancelExpense(CancelIncomeExpenseRequest $request, Expense $expense) { abort_unless($request->user()->hasPermission('expenses.cancel'),403); $this->service->cancelExpense($expense,$request->validated()['reason'],$request->user()); return back()->with('success','Expense cancelled.'); }
}
