<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
class ReceivePurchaseRequest extends FormRequest
{
    public function authorize(): bool { return $this->user()?->can('purchases.receive') ?? false; }
    public function rules(): array { return ['quantities'=>'required|array','quantities.*'=>'nullable|numeric|min:0']; }
}
