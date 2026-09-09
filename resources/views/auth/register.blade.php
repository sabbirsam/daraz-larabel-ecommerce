<x-guest-layout>
    <div class="mb-6 text-center">
        <h2 class="text-2xl font-bold text-gray-900">Create your Daraz Account</h2>
        <p class="text-sm text-gray-500 mt-1">Join millions of happy shoppers across Bangladesh.</p>
    </div>

    <form method="POST" action="{{ route('register') }}">
        @csrf

        <!-- Full Name -->
        <div>
            <x-input-label for="name" :value="__('Full Name')" />
            <x-text-input id="name" class="block mt-1 w-full focus:border-daraz focus:ring-daraz" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" placeholder="e.g. John Doe" />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <!-- Email Address -->
        <div class="mt-4">
            <x-input-label for="email" :value="__('Email Address')" />
            <x-text-input id="email" class="block mt-1 w-full focus:border-daraz focus:ring-daraz" type="email" name="email" :value="old('email')" required autocomplete="username" placeholder="e.g. customer@example.com" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Phone Number -->
        <div class="mt-4">
            <x-input-label for="phone" :value="__('Mobile Phone (Optional for OTP verification)')" />
            <x-text-input id="phone" class="block mt-1 w-full focus:border-daraz focus:ring-daraz" type="text" name="phone" :value="old('phone')" placeholder="e.g. 01712345678" />
            <x-input-error :messages="$errors->get('phone')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')" />
            <x-text-input id="password" class="block mt-1 w-full focus:border-daraz focus:ring-daraz"
                            type="password"
                            name="password"
                            required autocomplete="new-password"
                            placeholder="At least 8 characters" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Confirm Password -->
        <div class="mt-4">
            <x-input-label for="password_confirmation" :value="__('Confirm Password')" />
            <x-text-input id="password_confirmation" class="block mt-1 w-full focus:border-daraz focus:ring-daraz"
                            type="password"
                            name="password_confirmation" required autocomplete="new-password"
                            placeholder="Re-enter your password" />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="mt-6">
            <x-primary-button class="w-full py-3 text-sm">
                {{ __('Register Account') }}
            </x-primary-button>
        </div>

        <div class="mt-6 text-center text-sm text-gray-600 border-t pt-4">
            <span>Already have an account?</span>
            <a href="{{ route('login') }}" class="font-semibold text-daraz hover:underline ms-1">
                Log in here.
            </a>
        </div>
    </form>
</x-guest-layout>
