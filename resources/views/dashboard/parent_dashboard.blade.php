<x-layouts.app title="Parent Dashboard">
    <x-page-header :title="'Welcome, '.auth()->user()->name.'!'" :breadcrumbs="['Home' => route('home'), 'Parent Dashboard' => null]" />

    @forelse ($children as $child)
        @php($student = $child['student'])
        <section class="mb-8">
            <div class="mb-4 flex items-center gap-3">
                <x-avatar :src="$student->photo_url" :alt="$student->full_name" size="size-12" />
                <div>
                    <h2 class="text-lg font-bold text-slate-900">{{ $student->full_name }}</h2>
                    <p class="text-sm text-slate-500">Class {{ $student->schoolClass?->name }} - {{ $student->section?->name }} &middot; {{ $student->admission_id }}</p>
                </div>
            </div>
            @include('student._overview', $child)
        </section>
    @empty
        <div class="mb-6 flex items-start gap-3 rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-800">
            <x-icon name="information" class="mt-0.5" />
            <p>No children are linked to your account yet. Ask the school office to link you on your child's student profile.</p>
        </div>
    @endforelse

    <x-noticeboard />
</x-layouts.app>
