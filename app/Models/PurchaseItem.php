<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchaseItem extends Model
{
    protected $fillable = [
        'purchase_id','product_id','inventory_item_id','description','quantity','received_quantity','unit',
        'unit_price','discount_type','discount_value','discount_amount','tax_rate','tax_amount','line_total','sort_order','metadata',
    ];

    protected $casts = [
        'quantity'=>'decimal:3','received_quantity'=>'decimal:3','unit_price'=>'decimal:2',
        'discount_value'=>'decimal:2','discount_amount'=>'decimal:2','tax_rate'=>'decimal:3',
        'tax_amount'=>'decimal:2','line_total'=>'decimal:2','metadata'=>'array',
    ];

    public function purchase(): BelongsTo { return $this->belongsTo(Purchase::class); }
    public function product(): BelongsTo { return $this->belongsTo(Product::class); }
    public function inventoryItem(): BelongsTo { return $this->belongsTo(InventoryItem::class); }
}
