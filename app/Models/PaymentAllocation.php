<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class PaymentAllocation extends Model
{
    protected $fillable=['payment_id','invoice_id','order_id','purchase_id','allocated_amount','allocation_date','notes','reversed_at','reversed_by','reversal_reason'];
    protected $casts=['allocation_date'=>'date','reversed_at'=>'datetime','allocated_amount'=>'decimal:2'];
    public function payment(): BelongsTo { return $this->belongsTo(Payment::class); }
    public function invoice(): BelongsTo { return $this->belongsTo(Invoice::class); }
    public function order(): BelongsTo { return $this->belongsTo(Order::class); }
    public function purchase(): BelongsTo { return $this->belongsTo(Purchase::class); }
    public function reverser(): BelongsTo { return $this->belongsTo(User::class,'reversed_by'); }
}
