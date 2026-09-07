<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Support\Facades\DB;

class ProductService
{
    public function create(array $data): Product
    {
        return DB::transaction(function () use ($data) {

            return Product::create($data);
        });
    }

    public function update(Product $product, array $data): Product
    {
        $product->update($data);

        return $product->fresh();
    }

    public function delete(Product $product): void
    {
        /*
         * Production mein directly delete karne ki jagah
         * inactive karna safer hai.
         */
        $product->update([
            'status' => 'inactive',
        ]);
    }
}