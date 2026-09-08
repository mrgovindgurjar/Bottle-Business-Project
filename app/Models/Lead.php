<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Lead extends Model
{
    use SoftDeletes;

    public const STATUS_NEW = 'new';
    public const STATUS_CONTACTED = 'contacted';
    public const STATUS_QUALIFIED = 'qualified';
    public const STATUS_QUOTATION = 'quotation';
    public const STATUS_NEGOTIATION = 'negotiation';
    public const STATUS_WON = 'won';
    public const STATUS_LOST = 'lost';

    public const STATUSES = [
        self::STATUS_NEW,
        self::STATUS_CONTACTED,
        self::STATUS_QUALIFIED,
        self::STATUS_QUOTATION,
        self::STATUS_NEGOTIATION,
        self::STATUS_WON,
        self::STATUS_LOST,
    ];

    public const ACTIVITY_TYPES = [
        'call',
        'whatsapp',
        'email',
        'meeting',
        'note',
        'quotation',
        'sample',
        'followup',
        'other',
    ];

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
        'quantity_unit',
        'order_frequency',
        'estimated_value',
        'assigned_to',
        'status',
        'next_followup_at',
        'last_contacted_at',
        'address',
        'city',
        'state',
        'pincode',
        'notes',
        'lost_reason',
        'converted_customer_id',
    ];

    protected function casts(): array
    {
        return [
            'estimated_value' => 'decimal:2',
            'next_followup_at' => 'datetime',
            'last_contacted_at' => 'datetime',
        ];
    }

    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'assigned_to'
        );
    }

    public function activities(): HasMany
    {
        return $this->hasMany(
            LeadActivity::class
        )->latest('activity_at');
    }

    public function convertedCustomer(): BelongsTo
    {
        return $this->belongsTo(
            Customer::class,
            'converted_customer_id'
        );
    }

    public function scopeSearch(
        Builder $query,
        ?string $search
    ): Builder {
        if (!$search) {
            return $query;
        }

        return $query->where(function ($q) use ($search) {

            $q->where(
                'lead_code',
                'like',
                "%{$search}%"
            )
            ->orWhere(
                'business_name',
                'like',
                "%{$search}%"
            )
            ->orWhere(
                'contact_name',
                'like',
                "%{$search}%"
            )
            ->orWhere(
                'mobile',
                'like',
                "%{$search}%"
            )
            ->orWhere(
                'email',
                'like',
                "%{$search}%"
            );
        });
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'new' => 'New',
            'contacted' => 'Contacted',
            'qualified' => 'Qualified',
            'quotation' => 'Quotation',
            'negotiation' => 'Negotiation',
            'won' => 'Won',
            'lost' => 'Lost',
            default => ucfirst($this->status),
        };
    }
}