<x-layouts.app title="Students">
    <x-page-header title="Students" :breadcrumbs="['Dashboard' => route('home'), 'Students' => null]">
        <x-slot:actions>
            <x-button :href="route('student/add/page')" icon="plus">Add student</x-button>
        </x-slot:actions>
    </x-page-header>

    @include('student._toolbar')

    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
        @forelse ($studentList as $student)
            <a href="{{ url('student/profile/'.$student->id) }}" class="group rounded-xl border border-slate-200 bg-white p-5 text-center shadow-sm transition hover:border-primary-300 hover:shadow-md">
                <x-avatar :src="$student->photo_url" :alt="$student->full_name" size="size-20" class="mx-auto" />
                <p class="mt-3 font-semibold text-slate-900 group-hover:text-primary-600">{{ $student->full_name }}</p>
                <p class="text-sm text-slate-500">{{ $student->admission_id }}</p>
                <x-badge color="indigo" class="mt-2">Class {{ $student->schoolClass?->name }} - {{ $student->section?->name }}</x-badge>
            </a>
        @empty
            <p class="col-span-full rounded-xl border border-dashed border-slate-300 bg-white p-10 text-center text-sm text-slate-500">No students found.</p>
        @endforelse
    </div>

    <div class="mt-6">{{ $studentList->links() }}</div>
</x-layouts.app>
