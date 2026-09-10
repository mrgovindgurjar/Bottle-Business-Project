<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCustomerAddressRequest;
use App\Http\Requests\StoreCustomerRequest;
use App\Http\Requests\UpdateCustomerAddressRequest;
use App\Http\Requests\UpdateCustomerRequest;
use App\Models\Customer;
use App\Models\CustomerAddress;
use App\Services\CustomerService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class CustomerController extends Controller
{
    public function __construct(
        protected CustomerService $customerService
    ) {
    }

    public function index(Request $request): View
    {
        Gate::authorize('viewAny', Customer::class);

        $query = Customer::query()
            ->with(['user:id,name,email,mobile', 'defaultAddress:id,customer_id,type,city,state,pincode'])
            ->search($request->input('search'));

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        if ($request->filled('city')) {
            $query->where('city', $request->string('city'));
        }

        if ($request->filled('source')) {
            $query->where('source', $request->string('source'));
        }

        $customers = $query
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        $stats = [
            'total' => Customer::count(),
            'active' => Customer::where('status', Customer::STATUS_ACTIVE)->count(),
            'new' => Customer::where('created_at', '>=', now()->startOfMonth())->count(),
            'blocked' => Customer::where('status', Customer::STATUS_BLOCKED)->count(),
        ];

        $cities = Customer::whereNotNull('city')
            ->where('city', '!=', '')
            ->distinct()
            ->orderBy('city')
            ->pluck('city');

        $sources = Customer::whereNotNull('source')
            ->where('source', '!=', '')
            ->distinct()
            ->orderBy('source')
            ->pluck('source');

        return view('admin.customers.index', compact(
            'customers',
            'stats',
            'cities',
            'sources'
        ));
    }

    public function create(): View
    {
        Gate::authorize('create', Customer::class);

        return view('admin.customers.create');
    }

    public function store(StoreCustomerRequest $request): RedirectResponse
    {
        $result = $this->customerService->create($request->validated());

        return redirect()
            ->route('admin.customers.show', $result['customer'])
            ->with('success', 'Customer created successfully.')
            ->with('customer_credentials', $result['credentials']);
    }

    public function show(Customer $customer): View
    {
        Gate::authorize('view', $customer);

        $customer->load([
            'user',
            'addresses' => fn ($query) => $query->latest('is_default')->latest('id'),
            'prices.product',
        ]);

        return view('admin.customers.show', compact('customer'));
    }

    public function edit(Customer $customer): View
    {
        Gate::authorize('update', $customer);

        $customer->load('user');

        return view('admin.customers.edit', compact('customer'));
    }

    public function update(
        UpdateCustomerRequest $request,
        Customer $customer
    ): RedirectResponse {
        $this->customerService->update(
            $customer,
            $request->validated()
        );

        return redirect()
            ->route('admin.customers.show', $customer)
            ->with('success', 'Customer updated successfully.');
    }

    public function destroy(Customer $customer): RedirectResponse
    {
        Gate::authorize('delete', $customer);

        $this->customerService->delete($customer);

        return redirect()
            ->route('admin.customers.index')
            ->with('success', 'Customer deactivated and archived successfully.');
    }

    public function storeAddress(
        StoreCustomerAddressRequest $request,
        Customer $customer
    ): RedirectResponse {
        Gate::authorize('update', $customer);

        $this->customerService->saveAddress(
            $customer,
            $request->validated()
        );

        return back()->with('success', 'Address added successfully.');
    }

    public function updateAddress(
        UpdateCustomerAddressRequest $request,
        Customer $customer,
        CustomerAddress $address
    ): RedirectResponse {
        Gate::authorize('update', $customer);

        abort_unless($address->customer_id === $customer->id, 404);

        $this->customerService->saveAddress(
            $customer,
            $request->validated(),
            $address
        );

        return back()->with('success', 'Address updated successfully.');
    }

    public function destroyAddress(
        Customer $customer,
        CustomerAddress $address
    ): RedirectResponse {
        Gate::authorize('update', $customer);

        abort_unless($address->customer_id === $customer->id, 404);

        $this->customerService->deleteAddress($address);

        return back()->with('success', 'Address removed successfully.');
    }

    public function setDefaultAddress(
        Customer $customer,
        CustomerAddress $address
    ): RedirectResponse {
        Gate::authorize('update', $customer);

        abort_unless($address->customer_id === $customer->id, 404);

        $customer->addresses()->update(['is_default' => false]);
        $address->update(['is_default' => true]);

        return back()->with('success', 'Default address updated.');
    }
}
