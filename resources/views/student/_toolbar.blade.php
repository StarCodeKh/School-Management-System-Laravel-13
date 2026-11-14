{{-- Search, class filter and list/grid switch shared by the student list and grid pages. --}}
<div class="flex flex-col gap-2 lg:flex-row lg:items-start lg:justify-between">
    <x-search-bar :value="$search" placeholder="Search name, ID or phone...">
        <x-form.select name="class" :options="$classes->mapWithKeys(fn ($class) => [$class->id => 'Class '.$class->name])" :value="$classId"
                       placeholder="All classes" icon="academic-cap" wrapper="sm:w-48" />
    </x-search-bar>
    <x-view-switch :list="route('student/list', request()->query())" :grid="route('student/grid', request()->query())"
                   :current="request()->routeIs('student/grid') ? 'grid' : 'list'" class="mb-4 self-start" />
</div>
