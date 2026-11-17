<!DOCTYPE html>
<html lang="pt-BR" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    @php
        $pageTitle = trim($__env->yieldContent('title')) ?: ($shop['shop_name'].' | '.$shop['slogan']);
        $pageDescription = trim($__env->yieldContent('description')) ?: \Illuminate\Support\Str::limit($shop['about'], 155);
    @endphp
    <title>{{ $pageTitle }}</title>
    <meta name="description" content="{{ $pageDescription }}">
    <link rel="canonical" href="{{ url()->current() }}">

    {{-- Open Graph (prévia bonita ao compartilhar no WhatsApp/Instagram) --}}
    <meta property="og:type" content="website">
    <meta property="og:locale" content="pt_BR">
    <meta property="og:site_name" content="{{ $shop['shop_name'] }}">
    <meta property="og:title" content="{{ $pageTitle }}">
    <meta property="og:description" content="{{ $pageDescription }}">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:image" content="{{ asset('images/hero.jpg') }}">
    <meta name="twitter:card" content="summary_large_image">

    {{-- PWA / App --}}
    <link rel="manifest" href="/manifest.webmanifest">
    <meta name="theme-color" content="#0b0b0c">
    <link rel="icon" href="/favicon.ico">
    <link rel="apple-touch-icon" href="/icons/apple-touch-icon.png">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="{{ $shop['shop_name'] }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Playfair+Display:wght@600;700;800&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>html { scroll-behavior: smooth; }</style>
    @stack('head')
</head>
<body class="bg-ink text-neutral-200 font-sans min-h-screen flex flex-col">

    {{-- Cabeçalho --}}
    <header x-data="{ open: false, scrolled: false }" @scroll.window="scrolled = window.scrollY > 20"
            :class="scrolled || open ? 'bg-ink/90 backdrop-blur-xl border-b hairline' : 'bg-transparent'"
            class="fixed top-0 inset-x-0 z-40 transition-colors duration-300" style="padding-top: env(safe-area-inset-top)">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 h-16 flex items-center justify-between">
            <a href="{{ route('home') }}" class="font-display text-lg sm:text-xl font-bold tracking-wide text-gold">
                {{ $shop['shop_name'] }}
            </a>

            <nav class="hidden md:flex items-center gap-8 text-sm font-medium text-neutral-300">
                <a href="{{ route('home') }}#servicos" class="hover:text-gold transition-colors">Serviços</a>
                <a href="{{ route('home') }}#equipe" class="hover:text-gold transition-colors">Equipe</a>
                <a href="{{ route('gallery.index') }}" class="hover:text-gold transition-colors">Galeria</a>
                <a href="{{ route('home') }}#contato" class="hover:text-gold transition-colors">Contato</a>
                @auth
                    <a href="{{ route('dashboard') }}" class="hover:text-gold transition-colors">Painel</a>
                @endauth
                <a href="{{ route('booking.index') }}" class="bg-gold text-ink font-semibold px-5 py-2.5 rounded-full hover:bg-gold-300 transition-colors">Agendar horário</a>
            </nav>

            <div class="flex items-center gap-2 md:hidden">
                <a href="{{ route('booking.index') }}" class="bg-gold text-ink text-sm font-semibold px-4 py-2 rounded-full">Agendar</a>
                <button @click="open = !open" class="p-2 text-neutral-300" aria-label="Abrir menu">
                    <svg x-show="!open" class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M4 7h16M4 12h16M4 17h16"/></svg>
                    <svg x-show="open" x-cloak class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M6 6l12 12M18 6L6 18"/></svg>
                </button>
            </div>
        </div>
        <nav x-show="open" x-cloak x-transition class="md:hidden border-t hairline px-4 py-4 space-y-1 text-base">
            <a @click="open=false" href="{{ route('home') }}#servicos" class="block py-2.5 text-neutral-200">Serviços e preços</a>
            <a @click="open=false" href="{{ route('home') }}#equipe" class="block py-2.5 text-neutral-200">Equipe</a>
            <a href="{{ route('gallery.index') }}" class="block py-2.5 text-neutral-200">Galeria</a>
            <a @click="open=false" href="{{ route('home') }}#contato" class="block py-2.5 text-neutral-200">Contato e endereço</a>
            @auth
                <a href="{{ route('dashboard') }}" class="block py-2.5 text-gold">Painel da equipe</a>
            @endauth
        </nav>
    </header>

    <main class="flex-1">
        @yield('content')
    </main>

    {{-- Rodapé --}}
    @unless (View::hasSection('hide_footer'))
    <footer class="border-t hairline bg-black/40 mt-auto">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 py-12 grid gap-10 sm:grid-cols-3 text-sm">
            <div>
                <div class="font-display text-xl text-gold mb-3">{{ $shop['shop_name'] }}</div>
                <p class="text-neutral-400 leading-relaxed">{{ $shop['slogan'] }}. Agende online em menos de 1 minuto.</p>
            </div>
            <div>
                <div class="text-neutral-500 uppercase tracking-widest text-xs font-semibold mb-3">Funcionamento</div>
                <p class="text-neutral-300">{{ \App\Models\Setting::openDaysLabel() }}</p>
                <p class="text-neutral-300">{{ $shop['open_time'] }} às {{ $shop['close_time'] }}</p>
                @if ($shop['address'])
                    <p class="text-neutral-400 mt-3">{{ $shop['address'] }}@if($shop['city']) — {{ $shop['city'] }}@endif</p>
                @endif
            </div>
            <div>
                <div class="text-neutral-500 uppercase tracking-widest text-xs font-semibold mb-3">Links</div>
                <ul class="space-y-2 text-neutral-300">
                    <li><a href="{{ route('booking.index') }}" class="hover:text-gold">Agendar horário</a></li>
                    @if ($wa = \App\Models\Setting::whatsappUrl())<li><a href="{{ $wa }}" target="_blank" rel="noopener" class="hover:text-gold">WhatsApp</a></li>@endif
                    @if ($shop['instagram'])<li><a href="https://instagram.com/{{ $shop['instagram'] }}" target="_blank" rel="noopener" class="hover:text-gold">Instagram &#64;{{ $shop['instagram'] }}</a></li>@endif
                    <li><a href="{{ route('privacy') }}" class="hover:text-gold">Política de privacidade</a></li>
                </ul>
            </div>
        </div>
        <div class="border-t hairline py-5 text-center text-xs text-neutral-600 pb-safe">
            &copy; {{ date('Y') }} {{ $shop['shop_name'] }}. Todos os direitos reservados.
        </div>
    </footer>
    @endunless

    {{-- Botão flutuante do WhatsApp --}}
    @if (($wa = \App\Models\Setting::whatsappUrl('Olá! Vim pelo site e gostaria de mais informações.')) && ! View::hasSection('hide_whatsapp'))
        <a href="{{ $wa }}" target="_blank" rel="noopener" aria-label="Falar no WhatsApp"
           class="fixed right-4 z-40 w-14 h-14 rounded-full bg-[#25D366] text-white shadow-lg shadow-black/40 flex items-center justify-center hover:scale-105 transition-transform"
           style="bottom: max(1.25rem, env(safe-area-inset-bottom))">
            <svg class="w-7 h-7" viewBox="0 0 24 24" fill="currentColor"><path d="M17.47 14.38c-.3-.15-1.76-.87-2.03-.97-.27-.1-.47-.15-.67.15-.2.3-.77.97-.94 1.17-.17.2-.35.22-.65.07-.3-.15-1.26-.46-2.4-1.48-.89-.79-1.49-1.77-1.66-2.07-.17-.3-.02-.46.13-.61.13-.13.3-.35.45-.52.15-.17.2-.3.3-.5.1-.2.05-.37-.02-.52-.08-.15-.67-1.61-.92-2.21-.24-.58-.49-.5-.67-.51h-.57c-.2 0-.52.07-.8.37-.27.3-1.04 1.02-1.04 2.48s1.07 2.88 1.21 3.08c.15.2 2.1 3.2 5.08 4.49.71.31 1.26.49 1.69.63.71.23 1.36.2 1.87.12.57-.09 1.76-.72 2.01-1.41.25-.7.25-1.29.17-1.41-.07-.13-.27-.2-.57-.35zM12.04 21.5h-.01a9.4 9.4 0 0 1-4.8-1.32l-.34-.2-3.57.94.95-3.48-.22-.36a9.43 9.43 0 0 1-1.45-5.03c0-5.2 4.24-9.44 9.45-9.44a9.4 9.4 0 0 1 9.44 9.45c0 5.21-4.24 9.44-9.45 9.44zm8.04-17.48A11.3 11.3 0 0 0 12.04.7C5.77.7.67 5.8.67 12.06c0 2 .52 3.96 1.52 5.68L.57 23.7l6.1-1.6a11.33 11.33 0 0 0 5.37 1.37h.01c6.26 0 11.36-5.1 11.37-11.37 0-3.03-1.18-5.89-3.34-8.04z"/></svg>
        </a>
    @endif

    {{-- Convite para instalar o app (Android/Chrome) --}}
    <div x-data="installPrompt()" x-show="visible" x-cloak x-transition
         class="fixed inset-x-3 z-50 sm:left-auto sm:right-6 sm:w-96 bg-ink-700 border border-gold/30 rounded-2xl p-4 shadow-2xl flex items-center gap-3"
         style="bottom: max(1rem, env(safe-area-inset-bottom))">
        <img src="/icons/icon-192.png" alt="" class="w-12 h-12 rounded-xl">
        <div class="flex-1 min-w-0">
            <div class="font-semibold text-white text-sm">Instale nosso app</div>
            <div class="text-xs text-neutral-400">Agende mais rápido direto da tela inicial.</div>
        </div>
        <button @click="dismiss()" class="text-neutral-500 text-xs px-2 py-2">Agora não</button>
        <button @click="install()" class="bg-gold text-ink text-sm font-semibold px-3 py-2 rounded-lg">Instalar</button>
    </div>

    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => navigator.serviceWorker.register('/sw.js').catch(() => {}));
        }
        function installPrompt() {
            return {
                visible: false, deferred: null,
                init() {
                    let dismissed = false;
                    try { dismissed = localStorage.getItem('install-dismissed') === '1'; } catch (e) {}
                    window.addEventListener('beforeinstallprompt', (e) => {
                        e.preventDefault();
                        this.deferred = e;
                        if (!dismissed) setTimeout(() => this.visible = true, 8000);
                    });
                },
                async install() {
                    this.visible = false;
                    if (!this.deferred) return;
                    this.deferred.prompt();
                    await this.deferred.userChoice;
                    this.deferred = null;
                },
                dismiss() {
                    this.visible = false;
                    try { localStorage.setItem('install-dismissed', '1'); } catch (e) {}
                },
            };
        }
    </script>
    @stack('scripts')
</body>
</html>
