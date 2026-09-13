<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreBatchRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'production_order_id' => ['required', 'integer', 'exists:production_orders,id'],
            'production_order_item_id' => ['nullable', 'integer', 'exists:production_order_items,id'],
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'design_id' => ['nullable', 'integer', 'exists:design_requests,id'],
            'customer_id' => ['nullable', 'integer', 'exists:customers,id'],
            'manufacturing_date' => ['required', 'date'],
            'expiry_date' => ['nullable', 'date', 'after_or_equal:manufacturing_date'],
            'produced_quantity' => ['required', 'numeric', 'gt:0'],
            'rejected_quantity' => ['nullable', 'numeric', 'gte:0'],
            'quality_status' => ['required', 'string', 'in:pending,passed,failed,hold'],
            'quality_notes' => ['nullable', 'string', 'max:5000'],
            'notes' => ['nullable', 'string', 'max:5000'],
        ];
    }
}
