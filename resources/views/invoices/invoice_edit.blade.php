<x-layouts.app :title="'Edit '.$invoice->invoice_number">
    <x-page-header :title="'Edit '.$invoice->invoice_number" :breadcrumbs="['Invoices' => route('invoice/list/page'), $invoice->invoice_number => route('invoice/view/page', $invoice), 'Edit' => null]">
        <x-slot:actions>
            <x-delete-button :action="route('invoice/delete')" :fields="['invoice_number' => $invoice->invoice_number]" title="Delete invoice"
                             :message="'Delete invoice '.$invoice->invoice_number.'? This cannot be undone.'" />
        </x-slot:actions>
    </x-page-header>

    <form method="POST" action="{{ route('invoice/update/save') }}" enctype="multipart/form-data">
        @csrf
        <input type="hidden" name="invoice_number" value="{{ $invoice->invoice_number }}">
        @include('invoices._form')
        <div class="mt-6"><x-form.actions submit="Save changes" :cancel="route('invoice/view/page', $invoice)" /></div>
    </form>
</x-layouts.app>
