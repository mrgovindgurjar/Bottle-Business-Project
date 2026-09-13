<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProductionRequest;
use App\Http\Requests\UpdateProductionRequest;
use App\Models\Order;
use App\Models\ProductionOrder;
use App\Models\ProductionStep;
use App\Models\User;
use App\Services\ProductionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
class ProductionController extends Controller
{
    public function __construct(private ProductionService $service)
    {
    }
    public function index(Request $r): View
    {
        $this->authorize('viewAny', ProductionOrder::class);
        $q = ProductionOrder::with(['order', 'customer', 'assignee'])->withCount('items')->latest('id');
        if ($r->filled('q'))
            $q->search($r->string('q'));
        if ($r->filled('status'))
            $q->where('status', $r->status);
        if ($r->filled('priority'))
            $q->where('priority', $r->priority);
        if ($r->filled('date'))
            $q->whereDate('scheduled_date', $r->date);
        $productions = $q->paginate(20)->withQueryString();
        $stats = ['total' => ProductionOrder::count(), 'pending' => ProductionOrder::whereIn('status', ['pending', 'scheduled'])->count(), 'in_progress' => ProductionOrder::where('status', 'in_progress')->count(), 'quality' => ProductionOrder::where('status', 'quality_check')->count(), 'completed' => ProductionOrder::where('status', 'completed')->count(), 'on_hold' => ProductionOrder::where('status', 'on_hold')->count()];
        return view('admin.production.index', compact('productions', 'stats'));
    }
    public function create(): View
    {
        $this->authorize('create', ProductionOrder::class);
        $orders = Order::with(['customer', 'items'])->whereIn('status', ['confirmed', 'on_hold'])->whereNotExists(function ($q) {
            $q->select(DB::raw(1))->from('production_orders')->whereColumn('production_orders.order_id', 'orders.id')->where('production_orders.status', '!=', 'cancelled'); })->latest('id')->get();
        $users = User::orderBy('name')->get();
        return view('admin.production.create', compact('orders', 'users'));
    }
    public function store(StoreProductionRequest $r): RedirectResponse
    {
        $this->authorize('create', ProductionOrder::class);
        $order = Order::findOrFail($r->integer('order_id'));
        $p = $this->service->createFromOrder($order, (int) $r->user()->id);
        $this->service->update($p, $r->validated());
        return redirect()->route('admin.production.show', $p)->with('success', 'Production job created successfully.');
    }
    public function show(ProductionOrder $production): View
    {
        $p = $production;
        $this->authorize('view', $p);
        $p->load(['order.customer', 'order.items.product', 'customer.user', 'design', 'assignee', 'creator', 'items.product', 'items.design', 'steps']);
        return view('admin.production.show', compact('p'));
    }
    public function edit(ProductionOrder $production): View
    {
        $p = $production;
        $this->authorize('update', $p);
        $p->load(['assignee']);
        $users = User::orderBy('name')->get();
        return view('admin.production.edit', compact('p', 'users'));
    }
    public function update(UpdateProductionRequest $r, ProductionOrder $production): RedirectResponse
    {
        $p = $production;
        
        $this->authorize('update', $p);
        $this->service->update($p, $r->validated());
        return back()->with('success', 'Production details updated.');
    }
    public function fromOrder(Order $order): RedirectResponse
    {
        $this->authorize('create', ProductionOrder::class);
        $p = $this->service->createFromOrder($order, (int) auth()->id());
        return redirect()->route('admin.production.show', $p)->with('success', 'Production job ready.');
    }
    public function start(ProductionOrder $production): RedirectResponse
    {
        $p = $production;
        $this->authorize('start', $p);
        $target = $p->status === 'pending' ? 'in_progress' : ($p->status === 'scheduled' ? 'in_progress' : 'in_progress');
        $this->service->changeStatus($p, $target, (int) auth()->id());
        return back()->with('success', 'Production started.');
    }
    public function status(Request $r, ProductionOrder $production): RedirectResponse
    {
        $p = $production;
        $d = $r->validate(['status' => ['required', 'string', 'in:pending,scheduled,in_progress,quality_check,completed,on_hold,cancelled']]);
        $permission = $d['status'] === 'completed' ? 'complete' : ($d['status'] === 'cancelled' ? 'cancel' : 'progress');
        $this->authorize($permission, $p);
        $this->service->changeStatus($p, $d['status'], (int) auth()->id());
        return back()->with('success', 'Production status updated.');
    }
    public function progress(Request $r, ProductionOrder $production): RedirectResponse
    {
        $p = $production;

        $this->authorize('progress', $p);
        $d = $r->validate(['produced_quantity' => ['required', 'numeric', 'gte:0'], 'rejected_quantity' => ['nullable', 'numeric', 'gte:0'], 'waste_quantity' => ['nullable', 'numeric', 'gte:0']]);
        $this->service->updateProgress($p, $d);
        return back()->with('success', 'Production quantity updated.');
    }
    public function step(Request $r, ProductionOrder $production, ProductionStep $step): RedirectResponse
    {
        $p = $production;

        $this->authorize('step', $p);
        abort_unless($step->production_order_id === $p->id, 404);
        $d = $r->validate(['status' => ['required', 'in:pending,in_progress,completed,skipped']]);
        $this->service->updateStep($step, $d['status']);
        return back()->with('success', 'Production step updated.');
    }
    public function cancel(ProductionOrder $production): RedirectResponse
    {
        $p = $production;

        $this->authorize('cancel', $p);
        $this->service->changeStatus($p, 'cancelled', (int) auth()->id());
        if ($p->order && $p->order->status === 'in_production')
            $p->order->update(['status' => 'on_hold']);
        return back()->with('success', 'Production cancelled.');
    }
}
