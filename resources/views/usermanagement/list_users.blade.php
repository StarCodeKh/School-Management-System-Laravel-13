<x-layouts.app title="Users">
    <x-page-header title="Users" :breadcrumbs="['Dashboard' => route('home'), 'Users' => null]">
        <x-slot:actions>
            <x-button :href="route('register')" icon="plus" variant="secondary" target="_blank">Register page</x-button>
        </x-slot:actions>
    </x-page-header>

    <x-search-bar :value="$search" placeholder="Search name, email, ID or phone..." />

    <x-table>
        <x-slot:head>
            <tr><th>User ID</th><th>Name</th><th>Role</th><th>Position</th><th>Phone</th><th>Joined</th><th>Status</th><th class="text-right">Actions</th></tr>
        </x-slot:head>
        @forelse ($users as $user)
            <tr class="hover:bg-slate-50">
                <td class="font-medium text-slate-900">{{ $user->code }}</td>
                <td>
                    <div class="flex items-center gap-3">
                        <x-avatar :src="$user->avatar_url" :alt="$user->name" size="size-8" />
                        <div>
                            <p class="font-medium text-slate-900">{{ $user->name }}</p>
                            <p class="text-xs text-slate-500">{{ $user->email }}</p>
                        </div>
                    </div>
                </td>
                <td>{{ $user->role?->label() }}</td>
                <td>{{ $user->position ?: '-' }}</td>
                <td>{{ $user->phone_number ?: '-' }}</td>
                <td>{{ $user->join_date?->format('d M Y') }}</td>
                <td>
                    <x-badge :color="['active' => 'green', 'inactive' => 'amber', 'disabled' => 'red'][$user->status?->value] ?? 'slate'">{{ $user->status?->label() }}</x-badge>
                </td>
                <td class="text-right">
                    <x-icon-link :href="url('view/user/edit/'.$user->code)" icon="pencil" label="Edit" tone="primary" />
                    @unless ($user->is(auth()->user()))
                        <x-delete-button :action="route('user/delete')" :fields="['code' => $user->code]" title="Delete user"
                                         :message="'Delete the account of '.$user->name.'? This cannot be undone.'" />
                    @endunless
                </td>
            </tr>
        @empty
            <x-empty-row colspan="8" message="No users found." />
        @endforelse
        <x-slot:footer>{{ $users->links() }}</x-slot:footer>
    </x-table>
</x-layouts.app>
