<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreRecurringPlanRequest;
use App\Http\Requests\UpdateRecurringPlanRequest;
use App\Models\Customer;
use App\Models\Product;
use App\Models\RecurringPlan;
use App\Services\RecurringOrderService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RecurringPlanController extends Controller
{
    public function __construct(private RecurringOrderService $service) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', RecurringPlan::class);

        $query = RecurringPlan::with(['customer.user'])
            ->withCount('dailyOrders')
            ->latest('id');

        if ($request->filled('q')) {
            $query->search($request->string('q')->toString());
        }

        if ($request->filled('status')) {
            $query->where('status', $request->string('status')->toString());
        }

        if ($request->filled('customer_id')) {
            $query->where('customer_id', $request->integer('customer_id'));
        }

        $plans = $query->paginate(20)->withQueryString();

        return view('admin.recurring-plans.index', compact('plans'));
    }

    public function create(): View
    {
        $this->authorize('create', RecurringPlan::class);

        return view('admin.recurring-plans.create', $this->formData());
    }

    public function store(StoreRecurringPlanRequest $request): RedirectResponse
    {
        $this->authorize('create', RecurringPlan::class);

        $plan = $this->service->createPlan($request->validated(), auth()->id());

        return redirect()
            ->route('admin.recurring-plans.show', $plan)
            ->with('success', 'Recurring plan created.');
    }

    public function show(RecurringPlan $recurringPlan): View
    {
        $this->authorize('view', $recurringPlan);

        $recurringPlan->load([
            'customer.user',
            'items.product',
            'dailyOrders' => fn ($query) => $query->latest('scheduled_date')->limit(20),
        ]);

        return view('admin.recurring-plans.show', [
            'p' => $recurringPlan,
        ]);
    }

    public function edit(RecurringPlan $recurringPlan): View
    {
        $this->authorize('update', $recurringPlan);

        $recurringPlan->load('items');

        return view('admin.recurring-plans.edit', array_merge(
            ['p' => $recurringPlan],
            $this->formData()
        ));
    }

    public function update(UpdateRecurringPlanRequest $request, RecurringPlan $recurringPlan): RedirectResponse
    {
        $this->authorize('update', $recurringPlan);

        $this->service->updatePlan($recurringPlan, $request->validated());

        return redirect()
            ->route('admin.recurring-plans.show', $recurringPlan)
            ->with('success', 'Recurring plan updated.');
    }

    public function approve(RecurringPlan $recurringPlan): RedirectResponse
    {
        $this->authorize('approve', $recurringPlan);
        $this->service->approve($recurringPlan, auth()->id());

        return back()->with('success', 'Recurring plan approved.');
    }

    public function pause(RecurringPlan $recurringPlan): RedirectResponse
    {
        $this->authorize('pause', $recurringPlan);
        $this->service->pause($recurringPlan);

        return back()->with('success', 'Recurring plan paused.');
    }

    public function resume(RecurringPlan $recurringPlan): RedirectResponse
    {
        $this->authorize('resume', $recurringPlan);
        $this->service->resume($recurringPlan);

        return back()->with('success', 'Recurring plan resumed.');
    }

    public function cancel(RecurringPlan $recurringPlan): RedirectResponse
    {
        $this->authorize('cancel', $recurringPlan);
        $this->service->cancel($recurringPlan);

        return back()->with('success', 'Recurring plan cancelled.');
    }

    public function destroy(RecurringPlan $recurringPlan): RedirectResponse
    {
        $this->authorize('delete', $recurringPlan);
        $recurringPlan->delete();

        return redirect()
            ->route('admin.recurring-plans.index')
            ->with('success', 'Recurring plan archived.');
    }

    private function formData(): array
    {
        return [
            'customers' => Customer::with('user')->orderBy('business_name')->get(),
            'products' => Product::where('status', 'active')
                ->orderBy('name')
                ->get(['id', 'name', 'sku', 'bottle_size_ml', 'unit']),
        ];
    }
}
