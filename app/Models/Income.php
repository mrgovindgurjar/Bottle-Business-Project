<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Income extends Model
{
    use SoftDeletes;
    protected $fillable = ['income_number','category_id','income_date','amount','payment_method','reference_number','customer_id','invoice_id','order_id','payment_id','source_type','description','notes','status','created_by','updated_by','cancelled_by','cancelled_at','cancellation_reason'];
    protected $casts = ['income_date'=>'date','amount'=>'decimal:2','cancelled_at'=>'datetime'];
    public function category(): BelongsTo { return $this->belongsTo(IncomeCategory::class, 'category_id'); }
    public function customer(): BelongsTo { return $this->belongsTo(Customer::class); }
    public function invoice(): BelongsTo { return $this->belongsTo(Invoice::class); }
    public function order(): BelongsTo { return $this->belongsTo(Order::class); }
    public function payment(): BelongsTo { return $this->belongsTo(Payment::class); }
    public function creator(): BelongsTo { return $this->belongsTo(User::class,'created_by'); }
}
