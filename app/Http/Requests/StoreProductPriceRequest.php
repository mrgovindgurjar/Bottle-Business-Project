<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductPriceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'customer_id' => [
                'nullable',
                'exists:customers,id',
            ],

            'pricing_type' => [
                'required',
                'in:standard,bulk,recurring,custom',
            ],

            'min_quantity' => [
                'required',
                'integer',
                'min:1',
            ],

            'max_quantity' => [
                'nullable',
                'integer',
                'gte:min_quantity',
            ],

            'unit_price' => [
                'required',
                'numeric',
                'min:0',
            ],

            'effective_from' => [
                'nullable',
                'date',
            ],

            'effective_to' => [
                'nullable',
                'date',
                'after_or_equal:effective_from',
            ],

            'status' => [
                'nullable',
                'in:active,inactive',
            ],
        ];
    }
}