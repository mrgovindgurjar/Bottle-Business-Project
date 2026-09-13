<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Delivery extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'delivery_number','order_id','customer_id','delivery_date','scheduled_date','status',
        'delivery_address','delivery_city','delivery_state','delivery_pincode','assigned_to',
        'driver_name','driver_mobile','vehicle_number','notes','dispatch_notes','receiver_name',
        'receiver_mobile','dispatched_at','delivered_at','failed_at','cancelled_at','failed_reason',
        'proof_path','signature_path','created_by','dispatched_by','delivered_by','cancelled_by',
    ];

    protected $casts = [
        'delivery_date' => 'date', 'scheduled_date' => 'date',
        'dispatched_at' => 'datetime', 'delivered_at' => 'datetime',
        'failed_at' => 'datetime', 'cancelled_at' => 'datetime',
    ];

    public function order(): BelongsTo { return $this->belongsTo(Order::class); }
    public function customer(): BelongsTo { return $this->belongsTo(Customer::class); }
    public function assignee(): BelongsTo { return $this->belongsTo(User::class, 'assigned_to'); }
    public function creator(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }
    public function dispatcher(): BelongsTo { return $this->belongsTo(User::class, 'dispatched_by'); }
    public function deliverer(): BelongsTo { return $this->belongsTo(User::class, 'delivered_by'); }
    public function canceller(): BelongsTo { return $this->belongsTo(User::class, 'cancelled_by'); }
    public function items(): HasMany { return $this->hasMany(DeliveryItem::class); }

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        $term = trim((string) $term);
        if ($term === '') return $query;
        return $query->where(function (Builder $q) use ($term) {
            $q->where('delivery_number', 'like', "%{$term}%")
              ->orWhereHas('order', fn (Builder $o) => $o->where('order_number', 'like', "%{$term}%"))
              ->orWhereHas('customer', function (Builder $c) use ($term) {
                  $c->where('business_name','like',"%{$term}%")
                    ->orWhere('customer_code','like',"%{$term}%")
                    ->orWhereHas('user', fn (Builder $u) => $u->where('mobile','like',"%{$term}%"));
              });
        });
    }

    public function canEdit(): bool { return in_array($this->status, ['draft','ready','failed'], true); }
    public function canDispatch(): bool { return in_array($this->status, ['draft','ready','failed'], true); }
    public function canDeliver(): bool { return $this->status === 'out_for_delivery'; }
    public function canCancel(): bool { return !in_array($this->status, ['delivered','cancelled'], true); }
}
