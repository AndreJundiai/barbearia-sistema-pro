<x-guest-layout>
    <div x-data="{ 
        gatewayPassed: sessionStorage.getItem('gateway_passed') === 'true',
        user: '',
        pass: '',
        error: '',
        checkGateway() {
            if (this.user === 'login admin' && this.pass === 'admin') {
                sessionStorage.setItem('gateway_passed', 'true');
                this.gatewayPassed = true;
            } else {
                this.error = 'Credenciais incorretas.';
            }
        }
    }" class="relative">
        
        <!-- Tela de Desbloqueio (Gatekeeper) -->
        <template x-if="!gatewayPassed">
            <div class="flex flex-col items-center justify-center space-y-6 py-10 animate-fade-in">
                <div class="text-center">
                    <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-amber-50 mb-4">
                        <svg class="w-8 h-8 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                        </svg>
                    </div>
                    <h2 class="text-2xl font-bold text-gray-800">Acesso Restrito</h2>
                    <p class="text-sm text-gray-500 mt-2">Identifique-se para acessar o painel administrativo</p>
                </div>

                <div class="w-full space-y-4">
                    <div>
                        <x-input-label for="gw_user" value="Identificador" />
                        <x-text-input 
                            id="gw_user" 
                            x-model="user" 
                            class="block mt-1 w-full" 
                            type="text" 
                            placeholder="Digite o identificador..."
                            @keyup.enter="document.getElementById('gw_pass').focus()"
                        />
                    </div>

                    <div>
                        <x-input-label for="gw_pass" value="Chave de Acesso" />
                        <x-text-input 
                            id="gw_pass" 
                            x-model="pass" 
                            class="block mt-1 w-full" 
                            type="password" 
                            placeholder="••••••••"
                            @keyup.enter="checkGateway"
                        />
                    </div>

                    <div x-show="error" x-text="error" class="text-red-500 text-xs font-medium bg-red-50 p-2 rounded border border-red-100" x-cloak></div>

                    <button 
                        @click="checkGateway"
                        class="w-full flex justify-center py-3 px-4 border border-transparent rounded-md shadow-sm text-sm font-bold text-white bg-amber-600 hover:bg-amber-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-amber-500 transition-all duration-200 transform active:scale-[0.98]"
                    >
                        DESBLOQUEAR ACESSO
                    </button>
                    
                    <div class="text-center mt-4">
                        <a href="/" class="text-xs text-amber-600 hover:text-amber-800 font-medium">Voltar para o Início</a>
                    </div>
                </div>
            </div>
        </template>

        <!-- Formulário de Login Original -->
        <div x-show="gatewayPassed" x-cloak class="animate-fade-in">
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
                <p class="text-[10px] text-gray-400 uppercase tracking-widest leading-loose">
                    Sistema Barbearia Premium<br>
                    Último deploy: {{ $last_deploy->diffForHumans() ?? 'agora' }}
                </p>
            </div>
        </div>
    </div>

    <style>
        [x-cloak] { display: none !important; }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .animate-fade-in {
            animation: fadeIn 0.4s ease-out forwards;
        }
    </style>
</x-guest-layout>
