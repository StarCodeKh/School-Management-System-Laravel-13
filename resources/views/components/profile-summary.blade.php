{{--
    Summary card shown beside an edit form: photo, name, badges, key facts and linked records.
    The *-bind props are Alpine expressions from the page's x-data, so the card follows the form as it is typed.
    Give it class="xl:sticky xl:top-24" to keep it in view beside a long form.
      details   [term => value] rows
      badges    slot: <x-badge>s under the name
      links     slot: <x-profile-summary.link>s, listed under "Linked records"
--}}
@props(['image', 'name', 'subtitle' => null, 'details' => [], 'imageBind' => null, 'nameBind' => null, 'subtitleBind' => null, 'linksTitle' => 'Linked records'])

<aside {{ $attributes }}>
    <x-card :padding="false" class="overflow-hidden">
        <div class="h-20 bg-linear-to-br from-primary-500 to-primary-700"></div>
        <div class="-mt-10 flex flex-col items-center px-5 pb-5 text-center">
            <img src="{{ $image }}" @if ($imageBind) :src="{{ $imageBind }}" @endif alt="" class="size-20 rounded-full bg-white object-cover ring-4 ring-white">
            <h2 class="mt-3 text-lg font-semibold text-slate-900" @if ($nameBind) x-text="{{ $nameBind }} || 'No name'" @endif>{{ $name }}</h2>
            @if ($subtitle || $subtitleBind)
                <p class="text-sm break-all text-slate-500" @if ($subtitleBind) x-text="{{ $subtitleBind }}" @endif>{{ $subtitle }}</p>
            @endif
            @isset($badges)
                <div class="mt-3 flex flex-wrap justify-center gap-1.5">{{ $badges }}</div>
            @endisset
        </div>

        @if ($details)
            <dl class="divide-y divide-slate-100 border-t border-slate-100 text-sm">
                @foreach ($details as $term => $detail)
                    <div class="flex justify-between gap-4 px-5 py-2.5">
                        <dt class="shrink-0 text-slate-500">{{ $term }}</dt>
                        <dd class="truncate font-medium text-slate-900">{{ filled($detail) ? $detail : '-' }}</dd>
                    </div>
                @endforeach
            </dl>
        @endif

        @if (isset($links) && $links->isNotEmpty())
            <div class="border-t border-slate-100 px-5 py-4">
                <p class="text-xs font-semibold tracking-wide text-slate-400 uppercase">{{ $linksTitle }}</p>
                <ul class="mt-2 space-y-1.5 text-sm">{{ $links }}</ul>
            </div>
        @endif

        {{ $slot }}
    </x-card>
</aside>
