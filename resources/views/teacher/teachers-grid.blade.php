<x-layouts.app title="Teachers">
    <x-page-header title="Teachers" :breadcrumbs="['Dashboard' => route('home'), 'Teachers' => null]">
        <x-slot:actions>
@can('manage-school')
            <x-button :href="route('teacher/add/page')" icon="plus">Add teacher</x-button>
@endcan
        </x-slot:actions>
    </x-page-header>

    @include('teacher._toolbar')

    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
        @forelse ($teachers as $teacher)
            <a href="{{ auth()->user()->can('manage-school') ? url('teacher/edit/'.$teacher->id) : '#' }}" class="group rounded-xl border border-slate-200 bg-white p-5 text-center shadow-sm transition hover:border-primary-300 hover:shadow-md">
                <x-avatar :src="$teacher->user?->avatar_url" :alt="$teacher->full_name" size="size-20" class="mx-auto" />
                <p class="mt-3 font-semibold text-slate-900 group-hover:text-primary-600">{{ $teacher->full_name }}</p>
                <p class="text-sm text-slate-500">{{ $teacher->qualification }}</p>
                @if ($teacher->department)
                    <x-badge color="indigo" class="mt-2">{{ $teacher->department->department_name }}</x-badge>
                @endif
            </a>
        @empty
            <p class="col-span-full rounded-xl border border-dashed border-slate-300 bg-white p-10 text-center text-sm text-slate-500">No teachers found.</p>
        @endforelse
    </div>

    <div class="mt-6">{{ $teachers->links() }}</div>
</x-layouts.app>
