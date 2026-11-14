<x-layouts.app title="Parent Dashboard">
    <x-page-header :title="'Welcome, '.auth()->user()->name.'!'" :breadcrumbs="['Home' => route('home'), 'Parent Dashboard' => null]" />

    @if ($children->isNotEmpty())
        <div class="mb-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <x-stat-card label="Children" :value="$stats['children']" icon="users" />
            <x-stat-card label="Attendance" :value="$stats['attendance'] !== null ? $stats['attendance'].'%' : 'Not recorded'" icon="check-circle"
                         :color="attendance_color($stats['attendance'])" />
            <x-stat-card label="Fee balance" :value="money($stats['balance'])" icon="banknotes" :color="$stats['balance'] > 0 ? 'red' : 'green'" />
            <x-stat-card label="Upcoming events" :value="$stats['events']" icon="calendar" color="sky" :href="route('events.index')" />
        </div>
    @endif

    @forelse ($children as $child)
        @php($student = $child['student'])
        <section class="mb-8">
            <div class="mb-4 flex items-center gap-3">
                <x-avatar :src="$student->photo_url" :alt="$student->full_name" size="size-12" />
                <div>
                    <h2 class="text-lg font-semibold text-slate-900">{{ $student->full_name }}</h2>
                    <p class="text-sm text-slate-500">Class {{ $student->schoolClass?->name }} - {{ $student->section?->name }} &middot; {{ $student->admission_id }}</p>
                </div>
            </div>
            @include('student._overview', $child)
        </section>
    @empty
        <x-alert type="warning" title="No children linked yet" class="mb-6">No children are linked to your account yet. Ask the school office to link you on your child's student profile.</x-alert>
    @endforelse

    <x-noticeboard />
</x-layouts.app>
