{{-- Row actions for one invoice: view, edit, status changes and delete. --}}
<div x-data="{ open: false }" class="relative inline-block text-left">
    <x-icon-link :href="route('invoice/view/page', $invoice)" icon="eye" label="View" />
    <x-icon-link :href="route('invoice/edit/page', $invoice)" icon="pencil" label="Edit" tone="primary" />
    <x-icon-link icon="chevron-down" label="Change status" @click="open = !open" @click.outside="open = false" />
    <div x-cloak x-show="open" x-transition.origin.top.right class="absolute right-0 z-20 mt-1 w-44 rounded-lg border border-slate-200 bg-white py-1 text-left shadow-lg">
        @foreach (\App\Enums\InvoiceStatus::cases() as $status)
            @continue($invoice->status === $status)
            <form method="POST" action="{{ route('invoice/status', $invoice) }}">
                @csrf
                <input type="hidden" name="status" value="{{ $status->value }}">
                <button type="submit" class="block w-full px-4 py-2 text-left text-sm text-slate-700 hover:bg-slate-50">Mark as {{ $status->label() }}</button>
            </form>
        @endforeach
    </div>
    <x-delete-button :action="route('invoice/delete')" :fields="['invoice_number' => $invoice->invoice_number]" title="Delete invoice"
                     :message="'Delete invoice '.$invoice->invoice_number.'? This cannot be undone.'" />
</div>
