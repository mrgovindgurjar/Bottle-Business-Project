<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAttendanceRequest extends FormRequest
{
    public function authorize(): bool { return auth()->user()?->hasPermission('attendance.mark') === true; }
    public function rules(): array { return [
        'staff_id'=>['required','exists:staff,id'], 'attendance_date'=>['required','date'],
        'status'=>['required',Rule::in(['present','absent','late','half_day','leave','holiday'])],
        'check_in'=>['nullable','date_format:H:i'], 'check_out'=>['nullable','date_format:H:i'],
        'remarks'=>['nullable','string','max:1000'],
    ]; }
}
