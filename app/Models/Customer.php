<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Customer extends Model
{
    protected $fillable = [
         'customer_code',
         'user_id',
         'business_name',
         'address',
         'city',
         'state',
         'pincode',
         'source',
         'status',
         'notes',

    ];

  public function user(): BelongsTo 
  {
    return $this->belongsTo(User::class);
  }

  public function prices(){
    return $this->hasMany(ProductPrice::class);
  }

}
