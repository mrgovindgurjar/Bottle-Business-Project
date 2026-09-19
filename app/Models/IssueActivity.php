<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IssueActivity extends Model
{
    protected $fillable = ['issue_id','user_id','event_type','old_status','new_status','old_assignee','new_assignee','message','metadata'];
    protected $casts = ['metadata'=>'array'];
    public function issue(): BelongsTo { return $this->belongsTo(Issue::class); }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
}
