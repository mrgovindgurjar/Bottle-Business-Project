<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Purchase extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'purchase_number','supplier_id','purchase_date','expected_date','received_at','status',
        'subtotal','discount_type','discount_value','discount_amount','tax_rate','tax_amount',
        'shipping_amount','other_amount','grand_total','paid_amount','balance_amount','payment_status',
        'notes','terms_conditions','created_by',
    ];

    protected $casts = [
        'purchase_date'=>'date','expected_date'=>'date','received_at'=>'datetime',
        'subtotal'=>'decimal:2','discount_value'=>'decimal:2','discount_amount'=>'decimal:2',
        'tax_rate'=>'decimal:3','tax_amount'=>'decimal:2','shipping_amount'=>'decimal:2',
        'other_amount'=>'decimal:2','grand_total'=>'decimal:2','paid_amount'=>'decimal:2','balance_amount'=>'decimal:2',
    ];

    public function supplier(): BelongsTo { return $this->belongsTo(Supplier::class); }
    public function items(): HasMany { return $this->hasMany(PurchaseItem::class); }
    public function payments(): HasMany { return $this->hasMany(SupplierPayment::class); }
    public function creator(): BelongsTo { return $this->belongsTo(User::class,'created_by'); }

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        $term = trim((string)$term);
        if ($term === '') return $query;
        return $query->where(function(Builder $q) use ($term) {
            $q->where('purchase_number','like',"%{$term}%")
              ->orWhereHas('supplier', fn(Builder $s) => $s->where('business_name','like',"%{$term}%")->orWhere('supplier_code','like',"%{$term}%")->orWhere('mobile','like',"%{$term}%"));
        });
    }

    public function remainingQuantity(): float
    {
        return max(0, (float)$this->items->sum(fn($i) => (float)$i->quantity - (float)$i->received_quantity));
    }
    public function paymentAllocations(): HasMany
{
    return $this->hasMany(PaymentAllocation::class);
}
}
