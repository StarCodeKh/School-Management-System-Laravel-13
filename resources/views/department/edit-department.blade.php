@php
    $headNames = $teachers->pluck('full_name', 'user_id');
    $shown = 6; // teachers listed on the card before "and N more"
@endphp

<x-layouts.app title="Edit Department">
    <x-page-header title="Edit Department" :breadcrumbs="['Departments' => route('department/list/page'), $department->department_name => null]" />

    {{-- the summary card follows the form as you type --}}
    <div class="grid items-start gap-6 xl:grid-cols-[19rem_minmax(0,1fr)]"
         x-data="{
             name: @js(old('department_name', $department->department_name)),
             headId: @js((string) old('head_user_id', $department->head_user_id)),
             heads: @js($headNames),
         }"
         @input="$event.target.name === 'department_name' && (name = $event.target.value)"
         @change="$event.target.name === 'head_user_id' && (headId = $event.target.value)">

        <x-profile-summary class="xl:sticky xl:top-24" icon="building" :name="$department->department_name" name-bind="name"
                           :subtitle="'Department ID '.$department->code"
                           :details="[
                               'Teachers' => $department->teachers_count,
                               'Students' => number_format($department->no_of_students ?? 0),
                               'Started' => $department->department_start_date
                                   ? $department->department_start_date->format('d M Y').' ('.$department->department_start_date->diffForHumans(syntax: \Carbon\CarbonInterface::DIFF_ABSOLUTE).')'
                                   : null,
                               'Last updated' => $department->updated_at?->diffForHumans(),
                           ]"
                           links-title="Teachers">
            <x-slot:badges>
                <x-badge color="green" x-show="heads[headId]" :x-cloak="! $department->head">Head:&nbsp;<span x-text="heads[headId]">{{ $department->head?->name }}</span></x-badge>
                <x-badge color="amber" x-show="! heads[headId]" :x-cloak="(bool) $department->head">No head assigned</x-badge>
            </x-slot:badges>
            <x-slot:links>
                @foreach ($department->teachers->sortBy('full_name')->take($shown) as $teacher)
                    <x-profile-summary.link :href="url('teacher/edit/'.$teacher->id)" icon="academic-cap"
                                            :note="$teacher->user_id === $department->head_user_id ? '(Head)' : null">{{ $teacher->full_name }}</x-profile-summary.link>
                @endforeach
                @if ($department->teachers_count > $shown)
                    <li class="pl-6 text-slate-500">and {{ $department->teachers_count - $shown }} more</li>
                @endif
            </x-slot:links>
            @if ($department->teachers_count === 0)
                <p class="border-t border-slate-100 px-5 py-4 text-sm text-slate-500">No teachers yet. Choose this department on a teacher's profile.</p>
            @endif
        </x-profile-summary>

        <x-card>
            <form method="POST" action="{{ route('department/update') }}" class="space-y-6">
                @csrf
                <input type="hidden" name="code" value="{{ $department->code }}">
                @include('department._form')
                <x-form.actions submit="Save changes" :cancel="route('department/list/page')" />
            </form>
        </x-card>
    </div>
</x-layouts.app>
