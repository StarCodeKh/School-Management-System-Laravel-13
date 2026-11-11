<div class="flex flex-col gap-2 lg:flex-row lg:items-start lg:justify-between">
    <x-search-bar :value="$search" placeholder="Search name, user ID, qualification..." />
    <x-view-switch :list="route('teacher/list/page', request()->query())" :grid="route('teacher/grid/page', request()->query())"
                   :current="request()->routeIs('teacher/grid/page') ? 'grid' : 'list'" class="mb-4 self-start" />
</div>
