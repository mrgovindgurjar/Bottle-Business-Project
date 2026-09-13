<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
class RefundPaymentRequest extends FormRequest { public function authorize():bool{return $this->user()?->can('payments.refund') ?? false;} public function rules():array{return ['amount'=>'required|numeric|min:0.01','payment_date'=>'required|date','method'=>'required|string|max:30','reference_number'=>'nullable|string|max:120','notes'=>'nullable|string|max:1000'];} }
