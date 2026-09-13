<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
class UpdatePurchaseRequest extends FormRequest
{
    public function authorize(): bool { return $this->user()?->can('purchases.edit') ?? false; }
    public function rules(): array { return [
        'supplier_id'=>['required','integer','exists:suppliers,id'],'purchase_date'=>['required','date'],'expected_date'=>['nullable','date','after_or_equal:purchase_date'],
        'discount_type'=>['nullable','in:percent,fixed'],'discount_value'=>['nullable','numeric','min:0'],'tax_rate'=>['nullable','numeric','min:0','max:100'],
        'shipping_amount'=>['nullable','numeric','min:0'],'other_amount'=>['nullable','numeric','min:0'],'status'=>['nullable','in:draft,ordered'],
        'notes'=>['nullable','string'],'terms_conditions'=>['nullable','string'],'items'=>['required','array','min:1'],
        'items.*.product_id'=>['nullable','integer','exists:products,id'],'items.*.inventory_item_id'=>['nullable','integer','exists:inventory_items,id'],
        'items.*.description'=>['required','string','max:300'],'items.*.quantity'=>['required','numeric','gt:0'],'items.*.unit'=>['required','string','max:30'],
        'items.*.unit_price'=>['required','numeric','min:0'],'items.*.discount_type'=>['nullable','in:percent,fixed'],'items.*.discount_value'=>['nullable','numeric','min:0'],
        'items.*.tax_rate'=>['nullable','numeric','min:0','max:100'],'items.*.metadata'=>['nullable','array'],
    ]; }
}
