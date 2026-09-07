<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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

    public function prices()
    {
        return $this->hasMany(ProductPrice::class);
    }
}
