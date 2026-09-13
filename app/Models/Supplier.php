<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Supplier extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'supplier_code','business_name','contact_name','mobile','email','gstin','category',
        'payment_terms_days','address','city','state','pincode','status','notes',
    ];

    protected $casts = ['payment_terms_days' => 'integer'];

    public function purchases(): HasMany { return $this->hasMany(Purchase::class); }
    public function payments(): HasMany { return $this->hasMany(SupplierPayment::class); }

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        $term = trim((string) $term);
        if ($term === '') return $query;
        return $query->where(function (Builder $q) use ($term) {
            $q->where('business_name','like',"%{$term}%")
              ->orWhere('supplier_code','like',"%{$term}%")
              ->orWhere('mobile','like',"%{$term}%")
              ->orWhere('gstin','like',"%{$term}%");
        });
    }

 
}
