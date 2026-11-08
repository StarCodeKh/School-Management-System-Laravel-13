<div class="flex flex-col gap-2 lg:flex-row lg:items-start lg:justify-between">
    <x-search-bar :value="$search" placeholder="Search name, user ID, qualification..." />
    <div class="mb-4 flex w-fit self-start rounded-lg border border-slate-300 bg-white p-0.5 shadow-sm">
        <a href="{{ route('teacher/list/page', request()->query()) }}" @class(['rounded-md p-1.5', 'bg-primary-50 text-primary-700' => request()->routeIs('teacher/list/page'), 'text-slate-500' => ! request()->routeIs('teacher/list/page')]) title="List view"><x-icon name="list" /></a>
        <a href="{{ route('teacher/grid/page', request()->query()) }}" @class(['rounded-md p-1.5', 'bg-primary-50 text-primary-700' => request()->routeIs('teacher/grid/page'), 'text-slate-500' => ! request()->routeIs('teacher/grid/page')]) title="Grid view"><x-icon name="squares" /></a>
    </div>
</div>
