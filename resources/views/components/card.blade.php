@props(['title' => null, 'padding' => true])

<section {{ $attributes->class('min-w-0 rounded-xl border border-slate-200 bg-white shadow-sm') }}>
    @if ($title || isset($actions))
        <div class="flex min-h-15 items-center justify-between gap-4 border-b border-slate-200 px-5 py-3">
            <h2 class="text-base font-semibold text-slate-900">{{ $title }}</h2>
            @isset($actions)
                <div class="flex items-center gap-2">{{ $actions }}</div>
            @endisset
        </div>
    @endif
    <div @class(['p-5' => $padding])>
        {{ $slot }}
    </div>
</section>
