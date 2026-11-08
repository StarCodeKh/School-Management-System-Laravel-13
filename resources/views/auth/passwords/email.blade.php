<x-layouts.guest title="Reset Password">
    <h1 class="text-xl font-bold text-slate-900">Forgot your password?</h1>
    <p class="mt-1 text-sm text-slate-500">Enter your email and we will send you a link to choose a new one.</p>

    @if (session('status'))
        <div class="mt-4 rounded-lg bg-emerald-50 p-3 text-sm text-emerald-700">{{ session('status') }}</div>
    @endif

    <form method="POST" action="{{ route('password.email') }}" class="mt-6 space-y-5">
        @csrf
        <x-form.input name="email" type="email" label="Email" required autofocus autocomplete="username" />
        <x-button class="w-full">Send reset link</x-button>
    </form>

    <p class="mt-6 text-center text-sm">
        <a href="{{ route('login') }}" class="font-semibold text-primary-600 hover:text-primary-700">Back to login</a>
    </p>
</x-layouts.guest>
