<x-layouts.app :title="$book ? 'Edit Book' : 'Add Book'">
    <x-page-header :title="$book ? 'Edit Book' : 'Add Book'" :breadcrumbs="['Books' => route('books.index'), ($book ? $book->title : 'Add Book') => null]" />

    <x-card class="max-w-3xl">
        <form method="POST" action="{{ $book ? route('books.update', $book) : route('books.store') }}" class="space-y-6">
            @csrf
            @if ($book) @method('PUT') @endif
            <x-form.section title="Book" description="How the book appears in the catalogue." icon="book">
                <x-form.input name="title" label="Title" :value="$book?->title" required wrapper="sm:col-span-2" />
                <x-form.input name="author" label="Author" :value="$book?->author" />
                <x-form.input name="isbn" label="ISBN" :value="$book?->isbn" icon="hashtag" />
                <x-form.input name="category" label="Category" :value="$book?->category" placeholder="e.g. Science" icon="tag" />
                <x-form.input name="publisher" label="Publisher" :value="$book?->publisher" />
            </x-form.section>

            <x-form.section title="Copies" description="Where the book is kept and how many the library owns." icon="library">
                <x-form.input name="shelf" label="Shelf" :value="$book?->shelf" placeholder="e.g. A-3" />
                <x-form.input name="quantity" type="number" min="0" label="Copies" :value="$book?->quantity ?? 1" required />
            </x-form.section>
            <x-form.actions :submit="$book ? 'Save changes' : 'Add book'" :cancel="route('books.index')" />
        </form>
    </x-card>
</x-layouts.app>
