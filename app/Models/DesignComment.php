<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DesignComment extends Model
{
    protected $fillable = ['design_version_id','user_id','type','comment'];

    public function version(): BelongsTo { return $this->belongsTo(DesignVersion::class, 'design_version_id'); }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
}
