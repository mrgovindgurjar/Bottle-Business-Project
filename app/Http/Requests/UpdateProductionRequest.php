<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
class UpdateProductionRequest extends FormRequest {
 public function authorize():bool{return true;}
 public function rules():array{return ['scheduled_date'=>['nullable','date'],'priority'=>['required','string','in:low,normal,high,urgent'],'assigned_to'=>['nullable','integer','exists:users,id'],'notes'=>['nullable','string','max:5000'],'internal_notes'=>['nullable','string','max:5000'],'quality_status'=>['nullable','string','in:pending,passed,failed,hold'],'quality_notes'=>['nullable','string','max:5000']];}
}
