{{-- Search, class filter and list/grid switch shared by the student list and grid pages. --}}
<div class="flex flex-col gap-2 lg:flex-row lg:items-start lg:justify-between">
    <x-search-bar :value="$search" placeholder="Search name, admission ID or phone...">
        <select name="class" class="rounded-lg border-slate-300 text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500">
            <option value="">All classes</option>
            @foreach ($classes as $class)
                <option value="{{ $class->id }}" @selected($classId === $class->id)>Class {{ $class->name }}</option>
            @endforeach
        </select>
    </x-search-bar>
    <div class="mb-4 flex w-fit self-start rounded-lg border border-slate-300 bg-white p-0.5 shadow-sm">
        <a href="{{ route('student/list', request()->query()) }}" @class(['rounded-md p-1.5', 'bg-primary-50 text-primary-700' => request()->routeIs('student/list'), 'text-slate-500' => ! request()->routeIs('student/list')]) title="List view"><x-icon name="list" /></a>
        <a href="{{ route('student/grid', request()->query()) }}" @class(['rounded-md p-1.5', 'bg-primary-50 text-primary-700' => request()->routeIs('student/grid'), 'text-slate-500' => ! request()->routeIs('student/grid')]) title="Grid view"><x-icon name="squares" /></a>
    </div>
</div>
