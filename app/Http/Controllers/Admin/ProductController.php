<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Models\Product;
use App\Services\ProductService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function __construct(
        protected ProductService $productService
    ) {
    }

    public function index(): View
    {
        $products = Product::latest()
            ->paginate(20);

        return view(
            'admin.products.index',
            compact('products')
        );
    }

    public function create(): View
    {
        return view('admin.products.create');
    }

    public function store(
        StoreProductRequest $request
    ): RedirectResponse {

        $product = $this->productService->create(
            $request->validated()
        );

        return redirect()
            ->route('admin.products.show', $product)
            ->with(
                'success',
                'Product created successfully.'
            );
    }

    public function show(Product $product): View
    {
        $product->load([
            'prices.customer',
        ]);

        return view(
            'admin.products.show',
            compact('product')
        );
    }

    public function edit(Product $product): View
    {
        return view(
            'admin.products.edit',
            compact('product')
        );
    }

    public function update(
        UpdateProductRequest $request,
        Product $product
    ): RedirectResponse {

        $this->productService->update(
            $product,
            $request->validated()
        );

        return redirect()
            ->route('admin.products.show', $product)
            ->with(
                'success',
                'Product updated successfully.'
            );
    }

    public function destroy(
        Product $product
    ): RedirectResponse {

        $this->productService->delete($product);

        return redirect()
            ->route('admin.products.index')
            ->with(
                'success',
                'Product deactivated successfully.'
            );
    }
}