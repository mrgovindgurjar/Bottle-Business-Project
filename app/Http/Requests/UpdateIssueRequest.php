<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
class UpdateIssueRequest extends FormRequest
{
    public function authorize(): bool { return auth()->check() && auth()->user()->hasPermission('issues.edit'); }
    public function rules(): array { return [
        'category_id'=>['nullable','exists:issue_categories,id'],'customer_id'=>['nullable','exists:customers,id'],'order_id'=>['nullable','exists:orders,id'],
        'delivery_id'=>['nullable','exists:deliveries,id'],'batch_id'=>['nullable','exists:batches,id'],'assigned_to'=>['nullable','exists:staff,id'],
        'priority'=>['required',Rule::in(['low','normal','high','urgent'])],'status'=>['required',Rule::in(['open','in_progress','pending_customer','resolved','closed','cancelled'])],
        'title'=>['required','string','max:200'],'description'=>['required','string'],'source'=>['nullable','string','max:50'],
        'reported_at'=>['nullable','date'],'due_date'=>['nullable','date'],'resolution'=>['nullable','string'],'customer_visible'=>['nullable','boolean'],
        'attachment_files'=>['nullable','array'],'attachment_files.*'=>['file','max:5120','mimes:jpg,jpeg,png,pdf,webp,doc,docx'],
    ]; }
}
