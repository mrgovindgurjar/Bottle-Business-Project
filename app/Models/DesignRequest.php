<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DesignRequest extends Model
{
    protected $fillable = [
        'design_code','customer_id','product_id','created_by','title','design_type','status',
        'brief','due_date','submitted_at','approved_at','approved_by',
    ];

    protected $casts = [
        'due_date' => 'date',
        'submitted_at' => 'datetime',
        'approved_at' => 'datetime',
    ];

    public function customer(): BelongsTo { return $this->belongsTo(Customer::class); }
    public function product(): BelongsTo { return $this->belongsTo(Product::class); }
    public function creator(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }
    public function approver(): BelongsTo { return $this->belongsTo(User::class, 'approved_by'); }
    public function versions(): HasMany { return $this->hasMany(DesignVersion::class); }

    public function latestVersion(): ?DesignVersion
    {
        return $this->versions()->latest('version_no')->first();
    }
}
