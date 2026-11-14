{{--
    Responsive table with a sticky header and row hover. Slots: head (the <tr> of <th> / <x-th>), default (body rows),
    footer (e.g. {{ $items->links() }}). Pass `live` on list pages: searching, filtering, sorting and paging then
    swap just this table in place (see "Live tables" in resources/js/app.js) instead of reloading the page.
--}}
@props(['live' => false])

<div {{ $attributes->class('relative min-w-0 overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm') }}
     @if ($live) data-live-region="{{ $live === true ? 'table' : $live }}" @endif>
    <div class="max-h-[75vh] overflow-auto print:max-h-none print:overflow-visible">
        <table class="min-w-full text-sm">
            <thead class="sticky top-0 z-10 bg-slate-50 text-left text-xs font-semibold tracking-wide text-slate-500 uppercase shadow-[inset_0_-1px_0] shadow-slate-200 [&_th]:px-4 [&_th]:py-3 [&_th]:whitespace-nowrap">
                {{ $head }}
            </thead>
            <tbody class="divide-y divide-slate-100 tabular-nums [&_td]:px-4 [&_td]:py-3 [&_td]:whitespace-nowrap [&>tr]:transition-colors [&>tr:hover]:bg-slate-50/80">
                {{ $slot }}
            </tbody>
        </table>
    </div>
    @if (isset($footer) && trim((string) $footer) !== '')
        <div class="border-t border-slate-200 px-4 py-3">{{ $footer }}</div>
    @endif
</div>
