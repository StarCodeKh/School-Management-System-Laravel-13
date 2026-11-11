{{-- Status tabs and list/grid switch shared by the invoice list and grid pages ($tab is null on the grid, which shows every invoice). --}}
@php
    $routes = [
        'all' => ['All', 'invoice/list/page', null], 'paid' => ['Paid', 'invoice/paid/page', null], 'overdue' => ['Overdue', 'invoice/overdue/page', 'red'],
        'draft' => ['Draft', 'invoice/draft/page', null], 'recurring' => ['Recurring', 'invoice/recurring/page', null], 'cancelled' => ['Cancelled', 'invoice/cancelled/page', null],
    ];
    $items = collect($routes)->map(fn ($tab, $key) => ['label' => $tab[0], 'href' => route($tab[1]), 'count' => $tabCounts[$key] ?? null, 'tone' => $tab[2]])->all();
@endphp

<div class="mb-6 flex flex-col-reverse gap-3 sm:flex-row sm:items-end sm:gap-4">
    <x-tabs :items="$items" :active="$tab ?? 'all'" class="mb-0! min-w-0 flex-1" />
    <x-view-switch :list="route('invoice/list/page')" :grid="route('invoice/grid/page')" :current="isset($tab) ? 'list' : 'grid'" class="mb-2 self-end" />
</div>
