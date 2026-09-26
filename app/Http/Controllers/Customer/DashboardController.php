<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $customer = $request->user()->customer()->with('user')->firstOrFail();

        return view('customer.dashboard', [
            'customer' => $customer,
            'user' => $request->user(),
        ]);
    }

    public function updateProfile(Request $request): RedirectResponse
    {
        $user = $request->user();
        $customer = $user->customer()->firstOrFail();

        $data = $request->validate([
            'business_name' => ['required', 'string', 'max:300'],
            'contact_name' => ['required', 'string', 'max:150'],
            'mobile' => ['required', 'string', 'max:30', Rule::unique('users', 'mobile')->ignore($user->id)],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'business_type' => ['nullable', 'string', 'max:100'],
            'gstin' => ['nullable', 'string', 'max:15'],
            'address' => ['nullable', 'string', 'max:500'],
            'city' => ['nullable', 'string', 'max:100'],
            'state' => ['nullable', 'string', 'max:100'],
            'pincode' => ['nullable', 'string', 'max:20'],
        ]);

        DB::transaction(function () use ($user, $customer, $data) {
            $user->update([
                'name' => $data['contact_name'],
                'mobile' => $data['mobile'],
                'email' => $data['email'],
            ]);

            $customer->update([
                'business_name' => $data['business_name'],
                'business_type' => $data['business_type'] ?? null,
                'gstin' => $data['gstin'] ?? null,
                'address' => $data['address'] ?? null,
                'city' => $data['city'] ?? null,
                'state' => $data['state'] ?? null,
                'pincode' => $data['pincode'] ?? null,
            ]);
        });

        return back()->with('success', 'Business profile updated successfully.');
    }
}
