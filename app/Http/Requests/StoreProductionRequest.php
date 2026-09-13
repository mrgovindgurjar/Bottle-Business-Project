<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
class StoreProductionRequest extends FormRequest {
 public function authorize():bool{return true;}
 public function rules():array{return ['order_id'=>['required','integer','exists:orders,id'],'scheduled_date'=>['nullable','date'],'priority'=>['required','string','in:low,normal,high,urgent'],'assigned_to'=>['nullable','integer','exists:users,id'],'notes'=>['nullable','string','max:5000'],'internal_notes'=>['nullable','string','max:5000']];}
}
