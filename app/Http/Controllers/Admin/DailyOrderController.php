<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DailyOrder;
use App\Services\RecurringOrderService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class DailyOrderController extends Controller
{
    public function __construct(private RecurringOrderService $service) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', DailyOrder::class);

        $query = DailyOrder::with(['customer', 'plan'])
            ->latest('scheduled_date')
            ->latest('id');

        if ($request->filled('q')) {
            $query->search($request->string('q')->toString());
        }

        if ($request->filled('status')) {
            $query->where('status', $request->string('status')->toString());
        }

        if ($request->filled('date')) {
            $query->whereDate('scheduled_date', $request->string('date')->toString());
        }

        $orders = $query->paginate(30)->withQueryString();

        return view('admin.daily-orders.index', compact('orders'));
    }

    public function show(DailyOrder $dailyOrder): View
    {
        $this->authorize('view', $dailyOrder);

        $dailyOrder->load(['customer.user', 'plan', 'items.product', 'order']);

        return view('admin.daily-orders.show', compact('dailyOrder'));
    }

    public function generate(Request $request): RedirectResponse
    {
        $this->authorize('create', DailyOrder::class);

        $validated = $request->validate([
            'date' => ['nullable', 'date'],
        ]);

        $until = !empty($validated['date'])
            ? Carbon::parse($validated['date'])->startOfDay()
            : today();

        $count = $this->service->generateDue(auth()->id(), $until);

        return back()->with('success', "{$count} daily order(s) generated.");
    }

    public function convert(DailyOrder $dailyOrder): RedirectResponse
    {
        $this->authorize('approve', $dailyOrder);

        $order = $this->service->convert($dailyOrder, auth()->id());

        return redirect()
            ->route('admin.orders.show', $order)
            ->with('success', 'Daily order converted to confirmed order.');
    }

    public function cancel(DailyOrder $dailyOrder): RedirectResponse
    {
        $this->authorize('cancel', $dailyOrder);
        $dailyOrder->update(['status' => 'cancelled']);

        return back()->with('success', 'Daily order cancelled.');
    }
}
