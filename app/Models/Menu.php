<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Gate;

class Menu extends Model
{
    use HasFactory;

    protected $fillable = [
        'parent_id',
        'title',
        'icon',
        'route',
        'active_routes',
        'pattern',
        'ability',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'active_routes' => 'array',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    /** Top-level items with their children, filtered to what the current user may see. */
    public static function sidebar(): Collection
    {
        return static::whereNull('parent_id')
            ->where('is_active', true)
            ->with(['children' => fn ($q) => $q->where('is_active', true)])
            ->orderBy('sort_order')
            ->get()
            ->each(fn (Menu $menu) => $menu->setRelation('children', $menu->children
                ->filter(fn (Menu $child) => $child->route && $child->isVisible())
                // the sidebar is two levels deep: sub-items have no children of their own
                ->each(fn (Menu $child) => $child->setRelation('children', new Collection))
                ->values()))
            // hide a group the user may not see, or one whose links are all hidden
            ->filter(fn (Menu $menu) => $menu->isVisible() && ($menu->route || $menu->children->isNotEmpty()))
            ->values();
    }

    public function children(): HasMany
    {
        return $this->hasMany(Menu::class, 'parent_id')->orderBy('sort_order');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Menu::class, 'parent_id');
    }

    public function isVisible(): bool
    {
        return ! $this->ability || Gate::allows($this->ability);
    }

    /** Active when the current route or URL matches this item or one of its children. */
    public function isActive(): bool
    {
        $routes = array_filter($this->active_routes ?? []);

        return ($routes && request()->routeIs(...$routes))
            || ($this->pattern && request()->is(...explode('|', $this->pattern)))
            || $this->children->contains(fn (Menu $child) => $child->isActive());
    }

    public function url(): string
    {
        return $this->route ? route($this->route) : '#';
    }
}
