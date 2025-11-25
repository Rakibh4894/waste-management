<x-guest-background>
    <h2>Dhaka Waste Management</h2>

    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('register') }}" class="w-full max-w-md sm:max-w-lg mx-auto">
        @csrf

        <!-- Name -->
        <div class="mt-4">
            <x-input-label for="name" :value="__('Name')" />
            <x-text-input id="name" class="block mt-1 w-full rounded-lg border border-gray-300 p-3 focus:ring-1 focus:ring-green-500 focus:border-green-500" 
                          type="text" 
                          name="name" 
                          :value="old('name')" 
                          required autofocus autocomplete="name" />
            <x-input-error :messages="$errors->get('name')" class="mt-2 text-red-600 text-sm" />
        </div>

        <!-- Email Address -->
        <div class="mt-4">
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="block mt-1 w-full rounded-lg border border-gray-300 p-3 focus:ring-1 focus:ring-green-500 focus:border-green-500" 
                          type="email" 
                          name="email" 
                          :value="old('email')" 
                          required autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2 text-red-600 text-sm" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')" />
            <x-text-input id="password" class="block mt-1 w-full rounded-lg border border-gray-300 p-3 focus:ring-1 focus:ring-green-500 focus:border-green-500" 
                          type="password" 
                          name="password" 
                          required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2 text-red-600 text-sm" />
        </div>

        <!-- Confirm Password -->
        <div class="mt-4">
            <x-input-label for="password_confirmation" :value="__('Confirm Password')" />
            <x-text-input id="password_confirmation" class="block mt-1 w-full rounded-lg border border-gray-300 p-3 focus:ring-1 focus:ring-green-500 focus:border-green-500" 
                          type="password" 
                          name="password_confirmation" 
                          required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2 text-red-600 text-sm" />
        </div>

        <div class="flex items-center justify-end mt-6">
            <a class="underline text-sm text-green-600 hover:text-green-800" href="{{ route('login') }}">
                {{ __('Already registered?') }}
            </a>

            <x-primary-button class="ms-4 bg-green-600 hover:bg-green-700 text-white font-bold px-6 py-3 rounded-lg">
                {{ __('Register') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-background>
