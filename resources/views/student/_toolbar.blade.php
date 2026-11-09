{{-- Search, class filter and list/grid switch shared by the student list and grid pages. --}}
<div class="flex flex-col gap-2 lg:flex-row lg:items-start lg:justify-between">
    <x-search-bar :value="$search" placeholder="Search name, admission ID or phone...">
        <x-form.select name="class" :options="$classes->mapWithKeys(fn ($class) => [$class->id => 'Class '.$class->name])" :value="$classId"
                       placeholder="All classes" icon="academic-cap" wrapper="sm:w-48" />
    </x-search-bar>
    <div class="mb-4 flex w-fit self-start rounded-lg border border-slate-300 bg-white p-0.5 shadow-sm">
        <a href="{{ route('student/list', request()->query()) }}" @class(['rounded-md p-1.5', 'bg-primary-50 text-primary-700' => request()->routeIs('student/list'), 'text-slate-500' => ! request()->routeIs('student/list')]) data-tooltip="List view" aria-label="List view"><x-icon name="list" /></a>
        <a href="{{ route('student/grid', request()->query()) }}" @class(['rounded-md p-1.5', 'bg-primary-50 text-primary-700' => request()->routeIs('student/grid'), 'text-slate-500' => ! request()->routeIs('student/grid')]) data-tooltip="Grid view" aria-label="Grid view"><x-icon name="squares" /></a>
    </div>
</div>
