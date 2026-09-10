<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DesignVersion extends Model
{
    protected $fillable = [
        'design_request_id','version_no','name','design_data','front_artwork_path','back_artwork_path',
        'logo_path','preview_path','status','change_note','created_by','approved_by','approved_at',
    ];

    protected $casts = [
        'design_data' => 'array',
        'approved_at' => 'datetime',
    ];

    public function request(): BelongsTo { return $this->belongsTo(DesignRequest::class, 'design_request_id'); }
    public function creator(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }
    public function approver(): BelongsTo { return $this->belongsTo(User::class, 'approved_by'); }
    public function comments(): HasMany { return $this->hasMany(DesignComment::class); }
}
