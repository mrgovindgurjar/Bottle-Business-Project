<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
class UpdateExpenseRequest extends FormRequest { public function authorize(): bool { return $this->user()?->hasPermission('expenses.edit') ?? false; } public function rules(): array { return ['category_id'=>'required|exists:expense_categories,id','expense_date'=>'required|date','amount'=>'required|numeric|min:0.01','payment_method'=>'required|string|max:30','reference_number'=>'nullable|string|max:255','supplier_id'=>'nullable|exists:suppliers,id','purchase_id'=>'nullable|exists:purchases,id','payment_id'=>'nullable|exists:payments,id','source_type'=>'required|in:manual,purchase,operational,adjustment','description'=>'required|string|max:255','notes'=>'nullable|string|max:5000']; } }
