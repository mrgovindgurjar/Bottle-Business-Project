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
        return app(ProductPriceService::class)->findApplicablePrice(
            $product,
            $quantity,
            $customer,
            $pricingType,
            now()
        );
    }
}