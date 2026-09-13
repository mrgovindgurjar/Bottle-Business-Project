<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class ProductionStep extends Model
{
    protected $fillable=['production_order_id','code','name','sort_order','status','started_at','completed_at','notes'];
    protected $casts=['started_at'=>'datetime','completed_at'=>'datetime'];
    public function productionOrder(): BelongsTo{return $this->belongsTo(ProductionOrder::class);}
}
