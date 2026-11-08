{{-- Footer row of a form: primary submit plus an optional cancel link. --}}
@props(['submit' => 'Save', 'cancel' => null])

<div class="flex items-center justify-end gap-2 border-t border-slate-200 pt-5">
    @if ($cancel)
        <x-button variant="secondary" :href="$cancel">Cancel</x-button>
    @endif
    {{ $slot }}
    <x-button>{{ $submit }}</x-button>
</div>
