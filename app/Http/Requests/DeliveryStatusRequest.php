<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DeliveryStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        $action = $this->input('action');
        return $action === 'deliver'
            ? (bool) $this->user()?->hasPermission('deliveries.deliver')
            : ($action === 'fail' && (bool) $this->user()?->hasPermission('deliveries.fail'));
    }

    public function rules(): array
    {
        return [
            'action' => ['required','in:deliver,fail'],
            'receiver_name' => ['required_if:action,deliver','nullable','string','max:150'],
            'receiver_mobile' => ['nullable','string','max:30'],
            'delivered_quantities' => ['nullable','array'],
            'delivered_quantities.*' => ['numeric','gte:0'],
            'failed_reason' => ['required_if:action,fail','nullable','string','max:5000'],
            'proof' => ['nullable','file','image','max:5120','mimes:jpg,jpeg,png,webp'],
            'signature' => ['nullable','file','image','max:5120','mimes:jpg,jpeg,png,webp'],
        ];
    }
}
