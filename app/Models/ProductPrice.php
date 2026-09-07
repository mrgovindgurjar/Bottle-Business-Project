<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductPrice extends Model
{
    protected $fillable = [
        'product_id',
        'customer_id',
        'pricing_type',
        'min_quantity',
        'max_quantity',
        'unit_price',
        'effective_from',
        'effective_to',
        'status',
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'effective_from' => 'date',
        'effective_to' => 'date',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}
