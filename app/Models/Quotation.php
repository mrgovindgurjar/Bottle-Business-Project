<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Quotation extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'quotation_number', 'customer_id', 'design_id', 'quotation_date', 'valid_until',
        'status', 'subtotal', 'discount_type', 'discount_value', 'discount_amount',
        'tax_type', 'tax_rate', 'tax_amount', 'shipping_amount', 'other_amount',
        'grand_total', 'notes', 'terms_conditions', 'created_by', 'approved_by',
        'approved_at', 'sent_at', 'converted_to_order_at',
    ];

    protected $casts = [
        'quotation_date' => 'date',
        'valid_until' => 'date',
        'subtotal' => 'decimal:2',
        'discount_value' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'shipping_amount' => 'decimal:2',
        'other_amount' => 'decimal:2',
        'grand_total' => 'decimal:2',
        'approved_at' => 'datetime',
        'sent_at' => 'datetime',
        'converted_to_order_at' => 'datetime',
    ];

    public function customer(): BelongsTo { return $this->belongsTo(Customer::class); }
    public function design(): BelongsTo { return $this->belongsTo(DesignRequest::class, 'design_id'); }
    public function creator(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }
    public function approver(): BelongsTo { return $this->belongsTo(User::class, 'approved_by'); }
    public function items(): HasMany { return $this->hasMany(QuotationItem::class)->orderBy('sort_order'); }

    public function isEditable(): bool
    {
        return in_array($this->status, ['draft', 'sent', 'viewed'], true);
    }

    public function isConvertible(): bool
    {
        return $this->status === 'approved' && !$this->converted_to_order_at;
    }
}
