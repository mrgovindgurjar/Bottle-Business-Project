<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Builder; use Illuminate\Database\Eloquent\Model; use Illuminate\Database\Eloquent\Relations\BelongsTo; use Illuminate\Database\Eloquent\Relations\HasMany; use Illuminate\Database\Eloquent\SoftDeletes;
class RecurringPlan extends Model { use SoftDeletes;
 protected $fillable=['plan_number','customer_id','name','frequency','weekdays','day_of_month','start_date','end_date','next_order_date','status','auto_generate','delivery_address','delivery_city','delivery_state','delivery_pincode','notes','created_by','approved_by','approved_at'];
 protected $casts=['weekdays'=>'array','start_date'=>'date','end_date'=>'date','next_order_date'=>'date','auto_generate'=>'boolean','approved_at'=>'datetime'];
 public function customer():BelongsTo{return $this->belongsTo(Customer::class);} public function items():HasMany{return $this->hasMany(RecurringPlanItem::class)->orderBy('sort_order');} public function creator():BelongsTo{return $this->belongsTo(User::class,'created_by');} public function approver():BelongsTo{return $this->belongsTo(User::class,'approved_by');} public function dailyOrders():HasMany{return $this->hasMany(DailyOrder::class);}
 public function scopeSearch(Builder $q,?string $term):Builder { if(!$term)return $q; return $q->where(function($x)use($term){$x->where('plan_number','like',"%{$term}%")->orWhere('name','like',"%{$term}%")->orWhereHas('customer',fn($c)=>$c->where('business_name','like',"%{$term}%")->orWhere('customer_code','like',"%{$term}%"));}); }
}
