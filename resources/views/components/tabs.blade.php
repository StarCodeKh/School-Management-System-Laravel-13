{{--
    Page tabs: links that switch between views of one page.
    <x-tabs :active="$tab" :items="[
        'active' => ['label' => 'On loan', 'href' => ..., 'count' => 12],
        'overdue' => ['label' => 'Overdue', 'href' => ..., 'count' => 3, 'tone' => 'red'],
    ]" />
    Each item: label, href, and optionally icon, count (a pill), tone (red|amber: colour the pill while count > 0),
    progress ([done, total]: shows "done/total" with a tick once complete).
    Long rows scroll sideways with faded edges, and the active tab is scrolled into view.
--}}
@props(['items' => [], 'active' => null])

<div {{ $attributes->class('relative mb-6 border-b border-slate-200') }}
     x-data="{ start: false, end: false, measure() { const el = this.$refs.row; this.start = el.scrollLeft > 4; this.end = el.scrollLeft + el.clientWidth < el.scrollWidth - 4; } }"
     x-init="$refs.row.querySelector('[aria-current]')?.scrollIntoView({ block: 'nearest', inline: 'center' }); measure()"
     @resize.window.debounce.100ms="measure()">
    <nav x-ref="row" @scroll.debounce.20ms="measure()" class="-mb-px flex gap-1 overflow-x-auto [scrollbar-width:none] [&::-webkit-scrollbar]:hidden"
         :style="(start || end) && `mask-image: linear-gradient(to right, ${start ? 'transparent' : '#000'}, #000 2rem, #000 calc(100% - 2rem), ${end ? 'transparent' : '#000'})`">
        @foreach ($items as $key => $item)
            @php
                $current = (string) $key === (string) $active;
                [$done, $total] = $item['progress'] ?? [null, null];
                $complete = $total && $done >= $total;
                $alert = ($item['count'] ?? 0) > 0 && isset($item['tone']);
            @endphp
            <a href="{{ $item['href'] }}" @if ($current) aria-current="page" @endif @class([
                'group inline-flex shrink-0 items-center gap-2 border-b-2 px-3.5 py-2.5 text-sm font-medium whitespace-nowrap transition',
                'border-primary-600 text-primary-700' => $current,
                'border-transparent text-slate-500 hover:border-slate-300 hover:text-slate-800' => ! $current,
            ])>
                @isset($item['icon'])
                    <x-icon :name="$item['icon']" @class(['size-4', 'text-primary-600' => $current, 'text-slate-400 group-hover:text-slate-500' => ! $current]) />
                @endisset
                {{ $item['label'] }}
                @if (isset($item['count']))
                    <span @class([
                        'min-w-5 rounded-full px-1.5 py-0.5 text-center text-xs font-semibold tabular-nums',
                        'bg-red-100 text-red-700' => $alert && $item['tone'] === 'red',
                        'bg-amber-100 text-amber-800' => $alert && $item['tone'] === 'amber',
                        'bg-primary-100 text-primary-700' => ! $alert && $current,
                        'bg-slate-100 text-slate-600' => ! $alert && ! $current,
                    ])>{{ number_format($item['count']) }}</span>
                @elseif ($total)
                    @if ($complete)
                        <x-icon name="check-circle" class="size-4 text-emerald-500" data-tooltip="All {{ $total }} entered" />
                    @else
                        <span class="text-xs font-normal text-slate-400 tabular-nums">{{ $done }}/{{ $total }}</span>
                    @endif
                @endif
            </a>
        @endforeach
    </nav>
</div>
