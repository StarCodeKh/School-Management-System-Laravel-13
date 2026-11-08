<x-layouts.app :title="$book ? 'Edit Book' : 'Add Book'">
    <x-page-header :title="$book ? 'Edit book' : 'Add book'" :breadcrumbs="['Library' => route('books.index'), ($book ? 'Edit' : 'Add') => null]" />

    <x-card class="max-w-3xl">
        <form method="POST" action="{{ $book ? route('books.update', $book) : route('books.store') }}" class="space-y-6">
            @csrf
            @if ($book) @method('PUT') @endif
            <div class="grid gap-5 sm:grid-cols-2">
                <x-form.input name="title" label="Title" :value="$book?->title" required wrapper="sm:col-span-2" />
                <x-form.input name="author" label="Author" :value="$book?->author" />
                <x-form.input name="isbn" label="ISBN" :value="$book?->isbn" />
                <x-form.input name="category" label="Category" :value="$book?->category" placeholder="e.g. Science" />
                <x-form.input name="publisher" label="Publisher" :value="$book?->publisher" />
                <x-form.input name="shelf" label="Shelf" :value="$book?->shelf" placeholder="e.g. A-3" />
                <x-form.input name="quantity" type="number" min="0" label="Copies" :value="$book?->quantity ?? 1" required />
            </div>
            <x-form.actions :submit="$book ? 'Update book' : 'Add book'" :cancel="route('books.index')" />
        </form>
    </x-card>
</x-layouts.app>
