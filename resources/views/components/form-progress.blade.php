{{-- "4 of 9 required fields" bar for a long form, updated as it is filled in. <x-form-progress form="add-student-form" /> (formProgress in app.js) --}}
@props(['form'])

<div x-data="formProgress(@js($form))" {{ $attributes->class('border-t border-slate-100 px-5 py-4') }}>
    <div class="flex items-center justify-between text-xs">
        <span class="font-semibold tracking-wide text-slate-400 uppercase">Required fields</span>
        <span class="font-medium text-slate-600 tabular-nums" x-text="`${done} of ${total}`"></span>
    </div>
    <div class="mt-2 h-1.5 overflow-hidden rounded-full bg-slate-100">
        <div class="h-full rounded-full transition-all duration-300" :class="complete ? 'bg-emerald-500' : 'bg-primary-500'" :style="`width: ${total ? (done / total) * 100 : 0}%`"></div>
    </div>
    <p class="mt-2 flex items-center gap-1.5 text-xs" :class="complete ? 'text-emerald-700' : 'text-slate-500'">
        <x-icon name="check-circle" class="size-3.5" x-show="complete" x-cloak />
        <span x-text="complete ? 'Ready to save.' : `${total - done} still to fill in.`"></span>
    </p>
</div>
