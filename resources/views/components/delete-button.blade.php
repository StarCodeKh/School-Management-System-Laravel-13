{{-- Trash icon that asks in the shared confirmation dialog, then POSTs $fields to $action. --}}
@props(['action', 'fields' => [], 'title' => 'Delete record', 'message' => 'Are you sure you want to delete this record? This cannot be undone.'])

<form method="POST" action="{{ $action }}" class="inline-block">
    @csrf
    @foreach ($fields as $name => $value)
        <input type="hidden" name="{{ $name }}" value="{{ $value }}">
    @endforeach
    <x-icon-link icon="trash" label="Delete" tone="danger" type="submit"
                 :confirm="$message" :confirm-title="$title" confirm-label="Delete" confirm-tone="danger" />
</form>
