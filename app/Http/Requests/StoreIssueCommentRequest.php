<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
class StoreIssueCommentRequest extends FormRequest
{
    public function authorize(): bool { return auth()->check() && auth()->user()->hasPermission('issues.comment'); }
    public function rules(): array { return ['comment'=>['required','string'],'is_internal'=>['nullable','boolean'],'attachments'=>['nullable','array'],'attachments.*'=>['file','max:5120','mimes:jpg,jpeg,png,pdf,webp,doc,docx']]; }
}
