<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl">Configurações da barbearia</h2>
    </x-slot>

    @php
        $s = $settings;
        $field = 'mt-1 w-full rounded-lg border-gray-300 text-sm';
        $closed = array_filter(explode(',', (string) $s['closed_days']), 'strlen');
        $days = ['Domingo', 'Segunda', 'Terça', 'Quarta', 'Quinta', 'Sexta', 'Sábado'];
    @endphp

    <form method="POST" action="{{ route('settings.update') }}" class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-6">
        @csrf @method('PUT')

        <section class="bg-white rounded-2xl border border-gray-200 p-6">
            <h3 class="font-semibold">Site</h3>
            <p class="text-sm text-gray-500">Textos que aparecem para os clientes.</p>
            <div class="grid sm:grid-cols-2 gap-4 mt-4">
                <div><label class="text-sm font-medium">Nome da barbearia</label><input name="shop_name" required value="{{ old('shop_name', $s['shop_name']) }}" class="{{ $field }}"></div>
                <div><label class="text-sm font-medium">Frase de destaque</label><input name="slogan" value="{{ old('slogan', $s['slogan']) }}" class="{{ $field }}" placeholder="Estilo & Tradição"></div>
                <div class="sm:col-span-2"><label class="text-sm font-medium">Sobre (texto principal do site)</label><textarea name="about" rows="3" class="{{ $field }}">{{ old('about', $s['about']) }}</textarea></div>
            </div>
        </section>

        <section class="bg-white rounded-2xl border border-gray-200 p-6">
            <h3 class="font-semibold">Contato e endereço</h3>
            <div class="grid sm:grid-cols-2 gap-4 mt-4">
                <div><label class="text-sm font-medium">WhatsApp (com DDD)</label><input name="whatsapp" value="{{ old('whatsapp', $s['whatsapp']) }}" class="{{ $field }}" placeholder="11999999999" inputmode="tel"></div>
                <div><label class="text-sm font-medium">Instagram</label><input name="instagram" value="{{ old('instagram', $s['instagram']) }}" class="{{ $field }}" placeholder="barbeariaelite"></div>
                <div><label class="text-sm font-medium">Endereço</label><input name="address" value="{{ old('address', $s['address']) }}" class="{{ $field }}" placeholder="Rua Exemplo, 123 - Centro"></div>
                <div><label class="text-sm font-medium">Cidade / UF</label><input name="city" value="{{ old('city', $s['city']) }}" class="{{ $field }}" placeholder="Jundiaí - SP"></div>
            </div>
            <p class="text-xs text-gray-500 mt-3">Com o endereço preenchido, o site mostra mapa e botão "Como chegar". Com o WhatsApp, aparece o botão flutuante.</p>
        </section>

        <section class="bg-white rounded-2xl border border-gray-200 p-6">
            <h3 class="font-semibold">Agenda</h3>
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mt-4">
                <div><label class="text-sm font-medium">Abre</label><input type="time" name="open_time" required value="{{ old('open_time', $s['open_time']) }}" class="{{ $field }}"></div>
                <div><label class="text-sm font-medium">Fecha</label><input type="time" name="close_time" required value="{{ old('close_time', $s['close_time']) }}" class="{{ $field }}"></div>
                <div><label class="text-sm font-medium">Almoço início</label><input type="time" name="lunch_start" value="{{ old('lunch_start', $s['lunch_start']) }}" class="{{ $field }}"></div>
                <div><label class="text-sm font-medium">Almoço fim</label><input type="time" name="lunch_end" value="{{ old('lunch_end', $s['lunch_end']) }}" class="{{ $field }}"></div>
                <div><label class="text-sm font-medium">Intervalo (min)</label><input type="number" name="slot_interval" min="5" max="120" required value="{{ old('slot_interval', $s['slot_interval']) }}" class="{{ $field }}"></div>
                <div><label class="text-sm font-medium">Agendar até (dias)</label><input type="number" name="booking_days_ahead" min="1" max="120" required value="{{ old('booking_days_ahead', $s['booking_days_ahead']) }}" class="{{ $field }}"></div>
                <div><label class="text-sm font-medium">Antecedência mín. (min)</label><input type="number" name="min_notice_minutes" min="0" required value="{{ old('min_notice_minutes', $s['min_notice_minutes']) }}" class="{{ $field }}"></div>
                <div><label class="text-sm font-medium">Cancelar até (h antes)</label><input type="number" name="cancel_notice_hours" min="0" max="72" required value="{{ old('cancel_notice_hours', $s['cancel_notice_hours']) }}" class="{{ $field }}"></div>
            </div>
            <div class="mt-5">
                <div class="text-sm font-medium mb-2">Dias fechados</div>
                <div class="flex flex-wrap gap-2">
                    @foreach ($days as $i => $label)
                        <label class="flex items-center gap-2 border border-gray-200 rounded-lg px-3 py-2 text-sm">
                            <input type="checkbox" name="closed_days[]" value="{{ $i }}" @checked(in_array((string) $i, $closed, true)) class="rounded"> {{ $label }}
                        </label>
                    @endforeach
                </div>
            </div>
        </section>

        <section class="bg-white rounded-2xl border border-gray-200 p-6">
            <h3 class="font-semibold">Pagamento e fidelidade</h3>
            <div class="grid sm:grid-cols-2 gap-4 mt-4">
                <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="allow_pay_later" value="1" @checked($s['allow_pay_later']) class="rounded"> Permitir "pagar na barbearia"</label>
                <div><label class="text-sm font-medium">Fidelidade: 1 cortesia a cada</label><input type="number" name="loyalty_target" min="2" max="50" required value="{{ old('loyalty_target', $s['loyalty_target']) }}" class="{{ $field }}"></div>
            </div>
            <div class="mt-5 grid sm:grid-cols-2 gap-3 text-sm">
                <div class="rounded-lg px-4 py-3 {{ $mpConfigured ? 'bg-emerald-50 text-emerald-800' : 'bg-amber-50 text-amber-800' }}">
                    Mercado Pago (PIX/cartão): <strong>{{ $mpConfigured ? 'conectado' : 'não configurado' }}</strong>
                    @unless ($mpConfigured)<div class="text-xs mt-1">Defina MERCADO_PAGO_PUBLIC_KEY e MERCADO_PAGO_ACCESS_TOKEN no Render.</div>@endunless
                </div>
                <div class="rounded-lg px-4 py-3 {{ $twilioConfigured ? 'bg-emerald-50 text-emerald-800' : 'bg-gray-50 text-gray-600' }}">
                    Lembretes por SMS/WhatsApp (Twilio): <strong>{{ $twilioConfigured ? 'conectado' : 'não configurado' }}</strong>
                </div>
            </div>
        </section>

        <div class="flex justify-end">
            <button class="bg-gray-900 text-white font-semibold px-8 py-3 rounded-lg">Salvar configurações</button>
        </div>
    </form>
</x-app-layout>
