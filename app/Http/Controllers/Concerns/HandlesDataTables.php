<?php

namespace App\Http\Controllers\Concerns;

use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Server-side processing for jQuery DataTables.
 *
 * Only columns listed in $sortable can be used for ordering, and the page
 * size is capped, so request input never reaches the SQL as an identifier.
 */
trait HandlesDataTables
{
    /**
     * @param  array<int, string>  $searchable  columns matched against the search box
     * @param  array<int|string, string>  $sortable  columns the client may sort by
     * @param  callable(object): array  $mapRow  turns one record into a table row
     */
    protected function dataTableResponse(
        Request $request,
        Builder|EloquentBuilder $query,
        array $searchable,
        array $sortable,
        callable $mapRow,
    ): JsonResponse {
        // $sortable is either a list of columns, or [table key => SQL column] when they differ
        $sortable = array_is_list($sortable) ? array_combine($sortable, $sortable) : $sortable;
        $columnIndex = (int) $request->input('order.0.column', 0);
        $key = $request->input("columns.$columnIndex.data");
        $column = $sortable[$key] ?? reset($sortable);
        $direction = strtolower((string) $request->input('order.0.dir')) === 'desc' ? 'desc' : 'asc';
        $start = max(0, (int) $request->input('start', 0));
        $length = min(100, max(1, (int) $request->input('length', 10)));
        $search = trim((string) $request->input('search.value', ''));

        $total = (clone $query)->count();

        if ($search !== '') {
            $query->where(function ($q) use ($searchable, $search) {
                foreach ($searchable as $field) {
                    $q->orWhere($field, 'like', '%'.$search.'%');
                }
            });
        }

        $filtered = (clone $query)->count();

        $rows = $query->orderBy($column, $direction)->skip($start)->take($length)->get()->map($mapRow);

        return response()->json([
            'draw' => (int) $request->input('draw'),
            'iTotalRecords' => $total,
            'iTotalDisplayRecords' => $filtered,
            'aaData' => $rows->values(),
        ]);
    }
}
