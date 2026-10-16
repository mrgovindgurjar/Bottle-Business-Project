<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProductPriceRequest;
use App\Http\Requests\UpdateProductPriceRequest;
use App\Models\Product;
use App\Models\ProductPrice;
use App\Models\AuditLog;
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

        $price = $this->priceService->create($product, $request->validated());
        AuditLog::record('pricing.created', $price, [], $price->only(['product_id','customer_id','pricing_type','min_quantity','max_quantity','unit_price','status']), 'Pricing rule created.');

        return back()->with('success', 'Price added successfully.');
    }

    public function update(UpdateProductPriceRequest $request, ProductPrice $productPrice): RedirectResponse
    {
        $this->authorize('pricing', $productPrice->product);

        $before = $productPrice->only(['customer_id','pricing_type','min_quantity','max_quantity','unit_price','status']);
        $this->priceService->update($productPrice, $request->validated());
        AuditLog::record('pricing.updated', $productPrice, $before, $productPrice->fresh()->only(['customer_id','pricing_type','min_quantity','max_quantity','unit_price','status']), 'Pricing rule updated.');

        return back()->with('success', 'Price updated successfully.');
    }

    public function destroy(ProductPrice $productPrice): RedirectResponse
    {
        $this->authorize('pricing', $productPrice->product);

        $this->priceService->deactivate($productPrice);
        AuditLog::record('pricing.deactivated', $productPrice, ['status' => 'active'], ['status' => 'inactive'], 'Pricing rule deactivated.');

        return back()->with('success', 'Price deactivated successfully.');
    }
}
