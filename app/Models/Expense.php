<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Expense extends Model
{
    use SoftDeletes;
    protected $fillable = ['expense_number','category_id','expense_date','amount','payment_method','reference_number','supplier_id','purchase_id','payment_id','source_type','description','notes','status','created_by','updated_by','cancelled_by','cancelled_at','cancellation_reason'];
    protected $casts = ['expense_date'=>'date','amount'=>'decimal:2','cancelled_at'=>'datetime'];
    public function category(): BelongsTo { return $this->belongsTo(ExpenseCategory::class, 'category_id'); }
    public function supplier(): BelongsTo { return $this->belongsTo(Supplier::class); }
    public function purchase(): BelongsTo { return $this->belongsTo(Purchase::class); }
    public function payment(): BelongsTo { return $this->belongsTo(Payment::class); }
    public function creator(): BelongsTo { return $this->belongsTo(User::class,'created_by'); }
}
