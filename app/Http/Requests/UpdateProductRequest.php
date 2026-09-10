<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProductRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        $productId = $this->route('product')?->id;

        return [
            'name' => ['required','string','max:200'],
            'sku' => ['required','string','max:100','regex:/^[A-Za-z0-9._-]+$/',Rule::unique('products','sku')->ignore($productId)],
            'bottle_size_ml' => ['required','integer','min:1','max:10000'],
            'bottle_type' => ['nullable','string','max:50'],
            'material' => ['nullable','string','max:50'],
            'cap_type' => ['nullable','string','max:50'],
            'label_type' => ['nullable','string','max:50'],
            'units_per_box' => ['required','integer','min:1','max:10000'],
            'unit' => ['required','string','max:30'],
            'is_custom_branding' => ['nullable','boolean'],
            'short_description' => ['nullable','string','max:300'],
            'description' => ['nullable','string'],
            'status' => ['required','in:active,inactive'],
            'sort_order' => ['nullable','integer','min:0'],
            'image' => ['nullable','image','mimes:jpg,jpeg,png,webp','max:2048'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_custom_branding' => $this->boolean('is_custom_branding'),
            'status' => $this->input('status', 'active'),
            'sort_order' => $this->input('sort_order', 0),
        ]);
    }
}
