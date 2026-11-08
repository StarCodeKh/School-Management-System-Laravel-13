{{-- Responsive table. Slots: head (the <tr> of <th>), default (body rows), footer (e.g. pagination). --}}
<div {{ $attributes->class('overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm') }}>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50 text-left text-xs font-semibold tracking-wide text-slate-500 uppercase [&_th]:px-4 [&_th]:py-3 [&_th]:whitespace-nowrap">
                {{ $head }}
            </thead>
            <tbody class="divide-y divide-slate-100 [&_td]:px-4 [&_td]:py-3 [&_td]:whitespace-nowrap">
                {{ $slot }}
            </tbody>
        </table>
    </div>
    @if (isset($footer) && trim((string) $footer) !== '')
        <div class="border-t border-slate-200 px-4 py-3">{{ $footer }}</div>
    @endif
</div>
