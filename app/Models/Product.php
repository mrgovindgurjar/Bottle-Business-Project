<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Product extends Model
{
    protected $fillable = [
        'name','sku','bottle_size_ml','bottle_type','material','cap_type','label_type',
        'units_per_box','unit','is_custom_branding','short_description','description',
        'image_path','status','sort_order',
    ];

    protected $casts = [
        'bottle_size_ml' => 'integer',
        'units_per_box' => 'integer',
        'is_custom_branding' => 'boolean',
        'sort_order' => 'integer',
    ];

    protected $appends = ['image_url'];

    public function prices(): HasMany { return $this->hasMany(ProductPrice::class); }

    public function activePrices(): HasMany
    {
        return $this->hasMany(ProductPrice::class)->where('status', 'active');
    }

    public function getImageUrlAttribute(): ?string
    {
        return $this->image_path ? Storage::disk('public')->url($this->image_path) : null;
    }
}
