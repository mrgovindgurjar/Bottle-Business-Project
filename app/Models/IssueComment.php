<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IssueComment extends Model
{
    protected $fillable = ['issue_id','user_id','comment','is_internal','attachments'];
    protected $casts = ['is_internal'=>'boolean','attachments'=>'array'];
    public function issue(): BelongsTo { return $this->belongsTo(Issue::class); }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
}
