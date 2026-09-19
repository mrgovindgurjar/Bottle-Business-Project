<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
class StoreIssueCategoryRequest extends FormRequest
{
    public function authorize(): bool { return auth()->check() && auth()->user()->hasPermission('issues.categories'); }
    public function rules(): array { return ['name'=>['required','string','max:100'],'slug'=>['nullable','string','max:120','unique:issue_categories,slug'],'description'=>['nullable','string'],'is_active'=>['nullable','boolean'],'sort_order'=>['nullable','integer','min:0']]; }
}
