<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Issue extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'issue_number','category_id','customer_id','order_id','delivery_id','batch_id','assigned_to',
        'priority','status','title','description','source','reported_at','due_date','resolved_at','closed_at',
        'resolution','customer_visible','attachments','created_by','updated_by','closed_by',
    ];

    protected $casts = [
        'reported_at'=>'datetime','due_date'=>'date','resolved_at'=>'datetime','closed_at'=>'datetime',
        'customer_visible'=>'boolean','attachments'=>'array',
    ];

    public function category(): BelongsTo { return $this->belongsTo(IssueCategory::class, 'category_id'); }
    public function customer(): BelongsTo { return $this->belongsTo(Customer::class); }
    public function order(): BelongsTo { return $this->belongsTo(Order::class); }
    public function delivery(): BelongsTo { return $this->belongsTo(Delivery::class); }
    public function batch(): BelongsTo { return $this->belongsTo(Batch::class); }
    public function assignee(): BelongsTo { return $this->belongsTo(Staff::class, 'assigned_to'); }
    public function creator(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }
    public function updater(): BelongsTo { return $this->belongsTo(User::class, 'updated_by'); }
    public function closer(): BelongsTo { return $this->belongsTo(User::class, 'closed_by'); }
    public function comments(): HasMany { return $this->hasMany(IssueComment::class)->latest(); }
    public function activities(): HasMany { return $this->hasMany(IssueActivity::class)->latest(); }

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        if (!$term) return $query;
        $term = trim($term);
        return $query->where(function (Builder $q) use ($term) {
            $q->where('issue_number','like',"%{$term}%")
              ->orWhere('title','like',"%{$term}%")
              ->orWhere('description','like',"%{$term}%")
              ->orWhereHas('customer', fn($c) => $c->where('business_name','like',"%{$term}%"));
        });
    }

    public function getIsOverdueAttribute(): bool
    {
        return $this->due_date && $this->due_date->isPast() && !in_array($this->status, ['resolved','closed','cancelled'], true);
    }
}
