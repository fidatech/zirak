<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    /** Colour presets: [soft background, strong colour, dark text]. */
    public const COLORS = [
        'teal' => ['#CCFBF1', '#0F766E', '#134E4A'],
        'red' => ['#FEE2E2', '#DC2626', '#7F1D1D'],
        'violet' => ['#EDE9FE', '#7C3AED', '#4C1D95'],
        'blue' => ['#DBEAFE', '#1D4ED8', '#1E3A8A'],
        'orange' => ['#FFEDD5', '#EA580C', '#7C2D12'],
        'green' => ['#D1FAE5', '#059669', '#064E3B'],
        'pink' => ['#FCE7F3', '#DB2777', '#831843'],
        'amber' => ['#FEF3C7', '#D97706', '#78350F'],
        'indigo' => ['#E0E7FF', '#4F46E5', '#312E81'],
    ];

    public const ICONS = ['spark', 'film', 'story', 'code', 'palette', 'book', 'camera', 'music', 'chart', 'language'];

    public function courses(): HasMany
    {
        return $this->hasMany(Course::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true)->orderBy('sort');
    }

    public function name(): string
    {
        $locale = app()->getLocale();

        return $this->{'name_'.$locale} ?? $this->name_prs;
    }

    /** @return array{0:string,1:string,2:string} */
    public function palette(): array
    {
        return self::COLORS[$this->color] ?? self::COLORS['teal'];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
