<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
class StorePaymentRequest extends FormRequest {
 public function authorize(): bool { return $this->user()?->can('payments.create') ?? false; }
 public function rules(): array { return ['direction'=>'required|in:received,paid','payment_type'=>'required|string|max:40','customer_id'=>'nullable|exists:customers,id','supplier_id'=>'nullable|exists:suppliers,id','order_id'=>'nullable|exists:orders,id','purchase_id'=>'nullable|exists:purchases,id','invoice_id'=>'nullable|integer','payment_date'=>'required|date','amount'=>'required|numeric|min:0.01','method'=>'required|string|max:30','reference_number'=>'nullable|string|max:120','bank_name'=>'nullable|string|max:120','transaction_date'=>'nullable|date','notes'=>'nullable|string|max:5000','allocations'=>'nullable|array','allocations.*.invoice_id'=>'nullable|integer','allocations.*.order_id'=>'nullable|integer','allocations.*.purchase_id'=>'nullable|integer','allocations.*.amount'=>'nullable|numeric|min:0.01']; }
 public function withValidator($v): void { $v->after(function($v){ $c=(bool)$this->customer_id; $s=(bool)$this->supplier_id; if($c===$s) $v->errors()->add('customer_id','Exactly one of customer or supplier is required.'); if($this->direction==='received' && !$c) $v->errors()->add('customer_id','Customer payment requires a customer.'); if($this->direction==='paid' && !$s) $v->errors()->add('supplier_id','Supplier payment requires a supplier.'); }); }
}
