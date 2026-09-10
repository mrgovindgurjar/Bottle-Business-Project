<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProductPriceRequest;
use App\Http\Requests\UpdateProductPriceRequest;
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

    public function store(StoreProductPriceRequest $request, Product $product): RedirectResponse
    {
        $this->authorize('pricing', $product);

        $this->priceService->create($product, $request->validated());

        return back()->with('success', 'Price added successfully.');
    }

    public function update(UpdateProductPriceRequest $request, ProductPrice $productPrice): RedirectResponse
    {
        $this->authorize('pricing', $productPrice->product);

        $this->priceService->update($productPrice, $request->validated());

        return back()->with('success', 'Price updated successfully.');
    }

    public function destroy(ProductPrice $productPrice): RedirectResponse
    {
        $this->authorize('pricing', $productPrice->product);

        $this->priceService->deactivate($productPrice);

        return back()->with('success', 'Price deactivated successfully.');
    }
}
