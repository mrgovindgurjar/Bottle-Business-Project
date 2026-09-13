<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
class StoreIncomeRequest extends FormRequest { public function authorize(): bool { return $this->user()?->hasPermission('income.create') ?? false; } public function rules(): array { return ['category_id'=>'required|exists:income_categories,id','income_date'=>'required|date','amount'=>'required|numeric|min:0.01','payment_method'=>'required|string|max:30','reference_number'=>'nullable|string|max:255','customer_id'=>'nullable|exists:customers,id','invoice_id'=>'nullable|exists:invoices,id','order_id'=>'nullable|exists:orders,id','payment_id'=>'nullable|exists:payments,id','source_type'=>'required|in:manual,other,refund,adjustment','description'=>'required|string|max:255','notes'=>'nullable|string|max:5000']; } }
