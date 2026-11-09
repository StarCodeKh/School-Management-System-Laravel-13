{{--
    A titled group of fields inside a form, laid out on the standard responsive grid.
    <x-form.section title="Contact" description="How we reach the family." icon="phone" cols="3"> ...fields... </x-form.section>
    cols: 1, 2 or 3 columns on wide screens. Use wrapper="sm:col-span-2" (etc.) on a field to widen it.
--}}
@props(['title' => null, 'description' => null, 'icon' => null, 'cols' => 2])

<div {{ $attributes->class('[&+&]:border-t [&+&]:border-slate-200 [&+&]:pt-6') }}>
    @if ($title)
        <div class="mb-4 flex items-start gap-3">
            @if ($icon)
                <span class="grid size-8 shrink-0 place-items-center rounded-lg bg-primary-50 text-primary-600">
                    <x-icon :name="$icon" class="size-4" />
                </span>
            @endif
            <div>
                <h2 class="text-sm font-semibold text-slate-900">{{ $title }}</h2>
                @if ($description)
                    <p class="mt-0.5 text-sm text-slate-500">{{ $description }}</p>
                @endif
            </div>
        </div>
    @endif
    <div @class([
        'grid gap-5',
        'sm:grid-cols-2' => (int) $cols >= 2,
        'lg:grid-cols-3' => (int) $cols === 3,
    ])>
        {{ $slot }}
    </div>
</div>
