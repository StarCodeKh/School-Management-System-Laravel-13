@php($monthLabel = \Illuminate\Support\Carbon::createFromFormat('Y-m-d', $month.'-01')->format('F Y'))
<x-layouts.app title="Payroll">
    <x-page-header :title="'Payroll: '.$monthLabel" :breadcrumbs="['Finance' => route('finance.index'), 'Payroll' => null]">
        <x-slot:actions>
            <form method="GET"><x-form.input name="month" type="month" :value="$month" onchange="this.form.submit()" /></form>
            <x-button :href="route('payroll.create', ['month' => $month])" icon="plus">Add salary</x-button>
        </x-slot:actions>
    </x-page-header>

    <div class="mb-6 grid gap-4 sm:grid-cols-3">
        <x-stat-card label="Total net salary" :value="money($totals['net'])" icon="banknotes" />
        <x-stat-card label="Paid" :value="money($totals['paid'])" icon="check-circle" color="green" />
        <x-stat-card label="Pending" :value="money($totals['pending'])" icon="clipboard" color="amber" />
    </div>

    @if ($missingStaff > 0)
        <div class="mb-4 flex flex-wrap items-center justify-between gap-3 rounded-xl border border-sky-200 bg-sky-50 p-4 text-sm text-sky-800">
            <p>{{ $missingStaff }} staff {{ \Illuminate\Support\Str::plural('member', $missingStaff) }} have no salary record for {{ $monthLabel }}.</p>
            <form method="POST" action="{{ route('payroll.generate') }}">
                @csrf
                <input type="hidden" name="month" value="{{ $month }}">
                <x-button size="sm" variant="secondary" icon="copy">Copy from previous months</x-button>
            </form>
        </div>
    @endif

    <x-table>
        <x-slot:head>
            <tr><th>Staff member</th><th>Role</th><th class="text-right">Basic</th><th class="text-right">Allowances</th><th class="text-right">Deductions</th><th class="text-right">Net</th><th>Status</th><th class="text-right">Actions</th></tr>
        </x-slot:head>
        @forelse ($payrolls as $payroll)
            <tr class="hover:bg-slate-50">
                <td>
                    <div class="flex items-center gap-3">
                        <x-avatar :src="$payroll->user?->avatar_url" :alt="$payroll->user?->name" size="size-8" />
                        <span class="font-medium text-slate-900">{{ $payroll->user?->name }}</span>
                    </div>
                </td>
                <td>{{ $payroll->user?->role?->label() }}</td>
                <td class="text-right">{{ money($payroll->basic_salary) }}</td>
                <td class="text-right text-emerald-600">+{{ money($payroll->allowances) }}</td>
                <td class="text-right text-red-600">-{{ money($payroll->deductions) }}</td>
                <td class="text-right font-semibold text-slate-900">{{ money($payroll->net_salary) }}</td>
                <td>
                    <x-badge :color="$payroll->status->color()">{{ $payroll->status->label() }}</x-badge>
                    @if ($payroll->paid_on)<p class="mt-0.5 text-xs text-slate-500">{{ $payroll->paid_on->format('d M') }} &middot; {{ $payroll->payment_method?->label() }}</p>@endif
                </td>
                <td class="text-right">
                    @if ($payroll->status === \App\Enums\PayrollStatus::Pending)
                        <form method="POST" action="{{ route('payroll.pay', $payroll) }}" class="inline-flex items-center gap-1">
                            @csrf
                            <x-form.select name="payment_method" :options="\App\Enums\PaymentMethod::options()" :placeholder="false" :icon="false" size="sm" wrapper="w-32 text-left"
                                           :id="'payment_method_'.$payroll->id" />
                            <x-button size="sm">Pay</x-button>
                        </form>
                    @endif
                    <x-icon-link :href="route('payroll.edit', $payroll)" icon="pencil" label="Edit" tone="primary" />
                    <x-delete-button :action="route('payroll.destroy', $payroll)" :fields="['_method' => 'DELETE']" title="Delete salary record"
                                     :message="'Delete the '.$monthLabel.' salary record of '.$payroll->user?->name.'?'" />
                </td>
            </tr>
        @empty
            <x-empty-row colspan="8" :message="'No salary records for '.$monthLabel.'.'" />
        @endforelse
    </x-table>
</x-layouts.app>
