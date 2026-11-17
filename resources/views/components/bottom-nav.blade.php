@auth
@php $isAdmin = Auth::user()->isAdmin(); @endphp
<nav x-data="{ more: false }" class="lg:hidden fixed bottom-0 inset-x-0 z-40 bg-white border-t border-gray-200 pb-safe">
    <div class="grid grid-cols-4 h-16 text-[11px] font-medium text-gray-500">
        <a href="{{ route('dashboard') }}" class="flex flex-col items-center justify-center gap-1 {{ request()->routeIs('dashboard') ? 'text-gray-900' : '' }}">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><rect x="3" y="5" width="18" height="16" rx="2"/><path d="M16 3v4M8 3v4M3 10h18"/></svg>
            Agenda
        </a>
        <a href="{{ route('finance.index') }}" class="flex flex-col items-center justify-center gap-1 {{ request()->routeIs('finance.*') ? 'text-gray-900' : '' }}">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M4 19V9M10 19V5M16 19v-7M22 19H2"/></svg>
            Financeiro
        </a>
        <a href="{{ $isAdmin ? route('services.index') : route('booking.index') }}" class="flex flex-col items-center justify-center gap-1 {{ request()->routeIs('services.*') ? 'text-gray-900' : '' }}">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><circle cx="6" cy="6" r="3"/><circle cx="6" cy="18" r="3"/><path d="M20 4L8.1 15.9M14.5 14.5L20 20M8.1 8.1L12 12"/></svg>
            {{ $isAdmin ? 'Serviços' : 'Agendar' }}
        </a>
        <button type="button" @click="more = !more" class="flex flex-col items-center justify-center gap-1">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" d="M4 7h16M4 12h16M4 17h16"/></svg>
            Mais
        </button>
    </div>
    <div x-show="more" x-cloak x-transition @click.outside="more = false" class="absolute bottom-full inset-x-0 bg-white border-t border-gray-200 shadow-2xl p-2 grid grid-cols-2 gap-1 text-sm">
        @if ($isAdmin)
            <a href="{{ route('hairdressers.index') }}" class="px-4 py-3 rounded-lg hover:bg-gray-50">Equipe</a>
            <a href="{{ route('gallery.index') }}" class="px-4 py-3 rounded-lg hover:bg-gray-50">Galeria</a>
            <a href="{{ route('users.index') }}" class="px-4 py-3 rounded-lg hover:bg-gray-50">Usuários</a>
            <a href="{{ route('settings.edit') }}" class="px-4 py-3 rounded-lg hover:bg-gray-50">Configurações</a>
        @endif
        <a href="{{ route('profile.edit') }}" class="px-4 py-3 rounded-lg hover:bg-gray-50">Meu perfil</a>
        <a href="{{ route('home') }}" class="px-4 py-3 rounded-lg hover:bg-gray-50">Ver site</a>
        <form method="POST" action="{{ route('logout') }}" class="col-span-2">
            @csrf
            <button class="w-full text-left px-4 py-3 rounded-lg text-red-600 hover:bg-red-50">Sair</button>
        </form>
    </div>
</nav>
@endauth
