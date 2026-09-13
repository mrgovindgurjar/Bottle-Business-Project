<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class InventoryItem extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'product_id','name','sku','category','unit','location','on_hand','reserved',
        'reorder_level','reorder_quantity','average_cost','status','notes',
    ];

    protected $casts = [
        'on_hand' => 'decimal:3',
        'reserved' => 'decimal:3',
        'reorder_level' => 'decimal:3',
        'reorder_quantity' => 'decimal:3',
        'average_cost' => 'decimal:2',
    ];

    public function product(): BelongsTo { return $this->belongsTo(Product::class); }
    public function movements(): HasMany { return $this->hasMany(InventoryMovement::class); }

    public function availableQuantity(): float
    {
        return max(0, (float)$this->on_hand - (float)$this->reserved);
    }

    public function isLowStock(): bool
    {
        return (float)$this->on_hand <= (float)$this->reorder_level;
    }

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        $term = trim((string)$term);
        if ($term === '') return $query;
        return $query->where(function (Builder $q) use ($term) {
            $q->where('name','like',"%{$term}%")
              ->orWhere('sku','like',"%{$term}%")
              ->orWhereHas('product', fn(Builder $p) => $p->where('name','like',"%{$term}%")->orWhere('sku','like',"%{$term}%"));
        });
    }
}
