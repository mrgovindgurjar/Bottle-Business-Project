<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\Product;
use App\Models\ProductPrice;

class PricingService
{
    public function getPrice(
        Product $product,
        ?Customer $customer,
        int $quantity,
        string $pricingType = 'standard'
    ): ?ProductPrice {

        $query = ProductPrice::query()
            ->where('product_id', $product->id)
            ->where('status', 'active')
            ->where('pricing_type', $pricingType)
            ->where('min_quantity', '<=', $quantity)
            ->where(function ($query) use ($quantity) {
                $query->whereNull('max_quantity')
                    ->orWhere(
                        'max_quantity',
                        '>=',
                        $quantity
                    );
            });

        if ($customer) {
            $customerPrice = (clone $query)
                ->where('customer_id', $customer->id)
                ->latest('id')
                ->first();

            if ($customerPrice) {
                return $customerPrice;
            }
        }

        return $query
            ->whereNull('customer_id')
            ->latest('id')
            ->first();
    }
}