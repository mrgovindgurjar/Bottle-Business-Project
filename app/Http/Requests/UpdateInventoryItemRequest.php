<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateInventoryItemRequest extends FormRequest
{
    public function authorize(): bool { return $this->user()?->hasPermission('inventory.edit') ?? false; }
    public function rules(): array
    {
        $id = $this->route('inventory');
        return [
            'product_id'=>['nullable','integer','exists:products,id'],
            'name'=>['required','string','max:200'],
            'sku'=>['required','string','max:100',Rule::unique('inventory_items','sku')->ignore($id?->id ?? $id)],
            'category'=>['required',Rule::in(['finished_goods','raw_material','packaging','label','consumable','other'])],
            'unit'=>['required','string','max:30'],
            'location'=>['nullable','string','max:100'],
            'reorder_level'=>['nullable','numeric','gte:0'],
            'reorder_quantity'=>['nullable','numeric','gte:0'],
            'average_cost'=>['nullable','numeric','gte:0'],
            'status'=>['required',Rule::in(['active','inactive'])],
            'notes'=>['nullable','string','max:5000'],
        ];
    }
}
