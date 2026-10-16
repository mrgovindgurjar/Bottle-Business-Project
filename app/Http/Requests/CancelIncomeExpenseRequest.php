<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CancelIncomeExpenseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermission(
            $this->route('income') ? 'income.cancel' : 'expenses.cancel'
        ) ?? false;
    }

    public function rules(): array
    {
        return ['reason' => 'required|string|min:3|max:2000'];
    }
}
