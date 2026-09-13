<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBatchRequest;
use App\Http\Requests\UpdateBatchRequest;
use App\Models\Batch;
use App\Models\BatchAllocation;
use App\Models\Customer;
use App\Models\Order;
use App\Models\ProductionOrder;
use App\Models\Product;
use App\Services\BatchService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BatchController extends Controller
{
    public function __construct(private BatchService $service) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Batch::class);
        $this->service->expireDueBatches();
        $query = Batch::with(['productionOrder', 'product', 'customer'])
            ->withSum('allocations', 'quantity')->latest('id');

        if ($request->filled('q')) $query->search($request->string('q'));
        if ($request->filled('status')) $query->where('status', $request->string('status'));
        if ($request->filled('quality_status')) $query->where('quality_status', $request->string('quality_status'));
        if ($request->filled('customer_id')) $query->where('customer_id', $request->integer('customer_id'));
        if ($request->filled('date')) $query->whereDate('manufacturing_date', $request->date);

        $batches = $query->paginate(20)->withQueryString();
        $customers = Customer::orderBy('business_name')->get(['id', 'business_name', 'customer_code']);
        $stats = [
            'total' => Batch::count(),
            'quality_pending' => Batch::where('status', 'quality_pending')->count(),
            'released' => Batch::where('status', 'released')->count(),
            'allocated' => Batch::where('status', 'allocated')->count(),
            'exhausted' => Batch::where('status', 'exhausted')->count(),
            'blocked' => Batch::where('status', 'blocked')->count(),
        ];

        return view('admin.batches.index', compact('batches', 'stats', 'customers'));
    }

    public function create(Request $request): View
    {
        $this->authorize('create', Batch::class);
        $production = $request->filled('production')
            ? ProductionOrder::with(['items.product', 'customer', 'design'])->findOrFail($request->integer('production'))
            : null;
        $productions = ProductionOrder::with(['items.product', 'customer'])->where('status', 'completed')->latest('id')->limit(100)->get();
        $products = Product::where('status', 'active')->orderBy('name')->get(['id', 'name', 'sku', 'bottle_size_ml', 'unit']);
        $customers = Customer::orderBy('business_name')->get(['id', 'business_name', 'customer_code']);
        return view('admin.batches.create', compact('production', 'productions', 'products', 'customers'));
    }

    public function store(StoreBatchRequest $request): RedirectResponse
    {
        $this->authorize('create', Batch::class);
        $batch = $this->service->create($request->validated(), (int) $request->user()->id);
        return redirect()->route('admin.batches.show', $batch)->with('success', 'Batch created successfully.');
    }

    public function show(Batch $batch): View
    {
        $this->authorize('view', $batch);
        $batch->load(['productionOrder', 'productionOrderItem', 'product', 'design', 'customer.user', 'creator', 'releaser', 'allocations.customer', 'allocations.order', 'allocations.delivery', 'allocations.allocator']);
        $customers = Customer::with('user')->orderBy('business_name')->get();
        $orders = Order::with('customer')->where('customer_id', $batch->customer_id)->whereNotIn('status', ['cancelled'])->latest('id')->limit(100)->get();
        return view('admin.batches.show', compact('batch', 'customers', 'orders'));
    }

    public function edit(Batch $batch): View
    {
        $this->authorize('update', $batch);
        $batch->load(['productionOrder', 'product', 'design', 'customer']);
        return view('admin.batches.edit', compact('batch'));
    }

    public function update(UpdateBatchRequest $request, Batch $batch): RedirectResponse
    {
        $this->authorize('update', $batch);
        $this->service->update($batch, $request->validated());
        return back()->with('success', 'Batch updated successfully.');
    }

    public function release(Batch $batch): RedirectResponse
    {
        $this->authorize('release', $batch);
        $this->service->release($batch, (int) auth()->id());
        return back()->with('success', 'Batch released for allocation.');
    }

    public function block(Request $request, Batch $batch): RedirectResponse
    {
        $this->authorize('block', $batch);
        $data = $request->validate(['reason' => ['required', 'string', 'max:5000']]);
        $this->service->block($batch, $data['reason']);
        return back()->with('success', 'Batch blocked.');
    }

    public function allocate(Request $request, Batch $batch): RedirectResponse
    {
        $this->authorize('allocate', $batch);
        $data = $request->validate([
            'customer_id' => ['required', 'integer', 'exists:customers,id'],
            'order_id' => ['nullable', 'integer', 'exists:orders,id'],
            'delivery_id' => ['nullable', 'integer', 'exists:deliveries,id'],
            'quantity' => ['required', 'numeric', 'gt:0'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);
        $this->service->allocate($batch, $data, (int) auth()->id());
        return back()->with('success', 'Batch allocation recorded successfully.');
    }

    public function destroy(Batch $batch): RedirectResponse
    {
        $this->authorize('delete', $batch);
        if ($batch->allocations()->exists()) return back()->with('error', 'Allocated batches cannot be deleted.');
        if (in_array($batch->status, ['allocated', 'exhausted'], true)) return back()->with('error', 'Allocated/exhausted batches cannot be deleted.');
        $batch->delete();
        return redirect()->route('admin.batches.index')->with('success', 'Batch moved to history.');
    }
}
