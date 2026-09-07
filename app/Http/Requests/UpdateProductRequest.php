<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $productId = $this->route('product')?->id;

        return [
            'name' => [
                'required',
                'string',
                'max:200',
            ],

            'sku' => [
                'required',
                'string',
                'max:100',
                Rule::unique('products', 'sku')
                    ->ignore($productId),
            ],

            'bottle_size_ml' => [
                'required',
                'integer',
                'min:1',
            ],

            'bottle_type' => [
                'nullable',
                'string',
                'max:50',
            ],

            'material' => [
                'nullable',
                'string',
                'max:50',
            ],

            'units_per_box' => [
                'required',
                'integer',
                'min:1',
            ],

            'unit' => [
                'required',
                'string',
                'max:30',
            ],

            'description' => [
                'nullable',
                'string',
            ],

            'status' => [
                'nullable',
                'in:active,inactive',
            ],
        ];
    }
}