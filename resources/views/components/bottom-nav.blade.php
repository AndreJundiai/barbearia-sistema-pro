@auth
<div class="fixed bottom-0 left-0 z-50 w-full h-16 bg-white border-t border-gray-200 sm:hidden dark:bg-gray-800 dark:border-gray-700">
    <div class="grid h-full max-w-lg grid-cols-5 mx-auto font-medium">
        <a href="{{ route('dashboard') }}" class="inline-flex flex-col items-center justify-center px-5 hover:bg-gray-50 dark:hover:bg-gray-700 group {{ request()->routeIs('dashboard') ? 'text-gold-400' : 'text-gray-500 dark:text-gray-400' }}">
            <svg class="w-5 h-5 mb-1 {{ request()->routeIs('dashboard') ? 'text-gold-400' : 'text-gray-500 dark:text-gray-400' }} group-hover:text-gold-600 dark:group-hover:text-gold-300" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                <path d="m19.707 9.293-2-2-7-7a1 1 0 0 0-1.414 0l-7 7-2 2a1 1 0 0 0 1.414 1.414L2 10.414V18a2 2 0 0 0 2 2h3a1 1 0 0 0 1-1v-4a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v4a1 1 0 0 0 1 1h3a2 2 0 0 0 2-2v-7.586l.293.293a1 1 0 0 0 1.414-1.414Z"/>
            </svg>
            <span class="text-[10px]">Início</span>
        </a>
        <a href="{{ route('booking.index') }}" class="inline-flex flex-col items-center justify-center px-5 hover:bg-gray-50 dark:hover:bg-gray-700 group {{ request()->routeIs('booking.*') ? 'text-gold-400' : 'text-gray-500 dark:text-gray-400' }}">
            <svg class="w-5 h-5 mb-1 {{ request()->routeIs('booking.*') ? 'text-gold-400' : 'text-gray-500 dark:text-gray-400' }} group-hover:text-gold-600 dark:group-hover:text-gold-300" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                <path d="M6 1a1 1 0 0 0-2 0h2ZM4 4a1 1 0 0 0 2 0H4Zm7-3a1 1 0 1 0-2 0h2ZM9 4a1 1 0 1 0 2 0H9Zm7-3a1 1 0 1 0-2 0h2Zm-2 3a1 1 0 1 0 2 0h-2ZM1 6a1 1 0 0 0 0 2V6Zm18 2a1 1 0 1 0 0-2v2ZM5 11v-1H4v1h1Zm0 4h-1v1h1v-1Zm10-4v-1h-1v1h1Zm0 4h-1v1h1v-1ZM4 7h16V5H4v2Zm16 1a1 1 0 0 0-1-1v2a1 1 0 0 0 1-1Zm-1 1H5V7H1v2h18V9ZM5 8a1 1 0 0 0-1 1h2a1 1 0 0 0-1-1Zm-1 1v10h2V9H4Zm1 11h14v-2H5v2Zm15-1V9h-2v10h2Zm-1 1a1 1 0 0 0 1-1h-2a1 1 0 0 0 1 1ZM10 11h.01v-2H10v2Zm0 4h.01v-2H10v2Zm5-4h.01v-2H15v2Zm0 4h.01v-2H15v2ZM5 11h.01v-2H5v2Zm0 4h.01v-2H5v2Z"/>
            </svg>
            <span class="text-[10px]">Agendar</span>
        </a>
        <a href="{{ route('gallery.index') }}" class="inline-flex flex-col items-center justify-center px-5 hover:bg-gray-50 dark:hover:bg-gray-700 group {{ request()->routeIs('gallery.index') ? 'text-gold-400' : 'text-gray-500 dark:text-gray-400' }}">
            <svg class="w-5 h-5 mb-1 {{ request()->routeIs('gallery.index') ? 'text-gold-400' : 'text-gray-500 dark:text-gray-400' }} group-hover:text-gold-600 dark:group-hover:text-gold-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
            </svg>
            <span class="text-[10px]">Galeria</span>
        </a>
        <a href="{{ route('finance.index') }}" class="inline-flex flex-col items-center justify-center px-5 hover:bg-gray-50 dark:hover:bg-gray-700 group {{ request()->routeIs('finance.*') ? 'text-gold-400' : 'text-gray-500 dark:text-gray-400' }}">
            <svg class="w-5 h-5 mb-1 {{ request()->routeIs('finance.*') ? 'text-gold-400' : 'text-gray-500 dark:text-gray-400' }} group-hover:text-gold-600 dark:group-hover:text-gold-300" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                <path d="M11 9H9V2H7v7H5l3 3 3-3zM5 13v2h6v-2H5zm8-10h-2v2h2v11a1 1 0 0 1-1 1H2a1 1 0 0 1-1-1V5a1 1 0 0 1 1-1h2V2H2a3 3 0 0 0-3 3v11a3 3 0 0 0 3 3h10a3 3 0 0 0 3-3V5a3 3 0 0 0-3-3z"/>
            </svg>
            <span class="text-[10px]">Finanças</span>
        </a>
        <a href="{{ route('profile.edit') }}" class="inline-flex flex-col items-center justify-center px-5 hover:bg-gray-50 dark:hover:bg-gray-700 group {{ request()->routeIs('profile.*') ? 'text-gold-400' : 'text-gray-500 dark:text-gray-400' }}">
            <svg class="w-5 h-5 mb-1 {{ request()->routeIs('profile.*') ? 'text-gold-400' : 'text-gray-500 dark:text-gray-400' }} group-hover:text-gold-600 dark:group-hover:text-gold-300" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                <path d="M10 0a10 10 0 1 0 10 10A10.011 10.011 0 0 0 10 0Zm0 5a3 3 0 1 1 0 6 3 3 0 0 1 0-6Zm0 13a8.949 8.949 0 0 1-4.951-1.488A3.987 3.987 0 0 1 9 13h2a3.987 3.987 0 0 1 3.951 3.512A8.949 8.949 0 0 1 10 18Z"/>
            </svg>
            <span class="text-[10px]">Perfil</span>
        </a>
    </div>
</div>
@endauth
