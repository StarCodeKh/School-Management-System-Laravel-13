<x-layouts.guest title="Confirm Password">
    <h1 class="text-xl font-bold text-slate-900">Confirm your password</h1>
    <p class="mt-1 text-sm text-slate-500">Please confirm your password before continuing.</p>

    <form method="POST" action="{{ route('password.confirm') }}" class="mt-6 space-y-5">
        @csrf
        <x-form.input name="password" type="password" label="Password" required autofocus autocomplete="current-password" />
        <div class="flex items-center justify-between">
            <a href="{{ route('password.request') }}" class="text-sm font-semibold text-primary-600 hover:text-primary-700">Forgot password?</a>
            <x-button>Confirm</x-button>
        </div>
    </form>
</x-layouts.guest>
