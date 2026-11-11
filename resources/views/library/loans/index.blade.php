<x-layouts.app title="Book Loans">
    <x-page-header title="Book Loans" :breadcrumbs="['Library' => route('books.index'), 'Book Loans' => null]">
        <x-slot:actions>
            <x-button :href="route('loans.create')" icon="plus">Issue book</x-button>
        </x-slot:actions>
    </x-page-header>

    <x-tabs :active="$tab" :items="[
        'active' => ['label' => 'On loan', 'icon' => 'book', 'href' => route('loans.index', ['tab' => 'active']), 'count' => $counts['active']],
        'overdue' => ['label' => 'Overdue', 'icon' => 'clock', 'href' => route('loans.index', ['tab' => 'overdue']), 'count' => $counts['overdue'], 'tone' => 'red'],
        'returned' => ['label' => 'Returned', 'icon' => 'check-circle', 'href' => route('loans.index', ['tab' => 'returned'])],
    ]" />

    <x-table live>
        <x-slot:head>
            <tr><x-th sort="book">Book</x-th><x-th sort="student">Student</x-th><x-th sort="issued">Issued</x-th><x-th sort="due">Due</x-th>@if ($tab === 'returned')<x-th sort="returned">Returned</x-th>@else<th>Status</th>@endif<x-th sort="fine" align="right">Fine</x-th><th class="text-right">Actions</th></tr>
        </x-slot:head>
        @forelse ($loans as $loan)
            <tr class="hover:bg-slate-50">
                <td class="font-medium text-slate-900">{{ $loan->book?->title }}</td>
                <td>
                    <p>{{ $loan->student?->full_name }}</p>
                    <p class="text-xs text-slate-500">Class {{ $loan->student?->schoolClass?->name }} - {{ $loan->student?->section?->name }}</p>
                </td>
                <td>{{ $loan->issued_on->format('d M Y') }}</td>
                <td>{{ $loan->due_on->format('d M Y') }}</td>
                <td>
                    @if ($loan->returned_on)
                        {{ $loan->returned_on->format('d M Y') }}
                    @elseif ($loan->isOverdue())
                        <x-badge color="red">{{ $loan->daysLate() }} days late</x-badge>
                    @else
                        <x-badge color="green">On loan</x-badge>
                    @endif
                </td>
                <td class="text-right">{{ $loan->currentFine() > 0 ? money($loan->currentFine()) : '-' }}</td>
                <td class="text-right">
                    @unless ($loan->returned_on)
                        <form method="POST" action="{{ route('loans.return', $loan) }}" class="inline">
                            @csrf
                            <x-button size="sm" variant="secondary" icon="check">Return</x-button>
                        </form>
                    @endunless
                </td>
            </tr>
        @empty
            <x-empty-row colspan="7" message="No loans here." />
        @endforelse
        <x-slot:footer>{{ $loans->links() }}</x-slot:footer>
    </x-table>
</x-layouts.app>
