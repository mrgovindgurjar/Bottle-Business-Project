<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Product;
use App\Models\ProductPrice;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PricingController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless(auth()->user()?->hasPermission('pricing.view'), 403);

        $query = ProductPrice::query()->with(['product', 'customer']);

        if ($request->filled('search')) {
            $search = trim($request->string('search')->toString());
            $query->where(function ($q) use ($search) {
                $q->whereHas('product', fn ($p) => $p->where('name', 'like', "%{$search}%")->orWhere('sku', 'like', "%{$search}%"))
                    ->orWhereHas('customer', fn ($c) => $c->where('business_name', 'like', "%{$search}%"));
            });
        }

        if ($request->filled('pricing_type')) {
            $query->where('pricing_type', $request->string('pricing_type')->toString());
        }

        if ($request->filled('scope')) {
            $request->string('scope')->toString() === 'customer'
                ? $query->whereNotNull('customer_id')
                : $query->whereNull('customer_id');
        }

        if ($request->filled('status')) {
            $query->where('status', $request->string('status')->toString());
        }

        $prices = $query->latest('id')->paginate(20)->withQueryString();

        $stats = [
            'active' => ProductPrice::where('status', 'active')->count(),
            'standard' => ProductPrice::where('status', 'active')->where('pricing_type', 'standard')->count(),
            'bulk' => ProductPrice::where('status', 'active')->where('pricing_type', 'bulk')->count(),
            'customer' => ProductPrice::where('status', 'active')->whereNotNull('customer_id')->count(),
        ];

        return view('admin.pricing.index', compact('prices', 'stats'));
    }
}
