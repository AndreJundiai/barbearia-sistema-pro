@php
    $links = [
        ['dashboard', 'Agenda', 'dashboard', false],
        ['finance.index', 'Financeiro', 'finance.*', false],
        ['services.index', 'Serviços', 'services.*', true],
        ['hairdressers.index', 'Equipe', 'hairdressers.*', true],
        ['gallery.index', 'Galeria', 'gallery.*', true],
        ['users.index', 'Usuários', 'users.*', true],
        ['settings.edit', 'Configurações', 'settings.*', true],
    ];
    $isAdmin = Auth::user()?->isAdmin();
@endphp
<nav class="bg-gray-950 text-gray-300 sticky top-0 z-30">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-14 flex items-center justify-between gap-6">
        <a href="{{ route('dashboard') }}" class="font-luxury text-gold text-lg shrink-0">{{ $shop['shop_name'] }}</a>

        <div class="hidden lg:flex items-center gap-1 text-sm">
            @foreach ($links as [$route, $label, $pattern, $adminOnly])
                @if (! $adminOnly || $isAdmin)
                    <a href="{{ route($route) }}" class="px-3 py-2 rounded-lg {{ request()->routeIs($pattern) ? 'bg-white/10 text-white' : 'hover:text-white' }}">{{ $label }}</a>
                @endif
            @endforeach
        </div>

        <div class="flex items-center gap-3 text-sm">
            <a href="{{ route('home') }}" target="_blank" class="hidden sm:inline hover:text-white">Ver site ↗</a>
            <x-dropdown align="right" width="48">
                <x-slot name="trigger">
                    <button class="flex items-center gap-2 hover:text-white">
                        <span class="w-8 h-8 rounded-full bg-gold text-gray-950 font-bold flex items-center justify-center">{{ mb_substr(Auth::user()->name, 0, 1) }}</span>
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.3 7.3a1 1 0 011.4 0L10 10.6l3.3-3.3a1 1 0 111.4 1.4l-4 4a1 1 0 01-1.4 0l-4-4a1 1 0 010-1.4z" clip-rule="evenodd"/></svg>
                    </button>
                </x-slot>
                <x-slot name="content">
                    <div class="px-4 py-2 text-xs text-gray-500 border-b">{{ Auth::user()->email }}</div>
                    <x-dropdown-link :href="route('profile.edit')">Meu perfil</x-dropdown-link>
                    <x-dropdown-link :href="route('home')">Ver site</x-dropdown-link>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <x-dropdown-link :href="route('logout')" onclick="event.preventDefault(); this.closest('form').submit();">Sair</x-dropdown-link>
                    </form>
                </x-slot>
            </x-dropdown>
        </div>
    </div>
</nav>
