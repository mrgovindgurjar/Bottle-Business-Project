<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Product;
use App\Models\ProductPrice;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;
use App\Models\AuditLog;

class PricingController extends Controller
{

    public function export(Request $request): StreamedResponse
    {
        abort_unless(auth()->user()?->hasPermission('pricing.export'), 403);
        $query = ProductPrice::query()->with(['product','customer'])->latest('id');
        if ($request->filled('search')) {
            $search = trim($request->string('search')->toString());
            $query->where(function ($q) use ($search) {
                $q->whereHas('product', fn ($p) => $p->where('name','like',"%{$search}%")->orWhere('sku','like',"%{$search}%"))
                    ->orWhereHas('customer', fn ($c) => $c->where('business_name','like',"%{$search}%"));
            });
        }
        if ($request->filled('pricing_type')) $query->where('pricing_type',$request->string('pricing_type')->toString());
        if ($request->filled('scope')) {
            $request->string('scope')->toString() === 'customer'
                ? $query->whereNotNull('customer_id')
                : $query->whereNull('customer_id');
        }
        if ($request->filled('status')) $query->where('status',$request->string('status')->toString());
        AuditLog::record('pricing.export', null, [], ['filters'=>$request->only(['search','pricing_type','scope','status'])], 'Pricing rules exported.');
        return response()->streamDownload(function () use ($query) {
            $out=fopen('php://output','w');
            fputcsv($out,['ID','SKU','Product','Customer Scope','Type','Min Qty','Max Qty','Unit Price','Effective From','Effective To','Status']);
            $query->chunk(500,function($rows)use($out){foreach($rows as $row){fputcsv($out,[$row->id,$row->product?->sku,$row->product?->name,$row->customer?->business_name ?: 'Global',$row->pricing_type,$row->min_quantity,$row->max_quantity,$row->unit_price,$row->effective_from?->format('Y-m-d'),$row->effective_to?->format('Y-m-d'),$row->status]);}});
            fclose($out);
        }, 'pricing-'.now()->format('Y-m-d').'.csv',['Content-Type'=>'text/csv']);
    }

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
