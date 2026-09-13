<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeliveryItem extends Model
{
    protected $fillable = [
        'delivery_id','order_item_id','product_id','batch_id','description','quantity',
        'delivered_quantity','unit','inventory_movement_id','notes',
    ];

    protected $casts = ['quantity'=>'decimal:3','delivered_quantity'=>'decimal:3'];

    public function delivery(): BelongsTo { return $this->belongsTo(Delivery::class); }
    public function orderItem(): BelongsTo { return $this->belongsTo(OrderItem::class); }
    public function product(): BelongsTo { return $this->belongsTo(Product::class); }
    public function batch(): BelongsTo { return $this->belongsTo(Batch::class); }
    public function inventoryMovement(): BelongsTo { return $this->belongsTo(InventoryMovement::class); }
}
