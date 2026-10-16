<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AllocatePaymentRequest extends FormRequest
{
    public function authorize(): bool { return (bool) $this->user()?->hasPermission('payments.allocate'); }
    public function rules(): array
    {
        return [
            'allocations' => ['required','array','min:1'],
            'allocations.*.invoice_id' => ['nullable','exists:invoices,id'],
            'allocations.*.order_id' => ['nullable','exists:orders,id'],
            'allocations.*.purchase_id' => ['nullable','exists:purchases,id'],
            'allocations.*.amount' => ['required','numeric','min:0.01'],
            'allocations.*.notes' => ['nullable','string','max:1000'],
        ];
    }
}
