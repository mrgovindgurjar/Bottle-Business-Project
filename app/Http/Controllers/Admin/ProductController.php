<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Models\Product;
use App\Models\ProductPrice;
use App\Services\ProductService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function __construct(
        protected ProductService $productService
    ) {
    }

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Product::class);

        $query = Product::query();

        if ($request->filled('search')) {
            $search = trim($request->string('search')->toString());
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%")
                    ->orWhere('bottle_type', 'like', "%{$search}%")
                    ->orWhere('material', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->string('status')->toString());
        }

        if ($request->filled('bottle_size_ml')) {
            $query->where('bottle_size_ml', $request->integer('bottle_size_ml'));
        }

        if ($request->filled('bottle_type')) {
            $query->where('bottle_type', $request->string('bottle_type')->toString());
        }

        $products = $query
            ->with(['activePrices' => fn ($q) => $q->orderBy('customer_id')->orderBy('min_quantity')])
            ->orderBy('sort_order')
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        $stats = [
            'total' => Product::count(),
            'active' => Product::where('status', 'active')->count(),
            'sizes' => Product::where('status', 'active')->distinct('bottle_size_ml')->count('bottle_size_ml'),
            'custom' => Product::where('status', 'active')->where('is_custom_branding', true)->count(),
        ];

        $sizes = Product::query()->select('bottle_size_ml')->distinct()->orderBy('bottle_size_ml')->pluck('bottle_size_ml');
        $types = Product::query()->whereNotNull('bottle_type')->where('bottle_type', '!=', '')->distinct()->orderBy('bottle_type')->pluck('bottle_type');

        return view('admin.products.index', compact('products', 'stats', 'sizes', 'types'));
    }

    public function create(): View
    {
        $this->authorize('create', Product::class);

        return view('admin.products.create');
    }

    public function store(StoreProductRequest $request): RedirectResponse
    {
        $this->authorize('create', Product::class);

        $product = $this->productService->create($request->validated());

        return redirect()
            ->route('admin.products.show', $product)
            ->with('success', 'Product created successfully.');
    }

    public function show(Product $product): View
    {
        $this->authorize('view', $product);

        $product->load([
            'prices' => fn ($q) => $q->with('customer')->latest('id'),
        ]);

        $priceStats = [
            'active' => $product->prices->where('status', 'active')->count(),
            'standard' => $product->prices->where('status', 'active')->where('pricing_type', 'standard')->count(),
            'customer' => $product->prices->where('status', 'active')->whereNotNull('customer_id')->count(),
        ];

        return view('admin.products.show', compact('product', 'priceStats'));
    }

    public function edit(Product $product): View
    {
        $this->authorize('update', $product);

        return view('admin.products.edit', compact('product'));
    }

    public function update(UpdateProductRequest $request, Product $product): RedirectResponse
    {
        $this->authorize('update', $product);

        $this->productService->update($product, $request->validated());

        return redirect()
            ->route('admin.products.show', $product)
            ->with('success', 'Product updated successfully.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        $this->authorize('delete', $product);

        $this->productService->delete($product);

        return redirect()
            ->route('admin.products.index')
            ->with('success', 'Product deactivated successfully.');
    }
}
