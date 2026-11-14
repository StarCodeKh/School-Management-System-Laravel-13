<x-layouts.app title="Finance">
    <x-page-header title="Finance Overview" :breadcrumbs="['Dashboard' => route('home'), 'Finance' => null]">
        <x-slot:actions>
            <form method="GET" class="flex items-center gap-2">
                <x-form.input name="month" type="month" :value="$month" wrapper="w-56" onchange="this.form.submit()" />
            </form>
        </x-slot:actions>
    </x-page-header>

    @php($net = $feesIncome - $expenses - $salaries)
    <div class="grid grid-cols-2 gap-3 sm:gap-4 xl:grid-cols-4">
        <x-stat-card label="Fees collected" :value="money($feesIncome)" icon="banknotes" color="green" :href="route('account/fees/collections/page')" />
        <x-stat-card label="Expenses" :value="money($expenses)" icon="clipboard" color="amber" :href="route('expenses.index', ['month' => $month])" />
        <x-stat-card label="Salaries paid" :value="money($salaries)" icon="users" color="sky" :href="route('payroll.index', ['month' => $month])" />
        <x-stat-card :label="'Net for '.$start->format('F Y')" :value="($net < 0 ? '-' : '').money(abs($net))" icon="currency" :color="$net < 0 ? 'red' : 'indigo'" />
    </div>

    <div class="mt-6 grid gap-6 xl:grid-cols-3">
        <x-card title="Last six months" :padding="false" class="xl:col-span-2">
            <x-table class="rounded-none border-0 shadow-none">
                <x-slot:head>
                    <tr><th>Month</th><th class="text-right">Income</th><th class="text-right">Spent</th><th class="text-right">Net</th></tr>
                </x-slot:head>
                @foreach ($trend as $row)
                    <tr>
                        <td class="font-medium text-slate-900">{{ $row['label'] }}</td>
                        <td class="text-right text-emerald-600">{{ money($row['income']) }}</td>
                        <td class="text-right text-amber-700">{{ money($row['spent']) }}</td>
                        <td @class(['text-right font-semibold', 'text-red-600' => $row['net'] < 0, 'text-slate-900' => $row['net'] >= 0])>{{ $row['net'] < 0 ? '-' : '' }}{{ money(abs($row['net'])) }}</td>
                    </tr>
                @endforeach
            </x-table>
        </x-card>

        <x-card title="Expenses by category">
            @php($max = max(1, $byCategory->max() ?? 1))
            <ul class="space-y-3">
                @forelse ($byCategory as $category => $total)
                    <li>
                        <div class="mb-1 flex justify-between text-sm">
                            <span class="text-slate-700">{{ \App\Enums\ExpenseCategory::tryFrom($category)?->label() ?? $category }}</span>
                            <span class="font-medium text-slate-900">{{ money($total) }}</span>
                        </div>
                        <div class="h-2 rounded-full bg-slate-100"><div class="h-2 rounded-full bg-amber-500" style="width: {{ round($total / $max * 100) }}%"></div></div>
                    </li>
                @empty
                    <li class="text-sm text-slate-500">No expenses this month.</li>
                @endforelse
            </ul>
        </x-card>
    </div>
</x-layouts.app>
