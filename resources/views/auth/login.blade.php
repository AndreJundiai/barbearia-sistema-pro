<x-guest-layout>
    <style>
        /* Oculta o corpo da página por padrão para o gatekeeper */
        #login-content { display: none; }
    </style>

    <script>
        (function() {
            function checkGatekeeper() {
                // Se já validou nesta sessão, mostra o conteúdo
                if (sessionStorage.getItem('gateway_passed') === 'true') {
                    return true;
                }

                let user = prompt("Login Admin:");
                if (user === "login admin") {
                    let pass = prompt("Senha Admin:");
                    if (pass === "admin") {
                        sessionStorage.setItem('gateway_passed', 'true');
                        return true;
                    }
                }
                
                alert("Acesso negado.");
                window.location.href = "/";
                return false;
            }

            // Executa o check
            if (checkGatekeeper()) {
                // Se passou, mostra o conteúdo após o carregamento do DOM
                document.addEventListener('DOMContentLoaded', function() {
                    document.getElementById('login-content').style.display = 'block';
                });
            }
        })();
    </script>

    <div id="login-content">
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
                Último deploy: {{ $last_deploy->diffForHumans() ?? 'agora' }}
            </p>
        </div>
    </div>
</x-guest-layout>
