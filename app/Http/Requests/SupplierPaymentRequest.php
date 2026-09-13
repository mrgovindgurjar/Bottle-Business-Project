<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
class SupplierPaymentRequest extends FormRequest
{
    public function authorize(): bool { return $this->user()?->can('purchases.pay') ?? false; }
    public function rules(): array { return ['payment_date'=>'required|date','amount'=>'required|numeric|gt:0','method'=>'required|string|max:40','reference'=>'nullable|string|max:150','notes'=>'nullable|string']; }
}
