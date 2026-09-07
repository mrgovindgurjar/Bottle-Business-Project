<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateLeadRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'business_name' => [
                'required',
                'string',
                'max:300',
            ],

            'contact_name' => [
                'nullable',
                'string',
                'max:150',
            ],

            'mobile' => [
                'nullable',
                'string',
                'max:20',
            ],

            'email' => [
                'nullable',
                'email',
                'max:150',
            ],

            'business_type' => [
                'nullable',
                'string',
                'max:100',
            ],

            'source' => [
                'nullable',
                'string',
                'max:100',
            ],

            'requirement' => [
                'nullable',
                'string',
            ],

            'estimated_quantity' => [
                'nullable',
                'integer',
                'min:1',
            ],

            'estimated_value' => [
                'nullable',
                'numeric',
                'min:0',
            ],

            'assigned_to' => [
                'nullable',
                'exists:users,id',
            ],

            'status' => [
                'nullable',
                'string',
                'max:50',
            ],

            'next_followup_at' => [
                'nullable',
                'date',
            ],

            'notes' => [
                'nullable',
                'string',
            ],
        ];
    }
}
