<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'order_number', 'quotation_id', 'customer_id', 'design_id', 'order_date', 'required_date',
        'status', 'subtotal', 'discount_amount', 'tax_amount', 'shipping_amount', 'other_amount',
        'grand_total', 'delivery_address', 'delivery_city', 'delivery_state', 'delivery_pincode',
        'notes', 'internal_notes', 'created_by', 'confirmed_by', 'confirmed_at', 'cancelled_at',
    ];

    protected $casts = [
        'order_date' => 'date',
        'required_date' => 'date',
        'subtotal' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'shipping_amount' => 'decimal:2',
        'other_amount' => 'decimal:2',
        'grand_total' => 'decimal:2',
        'confirmed_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    public function customer(): BelongsTo { return $this->belongsTo(Customer::class); }
    public function quotation(): BelongsTo { return $this->belongsTo(Quotation::class); }
    public function design(): BelongsTo { return $this->belongsTo(DesignRequest::class, 'design_id'); }
    public function creator(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }
    public function confirmer(): BelongsTo { return $this->belongsTo(User::class, 'confirmed_by'); }
    public function items(): HasMany { return $this->hasMany(OrderItem::class)->orderBy('sort_order'); }

    public function scopeSearch(Builder $query, string $term): Builder
    {
        $term = trim($term);
        if ($term === '') return $query;

        return $query->where(function (Builder $q) use ($term) {
            $q->where('order_number', 'like', "%{$term}%")
                ->orWhereHas('customer', function (Builder $customer) use ($term) {
                    $customer->where('business_name', 'like', "%{$term}%")
                        ->orWhere('customer_code', 'like', "%{$term}%")
                        ->orWhereHas('user', fn (Builder $user) => $user->where('mobile', 'like', "%{$term}%"));
                });
        });
    }

    public function isEditable(): bool
    {
        return in_array($this->status, ['draft', 'on_hold'], true);
    }

    public function canConfirm(): bool
    {
        return in_array($this->status, ['draft', 'on_hold'], true);
    }

    public function canCancel(): bool
    {
        return !in_array($this->status, ['delivered', 'cancelled'], true);
    }

    public function productionOrders(): HasMany
{
    return $this->hasMany(ProductionOrder::class);
}

public function batchAllocations()
{
    return $this->hasMany(\App\Models\BatchAllocation::class);
}
public function deliveries(): HasMany { return $this->hasMany(Delivery::class); }
public function paymentAllocations(): HasMany
{
    return $this->hasMany(PaymentAllocation::class);
}

public function invoice(): HasMany
{
    return $this->hasMany(Invoice::class);
}

}
