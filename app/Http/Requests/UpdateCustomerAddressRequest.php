<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCustomerAddressRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermission('customers.edit') ?? false;
    }

    public function rules(): array
    {
        return [
            'type' => ['required', Rule::in(['billing', 'delivery', 'office', 'warehouse', 'other'])],
            'label' => ['nullable', 'string', 'max:100'],
            'contact_name' => ['nullable', 'string', 'max:150'],
            'contact_mobile' => ['nullable', 'string', 'max:20'],
            'address_line1' => ['required', 'string', 'max:300'],
            'address_line2' => ['nullable', 'string', 'max:300'],
            'landmark' => ['nullable', 'string', 'max:200'],
            'city' => ['required', 'string', 'max:100'],
            'state' => ['required', 'string', 'max:100'],
            'pincode' => ['required', 'string', 'max:10'],
            'country' => ['required', 'string', 'max:100'],
            'is_default' => ['nullable', 'boolean'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
