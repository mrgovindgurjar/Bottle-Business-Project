<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
class Invoice extends Model { use SoftDeletes;
 protected $fillable=['invoice_number','customer_id','order_id','invoice_date','due_date','status','payment_status','subtotal','discount','taxable_amount','tax_amount','shipping_amount','other_charges','round_off','grand_total','paid_amount','outstanding_amount','notes','terms','billing_name','billing_contact_name','billing_address','billing_city','billing_state','billing_pincode','billing_gstin','billing_mobile','billing_email','shipping_name','shipping_address','shipping_city','shipping_state','shipping_pincode','created_by','updated_by','cancelled_by','cancelled_at','cancellation_reason'];
 protected $casts=['invoice_date'=>'date','due_date'=>'date','cancelled_at'=>'datetime','subtotal'=>'decimal:2','discount'=>'decimal:2','taxable_amount'=>'decimal:2','tax_amount'=>'decimal:2','shipping_amount'=>'decimal:2','other_charges'=>'decimal:2','round_off'=>'decimal:2','grand_total'=>'decimal:2','paid_amount'=>'decimal:2','outstanding_amount'=>'decimal:2'];
 public function customer(): BelongsTo{return $this->belongsTo(Customer::class);} public function order(): BelongsTo{return $this->belongsTo(Order::class);} public function items(): HasMany{return $this->hasMany(InvoiceItem::class);} public function paymentAllocations(): HasMany{return $this->hasMany(PaymentAllocation::class);} public function creator(): BelongsTo{return $this->belongsTo(User::class,'created_by');}
 public function getIsOverdueAttribute(): bool{return $this->status!=='cancelled' && (float)$this->outstanding_amount>0 && $this->due_date && $this->due_date->lt(Carbon::today());}
}
