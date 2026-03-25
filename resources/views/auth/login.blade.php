<x-guest-layout>
    <div x-data="{ passed: localStorage.getItem('login_gate_passed') === 'true', gateLogin: '', gatePassword: '', error: false }">
        
        <!-- Gatekeeper -->
        <div x-show="!passed" class="p-6 bg-white rounded-lg shadow-xl border border-gold-light/20">
            <h2 class="text-xl font-bold mb-6 text-gray-800 text-center">Acesso Restrito</h2>
            
            <div class="space-y-4">
                <div>
                    <x-input-label for="gate_login" value="Login Admin" />
                    <x-text-input id="gate_login" class="block mt-1 w-full" type="text" x-model="gateLogin" @keyup.enter="$dispatch('check-gate')" />
                </div>

                <div>
                    <x-input-label for="gate_password" value="Senha Admin" />
                    <x-text-input id="gate_password" class="block mt-1 w-full" type="password" x-model="gatePassword" @keyup.enter="$dispatch('check-gate')" />
                </div>

                <div x-show="error" class="text-red-600 text-sm" x-cloak>
                    Credenciais de acesso incorretas.
                </div>

                <div class="flex justify-end mt-6">
                    <x-primary-button type="button" @click="$dispatch('check-gate')">
                        Acessar Portal
                    </x-primary-button>
                </div>
            </div>
        </div>

        <script>
            document.addEventListener('check-gate', () => {
                const data = document.querySelector('[x-data]').__x.$data;
                if (data.gateLogin === 'login admin' && data.gatePassword === 'admin') {
                    data.passed = true;
                    localStorage.setItem('login_gate_passed', 'true');
                    data.error = false;
                } else {
                    data.error = true;
                    data.gatePassword = '';
                }
            });
        </script>

        <!-- Actual Login Form -->
        <div x-show="passed" x-cloak>
            <!-- Session Status -->
            <x-auth-session-status class="mb-4" :status="session('status')" />

            <form method="POST" action="{{ route('login') }}">
                @csrf

                <!-- Email Address -->
                <div>
                    <x-input-label for="email" :value="__('Email')" />
                    <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
                    <x-input-error :messages="$errors->get('email')" class="mt-2" />
                </div>

                <!-- Password -->
                <div class="mt-4">
                    <x-input-label for="password" :value="__('Password')" />

                    <x-text-input id="password" class="block mt-1 w-full"
                                    type="password"
                                    name="password"
                                    required autocomplete="current-password" />

                    <x-input-error :messages="$errors->get('password')" class="mt-2" />
                </div>

                <!-- Remember Me -->
                <div class="block mt-4">
                    <label for="remember_me" class="inline-flex items-center">
                        <input id="remember_me" type="checkbox" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" name="remember">
                        <span class="ms-2 text-sm text-gray-600">{{ __('Remember me') }}</span>
                    </label>
                </div>

                <div class="flex items-center justify-end mt-4">
                    @if (Route::has('password.request'))
                        <a class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" href="{{ route('password.request') }}">
                            {{ __('Forgot your password?') }}
                        </a>
                    @endif

                    <x-primary-button class="ms-3">
                        {{ __('Log in') }}
                    </x-primary-button>
                </div>
            </form>

            <div class="mt-8 text-center border-t border-gray-100 pt-4">
                <p class="text-[10px] text-gray-400 uppercase tracking-widest">
                    Último deploy: {{ $last_deploy->diffForHumans() }}
                </p>
                <button @click="passed = false; localStorage.removeItem('login_gate_passed')" class="mt-2 text-[10px] text-red-400 hover:underline">
                    SAIR DO ACESSO RESTRITO
                </button>
            </div>
        </div>
    </div>
</x-guest-layout>
