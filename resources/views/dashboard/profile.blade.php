@php($user = auth()->user())

<x-layouts.app title="Profile">
    <x-page-header title="Profile" :breadcrumbs="['Dashboard' => route('home'), 'Profile' => null]" />

    <div class="grid gap-6 lg:grid-cols-3">
        <x-card class="lg:col-span-2">
            <div class="flex flex-col gap-5 sm:flex-row sm:items-center">
                <x-avatar :src="$user->avatar_url" :alt="$user->name" size="size-20" />
                <div class="flex-1">
                    <h2 class="text-lg font-semibold text-slate-900">{{ $user->name }}</h2>
                    <p class="text-sm text-slate-500">{{ $user->position ?: $user->role?->label() }}</p>
                    <div class="mt-2 flex flex-wrap gap-2">
                        <x-badge color="indigo">{{ $user->role?->label() }}</x-badge>
                        @if ($user->status)
                            <x-badge :color="$user->status->value === 'active' ? 'green' : 'amber'">{{ $user->status->label() }}</x-badge>
                        @endif
                    </div>
                </div>
            </div>

            <dl class="mt-6 grid gap-x-6 gap-y-4 border-t border-slate-200 pt-6 sm:grid-cols-2">
                @foreach ([
                    'User ID' => $user->code,
                    'Email' => $user->email,
                    'Phone' => $user->phone_number,
                    'Date of birth' => $user->date_of_birth?->format('d M Y'),
                    'Joined' => $user->join_date?->format('d M Y'),
                    'Department' => $user->teacher?->department?->department_name,
                ] as $label => $value)
                    <div>
                        <dt class="text-xs font-medium tracking-wide text-slate-500 uppercase">{{ $label }}</dt>
                        <dd class="mt-1 text-sm text-slate-900">{{ $value ?: '-' }}</dd>
                    </div>
                @endforeach
            </dl>
        </x-card>

        <x-card title="Change password">
            <form method="POST" action="{{ route('change/password') }}" class="space-y-4">
                @csrf
                <x-form.input name="current_password" type="password" label="Current password" required autocomplete="current-password" />
                <x-form.input name="new_password" type="password" label="New password" required autocomplete="new-password" hint="At least 8 characters." />
                <x-form.input name="new_confirm_password" type="password" label="Confirm new password" required autocomplete="new-password" />
                <x-button class="w-full">Update password</x-button>
            </form>
        </x-card>
    </div>
</x-layouts.app>
