@php
    $user = $users;
    $statusOptions = collect(\App\Enums\UserStatus::cases())->mapWithKeys(fn ($status) => [
        $status->value => ['label' => $status->label(), 'description' => $status->description(), 'color' => $status->color()],
    ]);
    $roleColors = collect(\App\Enums\Role::cases())->mapWithKeys(fn ($role) => [$role->value => ['label' => $role->label(), 'color' => $role->color()]]);
    $statusColors = collect(\App\Enums\UserStatus::cases())->mapWithKeys(fn ($status) => [$status->value => ['label' => $status->label(), 'color' => $status->color()]]);
    $badge = [
        'green' => 'bg-emerald-50 text-emerald-700 ring-emerald-600/20', 'red' => 'bg-red-50 text-red-700 ring-red-600/20',
        'amber' => 'bg-amber-50 text-amber-800 ring-amber-600/20', 'blue' => 'bg-sky-50 text-sky-700 ring-sky-600/20',
        'indigo' => 'bg-primary-50 text-primary-700 ring-primary-600/20', 'slate' => 'bg-slate-100 text-slate-700 ring-slate-500/20',
    ];
@endphp

<x-layouts.app title="Edit User">
    <x-page-header title="Edit User" :breadcrumbs="['Users' => route('list/users'), $user->name => null]" />

    {{-- the summary card follows the form as you type --}}
    <div class="grid items-start gap-6 xl:grid-cols-[19rem_minmax(0,1fr)]"
         x-data="{
             name: @js(old('name', $user->name)),
             email: @js(old('email', $user->email)),
             role: @js((string) old('role', $user->role?->value)),
             status: @js((string) old('status', $user->status?->value)),
             avatar: @js($user->avatar_url),
             roles: @js($roleColors),
             statuses: @js($statusColors),
             badge: @js($badge),
         }"
         @image-preview.window="$event.detail.name === 'avatar' && (avatar = $event.detail.url)">

        <x-profile-summary class="xl:sticky xl:top-24" :image="$user->avatar_url" image-bind="avatar" :name="$user->name" name-bind="name" :subtitle="$user->email" subtitle-bind="email"
                           :details="[
                               'User ID' => $user->code,
                               'Joined' => ($user->join_date ?? $user->created_at)?->format('d M Y'),
                               'Last updated' => $user->updated_at?->diffForHumans(),
                           ]">
            <x-slot:badges>
                <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium ring-1 ring-inset"
                      :class="badge[roles[role]?.color ?? 'slate']" x-text="roles[role]?.label">{{ $user->role?->label() }}</span>
                <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium ring-1 ring-inset"
                      :class="badge[statuses[status]?.color ?? 'slate']" x-text="statuses[status]?.label">{{ $user->status?->label() }}</span>
            </x-slot:badges>
            <x-slot:links>
                @if ($user->teacher)
                    <x-profile-summary.link :href="url('teacher/edit/'.$user->teacher->id)" icon="academic-cap" :note="$user->teacher->department?->department_name">Teacher profile</x-profile-summary.link>
                @endif
                @if ($user->student)
                    <x-profile-summary.link :href="url('student/profile/'.$user->student->id)" :note="$user->student->schoolClass?->name.'-'.$user->student->section?->name">Student record</x-profile-summary.link>
                @endif
                @foreach ($user->children as $child)
                    <x-profile-summary.link :href="url('student/profile/'.$child->id)" icon="users" :note="'('.ucfirst($child->pivot->relationship).')'">{{ $child->full_name }}</x-profile-summary.link>
                @endforeach
            </x-slot:links>
        </x-profile-summary>

        {{-- form --}}
        <x-card>
            <form method="POST" action="{{ route('user/update') }}" enctype="multipart/form-data" class="space-y-6">
                @csrf
                <input type="hidden" name="code" value="{{ $user->code }}">

                <x-form.section title="Profile photo" description="Shown in the top bar, lists and dashboards." icon="user" cols="1">
                    <x-form.image-upload name="avatar" :current="$user->avatar_url" />
                </x-form.section>

                <x-form.section title="Personal details" description="How the person is identified and contacted." icon="envelope">
                    <x-form.input name="name" label="Full name" :value="$user->name" required x-model="name" wrapper="sm:col-span-2" />
                    <x-form.input name="email" type="email" label="Email" :value="$user->email" required x-model="email" hint="Used to log in." />
                    <x-form.input name="phone_number" type="tel" label="Phone" :value="$user->phone_number" placeholder="e.g. 012 345 678" />
                    <x-form.input name="date_of_birth" type="date" label="Date of birth" :value="$user->date_of_birth" :max="now()->subDay()->toDateString()" />
                    <x-form.input name="position" label="Position" :value="$user->position" icon="building" placeholder="e.g. Mathematics Teacher" />
                </x-form.section>

                <x-form.section title="Access" description="What the person can see and whether they can log in." icon="shield" cols="1">
                    <x-form.select name="role" label="Role" :options="\App\Enums\Role::options()" :value="$user->role" :placeholder="false" required x-model="role"
                                   wrapper="sm:max-w-xs" hint="Changing the role changes the menus and pages this person sees." />
                    <x-form.choice-cards name="status" label="Status" :options="$statusOptions" :value="$user->status" required x-model="status" />
                </x-form.section>

                <x-form.actions submit="Save changes" :cancel="route('list/users')" />
            </form>
        </x-card>
    </div>
</x-layouts.app>
