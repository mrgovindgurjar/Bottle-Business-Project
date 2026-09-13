<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductionOrder extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'production_number','order_id','customer_id','design_id','planned_quantity','produced_quantity',
        'rejected_quantity','waste_quantity','status','priority','scheduled_date','started_at','completed_at',
        'assigned_to','quality_status','quality_notes','notes','internal_notes','created_by','completed_by','cancelled_at'
    ];
    protected $casts = [
        'planned_quantity'=>'decimal:3','produced_quantity'=>'decimal:3','rejected_quantity'=>'decimal:3','waste_quantity'=>'decimal:3',
        'scheduled_date'=>'date','started_at'=>'datetime','completed_at'=>'datetime','cancelled_at'=>'datetime'
    ];
    public function order(): BelongsTo { return $this->belongsTo(Order::class); }
    public function customer(): BelongsTo { return $this->belongsTo(Customer::class); }
    public function design(): BelongsTo { return $this->belongsTo(DesignRequest::class,'design_id'); }
    public function assignee(): BelongsTo { return $this->belongsTo(User::class,'assigned_to'); }
    public function creator(): BelongsTo { return $this->belongsTo(User::class,'created_by'); }
    public function completer(): BelongsTo { return $this->belongsTo(User::class,'completed_by'); }
    public function items(): HasMany { return $this->hasMany(ProductionOrderItem::class)->orderBy('sort_order'); }
    public function steps(): HasMany { return $this->hasMany(ProductionStep::class)->orderBy('sort_order'); }
    public function batches(): HasMany { return $this->hasMany(Batch::class); }
    public function scopeSearch(Builder $query, string $term): Builder {
        $term=trim($term); if($term==='') return $query;
        return $query->where(function($q)use($term){
            $q->where('production_number','like',"%{$term}%")
              ->orWhereHas('order',fn($o)=>$o->where('order_number','like',"%{$term}%"))
              ->orWhereHas('customer',fn($c)=>$c->where('business_name','like',"%{$term}%")->orWhere('customer_code','like',"%{$term}%"));
        });
    }
    public function isActive(): bool { return !in_array($this->status,['completed','cancelled'],true); }
    public function progress(): float {
        $planned=(float)$this->planned_quantity; if($planned<=0) return 0;
        return min(100,round(((float)$this->produced_quantity+(float)$this->rejected_quantity)/$planned*100,1));
    }
}
