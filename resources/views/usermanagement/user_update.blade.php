<x-layouts.app title="Edit User">
    <x-page-header title="Edit User" :breadcrumbs="['Users' => route('list/users'), $users->name => null]" />

    <x-card class="max-w-4xl">
        <form method="POST" action="{{ route('user/update') }}" enctype="multipart/form-data" class="space-y-6">
            @csrf
            <input type="hidden" name="code" value="{{ $users->code }}">

            <div class="flex items-center gap-4">
                <x-avatar :src="$users->avatar_url" :alt="$users->name" size="size-16" />
                <x-form.input name="avatar" type="file" label="Profile photo" accept="image/*" wrapper="flex-1" hint="JPG, PNG or GIF, up to 2 MB." />
            </div>

            <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                <x-form.input name="name" label="Name" :value="$users->name" required />
                <x-form.input name="email" type="email" label="Email" :value="$users->email" required />
                <x-form.input name="phone_number" type="tel" label="Phone" :value="$users->phone_number" />
                <x-form.input name="date_of_birth" type="date" label="Date of birth" :value="$users->date_of_birth" />
                <x-form.select name="role" label="Role" :options="\App\Enums\Role::options()" :value="$users->role" :placeholder="false" required />
                <x-form.select name="status" label="Status" :options="\App\Enums\UserStatus::options()" :value="$users->status" :placeholder="false" required />
                <x-form.input name="position" label="Position" :value="$users->position" />
                <x-form.input name="updated_display" label="Last updated" :value="$users->updated_at?->format('d M Y H:i')" readonly />
            </div>

            <x-form.actions submit="Update user" :cancel="route('list/users')" />
        </form>
    </x-card>
</x-layouts.app>
