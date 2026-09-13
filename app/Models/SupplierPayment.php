<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SupplierPayment extends Model
{
    protected $fillable = ['supplier_id','purchase_id','payment_date','amount','method','reference','notes','created_by'];
    protected $casts = ['payment_date'=>'date','amount'=>'decimal:2'];
    public function supplier(): BelongsTo { return $this->belongsTo(Supplier::class); }
    public function purchase(): BelongsTo { return $this->belongsTo(Purchase::class); }
    public function creator(): BelongsTo { return $this->belongsTo(User::class,'created_by'); }
}
