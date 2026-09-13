<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOrderRequest;
use App\Http\Requests\UpdateOrderRequest;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use App\Models\Quotation;
use App\Services\OrderService;
use App\Services\PricingService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function __construct(private OrderService $service) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Order::class);

        $query = Order::query()
            ->with(['customer.user', 'creator'])
            ->withCount('items')
            ->latest('id');

        if ($term = trim((string) $request->get('q'))) {
            $query->search($term);
        }
        foreach (['status', 'customer_id', 'created_by'] as $filter) {
            if ($request->filled($filter)) $query->where($filter, $request->input($filter));
        }
        if ($request->filled('date_from')) $query->whereDate('order_date', '>=', $request->date('date_from'));
        if ($request->filled('date_to')) $query->whereDate('order_date', '<=', $request->date('date_to'));
        if ($request->filled('value_min')) $query->where('grand_total', '>=', (float) $request->input('value_min'));
        if ($request->filled('value_max')) $query->where('grand_total', '<=', (float) $request->input('value_max'));

        $orders = $query->paginate(20)->withQueryString();

        $stats = [
            'total' => Order::count(),
            'draft' => Order::where('status', 'draft')->count(),
            'confirmed' => Order::where('status', 'confirmed')->count(),
            'production' => Order::where('status', 'in_production')->count(),
            'ready' => Order::where('status', 'ready')->count(),
            'dispatched' => Order::where('status', 'dispatched')->count(),
            'delivered' => Order::where('status', 'delivered')->count(),
            'value' => (float) Order::sum('grand_total'),
        ];

        $customers = Customer::with('user')->orderBy('business_name')->get();
        $creators = Order::query()->with('creator')->select('created_by')->distinct()->get()->pluck('creator')->filter()->unique('id')->values();

        return view('admin.orders.index', compact('orders', 'stats', 'customers', 'creators'));
    }

    public function create(Request $request): View
    {
        $this->authorize('create', Order::class);

        $sourceQuotation = null;
        if ($request->filled('quotation_id')) {
            $sourceQuotation = Quotation::with(['customer.user', 'items.product', 'items.design', 'design'])
                ->findOrFail($request->integer('quotation_id'));
        }

        return view('admin.orders.create', $this->formData($sourceQuotation));
    }

    public function store(StoreOrderRequest $request): RedirectResponse
    {
        $this->authorize('create', Order::class);
        $order = $this->service->create($request->validated(), (int) $request->user()->id);

        return redirect()->route('admin.orders.show', $order)->with('success', 'Order created successfully.');
    }

    public function show(Order $order): View
    {
        $this->authorize('view', $order);
        $order->load(['customer.user', 'quotation', 'items.product', 'items.design', 'design', 'creator', 'confirmer']);

        return view('admin.orders.show', compact('order'));
    }

    public function edit(Order $order): View
    {
        $this->authorize('update', $order);
        $order->load(['customer.user', 'quotation', 'items.product', 'items.design', 'design']);

        return view('admin.orders.edit', array_merge(['order' => $order], $this->formData($order->quotation, $order->customer_id)));
    }

    public function update(UpdateOrderRequest $request, Order $order): RedirectResponse
    {
        $this->authorize('update', $order);
        $this->service->update($order, $request->validated());

        return redirect()->route('admin.orders.show', $order)->with('success', 'Order updated successfully.');
    }

    public function destroy(Order $order): RedirectResponse
    {
        $this->authorize('delete', $order);
        $order->delete();

        return redirect()->route('admin.orders.index')->with('success', 'Order moved to history.');
    }

    public function duplicate(Order $order): RedirectResponse
    {
        $this->authorize('duplicate', $order);
        $copy = $this->service->duplicate($order, (int) auth()->id());

        return redirect()->route('admin.orders.edit', $copy)->with('success', 'Order duplicated as '.$copy->order_number.'.');
    }

    public function confirm(Order $order): RedirectResponse
    {
        $this->authorize('confirm', $order);
        $this->service->changeStatus($order, 'confirmed', (int) auth()->id());

        return back()->with('success', 'Order confirmed.');
    }

    public function status(Request $request, Order $order): RedirectResponse
    {
        $this->authorize('changeStatus', $order);
        $data = $request->validate(['status' => ['required', 'string', 'in:draft,confirmed,in_production,ready,dispatched,delivered,on_hold,cancelled']]);
        $this->service->changeStatus($order, $data['status'], (int) auth()->id());

        return back()->with('success', 'Order status updated.');
    }

    public function cancel(Order $order): RedirectResponse
    {
        $this->authorize('cancel', $order);
        $this->service->changeStatus($order, 'cancelled', (int) auth()->id());

        return back()->with('success', 'Order cancelled.');
    }

    public function preview(Order $order): View
    {
        $this->authorize('view', $order);
        $order->load(['customer.user', 'quotation', 'items.product', 'items.design', 'design', 'creator']);

        return view('admin.orders.preview', compact('order'));
    }

    public function pdf(Order $order)
    {
        $this->authorize('export', $order);
        $order->load(['customer.user', 'quotation', 'items.product', 'items.design', 'design', 'creator']);

        if (!class_exists(Pdf::class)) {
            return redirect()->route('admin.orders.preview', $order)->with('error', 'PDF package is not installed. Use Print or install barryvdh/laravel-dompdf.');
        }

        return Pdf::loadView('admin.orders.pdf', compact('order'))->setPaper('a4')->download($order->order_number.'.pdf');
    }

    public function quotation(Quotation $quotation): JsonResponse
    {
        $this->authorize('create', Order::class);
        $quotation->load(['customer.user', 'items.product', 'items.design', 'design']);

        return response()->json([
            'id' => $quotation->id,
            'number' => $quotation->quotation_number,
            'status' => $quotation->status,
            'customer' => [
                'id' => $quotation->customer_id,
                'business_name' => $quotation->customer->business_name,
                'customer_code' => $quotation->customer->customer_code,
                'mobile' => $quotation->customer->user?->mobile,
                'email' => $quotation->customer->user?->email,
                'address' => $quotation->customer->address,
                'city' => $quotation->customer->city,
                'state' => $quotation->customer->state,
                'pincode' => $quotation->customer->pincode,
            ],
            'design_id' => $quotation->design_id,
            'items' => $quotation->items->map(fn ($item) => [
                'product_id' => $item->product_id,
                'design_id' => $item->design_id,
                'description' => $item->description,
                'quantity' => (float) $item->quantity,
                'unit' => $item->unit,
                'unit_price' => (float) $item->unit_price,
                'discount_amount' => (float) $item->discount_amount,
                'tax_rate' => (float) $item->tax_rate,
                'tax_amount' => (float) $item->tax_amount,
                'line_total' => (float) $item->line_total,
            ])->values(),
            'totals' => [
                'subtotal' => (float) $quotation->subtotal,
                'discount_amount' => (float) $quotation->discount_amount,
                'tax_amount' => (float) $quotation->tax_amount,
                'shipping_amount' => (float) $quotation->shipping_amount,
                'other_amount' => (float) $quotation->other_amount,
                'grand_total' => (float) $quotation->grand_total,
            ],
        ]);
    }

    public function price(Request $request): JsonResponse
    {
        $data = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'customer_id' => ['nullable', 'integer', 'exists:customers,id'],
            'quantity' => ['required', 'numeric', 'gt:0'],
        ]);

        $product = Product::findOrFail($data['product_id']);
        $customer = !empty($data['customer_id']) ? Customer::find($data['customer_id']) : null;
        $pricing = app(PricingService::class);
        $types = ['bulk', 'recurring', 'standard'];
        foreach ($types as $type) {
            $price = $pricing->getPrice($product, $customer, (int) ceil($data['quantity']), $type);
            if ($price) {
                return response()->json(['price' => (float) $price->unit_price, 'pricing_type' => $type]);
            }
        }

        return response()->json(['price' => 0, 'pricing_type' => null]);
    }

    public function designs(Request $request): JsonResponse
    {
        if (!Schema::hasTable('design_requests')) return response()->json([]);

        $customerId = $request->validate(['customer_id' => ['required', 'integer', 'exists:customers,id']])['customer_id'];
        $designs = \App\Models\DesignRequest::where('customer_id', $customerId)
            ->with('versions')
            ->latest('id')
            ->get()
            ->map(fn ($design) => [
                'id' => $design->id,
                'code' => $design->design_code,
                'title' => $design->title,
                'status' => $design->status,
                'versions' => $design->versions->sortByDesc('version_no')->map(fn ($version) => [
                    'id' => $version->id,
                    'version_no' => $version->version_no,
                    'name' => $version->name,
                    'status' => $version->status,
                ])->values(),
            ]);

        return response()->json($designs);
    }

    private function formData(?Quotation $sourceQuotation = null, ?int $customerId = null): array
    {
        $customers = Customer::with('user')->orderBy('business_name')->get();
        $products = Product::where('status', 'active')->orderBy('name')->get(['id', 'name', 'sku', 'bottle_size_ml', 'unit']);
        $quotations = Quotation::whereIn('status', ['approved'])->with('customer')->latest('id')->limit(100)->get();
        if ($sourceQuotation && !$quotations->contains('id', $sourceQuotation->id)) {
            $quotations->prepend($sourceQuotation);
        }
        $designs = collect();

        if (Schema::hasTable('design_requests') && ($customerId || $sourceQuotation?->customer_id)) {
            $id = $customerId ?: $sourceQuotation->customer_id;
            $designs = \App\Models\DesignRequest::where('customer_id', $id)->with('versions')->latest('id')->get();
        }

        return compact('customers', 'products', 'quotations', 'designs', 'sourceQuotation');
    }
}
