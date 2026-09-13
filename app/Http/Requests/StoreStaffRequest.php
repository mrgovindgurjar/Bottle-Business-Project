<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreStaffRequest extends FormRequest
{
    public function authorize(): bool { return auth()->user()?->hasPermission('staff.create') === true; }
    public function rules(): array { return [
        'employee_code'=>['nullable','string','max:50','unique:staff,employee_code'],
        'first_name'=>['required','string','max:100'], 'last_name'=>['nullable','string','max:100'],
        'email'=>['nullable','email','max:150','unique:staff,email'], 'mobile'=>['nullable','string','max:30'],
        'date_of_birth'=>['nullable','date','before:today'], 'gender'=>['nullable',Rule::in(['male','female','other'])],
        'designation'=>['nullable','string','max:100'], 'department'=>['nullable','string','max:100'],
        'joining_date'=>['required','date'], 'employment_type'=>['required',Rule::in(['full_time','part_time','contract','intern'])],
        'salary'=>['nullable','numeric','min:0'], 'address'=>['nullable','string','max:500'],
        'city'=>['nullable','string','max:100'], 'state'=>['nullable','string','max:100'], 'pincode'=>['nullable','string','max:20'],
        'emergency_contact_name'=>['nullable','string','max:150'], 'emergency_contact_mobile'=>['nullable','string','max:30'],
        'status'=>['required',Rule::in(['active','inactive','on_leave','terminated'])], 'notes'=>['nullable','string','max:2000'],
    ]; }
}
