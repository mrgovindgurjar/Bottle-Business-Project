<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAttendanceRequest extends FormRequest
{
    public function authorize(): bool { return auth()->user()?->hasPermission('attendance.edit') === true; }
    public function rules(): array { return [
        'status'=>['required',Rule::in(['present','absent','late','half_day','leave','holiday'])],
        'check_in'=>['nullable','date_format:H:i'], 'check_out'=>['nullable','date_format:H:i'],
        'remarks'=>['nullable','string','max:1000'],
    ]; }
}
