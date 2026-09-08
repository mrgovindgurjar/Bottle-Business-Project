<?php

namespace App\Http\Requests;

use App\Models\Lead;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateLeadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()
            ->can(
                'update',
                $this->route('lead')
            );
    }

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
                'max:190',
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

            'quantity_unit' => [
                'nullable',
                'string',
                'max:30',
            ],

            'order_frequency' => [
                'nullable',
                'string',
                'max:50',
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
                'required',
                Rule::in(Lead::STATUSES),
            ],

            'next_followup_at' => [
                'nullable',
                'date',
            ],

            'address' => [
                'nullable',
                'string',
                'max:500',
            ],

            'city' => [
                'nullable',
                'string',
                'max:100',
            ],

            'state' => [
                'nullable',
                'string',
                'max:100',
            ],

            'pincode' => [
                'nullable',
                'string',
                'max:10',
            ],

            'notes' => [
                'nullable',
                'string',
            ],

            'lost_reason' => [
                'nullable',
                'string',
            ],
        ];
    }
}