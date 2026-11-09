{{-- Row actions for one invoice: view, edit, status changes and delete. --}}
@php($statusIcons = ['draft' => 'pencil', 'sent' => 'envelope', 'paid' => 'check-circle', 'cancelled' => 'x-circle'])
<div class="inline-flex items-center">
    <x-icon-link :href="route('invoice/view/page', $invoice)" icon="eye" label="View" />
    <x-icon-link :href="route('invoice/edit/page', $invoice)" icon="pencil" label="Edit" tone="primary" />
    <x-dropdown width="184">
        <x-slot:trigger><x-icon-link icon="chevron-down" label="Change status" /></x-slot:trigger>
        <p class="px-3 pt-1.5 pb-1 text-[11px] font-semibold tracking-wide text-slate-400 uppercase">Change status</p>
        @foreach (\App\Enums\InvoiceStatus::cases() as $status)
            @continue($invoice->status === $status)
            <form method="POST" action="{{ route('invoice/status', $invoice) }}">
                @csrf
                <input type="hidden" name="status" value="{{ $status->value }}">
                <x-dropdown.item type="submit" :icon="$statusIcons[$status->value] ?? 'check'" :tone="$status === \App\Enums\InvoiceStatus::Cancelled ? 'danger' : 'slate'"
                                 :confirm="$status === \App\Enums\InvoiceStatus::Cancelled ? 'Cancel invoice '.$invoice->invoice_number.'? It will no longer count as money owed.' : null"
                                 confirm-title="Cancel invoice" confirm-label="Cancel invoice">Mark as {{ $status->label() }}</x-dropdown.item>
            </form>
        @endforeach
    </x-dropdown>
    <x-delete-button :action="route('invoice/delete')" :fields="['invoice_number' => $invoice->invoice_number]" title="Delete invoice"
                     :message="'Delete invoice '.$invoice->invoice_number.'? This cannot be undone.'" />
</div>
