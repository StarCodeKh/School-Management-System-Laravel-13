<x-layouts.app title="Fees Collection">
    <x-page-header title="Fees Collection" :breadcrumbs="['Dashboard' => route('home'), 'Fees Collection' => null]">
        <x-slot:actions>
            <x-button :href="route('add/fees/collection/page')" icon="plus">Add payment</x-button>
        </x-slot:actions>
    </x-page-header>

    <x-table live>
        <x-slot:head>
            <tr><x-th sort="admission_id">Admission ID</x-th><x-th sort="student">Student</x-th><x-th sort="fee_type">Fee type</x-th><x-th sort="amount" align="right">Amount</x-th><x-th sort="paid_date">Paid date</x-th><th class="text-right">Status</th></tr>
        </x-slot:head>
        @forelse ($feeCollections as $payment)
            <tr class="hover:bg-slate-50">
                <td class="font-medium text-slate-900">{{ $payment->student?->admission_id }}</td>
                <td>
                    <a href="{{ url('student/profile/'.$payment->student_id) }}" class="flex items-center gap-3 hover:text-primary-600">
                        <x-avatar :src="$payment->student?->photo_url" :alt="$payment->student?->full_name" size="size-8" />
                        <span class="font-medium">{{ $payment->student?->full_name }}</span>
                    </a>
                </td>
                <td>{{ $payment->feeType?->name }}</td>
                <td class="text-right font-semibold text-slate-900 tabular-nums">{{ money($payment->amount) }}</td>
                <td>{{ $payment->paid_date->format('d M Y') }}</td>
                <td class="text-right"><x-badge color="green">Paid</x-badge></td>
            </tr>
        @empty
            <x-empty-row colspan="6" message="No payments recorded yet." />
        @endforelse
        <x-slot:footer>{{ $feeCollections->links() }}</x-slot:footer>
    </x-table>
</x-layouts.app>
