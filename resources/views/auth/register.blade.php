<x-guest-layout>
    <div class="mb-8">
        <h2 class="text-3xl font-extrabold text-gray-900 tracking-tight">Create an account</h2>
        <p class="text-sm text-gray-500 mt-2">Join us to report issues and track resolutions.</p>
    </div>

    <form method="POST" action="{{ route('register') }}" class="space-y-5">
        @csrf

        <!-- Name -->
        <div>
            <x-input-label for="name" :value="__('Full Name')" class="font-medium text-gray-700" />
            <x-text-input id="name" class="block mt-1 w-full rounded-xl" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('Email Address')" class="font-medium text-gray-700" />
            <x-text-input id="email" class="block mt-1 w-full rounded-xl" type="email" name="email" :value="old('email')" required autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Phone Number -->
        <div>
            <x-input-label for="phone_number_input" :value="__('Phone Number')" class="font-medium text-gray-700" />
            <div class="flex mt-1 rounded-xl shadow-sm overflow-hidden">
                <span class="inline-flex items-center px-4 text-sm text-gray-500 bg-gray-50 border border-e-0 border-gray-300">
                    +62
                </span>
                <input type="text" 
                       id="phone_number_input" 
                       name="phone_number_input" 
                       class="rounded-none border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 block flex-1 min-w-0 w-full text-sm p-2.5" 
                       placeholder="812xxxxxx" 
                       value="{{ old('phone_number_input') }}" 
                       oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                       required>
            </div>
            <x-input-error :messages="$errors->get('phone_number')" class="mt-2" />
        </div>

        <!-- Password -->
        <div>
            <x-input-label for="password" :value="__('Password')" class="font-medium text-gray-700" />
            <x-text-input id="password" class="block mt-1 w-full rounded-xl"
                            type="password"
                            name="password"
                            required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Confirm Password -->
        <div>
            <x-input-label for="password_confirmation" :value="__('Confirm Password')" class="font-medium text-gray-700" />
            <x-text-input id="password_confirmation" class="block mt-1 w-full rounded-xl"
                            type="password"
                            name="password_confirmation" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="pt-2">
            <x-primary-button class="w-full justify-center py-3 text-sm rounded-xl bg-gradient-to-r from-indigo-600 to-indigo-700 hover:from-indigo-500 hover:to-indigo-600 shadow-md transition-all">
                {{ __('Register') }}
            </x-primary-button>
        </div>

        <div class="mt-6 text-center">
            <p class="text-sm text-gray-600">
                Already registered?
                <a href="{{ route('login') }}" class="text-indigo-600 hover:text-indigo-500 font-semibold transition">
                    Log in
                </a>
            </p>
        </div>
    </form>
</x-guest-layout>
