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
                        <style>
                            @keyframes shimmer {
                                0% { transform: translateX(-100%) rotate(25deg); }
                                100% { transform: translateX(200%) rotate(25deg); }
                            }
                            .shimmer-effect {
                                position: absolute; top: 0; left: 0; width: 50%; height: 200%;
                                background: linear-gradient(to right, transparent, rgba(255,255,255,0.1), transparent);
                                animation: shimmer 4s infinite linear;
                                pointer-events: none;
                            }
                        </style>
                        <div class="bg-gradient-to-br from-[#1a1a1a] to-[#2d2d2d] text-white p-8 rounded-[2rem] shadow-2xl relative overflow-hidden border border-gold/30 hover:border-gold/60 transition-all duration-500 group">
                            <div class="shimmer-effect"></div>
                            
                            <div class="relative z-10">
                                @php
                                    $topCustomer = $appointments->first()?->customer;
                                    $points = $topCustomer?->loyalty_points ?? 0;
                                    $target = 10;
                                    $progress = min(($points / $target) * 100, 100);
                                @endphp
                                
                                <div class="flex justify-between items-start mb-8">
                                    <div>
                                        <div class="text-[10px] text-gold-400 font-black uppercase tracking-[0.2em] mb-1">Membro Exclusivo</div>
                                        <h4 class="text-2xl font-luxury text-white">Elite Membership</h4>
                                    </div>
                                    <div class="bg-gold-400/10 p-2 rounded-xl border border-gold-400/20">
                                        <svg class="w-8 h-8 text-gold-400" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M5 2a1 1 0 011 1v1h1a1 1 0 010 2H6v1a1 1 0 01-2 0V6H3a1 1 0 110-2h1V3a1 1 0 011-1zm0 10a1 1 0 011 1v1h1a1 1 0 110 2H6v1a1 1 0 11-2 0v-1H3a1 1 0 110-2h1v-1a1 1 0 011-1zM12 2a1 1 0 01.967.744L14.146 7.2 17.5 9.134a1 1 0 010 1.732l-3.354 1.935-1.18 4.455a1 1 0 01-1.933 0L9.854 12.8 6.5 10.866a1 1 0 010-1.732l3.354-1.935 1.18-4.455A1 1 0 0112 2z"></path>
                                        </svg>
                                    </div>
                                </div>

                                <div class="mb-8">
                                    <div class="flex justify-between items-end mb-2">
                                        <span class="text-xs text-gray-400 uppercase font-bold">Progresso VIP</span>
                                        <span class="text-gold-400 font-bold">{{ $points }}/{{ $target }}</span>
                                    </div>
                                    <div class="w-full bg-black/40 rounded-full h-2 relative border border-white/5 overflow-hidden">
                                        <div class="bg-gradient-to-r from-gold-600 to-gold-400 h-full rounded-full transition-all duration-1000 relative" style="width: {{ $progress }}%">
                                            <div class="absolute inset-0 bg-white/20 animate-pulse"></div>
                                        </div>
                                    </div>
                                </div>

                                <div class="flex items-center justify-between">
                                    <div class="text-sm font-mono text-gray-500 tracking-widest">
                                        **** **** **** {{ str_pad(Auth::id(), 4, '0', STR_PAD_LEFT) }}
                                    </div>
                                    <div class="text-right">
                                        <div class="text-[10px] text-gray-400 uppercase mb-1">Status</div>
                                        <span class="inline-flex px-3 py-1 rounded-full text-[10px] font-bold {{ $points >= $target ? 'bg-green-500/20 text-green-400 border border-green-500/30' : 'bg-gold-500/20 text-gold-400 border border-gold-500/30' }}">
                                            {{ $points >= $target ? 'RECOMPENSA DISPONÍVEL' : 'PROX-LEVEL ELITE' }}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <!-- Geometric Patterns -->
                            <div class="absolute top-[-10%] right-[-10%] w-40 h-40 bg-gold-400/5 rounded-full blur-3xl group-hover:bg-gold-400/10 transition-colors"></div>
                            <div class="absolute bottom-[-10%] left-[-10%] w-32 h-32 bg-gold-400/5 rounded-full blur-2xl group-hover:bg-gold-400/10 transition-colors"></div>
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
