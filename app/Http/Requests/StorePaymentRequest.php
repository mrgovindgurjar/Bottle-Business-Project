<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePaymentRequest extends FormRequest
{
    public function authorize(): bool { return (bool) $this->user()?->hasPermission('payments.create'); }

    public function rules(): array
    {
        return [
            'direction' => ['required','in:received,paid'],
            'payment_type' => ['required','string','max:40'],
            'customer_id' => ['nullable','exists:customers,id'],
            'supplier_id' => ['nullable','exists:suppliers,id'],
            'order_id' => ['nullable','exists:orders,id'],
            'purchase_id' => ['nullable','exists:purchases,id'],
            'invoice_id' => ['nullable','exists:invoices,id'],
            'payment_date' => ['required','date'],
            'amount' => ['required','numeric','min:0.01'],
            'method' => ['required','string','max:30'],
            'reference_number' => ['nullable','string','max:120'],
            'bank_name' => ['nullable','string','max:120'],
            'transaction_date' => ['nullable','date'],
            'notes' => ['nullable','string','max:5000'],
            'allocations' => ['nullable','array'],
            'allocations.*.invoice_id' => ['nullable','exists:invoices,id'],
            'allocations.*.order_id' => ['nullable','exists:orders,id'],
            'allocations.*.purchase_id' => ['nullable','exists:purchases,id'],
            'allocations.*.amount' => ['nullable','numeric','min:0.01'],
            'allocations.*.notes' => ['nullable','string','max:1000'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $customer = (bool) $this->customer_id;
            $supplier = (bool) $this->supplier_id;
            if ($customer === $supplier) $validator->errors()->add('customer_id', 'Exactly one of customer or supplier is required.');
            if ($this->direction === 'received' && !$customer) $validator->errors()->add('customer_id', 'Customer payment requires a customer.');
            if ($this->direction === 'paid' && !$supplier) $validator->errors()->add('supplier_id', 'Supplier payment requires a supplier.');
            if ($this->direction === 'received' && $this->purchase_id) $validator->errors()->add('purchase_id', 'Received customer payments cannot target a purchase.');
            if ($this->direction === 'paid' && ($this->invoice_id || $this->order_id)) $validator->errors()->add('invoice_id', 'Supplier payments cannot target customer receivables.');
        });
    }
}
