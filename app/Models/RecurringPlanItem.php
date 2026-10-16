<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model; use Illuminate\Database\Eloquent\Relations\BelongsTo;
class RecurringPlanItem extends Model { protected $fillable=['recurring_plan_id','product_id','design_id','description','quantity','unit','unit_price','discount_amount','tax_rate','sort_order','metadata']; protected $casts=['quantity'=>'decimal:3','unit_price'=>'decimal:2','discount_amount'=>'decimal:2','tax_rate'=>'decimal:2','metadata'=>'array']; public function plan():BelongsTo{return $this->belongsTo(RecurringPlan::class,'recurring_plan_id');} public function product():BelongsTo{return $this->belongsTo(Product::class);} public function design():BelongsTo{return $this->belongsTo(DesignRequest::class,'design_id');} }
