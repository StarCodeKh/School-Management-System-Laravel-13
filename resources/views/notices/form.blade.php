<x-layouts.app :title="$notice ? 'Edit Notice' : 'New Notice'">
    <x-page-header :title="$notice ? 'Edit notice' : 'New notice'" :breadcrumbs="['Notices' => route('notices.index'), ($notice ? 'Edit' : 'New') => null]" />

    <x-card class="max-w-3xl">
        <form method="POST" action="{{ $notice ? route('notices.update', $notice) : route('notices.store') }}" class="space-y-6">
            @csrf
            @if ($notice) @method('PUT') @endif
            <div class="grid gap-5 sm:grid-cols-2">
                <x-form.input name="title" label="Title" :value="$notice?->title" required wrapper="sm:col-span-2" />
                <x-form.textarea name="body" label="Message" :value="$notice?->body" rows="6" required wrapper="sm:col-span-2" />
                <x-form.select name="audience" label="Audience" :options="\App\Enums\Audience::options()" :value="$notice?->audience ?? 'everyone'" :placeholder="false" required />
                <x-form.input name="published_at" type="datetime-local" label="Publish at" :value="($notice?->published_at ?? now())->format('Y-m-d\TH:i')" hint="Leave as now to publish immediately." />
                <x-form.input name="expires_on" type="date" label="Show until" :value="$notice?->expires_on" hint="Optional." />
                <label class="flex items-center gap-2 self-end pb-2 text-sm font-medium text-slate-700">
                    <input type="checkbox" name="is_pinned" value="1" @checked(old('is_pinned', $notice?->is_pinned)) class="rounded border-slate-300 text-primary-600 focus:ring-primary-500">
                    Pin to the top
                </label>
            </div>
            <x-form.actions :submit="$notice ? 'Update notice' : 'Publish notice'" :cancel="route('notices.index')" />
        </form>
    </x-card>
</x-layouts.app>
