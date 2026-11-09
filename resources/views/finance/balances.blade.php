<x-layouts.app title="Fee Balances">
    <x-page-header title="Fee balances" :breadcrumbs="['Finance' => route('finance.index'), 'Fee balances' => null]">
        <x-slot:actions>
            <x-button variant="secondary" :href="route('fee-structures.index')" icon="cog">Fee structures</x-button>
            <x-button :href="route('add/fees/collection/page')" icon="plus">Record payment</x-button>
        </x-slot:actions>
    </x-page-header>

    <div class="mb-6 grid gap-4 sm:grid-cols-3">
        <x-stat-card label="Total due" :value="money($totals['due'])" icon="document" />
        <x-stat-card label="Collected" :value="money($totals['paid'])" icon="check-circle" color="green" />
        <x-stat-card label="Outstanding" :value="money($totals['balance'])" icon="warning" :color="$totals['balance'] > 0 ? 'red' : 'green'" />
    </div>

    <x-search-bar :value="$search" placeholder="Search name or admission ID...">
        <x-form.select name="class" :options="$classes->mapWithKeys(fn ($class) => [$class->id => 'Class '.$class->name])" :value="$classId"
                       placeholder="All classes" icon="academic-cap" wrapper="sm:w-48" />
    </x-search-bar>

    <x-table live>
        <x-slot:head>
            <tr><x-th sort="student">Student</x-th><x-th sort="class">Class</x-th><th class="text-right">Due</th><th class="text-right">Paid</th><th class="text-right">Balance</th><th class="text-right">Status</th></tr>
        </x-slot:head>
        @forelse ($students as $student)
            @php($b = $balances[$student->id])
            <tr class="hover:bg-slate-50">
                <td>
                    <a href="{{ url('student/profile/'.$student->id) }}" class="flex items-center gap-3 hover:text-primary-600">
                        <x-avatar :src="$student->photo_url" :alt="$student->full_name" size="size-8" />
                        <div><p class="font-medium">{{ $student->full_name }}</p><p class="text-xs text-slate-500">{{ $student->admission_id }}</p></div>
                    </a>
                </td>
                <td>{{ $student->schoolClass?->name }} - {{ $student->section?->name }}</td>
                <td class="text-right">{{ money($b['due']) }}</td>
                <td class="text-right text-emerald-600">{{ money($b['paid']) }}</td>
                <td class="text-right font-semibold {{ $b['balance'] > 0 ? 'text-red-600' : 'text-slate-900' }}">{{ money($b['balance']) }}</td>
                <td class="text-right">
                    @if ($b['due'] == 0)
                        <x-badge>No fees set</x-badge>
                    @elseif ($b['balance'] == 0)
                        <x-badge color="green">Paid</x-badge>
                    @elseif ($b['paid'] > 0)
                        <x-badge color="amber">Partial</x-badge>
                    @else
                        <x-badge color="red">Unpaid</x-badge>
                    @endif
                </td>
            </tr>
        @empty
            <x-empty-row colspan="6" message="No students found." />
        @endforelse
        <x-slot:footer>{{ $students->links() }}</x-slot:footer>
    </x-table>
</x-layouts.app>
