<x-guest-layout>
    <div class="mb-6 text-center">
        <h2 class="text-2xl font-bold text-gray-900">Welcome to Daraz!</h2>
        <p class="text-sm text-gray-500 mt-1">Please log in with your customer account.</p>
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('Email Address')" />
            <x-text-input id="email" class="block mt-1 w-full focus:border-daraz focus:ring-daraz" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" placeholder="e.g. customer@daraz.local" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')" />

            <x-text-input id="password" class="block mt-1 w-full focus:border-daraz focus:ring-daraz"
                            type="password"
                            name="password"
                            required autocomplete="current-password"
                            placeholder="••••••••" />

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Remember Me & Forgot Password -->
        <div class="flex items-center justify-between mt-4">
            <label for="remember_me" class="inline-flex items-center cursor-pointer">
                <input id="remember_me" type="checkbox" class="rounded border-gray-300 text-daraz shadow-sm focus:ring-daraz" name="remember">
                <span class="ms-2 text-sm text-gray-600">{{ __('Remember me') }}</span>
            </label>

            @if (Route::has('password.request'))
                <a class="text-xs text-daraz hover:underline" href="{{ route('password.request') }}">
                    {{ __('Forgot password?') }}
                </a>
            @endif
        </div>

        <div class="mt-6">
            <x-primary-button class="w-full py-3 text-sm">
                {{ __('Log in') }}
            </x-primary-button>
        </div>

        <div class="mt-6 text-center text-sm text-gray-600 border-t pt-4">
            <span>New member?</span>
            <a href="{{ route('register') }}" class="font-semibold text-daraz hover:underline ms-1">
                Register here.
            </a>
        </div>
    </form>
</x-guest-layout>
