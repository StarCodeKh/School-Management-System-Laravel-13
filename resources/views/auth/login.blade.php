<x-layouts.guest title="Login">
    <h1 class="text-xl font-bold text-slate-900">Welcome back</h1>
    <p class="mt-1 text-sm text-slate-500">
        Need an account? <a href="{{ route('register') }}" class="font-semibold text-primary-600 hover:text-primary-700">Sign up</a>
    </p>

    <form method="POST" action="{{ route('login') }}" class="mt-6 space-y-5">
        @csrf
        <x-form.input name="email" type="email" label="Email" required autofocus autocomplete="username" />
        <x-form.input name="password" type="password" label="Password" required autocomplete="current-password" />

        <div class="flex items-center justify-between">
            <x-form.checkbox name="remember">Remember me</x-form.checkbox>
            <a href="{{ route('password.request') }}" class="text-sm font-semibold text-primary-600 hover:text-primary-700">Forgot password?</a>
        </div>

        <x-button class="w-full">Login</x-button>
    </form>
</x-layouts.guest>
