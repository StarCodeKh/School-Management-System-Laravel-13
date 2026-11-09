{{-- Status tabs and list/grid switch shared by the invoice list and grid pages. --}}
@php
    $tabs = [
        'all' => ['label' => 'All', 'route' => 'invoice/list/page'],
        'paid' => ['label' => 'Paid', 'route' => 'invoice/paid/page'],
        'overdue' => ['label' => 'Overdue', 'route' => 'invoice/overdue/page'],
        'draft' => ['label' => 'Draft', 'route' => 'invoice/draft/page'],
        'recurring' => ['label' => 'Recurring', 'route' => 'invoice/recurring/page'],
        'cancelled' => ['label' => 'Cancelled', 'route' => 'invoice/cancelled/page'],
    ];
    $currentTab = $tab ?? null;
@endphp

<div class="mb-6 flex flex-col gap-3 border-b border-slate-200 sm:flex-row sm:items-end sm:justify-between">
    <nav class="-mb-px flex gap-1 overflow-x-auto">
        @foreach ($tabs as $key => $item)
            <a href="{{ route($item['route']) }}" @class([
                'border-b-2 px-4 py-2 text-sm font-medium whitespace-nowrap',
                'border-primary-600 text-primary-700' => $currentTab === $key,
                'border-transparent text-slate-500 hover:border-slate-300 hover:text-slate-700' => $currentTab !== $key,
            ])>{{ $item['label'] }}</a>
        @endforeach
    </nav>
    <div class="mb-2 flex rounded-lg border border-slate-300 bg-white p-0.5 shadow-sm">
        <a href="{{ route('invoice/list/page') }}" @class(['rounded-md p-1.5', 'bg-primary-50 text-primary-700' => $currentTab, 'text-slate-500' => ! $currentTab]) data-tooltip="List view" aria-label="List view"><x-icon name="list" /></a>
        <a href="{{ route('invoice/grid/page') }}" @class(['rounded-md p-1.5', 'bg-primary-50 text-primary-700' => ! $currentTab, 'text-slate-500' => $currentTab]) data-tooltip="Grid view" aria-label="Grid view"><x-icon name="squares" /></a>
    </div>
</div>
