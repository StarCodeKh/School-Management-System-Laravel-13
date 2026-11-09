{{--
    Table header cell. With sort="key" it links to ?sort=key&direction=..., toggling direction on each click;
    the key must be one the controller's ->sortable([...]) accepts. align: left | center | right.
--}}
@props(['sort' => null, 'align' => 'left'])

@php
    $state = request()->attributes->get('table_sort', []);
    $active = $sort !== null && ($state['key'] ?? null) === $sort;
    $direction = $active ? $state['direction'] : null;
    $url = $sort ? request()->fullUrlWithQuery(['sort' => $sort, 'direction' => $active && $direction === 'asc' ? 'desc' : 'asc', 'page' => null]) : null;
@endphp

<th {{ $attributes->class(['text-right' => $align === 'right', 'text-center' => $align === 'center']) }}
    @if ($active) aria-sort="{{ $direction === 'asc' ? 'ascending' : 'descending' }}" @endif>
    @if ($sort)
        <a href="{{ $url }}" data-live-link data-tooltip="Sort by {{ strtolower(trim(strip_tags($slot))) }}"
           @class([
               'group -mx-1 inline-flex items-center gap-1 rounded px-1 py-0.5 transition hover:bg-slate-200/60 hover:text-slate-800',
               'flex-row-reverse' => $align === 'right',
               'text-slate-800' => $active,
           ])>
            <span>{{ $slot }}</span>
            @if ($active)
                <x-icon name="chevron-down" @class(['size-3.5 text-primary-600 transition', 'rotate-180' => $direction === 'asc']) />
            @else
                <x-icon name="chevron-up-down" class="size-3.5 text-slate-300 transition group-hover:text-slate-500" />
            @endif
        </a>
    @else
        {{ $slot }}
    @endif
</th>
