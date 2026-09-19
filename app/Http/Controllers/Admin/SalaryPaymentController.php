<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSalaryPaymentRequest;
use App\Http\Requests\UpdateSalaryPaymentRequest;
use App\Models\Role;
use App\Models\SalaryPayment;
use App\Models\Staff;
use App\Services\SalaryService;
use Illuminate\Http\Request;

class SalaryPaymentController extends Controller
{
    public function __construct(private SalaryService $service) {}

    public function index(Request $request)
    {
        abort_unless($request->user()->hasPermission('salary.view'),403);
        $year=(int)$request->input('year',now()->year); $month=(int)$request->input('month',now()->month);
        $payments=SalaryPayment::with('staff')->when($request->filled('q'),fn($q)=>$q->whereHas('staff',fn($s)=>$s->where('first_name','like','%'.$request->q.'%')->orWhere('last_name','like','%'.$request->q.'%')->orWhere('employee_code','like','%'.$request->q.'%')))->where('salary_year',$year)->where('salary_month',$month)->latest('id')->paginate(20)->withQueryString();
        $stats=['staff'=>Staff::where('status','active')->count(),'paid'=>SalaryPayment::where('salary_year',$year)->where('salary_month',$month)->where('status','paid')->sum('net_salary'),'draft'=>SalaryPayment::where('salary_year',$year)->where('salary_month',$month)->where('status','draft')->count(),'count'=>SalaryPayment::where('salary_year',$year)->where('salary_month',$month)->count()];
        return view('admin.salary.index',compact('payments','year','month','stats'));
    }

    public function create(Request $request)
    {
        abort_unless($request->user()->hasPermission('salary.create'),403);
        $staff=Staff::where('status','active')->orderBy('first_name')->get();
        return view('admin.salary.create',compact('staff'));
    }

    public function store(StoreSalaryPaymentRequest $request)
    {
        abort_unless($request->user()->hasPermission('salary.create'),403);
        $data=$request->validated();
        [$data['salary_year'],$data['salary_month']]=array_map('intval',explode('-', $data['salary_period']));
        unset($data['salary_period']);
        $salary=$this->service->create($data,$request->user()->id);
        return redirect()->route('admin.salary.show',$salary)->with('success','Salary calculation prepared. Review and mark it paid when ready.');
    }

    public function show(Request $request, SalaryPayment $salary)
    {
        abort_unless($request->user()->hasPermission('salary.view'),403);
        $salary->load(['staff','paidBy']);
        return view('admin.salary.show',compact('salary'));
    }

    public function edit(Request $request, SalaryPayment $salary)
    {
        abort_unless($request->user()->hasPermission('salary.edit'),403);
        $salary->load('staff');
        return view('admin.salary.edit',compact('salary'));
    }

    public function update(UpdateSalaryPaymentRequest $request, SalaryPayment $salary)
    {
        $salary=$this->service->update($salary,$request->validated());
        return redirect()->route('admin.salary.show',$salary)->with('success','Salary calculation updated.');
    }

    public function pay(Request $request, SalaryPayment $salary)
    {
        abort_unless($request->user()->hasPermission('salary.pay'),403);
        $salary=$this->service->markPaid($salary,$request->user()->id);
        return redirect()->route('admin.salary.show',$salary)->with('success','Salary marked as paid.');
    }

    public function cancel(Request $request, SalaryPayment $salary)
    {
        abort_unless($request->user()->hasPermission('salary.cancel'),403);
        $salary=$this->service->cancel($salary);
        return redirect()->route('admin.salary.show',$salary)->with('success','Salary draft cancelled.');
    }
}
