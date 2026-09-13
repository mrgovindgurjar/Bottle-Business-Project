<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
class AllocatePaymentRequest extends FormRequest { public function authorize(): bool{return $this->user()?->can('payments.allocate') ?? false;} public function rules():array{return ['allocations'=>'required|array|min:1','allocations.*.invoice_id'=>'nullable|integer','allocations.*.order_id'=>'nullable|integer','allocations.*.purchase_id'=>'nullable|integer','allocations.*.amount'=>'required|numeric|min:0.01','notes'=>'nullable|string|max:1000'];} }
