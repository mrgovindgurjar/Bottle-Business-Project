<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SaveDesignVersionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermission('designs.edit') ?? false;
    }

    public function rules(): array
    {
        return [
            'design_data' => ['required', 'string', 'max:500000'],
            'version_name' => ['nullable', 'string', 'max:150'],
            'change_note' => ['nullable', 'string', 'max:3000'],
        ];
    }
}
