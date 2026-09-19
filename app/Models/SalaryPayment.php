<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SalaryPayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'staff_id','salary_year','salary_month','payment_date','base_salary','working_days','present_days',
        'half_days','leave_days','absent_days','holiday_days','paid_leave_days','payable_days','per_day_salary',
        'attendance_earned','bonus','deduction','net_salary','payment_method','reference_number','status','notes','paid_by',
    ];

    protected $casts = [
        'payment_date'=>'date','base_salary'=>'decimal:2','per_day_salary'=>'decimal:2','attendance_earned'=>'decimal:2',
        'bonus'=>'decimal:2','deduction'=>'decimal:2','net_salary'=>'decimal:2',
        'working_days'=>'decimal:2','present_days'=>'decimal:2','half_days'=>'decimal:2','leave_days'=>'decimal:2',
        'absent_days'=>'decimal:2','holiday_days'=>'decimal:2','paid_leave_days'=>'decimal:2','payable_days'=>'decimal:2',
    ];

    public function staff(): BelongsTo { return $this->belongsTo(Staff::class); }
    public function paidBy(): BelongsTo { return $this->belongsTo(User::class,'paid_by'); }

    public function getMonthLabelAttribute(): string
    {
        return sprintf('%02d/%04d', $this->salary_month, $this->salary_year);
    }
}
