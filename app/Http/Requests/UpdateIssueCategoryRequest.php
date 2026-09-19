<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
class UpdateIssueCategoryRequest extends FormRequest
{
    public function authorize(): bool { return auth()->check() && auth()->user()->hasPermission('issues.categories'); }
    public function rules(): array { return ['name'=>['required','string','max:100'],'slug'=>['nullable','string','max:120',Rule::unique('issue_categories','slug')->ignore($this->route('category'))],'description'=>['nullable','string'],'is_active'=>['nullable','boolean'],'sort_order'=>['nullable','integer','min:0']]; }
}
