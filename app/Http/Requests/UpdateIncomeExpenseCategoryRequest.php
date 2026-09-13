<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateIncomeExpenseCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermission('finance.categories') === true;
    }

    public function rules(): array
    {
        $type = $this->input('type');
        $table = $type === 'expense' ? 'expense_categories' : 'income_categories';
        $category = $this->route('category');

        return [
            'type' => ['required', Rule::in(['income', 'expense'])],
            'name' => ['required', 'string', 'max:100'],
            'slug' => [
                'required', 'string', 'max:120', 'alpha_dash',
                Rule::unique($table, 'slug')->ignore($category?->id),
            ],
            'status' => ['required', Rule::in(['active', 'inactive'])],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
        ];
    }
}
