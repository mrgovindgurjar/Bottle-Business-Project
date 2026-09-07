<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductPrice;
use Illuminate\Support\Facades\DB;

class ProductPriceService
{
    public function create(Product $product, array $data): ProductPrice
    {
        return DB::transaction(function () use ($product, $data) {

            $data['product_id'] = $product->id;

            return ProductPrice::create($data);
        });
    }

    public function update(
        ProductPrice $price,
        array $data
    ): ProductPrice {

        $price->update($data);

        return $price->fresh();
    }

    public function deactivate(ProductPrice $price): void
    {
        $price->update([
            'status' => 'inactive',
        ]);
    }
}