<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBatchRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'manufacturing_date' => ['required', 'date'],
            'expiry_date' => ['nullable', 'date', 'after_or_equal:manufacturing_date'],
            'quality_status' => ['required', 'string', 'in:pending,passed,failed,hold'],
            'quality_notes' => ['nullable', 'string', 'max:5000'],
            'notes' => ['nullable', 'string', 'max:5000'],
            'blocked_reason' => ['nullable', 'string', 'max:5000'],
        ];
    }
}
