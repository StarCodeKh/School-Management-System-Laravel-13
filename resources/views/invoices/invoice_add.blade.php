<x-layouts.app title="New Invoice">
    <x-page-header title="New Invoice" :breadcrumbs="['Invoices' => route('invoice/list/page'), 'New Invoice' => null]" />

    <form method="POST" action="{{ route('invoice/add/save') }}" enctype="multipart/form-data">
        @csrf
        @include('invoices._form', ['invoice' => null])
        <div class="mt-6"><x-form.actions submit="Save invoice" :cancel="route('invoice/list/page')" /></div>
    </form>
</x-layouts.app>
