<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
class UpdateSupplierRequest extends FormRequest
{
    public function authorize(): bool { return $this->user()?->can('suppliers.edit') ?? false; }
    public function rules(): array { return [
        'business_name'=>['required','string','max:200'],'contact_name'=>['nullable','string','max:150'],'mobile'=>['nullable','string','max:30'],
        'email'=>['nullable','email','max:190'],'gstin'=>['nullable','string','max:30'],'category'=>['nullable','string','max:80'],
        'payment_terms_days'=>['nullable','integer','min:0','max:3650'],'address'=>['nullable','string','max:500'],'city'=>['nullable','string','max:100'],
        'state'=>['nullable','string','max:100'],'pincode'=>['nullable','string','max:10'],'status'=>['required','in:active,inactive,blocked'],'notes'=>['nullable','string'],
    ]; }
}
