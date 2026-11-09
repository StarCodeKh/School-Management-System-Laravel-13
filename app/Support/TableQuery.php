<?php

namespace App\Support;

use Closure;
use Illuminate\Database\Eloquent\Builder;

/**
 * Query macros for list pages.
 *
 *   Student::query()->sortable([
 *       'name' => ['first_name', 'last_name'],                       // one or more columns
 *       'class' => fn ($query, $direction) => $query->orderBy(...),  // anything custom
 *       'newest' => 'created_at',
 *   ], default: 'newest', direction: 'desc')->paginateFromRequest(15);
 *
 * ?sort=name&direction=desc picks the order (unknown keys fall back to the default) and
 * ?per_page=50 the page size. <x-th sort="name"> reads the resolved sort back from the request.
 */
class TableQuery
{
    public const PER_PAGE_OPTIONS = [10, 15, 20, 25, 50, 100];

    public static function register(): void
    {
        Builder::macro('sortable', function (array $columns, string $default, string $direction = 'asc') {
            /** @var Builder $this */
            $request = request();
            $key = $request->query('sort');
            $dir = $request->query('direction') === 'desc' ? 'desc' : 'asc';

            if (! is_string($key) || ! array_key_exists($key, $columns)) {
                [$key, $dir] = [$default, $direction === 'desc' ? 'desc' : 'asc'];
            }
            $request->attributes->set('table_sort', ['key' => $key, 'direction' => $dir]);

            $column = $columns[$key];
            if ($column instanceof Closure) {
                $column($this, $dir);
            } else {
                foreach ((array) $column as $name) {
                    $this->orderBy($name, $dir);
                }
            }

            // a stable tie-breaker so rows don't jump between pages
            return $this->orderBy($this->getModel()->getQualifiedKeyName(), $dir);
        });

        Builder::macro('paginateFromRequest', function (int $default = 15) {
            /** @var Builder $this */
            $perPage = request()->integer('per_page', $default);

            return $this
                ->paginate(in_array($perPage, TableQuery::PER_PAGE_OPTIONS, true) ? $perPage : $default)
                ->withQueryString();
        });
    }
}
