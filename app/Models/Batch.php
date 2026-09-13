<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Batch extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'batch_number', 'production_order_id', 'production_order_item_id', 'product_id', 'design_id',
        'customer_id', 'manufacturing_date', 'expiry_date', 'produced_quantity', 'rejected_quantity',
        'available_quantity', 'status', 'quality_status', 'quality_notes', 'notes', 'created_by',
        'released_by', 'released_at', 'blocked_at', 'blocked_reason',
    ];

    protected $casts = [
        'manufacturing_date' => 'date',
        'expiry_date' => 'date',
        'produced_quantity' => 'decimal:3',
        'rejected_quantity' => 'decimal:3',
        'available_quantity' => 'decimal:3',
        'released_at' => 'datetime',
        'blocked_at' => 'datetime',
    ];

    public function productionOrder(): BelongsTo { return $this->belongsTo(ProductionOrder::class); }
    public function productionOrderItem(): BelongsTo { return $this->belongsTo(ProductionOrderItem::class); }
    public function product(): BelongsTo { return $this->belongsTo(Product::class); }
    public function design(): BelongsTo { return $this->belongsTo(DesignRequest::class, 'design_id'); }
    public function customer(): BelongsTo { return $this->belongsTo(Customer::class); }
    public function creator(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }
    public function releaser(): BelongsTo { return $this->belongsTo(User::class, 'released_by'); }
    public function allocations(): HasMany { return $this->hasMany(BatchAllocation::class); }

    public function scopeSearch(Builder $query, string $term): Builder
    {
        $term = trim($term);
        if ($term === '') return $query;

        return $query->where(function (Builder $q) use ($term) {
            $q->where('batch_number', 'like', "%{$term}%")
                ->orWhereHas('productionOrder', fn (Builder $p) => $p->where('production_number', 'like', "%{$term}%"))
                ->orWhereHas('customer', fn (Builder $c) => $c->where('business_name', 'like', "%{$term}%")->orWhere('customer_code', 'like', "%{$term}%"))
                ->orWhereHas('product', fn (Builder $p) => $p->where('name', 'like', "%{$term}%")->orWhere('sku', 'like', "%{$term}%"));
        });
    }

   public function allocatedQuantity(): float
{
    return (float) $this->allocations()->whereNull('reversed_at')->sum('quantity');
}

    public function calculatedAvailableQuantity(): float
    {
        return max(0, (float) $this->available_quantity - $this->allocatedQuantity());
    }

    public function isExpired(): bool
    {
        return $this->expiry_date?->isBefore(today()) ?? false;
    }

    public function canAllocate(): bool
    {
        return in_array($this->status, ['released', 'allocated'], true) && !$this->isExpired() && $this->quality_status === 'passed' && $this->calculatedAvailableQuantity() > 0;
    }

    public function inventoryMovements()
{
    return $this->hasMany(InventoryMovement::class);
}

}
