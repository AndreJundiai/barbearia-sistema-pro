<x-app-layout>
    @php
        $money = fn ($v) => 'R$ '.number_format((float) $v, 2, ',', '.');
        $badge = [
            'scheduled' => 'bg-blue-50 text-blue-700 ring-blue-200',
            'pending_payment' => 'bg-amber-50 text-amber-700 ring-amber-200',
            'completed' => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
            'cancelled' => 'bg-gray-100 text-gray-500 ring-gray-200',
            'no_show' => 'bg-red-50 text-red-700 ring-red-200',
        ];
        $isToday = $date->isToday();
    @endphp

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-6" x-data="{ newOpen: {{ $errors->any() ? 'true' : 'false' }} }">

        {{-- Cabeçalho --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold">Olá, {{ \Illuminate\Support\Str::before(Auth::user()->name, ' ') }} 👋</h1>
                <p class="text-sm text-gray-500">{{ ucfirst(now()->translatedFormat('l, d \d\e F')) }}</p>
            </div>
            <div class="flex gap-2">
                <button type="button" @click="newOpen = true" class="bg-gray-900 text-white text-sm font-semibold px-4 py-2.5 rounded-lg">+ Novo agendamento</button>
                <button type="button" onclick="navigator.clipboard.writeText('{{ route('booking.index') }}'); this.innerText='Link copiado!'" class="border border-gray-300 bg-white text-sm font-medium px-4 py-2.5 rounded-lg">Copiar link de agendamento</button>
            </div>
        </div>

        {{-- Indicadores --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
            <div class="bg-white rounded-2xl border border-gray-200 p-4">
                <div class="text-xs text-gray-500">Agendamentos hoje</div>
                <div class="text-2xl font-bold mt-1">{{ $kpis['today'] }}</div>
                <div class="text-xs text-gray-500 mt-1">{{ $money($kpis['today_revenue']) }} previstos</div>
            </div>
            <div class="bg-white rounded-2xl border border-gray-200 p-4">
                <div class="text-xs text-gray-500">Faturamento do mês</div>
                <div class="text-2xl font-bold mt-1">{{ $money($kpis['revenue_month']) }}</div>
                @if (! is_null($kpis['revenue_growth']))
                    <div class="text-xs mt-1 {{ $kpis['revenue_growth'] >= 0 ? 'text-emerald-600' : 'text-red-600' }}">
                        {{ $kpis['revenue_growth'] >= 0 ? '▲' : '▼' }} {{ number_format(abs($kpis['revenue_growth']), 0) }}% vs. mesmo período do mês passado
                    </div>
                @else
                    <div class="text-xs text-gray-400 mt-1">Recebido no mês</div>
                @endif
            </div>
            <div class="bg-white rounded-2xl border border-gray-200 p-4">
                <div class="text-xs text-gray-500">Ticket médio</div>
                <div class="text-2xl font-bold mt-1">{{ $money($kpis['ticket']) }}</div>
                <div class="text-xs text-gray-500 mt-1">{{ $kpis['new_customers'] }} cliente(s) novo(s) no mês</div>
            </div>
            <div class="bg-white rounded-2xl border border-gray-200 p-4">
                <div class="text-xs text-gray-500">Faltas no mês</div>
                <div class="text-2xl font-bold mt-1">{{ number_format($kpis['no_show_rate'], 0) }}%</div>
                <div class="text-xs mt-1 {{ $kpis['pending_payment'] ? 'text-amber-600' : 'text-gray-400' }}">{{ $kpis['pending_payment'] }} aguardando pagamento</div>
            </div>
        </div>

        <div class="grid lg:grid-cols-3 gap-6 items-start">
            {{-- Agenda --}}
            <section class="lg:col-span-2 bg-white rounded-2xl border border-gray-200">
                <div class="p-4 border-b border-gray-100 flex flex-wrap items-center gap-2 justify-between">
                    <div class="flex items-center gap-1">
                        <a href="{{ route('dashboard', ['data' => $date->copy()->subDay()->format('Y-m-d'), 'profissional' => $hairdresserId]) }}" class="w-9 h-9 rounded-lg border border-gray-200 flex items-center justify-center" aria-label="Dia anterior">‹</a>
                        <div class="px-3 text-center min-w-[140px]">
                            <div class="font-semibold">{{ $isToday ? 'Hoje' : ucfirst($date->translatedFormat('l')) }}</div>
                            <div class="text-xs text-gray-500">{{ $date->format('d/m/Y') }}</div>
                        </div>
                        <a href="{{ route('dashboard', ['data' => $date->copy()->addDay()->format('Y-m-d'), 'profissional' => $hairdresserId]) }}" class="w-9 h-9 rounded-lg border border-gray-200 flex items-center justify-center" aria-label="Próximo dia">›</a>
                        @unless ($isToday)
                            <a href="{{ route('dashboard') }}" class="text-xs text-gray-600 underline ml-2">Hoje</a>
                        @endunless
                    </div>
                    <form method="GET" class="flex gap-2">
                        <input type="date" name="data" value="{{ $date->format('Y-m-d') }}" onchange="this.form.submit()" class="text-sm rounded-lg border-gray-200 py-1.5">
                        <select name="profissional" onchange="this.form.submit()" class="text-sm rounded-lg border-gray-200 py-1.5">
                            <option value="">Todos</option>
                            @foreach ($hairdressers as $h)
                                <option value="{{ $h->id }}" @selected($hairdresserId === $h->id)>{{ $h->name }}</option>
                            @endforeach
                        </select>
                    </form>
                </div>

                <ul class="divide-y divide-gray-100">
                    @forelse ($appointments as $a)
                        <li class="p-4 flex gap-4 {{ in_array($a->status, ['cancelled', 'no_show']) ? 'opacity-60' : '' }}">
                            <div class="w-14 shrink-0 text-center">
                                <div class="text-lg font-bold">{{ $a->scheduled_at->format('H:i') }}</div>
                                <div class="text-[11px] text-gray-400">{{ $a->service->duration_minutes ?? '' }} min</div>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="font-semibold truncate">{{ $a->client_name }}</span>
                                    <span class="text-[11px] px-2 py-0.5 rounded-full ring-1 {{ $badge[$a->status] ?? 'bg-gray-50 text-gray-600 ring-gray-200' }}">{{ $a->statusLabel() }}</span>
                                    @if ($a->payment_status === 'paid')
                                        <span class="text-[11px] px-2 py-0.5 rounded-full bg-emerald-600 text-white">Pago</span>
                                    @endif
                                </div>
                                <div class="text-sm text-gray-600 mt-0.5">
                                    {{ $a->service->name ?? '—' }} · {{ $a->hairdresser->name ?? '—' }} · {{ $money($a->total_price) }} · {{ $a->paymentLabel() }}
                                </div>
                                @if ($a->notes)
                                    <div class="text-xs text-gray-500 mt-1 italic">“{{ $a->notes }}”</div>
                                @endif
                                @if ($a->customer && $a->customer->loyalty_points > 0)
                                    <div class="text-xs text-gold-700 mt-1">★ {{ $a->customer->loyalty_points }} ponto(s) de fidelidade</div>
                                @endif

                                <div class="flex flex-wrap gap-2 mt-3">
                                    @if (in_array($a->status, ['scheduled', 'pending_payment']))
                                        <form method="POST" action="{{ route('appointments.status', $a) }}">
                                            @csrf @method('PATCH')
                                            <button name="action" value="complete" class="text-xs font-semibold bg-emerald-600 text-white px-3 py-1.5 rounded-lg">✓ Concluir{{ $a->payment_status !== 'paid' ? ' e receber' : '' }}</button>
                                        </form>
                                        @if ($a->payment_status !== 'paid')
                                            <form method="POST" action="{{ route('appointments.status', $a) }}">
                                                @csrf @method('PATCH')
                                                <button name="action" value="paid" class="text-xs font-medium border border-gray-300 px-3 py-1.5 rounded-lg">Marcar pago</button>
                                            </form>
                                        @endif
                                        <form method="POST" action="{{ route('appointments.status', $a) }}" onsubmit="return confirm('Marcar que o cliente não compareceu?')">
                                            @csrf @method('PATCH')
                                            <button name="action" value="no_show" class="text-xs font-medium border border-gray-300 px-3 py-1.5 rounded-lg">Faltou</button>
                                        </form>
                                        <form method="POST" action="{{ route('appointments.status', $a) }}" onsubmit="return confirm('Cancelar este agendamento?')">
                                            @csrf @method('PATCH')
                                            <button name="action" value="cancel" class="text-xs font-medium text-red-600 px-2 py-1.5">Cancelar</button>
                                        </form>
                                    @elseif (in_array($a->status, ['cancelled', 'no_show']))
                                        <form method="POST" action="{{ route('appointments.status', $a) }}">
                                            @csrf @method('PATCH')
                                            <button name="action" value="reopen" class="text-xs font-medium border border-gray-300 px-3 py-1.5 rounded-lg">Reabrir</button>
                                        </form>
                                    @endif
                                    @if ($wa = $a->whatsappLink('Olá '.\Illuminate\Support\Str::before($a->client_name, ' ').'! Passando para confirmar seu horário de '.($a->service->name ?? '').' '.($a->scheduled_at->isToday() ? 'hoje' : 'em '.$a->scheduled_at->format('d/m')).' às '.$a->scheduled_at->format('H:i').' na '.$shop['shop_name'].'. Até lá!'))
                                        <a href="{{ $wa }}" target="_blank" rel="noopener" class="text-xs font-medium text-emerald-700 border border-emerald-200 bg-emerald-50 px-3 py-1.5 rounded-lg">WhatsApp</a>
                                    @endif
                                </div>
                            </div>
                        </li>
                    @empty
                        <li class="p-10 text-center text-gray-500">
                            <div class="text-4xl mb-2">📅</div>
                            Nenhum agendamento {{ $isToday ? 'hoje' : 'neste dia' }}.
                            <div class="mt-3"><button type="button" @click="newOpen = true" class="text-sm font-semibold underline">Criar agendamento</button></div>
                        </li>
                    @endforelse
                </ul>
            </section>

            {{-- Lateral --}}
            <aside class="space-y-6">
                <div class="bg-white rounded-2xl border border-gray-200 p-5">
                    <h2 class="font-semibold">Clientes para chamar de volta</h2>
                    <p class="text-xs text-gray-500 mt-1">Já vieram, mas não aparecem há mais de 30 dias.</p>
                    <ul class="mt-4 space-y-3">
                        @forelse ($inactiveCustomers as $c)
                            @php
                                $phone = preg_replace('/\D/', '', $c->phone);
                                $phone = strlen($phone) <= 11 ? '55'.$phone : $phone;
                                $msg = 'Olá '.\Illuminate\Support\Str::before($c->name, ' ').'! Sentimos sua falta na '.$shop['shop_name'].'. Que tal renovar o visual? Agende em 1 minuto: '.route('booking.index');
                            @endphp
                            <li class="flex items-center justify-between gap-3">
                                <div class="min-w-0">
                                    <div class="text-sm font-medium truncate">{{ $c->name }}</div>
                                    <div class="text-xs text-gray-500">Última vez: {{ \Carbon\Carbon::parse($c->appointments_max_scheduled_at)->diffForHumans() }}</div>
                                </div>
                                <a href="https://wa.me/{{ $phone }}?text={{ rawurlencode($msg) }}" target="_blank" rel="noopener" class="text-xs font-semibold text-emerald-700 border border-emerald-200 bg-emerald-50 px-3 py-1.5 rounded-lg shrink-0">Chamar</a>
                            </li>
                        @empty
                            <li class="text-sm text-gray-400">Nenhum cliente sumido por enquanto. 👏</li>
                        @endforelse
                    </ul>
                </div>

                <div class="bg-gray-950 text-white rounded-2xl p-5">
                    <h2 class="font-semibold">Divulgue sua agenda online</h2>
                    <p class="text-sm text-gray-400 mt-1">Coloque o link na bio do Instagram e no status do WhatsApp.</p>
                    <div class="mt-3 bg-white/10 rounded-lg px-3 py-2 text-xs font-mono break-all">{{ route('booking.index') }}</div>
                </div>
            </aside>
        </div>

        {{-- Modal: novo agendamento --}}
        <div x-show="newOpen" x-cloak class="fixed inset-0 z-50 flex items-end sm:items-center justify-center bg-black/50 p-0 sm:p-4" @keydown.escape.window="newOpen = false">
            <div @click.outside="newOpen = false" class="bg-white w-full sm:max-w-lg rounded-t-2xl sm:rounded-2xl p-6 max-h-[90vh] overflow-y-auto"
                 x-data="manualBooking()">
                <div class="flex justify-between items-center mb-5">
                    <h2 class="text-lg font-bold">Novo agendamento</h2>
                    <button type="button" @click="newOpen = false" class="text-gray-400 text-xl" aria-label="Fechar">✕</button>
                </div>
                <form method="POST" action="{{ route('appointments.store') }}" class="space-y-4">
                    @csrf
                    <div class="grid grid-cols-2 gap-3">
                        <div class="col-span-2 sm:col-span-1">
                            <label class="text-sm font-medium">Cliente</label>
                            <input name="client_name" value="{{ old('client_name') }}" required class="mt-1 w-full rounded-lg border-gray-300">
                        </div>
                        <div class="col-span-2 sm:col-span-1">
                            <label class="text-sm font-medium">WhatsApp</label>
                            <input name="client_phone" value="{{ old('client_phone') }}" inputmode="tel" class="mt-1 w-full rounded-lg border-gray-300">
                        </div>
                        <div class="col-span-2">
                            <label class="text-sm font-medium">Serviço</label>
                            <select name="service_id" x-model="service" @change="load()" required class="mt-1 w-full rounded-lg border-gray-300">
                                <option value="">Selecione…</option>
                                @foreach ($services as $s)
                                    <option value="{{ $s->id }}">{{ $s->name }} — {{ $money($s->price) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-span-2 sm:col-span-1">
                            <label class="text-sm font-medium">Profissional</label>
                            <select name="hairdresser_id" x-model="hairdresser" @change="load()" required class="mt-1 w-full rounded-lg border-gray-300">
                                <option value="">Selecione…</option>
                                @foreach ($hairdressers as $h)
                                    <option value="{{ $h->id }}">{{ $h->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-span-2 sm:col-span-1">
                            <label class="text-sm font-medium">Data</label>
                            <input type="date" x-model="date" @change="load()" class="mt-1 w-full rounded-lg border-gray-300">
                        </div>
                    </div>

                    <div>
                        <label class="text-sm font-medium">Horário</label>
                        <div class="mt-2 grid grid-cols-5 gap-2" x-show="slots.length">
                            <template x-for="s in slots" :key="s.time">
                                <button type="button" @click="time = s.time" :disabled="!s.available"
                                        :class="time === s.time ? 'bg-gray-900 text-white' : (s.available ? 'bg-white border-gray-300' : 'bg-gray-50 text-gray-300 line-through')"
                                        class="border rounded-lg py-2 text-sm" x-text="s.time"></button>
                            </template>
                        </div>
                        <p x-show="!slots.length" class="text-xs text-gray-500 mt-1">Escolha serviço, profissional e data para ver os horários. Ou digite manualmente:</p>
                        <input type="time" x-model="time" class="mt-2 rounded-lg border-gray-300 text-sm">
                        <input type="hidden" name="scheduled_at" :value="date && time ? date + ' ' + time : ''">
                    </div>

                    <div>
                        <label class="text-sm font-medium">Observação</label>
                        <input name="notes" value="{{ old('notes') }}" class="mt-1 w-full rounded-lg border-gray-300">
                    </div>

                    <button :disabled="!date || !time" class="w-full bg-gray-900 text-white font-semibold py-3 rounded-lg disabled:opacity-40">Salvar agendamento</button>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function manualBooking() {
            return {
                service: '', hairdresser: '', date: @js($date->format('Y-m-d')), time: '', slots: [],
                async load() {
                    this.slots = [];
                    if (!this.service || !this.hairdresser || !this.date) return;
                    const p = new URLSearchParams({ service_id: this.service, hairdresser_id: this.hairdresser, date: this.date });
                    const r = await fetch(@js(route('appointments.slots')) + '?' + p, { headers: { Accept: 'application/json' } });
                    if (r.ok) this.slots = await r.json();
                },
            };
        }
    </script>
    @endpush
</x-app-layout>
