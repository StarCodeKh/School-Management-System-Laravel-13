<x-layouts.app title="Dashboard">
    <x-page-header :title="'Welcome, '.auth()->user()->name.'!'" :breadcrumbs="['Home' => route('home'), 'Admin Dashboard' => null]" />

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <x-stat-card label="Students" :value="number_format($stats['students'])" icon="academic-cap" :href="route('student/list')" />
        <x-stat-card label="Teachers" :value="number_format($stats['teachers'])" icon="users" color="sky" :href="route('teacher/list/page')" />
        <x-stat-card label="Attendance today" :value="$stats['attendance'] !== null ? $stats['attendance'].'%' : 'Not taken'" icon="check-circle"
                     :color="$stats['attendance'] === null ? 'amber' : ($stats['attendance'] >= 90 ? 'green' : ($stats['attendance'] >= 75 ? 'amber' : 'red'))" :href="route('attendance.index')" />
        <x-stat-card label="Fees collected" :value="money($stats['revenue'])" icon="banknotes" color="green" :href="route('account/fees/collections/page')" />
    </div>

    <div class="mt-6 grid gap-6 xl:grid-cols-3">
        <x-card title="Recent students" :padding="false" class="xl:col-span-2">
            <x-slot:actions>
                <x-button variant="ghost" size="sm" :href="route('student/list')">View all</x-button>
            </x-slot:actions>
            <ul class="divide-y divide-slate-100">
                @forelse ($recentStudents as $student)
                    <li class="flex items-center gap-3 px-5 py-3">
                        <x-avatar :src="$student->photo_url" :alt="$student->full_name" />
                        <div class="min-w-0 flex-1">
                            <a href="{{ url('student/profile/'.$student->id) }}" class="block truncate text-sm font-medium text-slate-900 hover:text-primary-600">{{ $student->full_name }}</a>
                            <p class="text-xs text-slate-500">{{ $student->admission_id }}</p>
                        </div>
                        <x-badge color="indigo">Class {{ $student->schoolClass?->name }} - {{ $student->section?->name }}</x-badge>
                        <span class="hidden text-xs text-slate-400 sm:block">{{ $student->created_at->format('d M Y') }}</span>
                    </li>
                @empty
                    <li class="px-5 py-8 text-center text-sm text-slate-500">No students yet.</li>
                @endforelse
            </ul>
        </x-card>

        <x-card title="Students per class">
            <ul class="space-y-3">
                @php($max = max(1, $classes->max('students_count')))
                @forelse ($classes as $class)
                    <li>
                        <div class="mb-1 flex justify-between text-sm">
                            <span class="font-medium text-slate-700">Class {{ $class->name }}</span>
                            <span class="text-slate-500">{{ $class->students_count }}</span>
                        </div>
                        <div class="h-2 rounded-full bg-slate-100">
                            <div class="h-2 rounded-full bg-primary-500" style="width: {{ round($class->students_count / $max * 100) }}%"></div>
                        </div>
                    </li>
                @empty
                    <li class="text-sm text-slate-500">No classes yet. <a href="{{ route('class/list/page') }}" class="text-primary-600 hover:underline">Add one</a>.</li>
                @endforelse
            </ul>
        </x-card>
    </div>

    <div class="mt-6 grid gap-6 xl:grid-cols-3">
        <x-card title="Recent fee payments" :padding="false" class="xl:col-span-2">
            <x-slot:actions>
                <x-button variant="ghost" size="sm" :href="route('account/fees/collections/page')">View all</x-button>
            </x-slot:actions>
            <ul class="divide-y divide-slate-100">
                @forelse ($recentPayments as $payment)
                    <li class="flex items-center justify-between gap-3 px-5 py-3 text-sm">
                        <div class="min-w-0">
                            <p class="truncate font-medium text-slate-900">{{ $payment->student?->full_name }}</p>
                            <p class="text-xs text-slate-500">{{ $payment->feeType?->name }} &middot; {{ $payment->paid_date->format('d M Y') }}</p>
                        </div>
                        <span class="font-semibold text-emerald-600">{{ money($payment->amount) }}</span>
                    </li>
                @empty
                    <li class="px-5 py-8 text-center text-sm text-slate-500">No payments recorded yet.</li>
                @endforelse
            </ul>
        </x-card>

        <x-card title="Quick actions">
            <div class="grid grid-cols-2 gap-2">
                @foreach ([
                    ['Add student', 'academic-cap', route('student/add/page')],
                    ['Add teacher', 'users', route('teacher/add/page')],
                    ['Take attendance', 'check-circle', route('attendance.index')],
                    ['New invoice', 'document', route('invoice/add/page')],
                    ['Post notice', 'megaphone', route('notices.create')],
                    ['Timetable', 'calendar', route('timetable.index')],
                ] as [$label, $icon, $href])
                    <a href="{{ $href }}" class="flex flex-col items-center gap-2 rounded-lg border border-slate-200 px-2 py-3 text-center text-xs font-medium text-slate-700 transition hover:border-primary-300 hover:bg-primary-50 hover:text-primary-700">
                        <x-icon :name="$icon" class="size-5 text-primary-600" />
                        {{ $label }}
                    </a>
                @endforeach
            </div>
            <a href="{{ route('invoice/list/page') }}" class="mt-4 flex items-center justify-between rounded-lg bg-amber-50 px-3 py-2.5 text-sm text-amber-800 hover:bg-amber-100">
                <span>Unpaid invoices</span>
                <span class="font-semibold tabular-nums">{{ $unpaidInvoices }}</span>
            </a>
        </x-card>
    </div>
    <div class="mt-6"><x-noticeboard /></div>
</x-layouts.app>
