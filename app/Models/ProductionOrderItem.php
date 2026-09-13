<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class ProductionOrderItem extends Model
{
    protected $fillable=['production_order_id','order_item_id','product_id','design_id','description','planned_quantity','produced_quantity','rejected_quantity','unit','sort_order','metadata'];
    protected $casts=['planned_quantity'=>'decimal:3','produced_quantity'=>'decimal:3','rejected_quantity'=>'decimal:3','metadata'=>'array'];
    public function productionOrder(): BelongsTo{return $this->belongsTo(ProductionOrder::class);}
    public function orderItem(): BelongsTo{return $this->belongsTo(OrderItem::class);}
    public function product(): BelongsTo{return $this->belongsTo(Product::class);}
    public function design(): BelongsTo{return $this->belongsTo(DesignRequest::class,'design_id');}
}
