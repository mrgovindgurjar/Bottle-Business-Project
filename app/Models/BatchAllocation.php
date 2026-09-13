<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BatchAllocation extends Model
{
    protected $fillable = [
        'batch_id', 'customer_id', 'order_id', 'delivery_id', 'quantity', 'allocated_at', 'allocated_by', 'notes','reversed_at','reversed_by','reversal_reason',
    ];

    protected $casts = [
        'quantity' => 'decimal:3',
        'allocated_at' => 'datetime',
        'reversed_at' => 'datetime',
     ];

    public function batch(): BelongsTo { return $this->belongsTo(Batch::class); }
    public function customer(): BelongsTo { return $this->belongsTo(Customer::class); }
    public function order(): BelongsTo { return $this->belongsTo(Order::class); }
    public function delivery(): BelongsTo { return $this->belongsTo(Delivery::class); }
    public function allocator(): BelongsTo { return $this->belongsTo(User::class, 'allocated_by'); }

    public function reverser(): BelongsTo { return $this->belongsTo(User::class, 'reversed_by'); }

}
