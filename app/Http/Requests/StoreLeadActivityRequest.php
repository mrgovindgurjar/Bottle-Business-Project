<?php

namespace App\Http\Requests;

use App\Models\Lead;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreLeadActivityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()
            ->can('activity', Lead::class);
    }

    public function rules(): array
    {
        return [

            'type' => [
                'required',
                Rule::in(
                    Lead::ACTIVITY_TYPES
                ),
            ],

            'subject' => [
                'nullable',
                'string',
                'max:200',
            ],

            'description' => [
                'required',
                'string',
            ],

            'activity_at' => [
                'required',
                'date',
            ],

            'next_followup_at' => [
                'nullable',
                'date',
            ],
        ];
    }
}