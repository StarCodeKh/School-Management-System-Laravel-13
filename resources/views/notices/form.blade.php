<x-layouts.app :title="$notice ? 'Edit Notice' : 'New Notice'">
    <x-page-header :title="$notice ? 'Edit Notice' : 'New Notice'" :breadcrumbs="['Notice Board' => route('notices.index'), ($notice ? $notice->title : 'New Notice') => null]" />

    <x-card class="max-w-3xl">
        <form method="POST" action="{{ $notice ? route('notices.update', $notice) : route('notices.store') }}" class="space-y-6">
            @csrf
            @if ($notice) @method('PUT') @endif
            <x-form.section title="Message" description="What everyone will read on the notice board." icon="megaphone">
                <x-form.input name="title" label="Title" :value="$notice?->title" required wrapper="sm:col-span-2" />
                <x-form.textarea name="body" label="Message" :value="$notice?->body" rows="6" required wrapper="sm:col-span-2" />
            </x-form.section>

            <x-form.section title="Who and when" description="Who sees the notice and for how long." icon="calendar">
                <x-form.select name="audience" label="Audience" :options="\App\Enums\Audience::options()" :value="$notice?->audience ?? 'everyone'" :placeholder="false" required />
                <x-form.input name="published_at" type="datetime-local" label="Publish at" :value="($notice?->published_at ?? now())->format('Y-m-d\TH:i')" hint="Leave as now to publish immediately." />
                <x-form.input name="expires_on" type="date" label="Show until" :value="$notice?->expires_on" hint="Optional." />
                <div class="self-end pb-1.5">
                    <x-form.checkbox name="is_pinned" switch :checked="$notice?->is_pinned" hint="Pinned notices stay above the rest.">Pin to the top</x-form.checkbox>
                </div>
            </x-form.section>
            <x-form.actions :submit="$notice ? 'Save changes' : 'Publish notice'" :cancel="route('notices.index')" />
        </form>
    </x-card>
</x-layouts.app>
