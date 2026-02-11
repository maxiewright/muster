<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class FocusArea extends Model
{
    use HasFactory, HasSlug;

    protected $fillable = [
        'name',
        'description',
    ];

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('name')
            ->saveSlugsTo('slug');
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function standups(): BelongsToMany
    {
        return $this->belongsToMany(
            Standup::class,
            'standup_focus_area',
            'focus_area_id',
            'standup_id'
        )->withTimestamps();
    }
}
