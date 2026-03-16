<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Painel de Agendamentos') }}
        </h2>
    </x-slot>

    <div class="py-6 sm:py-12 px-4 sm:px-0">
        <div class="max-w-7xl mx-auto">
            <div class="space-y-6">
                <!-- Header Section -->
                <div class="flex items-center justify-between mb-2">
                    <h3 class="text-2xl font-bold text-gray-900">Olá, {{ Auth::user()->name }}!</h3>
                    <div class="bg-gold-100 text-gold-800 px-3 py-1 rounded-full text-sm font-semibold">
                        Nível Elite
                    </div>
                </div>

                <!-- Quick Action Button -->
                <div class="block sm:hidden">
                    <a href="{{ route('booking.index') }}" class="flex items-center justify-center w-full bg-gold-400 hover:bg-gold-500 text-white py-4 rounded-2xl shadow-lg transition-all active:scale-95">
                        <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        <span class="font-bold uppercase tracking-wider">Novo Agendamento</span>
                    </a>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- Column 1: Next Appointments (Modern Card) -->
                    <div class="md:col-span-2 space-y-4">
                        <div class="flex items-center justify-between">
                            <h4 class="text-lg font-semibold text-gray-700">Seus Próximos Cortes</h4>
                            <span class="text-sm text-gold-600 font-medium">Ver todos</span>
                        </div>

                        @forelse($appointments as $appointment)
                            <div class="bg-white p-5 rounded-3xl shadow-sm border border-gray-100 flex items-center justify-between transform transition-all hover:shadow-md">
                                <div class="flex items-center space-x-4">
                                    <div class="bg-gold-50 p-3 rounded-2xl">
                                        <div class="text-center">
                                            <span class="block text-xs font-bold text-gold-800 uppercase">{{ \Carbon\Carbon::parse($appointment->scheduled_at)->translatedFormat('M') }}</span>
                                            <span class="block text-xl font-black text-gold-900">{{ \Carbon\Carbon::parse($appointment->scheduled_at)->format('d') }}</span>
                                        </div>
                                    </div>
                                    <div>
                                        <h5 class="font-bold text-gray-900 text-lg">{{ $appointment->service->name }}</h5>
                                        <div class="flex items-center text-sm text-gray-500">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                            {{ \Carbon\Carbon::parse($appointment->scheduled_at)->format('H:i') }} • {{ $appointment->hairdresser->name ?? 'Qualquer Profissional' }}
                                        </div>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <div class="text-lg font-black text-gray-900">R$ {{ number_format($appointment->total_price, 2, ',', '.') }}</div>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">Confirmado</span>
                                </div>
                            </div>
                        @empty
                            <div class="bg-white p-10 rounded-3xl border-2 border-dashed border-gray-200 text-center">
                                <div class="bg-gray-50 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                                    <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                    </svg>
                                </div>
                                <p class="text-gray-500 font-medium">Você ainda não tem agendamentos.</p>
                                <a href="{{ route('booking.index') }}" class="mt-4 inline-block text-gold-600 font-bold hover:underline">Agendar meu primeiro corte</a>
                            </div>
                        @endforelse
                    </div>

                    <!-- Column 2: Stats / Sidebar -->
                    <div class="space-y-6">
                        <div class="bg-deep-black text-white p-6 rounded-3xl shadow-xl relative overflow-hidden">
                            <div class="relative z-10">
                                <h4 class="text-gold-400 font-bold text-sm uppercase mb-1">Status da Conta</h4>
                                <div class="text-3xl font-black mb-4">Cliente VIP</div>
                                <div class="w-full bg-gray-800 rounded-full h-2 mb-2">
                                    <div class="bg-gold-400 h-2 rounded-full" style="width: 75%"></div>
                                </div>
                                <p class="text-xs text-gray-400">Faltam 2 cortes para seu próximo desconto!</p>
                            </div>
                            <!-- Subtle background SVG -->
                            <svg class="absolute right-[-20px] bottom-[-20px] w-32 h-32 text-gray-800 opacity-20 transform -rotate-12" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M5 2a1 1 0 011 1v1h1a1 1 0 010 2H6v1a1 1 0 01-2 0V6H3a1 1 0 110-2h1V3a1 1 0 011-1zm0 10a1 1 0 011 1v1h1a1 1 0 110 2H6v1a1 1 0 11-2 0v-1H3a1 1 0 110-2h1v-1a1 1 0 011-1zM12 2a1 1 0 01.967.744L14.146 7.2 17.5 9.134a1 1 0 010 1.732l-3.354 1.935-1.18 4.455a1 1 0 01-1.933 0L9.854 12.8 6.5 10.866a1 1 0 010-1.732l3.354-1.935 1.18-4.455A1 1 0 0112 2z" clip-rule="evenodd"></path>
                            </svg>
                        </div>

                        <div class="bg-white p-6 rounded-3xl shadow-sm border border-gray-100">
                            <h4 class="font-bold text-gray-900 mb-4">Avisos</h4>
                            <ul class="space-y-3">
                                <li class="flex items-start">
                                    <span class="bg-blue-100 text-blue-800 p-1 rounded mr-3 mt-0.5">
                                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 10-2 0v2H7a1 1 0 100 2h2v2a1 1 0 102 0v-2h2a1 1 0 100-2h-2V7z"></path></svg>
                                    </span>
                                    <p class="text-sm text-gray-600">Teremos horários especiais no feriado de Páscoa.</p>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
