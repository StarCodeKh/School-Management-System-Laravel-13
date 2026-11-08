@props(['invoice'])

@php
    $color = $invoice->isOverdue() ? 'amber' : match ($invoice->status) {
        \App\Enums\InvoiceStatus::Paid => 'green',
        \App\Enums\InvoiceStatus::Sent => 'blue',
        \App\Enums\InvoiceStatus::Cancelled => 'red',
        default => 'slate',
    };
@endphp

<x-badge :color="$color">{{ $invoice->statusLabel() }}</x-badge>
@if ($invoice->is_recurring)
    <x-badge color="indigo">Recurring</x-badge>
@endif
