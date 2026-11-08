<x-layouts.guest title="Reset Password">
    <h1 class="text-xl font-bold text-slate-900">Choose a new password</h1>

    <form method="POST" action="{{ route('password.update') }}" class="mt-6 space-y-5">
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">
        <x-form.input name="email" type="email" label="Email" :value="$email ?? null" required autocomplete="username" />
        <x-form.input name="password" type="password" label="New password" required autofocus autocomplete="new-password" />
        <x-form.input name="password_confirmation" type="password" label="Confirm password" required autocomplete="new-password" />
        <x-button class="w-full">Reset password</x-button>
    </form>
</x-layouts.guest>
