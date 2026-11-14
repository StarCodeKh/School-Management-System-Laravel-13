<x-layouts.app title="Library">
    <x-page-header title="Books" :breadcrumbs="['Dashboard' => route('home'), 'Library' => null]">
        <x-slot:actions>
            <x-button variant="secondary" :href="route('loans.create')" icon="clipboard">Issue book</x-button>
            <x-button :href="route('books.create')" icon="plus">Add book</x-button>
        </x-slot:actions>
    </x-page-header>

    <x-search-bar :value="$search" placeholder="Search title, author or ISBN..." />

    <x-table live>
        <x-slot:head>
            <tr><x-th sort="title">Title</x-th><x-th sort="category">Category</x-th><x-th sort="isbn">ISBN</x-th><x-th sort="shelf">Shelf</x-th><x-th sort="copies" align="center">Copies</x-th><th class="text-center">Available</th><th class="text-right">Actions</th></tr>
        </x-slot:head>
        @forelse ($books as $book)
            <tr class="hover:bg-slate-50">
                <td>
                    <p class="font-medium text-slate-900">{{ $book->title }}</p>
                    <p class="text-xs text-slate-500">{{ $book->author }}</p>
                </td>
                <td>{{ $book->category ?: '-' }}</td>
                <td class="text-slate-500">{{ $book->isbn ?: '-' }}</td>
                <td>{{ $book->shelf ?: '-' }}</td>
                <td class="text-center">{{ $book->quantity }}</td>
                <td class="text-center"><x-badge :color="$book->available() > 0 ? 'green' : 'red'">{{ $book->available() }}</x-badge></td>
                <td class="text-right">
                    @if ($book->available() > 0)
                        <x-icon-link :href="route('loans.create', ['book_id' => $book->id])" icon="clipboard" label="Issue" />
                    @endif
                    <x-icon-link :href="route('books.edit', $book)" icon="pencil" label="Edit" tone="primary" />
                    <x-delete-button :action="route('books.destroy', $book)" :fields="['_method' => 'DELETE']" title="Delete book" :message="'Delete '.$book->title.'?'" />
                </td>
            </tr>
        @empty
            <x-empty-row colspan="7" message="No books found." />
        @endforelse
        <x-slot:footer>{{ $books->links() }}</x-slot:footer>
    </x-table>
</x-layouts.app>
