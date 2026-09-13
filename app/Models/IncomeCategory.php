<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class IncomeCategory extends Model
{
    protected $fillable = ['name','slug','status','sort_order'];
    protected $casts = ['sort_order'=>'integer'];
    public function incomes(): HasMany { return $this->hasMany(Income::class, 'category_id'); }
}
