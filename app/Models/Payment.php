<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Payment extends Model
{
    use SoftDeletes;
    protected $fillable = ['payment_number','direction','payment_type','customer_id','supplier_id','order_id','purchase_id','invoice_id','payment_date','amount','method','reference_number','bank_name','transaction_date','notes','status','received_by','paid_by','created_by','approved_by','approved_at','receipt_number','refund_of_payment_id','metadata'];
    protected $casts = ['payment_date'=>'date','transaction_date'=>'date','approved_at'=>'datetime','amount'=>'decimal:2','metadata'=>'array'];
    public function customer(): BelongsTo { return $this->belongsTo(Customer::class); }
    public function supplier(): BelongsTo { return $this->belongsTo(Supplier::class); }
    public function order(): BelongsTo { return $this->belongsTo(Order::class); }
    public function purchase(): BelongsTo { return $this->belongsTo(Purchase::class); }
    public function invoice(): BelongsTo { return $this->belongsTo(Invoice::class); }
    public function creator(): BelongsTo { return $this->belongsTo(User::class,'created_by'); }
    public function approver(): BelongsTo { return $this->belongsTo(User::class,'approved_by'); }
    public function refundOf(): BelongsTo { return $this->belongsTo(self::class,'refund_of_payment_id'); }
    public function refunds(): HasMany { return $this->hasMany(self::class,'refund_of_payment_id'); }
    public function allocations(): HasMany { return $this->hasMany(PaymentAllocation::class); }
    public function getAllocatedAmountAttribute(): float { return (float)$this->allocations()->whereNull('reversed_at')->sum('allocated_amount'); }
    public function getUnallocatedAmountAttribute(): float { return max(0, (float)$this->amount - $this->allocated_amount); }
}
