<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    protected $fillable = [
        'name',
        'sku',
        'bottle_size_ml',
        'bottle_type',
        'material',
        'units_per_box',
        'unit',
        'description',
        'status',
    ];

    protected $casts = [
        'bottle_size_ml' => 'integer',
        'units_per_box' => 'integer',
    ];

    public function prices(): HasMany
    {
        return $this->hasMany(ProductPrice::class);
    }

    public function activePrices(): HasMany
    {
        return $this->hasMany(ProductPrice::class)
            ->where('status', 'active');
    }
}