<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Models\Product;
use App\Models\ProductPrice;
use App\Models\Customer;
use App\Services\ProductService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use App\Models\AuditLog;
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


    public function export(Request $request): StreamedResponse
    {
        $this->authorize('export', Product::class);

        $search = trim((string) $request->get('search', ''));
        $query = Product::query()->orderBy('sort_order')->orderBy('name');

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%")
                    ->orWhere('bottle_type', 'like', "%{$search}%")
                    ->orWhere('material', 'like', "%{$search}%");
            });
        }
        if ($request->filled('status')) $query->where('status', $request->string('status')->toString());
        if ($request->filled('bottle_size_ml')) $query->where('bottle_size_ml', $request->integer('bottle_size_ml'));
        if ($request->filled('bottle_type')) $query->where('bottle_type', $request->string('bottle_type')->toString());

        AuditLog::record('product.export', null, [], ['filters' => $request->only(['search','status','bottle_size_ml','bottle_type'])], 'Product catalog exported.');

        return response()->streamDownload(function () use ($query) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['ID','SKU','Name','Bottle Size (ml)','Bottle Type','Material','Cap Type','Label Type','Units/Box','Unit','Custom Branding','Status']);
            $query->chunk(500, function ($products) use ($out) {
                foreach ($products as $product) {
                    fputcsv($out, [$product->id,$product->sku,$product->name,$product->bottle_size_ml,$product->bottle_type,$product->material,$product->cap_type,$product->label_type,$product->units_per_box,$product->unit,$product->is_custom_branding ? 'Yes' : 'No',$product->status]);
                }
            });
            fclose($out);
        }, 'products-'.now()->format('Y-m-d').'.csv', ['Content-Type' => 'text/csv']);
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
        AuditLog::record('product.created', $product, [], $product->only(['name','sku','status']), 'Product created.');

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

        $customers = Customer::query()
            ->where('status', 'active')
            ->orderBy('business_name')
            ->get(['id', 'business_name']);

        $priceStats = [
            'active' => $product->prices->where('status', 'active')->count(),
            'standard' => $product->prices->where('status', 'active')->where('pricing_type', 'standard')->count(),
            'customer' => $product->prices->where('status', 'active')->whereNotNull('customer_id')->count(),
        ];

        return view('admin.products.show', compact('product', 'priceStats', 'customers'));
    }

    public function edit(Product $product): View
    {
        $this->authorize('update', $product);

        return view('admin.products.edit', compact('product'));
    }

    public function update(UpdateProductRequest $request, Product $product): RedirectResponse
    {
        $this->authorize('update', $product);

        $before = $product->only(['name','sku','bottle_size_ml','status','is_custom_branding']);
        $this->productService->update($product, $request->validated());
        AuditLog::record('product.updated', $product, $before, $product->fresh()->only(['name','sku','bottle_size_ml','status','is_custom_branding']), 'Product updated.');

        return redirect()
            ->route('admin.products.show', $product)
            ->with('success', 'Product updated successfully.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        $this->authorize('delete', $product);

        $before = ['status' => $product->status];
        $this->productService->delete($product);
        AuditLog::record('product.deactivated', $product, $before, ['status' => 'inactive'], 'Product deactivated.');

        return redirect()
            ->route('admin.products.index')
            ->with('success', 'Product deactivated successfully.');
    }
}
