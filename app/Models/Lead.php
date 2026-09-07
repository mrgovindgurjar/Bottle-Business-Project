<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\belongsTo;
use Illuminate\Database\Eloquent\Relations\hasMany;
class Lead extends Model
{
    protected $fillable = [
        'lead_code',
        'business_name',
        'contact_name',
        'mobile',
        'email',
        'business_type',
        'source',
        'requirement',
        'estimated_quantity',
        'estimated_value',
        'assigned_to',
        'status',
        'next_followup_at',
        'notes',
    ];

    protected $casts = [
        'estimated_value' => 'decimal:2',
        'next_followup_at' => 'datetime',
    ];

    public function assignedUser(): belongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function activities(): hasMany
    {
        return $this->hasMany(LeadActivity::class);
    }
}