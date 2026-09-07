<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProductPriceRequest;
use App\Models\Product;
use App\Models\ProductPrice;
use App\Services\ProductPriceService;
use Illuminate\Http\RedirectResponse;

class ProductPriceController extends Controller
{
    public function __construct(
        protected ProductPriceService $priceService
    ) {
    }

    public function store(
        StoreProductPriceRequest $request,
        Product $product
    ): RedirectResponse {

        $this->priceService->create(
            $product,
            $request->validated()
        );

        return back()->with(
            'success',
            'Price added successfully.'
        );
    }

    public function destroy(
        ProductPrice $productPrice
    ): RedirectResponse {

        $this->priceService->deactivate(
            $productPrice
        );

        return back()->with(
            'success',
            'Price deactivated successfully.'
        );
    }
}