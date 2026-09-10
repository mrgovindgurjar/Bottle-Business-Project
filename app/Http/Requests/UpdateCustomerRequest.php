<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCustomerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermission('customers.edit') ?? false;
    }

    public function rules(): array
    {
        $customer = $this->route('customer');
        $userId = $customer?->user_id;

        return [
            'business_name' => ['required', 'string', 'max:300'],
            'contact_name' => ['required', 'string', 'max:150'],
            'mobile' => [
                'required', 'string', 'max:20',
                Rule::unique('users', 'mobile')->ignore($userId),
            ],
            'email' => [
                'nullable', 'email', 'max:255',
                Rule::unique('users', 'email')->ignore($userId),
            ],
            'business_type' => ['nullable', 'string', 'max:100'],
            'source' => ['nullable', 'string', 'max:100'],
            'gstin' => [
                'nullable', 'string', 'max:15',
                Rule::unique('customers', 'gstin')->ignore($customer?->id),
            ],
            'pan_number' => ['nullable', 'string', 'max:10'],
            'payment_terms_days' => ['nullable', 'integer', 'min:0', 'max:365'],
            'credit_limit' => ['nullable', 'numeric', 'min:0', 'max:9999999999.99'],
            'currency' => ['nullable', 'string', 'size:3'],
            'address' => ['nullable', 'string', 'max:500'],
            'city' => ['nullable', 'string', 'max:100'],
            'state' => ['nullable', 'string', 'max:100'],
            'pincode' => ['nullable', 'string', 'max:10'],
            'status' => ['required', Rule::in(['active', 'inactive', 'blocked'])],
            'notes' => ['nullable', 'string', 'max:5000'],
            'password' => ['nullable', 'string', 'min:8', 'max:100'],
        ];
    }
}
