<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreDesignRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermission('designs.create') ?? false;
    }

    public function rules(): array
    {
        return [
            'customer_id' => ['required', 'integer', 'exists:customers,id'],
            'product_id' => ['nullable', 'integer', 'exists:products,id'],
            'title' => ['required', 'string', 'max:200'],
            'design_type' => ['required', 'string', 'max:40'],
            'brief' => ['nullable', 'string', 'max:5000'],
            'due_date' => ['nullable', 'date'],
        ];
    }
}
