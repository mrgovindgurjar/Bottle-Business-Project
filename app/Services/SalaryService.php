<?php
namespace App\Services;

use App\Models\SalaryPayment;
use App\Models\Staff;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SalaryService
{
    public function prepare(Staff $staff, int $year, int $month): array
    {
        $start = Carbon::create($year,$month,1)->startOfMonth();
        $end = $start->copy()->endOfMonth();
        $rows = $staff->attendances()->whereBetween('attendance_date',[$start->toDateString(),$end->toDateString()])->get();
        $workingDays = 0;
        for ($d=$start->copy(); $d->lte($end); $d->addDay()) if ($d->dayOfWeek !== Carbon::SUNDAY) $workingDays++;
        $present = $rows->whereIn('status',['present','late'])->count();
        $half = $rows->where('status','half_day')->count();
        $leave = $rows->where('status','leave')->count();
        $absent = $rows->where('status','absent')->count();
        $holiday = $rows->where('status','holiday')->count();
        $payable = min((float)$workingDays, $present + ($half * .5));
        $base = (float)($staff->salary ?? 0);
        $perDay = $workingDays > 0 ? $base / $workingDays : 0;
        return compact('year','month','workingDays','present','half','leave','absent','holiday','payable','base','perDay');
    }

    public function create(array $data, int $userId): SalaryPayment
    {
        return DB::transaction(function () use ($data,$userId) {
            $staff = Staff::lockForUpdate()->findOrFail($data['staff_id']);
            $calc = $this->prepare($staff,(int)$data['salary_year'],(int)$data['salary_month']);
            $paidLeave = min((float)($data['paid_leave_days'] ?? 0), (float)$calc['leave']);
            $payable = array_key_exists('payable_days',$data) && $data['payable_days'] !== null ? min((float)$data['payable_days'],(float)$calc['workingDays']) : $calc['payable'] + $paidLeave;
            $bonus=(float)($data['bonus']??0); $deduction=(float)($data['deduction']??0);
            $earned=round($calc['perDay']*$payable,2); $net=max(0,round($earned+$bonus-$deduction,2));
            return SalaryPayment::updateOrCreate(
                ['staff_id'=>$staff->id,'salary_year'=>$calc['year'],'salary_month'=>$calc['month']],
                ['payment_date'=>$data['payment_date']??null,'base_salary'=>$calc['base'],'working_days'=>$calc['workingDays'],'present_days'=>$calc['present'],'half_days'=>$calc['half'],'leave_days'=>$calc['leave'],'absent_days'=>$calc['absent'],'holiday_days'=>$calc['holiday'],'paid_leave_days'=>$paidLeave,'payable_days'=>$payable,'per_day_salary'=>$calc['perDay'],'attendance_earned'=>$earned,'bonus'=>$bonus,'deduction'=>$deduction,'net_salary'=>$net,'payment_method'=>$data['payment_method']??null,'reference_number'=>$data['reference_number']??null,'status'=>'draft','notes'=>$data['notes']??null,'paid_by'=>null]
            );
        });
    }

    public function update(SalaryPayment $salary, array $data): SalaryPayment
    {
        if ($salary->status === 'paid') throw ValidationException::withMessages(['salary'=>'Paid salary cannot be edited. Create an adjustment/reversal instead.']);
        $staff=$salary->staff; $calc=$this->prepare($staff,$salary->salary_year,$salary->salary_month);
        $paidLeave=min((float)($data['paid_leave_days']??$salary->paid_leave_days),(float)$calc['leave']);
        $payable=array_key_exists('payable_days',$data)&&$data['payable_days']!==null?min((float)$data['payable_days'],(float)$calc['workingDays']):($calc['payable']+$paidLeave);
        $bonus=(float)($data['bonus']??$salary->bonus); $deduction=(float)($data['deduction']??$salary->deduction); $earned=round($calc['perDay']*$payable,2);
        $salary->update(array_merge($data,['working_days'=>$calc['workingDays'],'present_days'=>$calc['present'],'half_days'=>$calc['half'],'leave_days'=>$calc['leave'],'absent_days'=>$calc['absent'],'holiday_days'=>$calc['holiday'],'paid_leave_days'=>$paidLeave,'payable_days'=>$payable,'per_day_salary'=>$calc['perDay'],'attendance_earned'=>$earned,'bonus'=>$bonus,'deduction'=>$deduction,'net_salary'=>max(0,round($earned+$bonus-$deduction,2))]));
        return $salary->refresh();
    }

    public function markPaid(SalaryPayment $salary, int $userId): SalaryPayment
    {
        return DB::transaction(function() use($salary,$userId){
            $salary=SalaryPayment::lockForUpdate()->findOrFail($salary->id);
            if($salary->status==='paid') return $salary;
            $salary->update(['status'=>'paid','payment_date'=>$salary->payment_date??now()->toDateString(),'paid_by'=>$userId]);
            return $salary->refresh();
        });
    }

    public function cancel(SalaryPayment $salary): SalaryPayment
    {
        if($salary->status==='paid') throw ValidationException::withMessages(['salary'=>'Paid salary cannot be cancelled directly. Use a proper financial reversal.']);
        $salary->update(['status'=>'cancelled']); return $salary->refresh();
    }
}
