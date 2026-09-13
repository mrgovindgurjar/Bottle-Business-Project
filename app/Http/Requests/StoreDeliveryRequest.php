<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreDeliveryRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'order_id' => ['required','integer','exists:orders,id'],
            'delivery_date' => ['required','date'],
            'scheduled_date' => ['nullable','date'],
            'delivery_address' => ['nullable','string','max:5000'],
            'delivery_city' => ['nullable','string','max:100'],
            'delivery_state' => ['nullable','string','max:100'],
            'delivery_pincode' => ['nullable','string','max:10'],
            'assigned_to' => ['nullable','integer','exists:users,id'],
            'driver_name' => ['nullable','string','max:150'],
            'driver_mobile' => ['nullable','string','max:30'],
            'vehicle_number' => ['nullable','string','max:50'],
            'notes' => ['nullable','string','max:5000'],
            'dispatch_notes' => ['nullable','string','max:5000'],
            'items' => ['required','array','min:1'],
            'items.*.order_item_id' => ['required','integer','exists:order_items,id'],
            'items.*.product_id' => ['nullable','integer','exists:products,id'],
            'items.*.batch_id' => ['nullable','integer','exists:batches,id'],
            'items.*.description' => ['nullable','string','max:2000'],
            'items.*.quantity' => ['required','numeric','gt:0'],
            'items.*.unit' => ['required','string','max:30'],
            'items.*.notes' => ['nullable','string','max:2000'],
        ];
    }
}
