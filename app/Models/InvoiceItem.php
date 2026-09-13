<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model; use Illuminate\Database\Eloquent\Relations\BelongsTo;
class InvoiceItem extends Model { protected $fillable=['invoice_id','product_id','order_item_id','description','quantity','unit','unit_price','discount','discount_type','taxable_amount','tax_rate','tax_amount','total','sort_order']; protected $casts=['quantity'=>'decimal:3','unit_price'=>'decimal:2','discount'=>'decimal:2','tax_rate'=>'decimal:2','taxable_amount'=>'decimal:2','tax_amount'=>'decimal:2','total'=>'decimal:2']; public function invoice():BelongsTo{return $this->belongsTo(Invoice::class);} public function product():BelongsTo{return $this->belongsTo(Product::class);} public function orderItem():BelongsTo{return $this->belongsTo(OrderItem::class);} }
