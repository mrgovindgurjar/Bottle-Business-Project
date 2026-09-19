<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Staff extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'employee_code','user_id','first_name','last_name','email','mobile','date_of_birth',
        'gender','designation','department','joining_date','employment_type','salary',
        'address','city','state','pincode','emergency_contact_name','emergency_contact_mobile',
        'status','notes',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'joining_date' => 'date',
        'salary' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function attendances()
    {
        return $this->hasMany(StaffAttendance::class);
    }

    public function salaryPayments()
    {
        return $this->hasMany(SalaryPayment::class);
    }

    public function getFullNameAttribute(): string
    {
        return trim($this->first_name.' '.$this->last_name);
    }

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        if (!$term) return $query;
        $term = trim($term);
        return $query->where(function (Builder $q) use ($term) {
            $q->where('employee_code','like',"%{$term}%")
              ->orWhere('first_name','like',"%{$term}%")
              ->orWhere('last_name','like',"%{$term}%")
              ->orWhere('mobile','like',"%{$term}%")
              ->orWhere('email','like',"%{$term}%")
              ->orWhere('designation','like',"%{$term}%")
              ->orWhere('department','like',"%{$term}%");
        });
    }
}
