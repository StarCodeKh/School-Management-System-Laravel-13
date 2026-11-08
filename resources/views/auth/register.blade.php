<x-layouts.guest title="Register">
    <h1 class="text-xl font-bold text-slate-900">Create an account</h1>
    <p class="mt-1 text-sm text-slate-500">
        Already registered? <a href="{{ route('login') }}" class="font-semibold text-primary-600 hover:text-primary-700">Login</a>
    </p>

    <form method="POST" action="{{ route('register') }}" class="mt-6 space-y-5">
        @csrf
        <x-form.input name="name" label="Full name" required autofocus autocomplete="name" />
        <x-form.input name="email" type="email" label="Email" required autocomplete="username" />
        <x-form.select name="role" label="Account type" required placeholder="Select account type"
                       :options="collect($roles)->mapWithKeys(fn ($role) => [$role->value => $role->label()])" />
        <x-form.input name="password" type="password" label="Password" required autocomplete="new-password" hint="At least 8 characters." />
        <x-form.input name="password_confirmation" type="password" label="Confirm password" required autocomplete="new-password" />

        <x-button class="w-full">Register</x-button>
    </form>
</x-layouts.guest>
