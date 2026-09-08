<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeadActivity extends Model
{
    protected $fillable = [
        'lead_id',
        'user_id',
        'type',
        'subject',
        'description',
        'activity_at',
        'next_followup_at',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'activity_at' => 'datetime',
            'next_followup_at' => 'datetime',
            'metadata' => 'array',
        ];
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(
            Lead::class
        );
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(
            User::class
        );
    }
}