<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
class UpdateSalaryPaymentRequest extends FormRequest
{
    public function authorize(): bool { return auth()->user()?->hasPermission('salary.edit') === true; }
    public function rules(): array { return [
        'paid_leave_days'=>['nullable','numeric','min:0'], 'bonus'=>['nullable','numeric','min:0'], 'deduction'=>['nullable','numeric','min:0'],
        'payable_days'=>['nullable','numeric','min:0'], 'payment_date'=>['nullable','date'],
        'payment_method'=>['nullable',Rule::in(['cash','upi','bank_transfer','cheque','neft','rtgs','imps','other'])],
        'reference_number'=>['nullable','string','max:100'], 'notes'=>['nullable','string','max:2000'],
    ]; }
}
