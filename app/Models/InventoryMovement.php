<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryMovement extends Model
{
    protected $fillable = [
        'inventory_item_id','batch_id','movement_type','quantity','unit_cost',
        'reference_type','reference_id','movement_date','performed_by','notes','metadata',
    ];

    protected $casts = [
        'quantity' => 'decimal:3',
        'unit_cost' => 'decimal:2',
        'movement_date' => 'datetime',
        'metadata' => 'array',
    ];

    public function item(): BelongsTo { return $this->belongsTo(InventoryItem::class,'inventory_item_id'); }
    public function batch(): BelongsTo { return $this->belongsTo(Batch::class); }
    public function performer(): BelongsTo { return $this->belongsTo(User::class,'performed_by'); }
}
