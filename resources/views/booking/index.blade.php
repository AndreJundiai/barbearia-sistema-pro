@extends('layouts.site')

@section('title', 'Agendar horário | '.$shop['shop_name'])
@section('description', 'Agende seu corte ou barba online na '.$shop['shop_name'].'. Escolha o serviço, o profissional e o horário em menos de 1 minuto.')
@section('hide_footer', true)
@section('hide_whatsapp', true)

@push('head')
    @if ($paymentsEnabled && $mpPublicKey)
        <script src="https://sdk.mercadopago.com/js/v2"></script>
    @endif
@endpush

@section('content')
<div class="max-w-5xl mx-auto px-4 sm:px-6 pt-24 pb-40 lg:pb-16" x-data="booking()">

    <div class="mb-8">
        <h1 class="font-display text-3xl sm:text-4xl font-bold text-white">Agende seu horário</h1>
        <p class="text-neutral-400 mt-2">Rápido, sem cadastro e com confirmação na hora.</p>
    </div>

    {{-- Progresso --}}
    <ol class="grid grid-cols-4 gap-2 mb-8 text-[11px] sm:text-xs font-medium">
        <template x-for="(label, i) in ['Serviço', 'Profissional', 'Horário', 'Confirmar']" :key="i">
            <li>
                <button type="button" @click="goTo(i + 1)" :disabled="!canGoTo(i + 1)" class="w-full text-left disabled:cursor-not-allowed">
                    <div class="h-1 rounded-full mb-2 transition-colors" :class="step > i ? 'bg-gold' : 'bg-white/10'"></div>
                    <span :class="step === i + 1 ? 'text-gold' : (step > i + 1 ? 'text-neutral-300' : 'text-neutral-600')" x-text="label"></span>
                </button>
            </li>
        </template>
    </ol>

    <div x-show="error" x-cloak x-transition class="mb-6 rounded-xl border border-red-500/40 bg-red-500/10 px-4 py-3 text-sm text-red-200 flex items-start gap-3" role="alert">
        <svg class="w-5 h-5 shrink-0 text-red-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
        <span class="flex-1" x-text="error"></span>
        <button @click="error = null" class="text-red-300 hover:text-white" aria-label="Fechar">✕</button>
    </div>

    <div class="grid lg:grid-cols-[minmax(0,1fr)_320px] gap-8 items-start">
        <div class="min-w-0">
            {{-- 1. Serviço --}}
            <section x-show="step === 1">
                <h2 class="text-lg font-semibold text-white mb-4">Qual serviço você quer?</h2>
                <div class="grid sm:grid-cols-2 gap-3">
                    <template x-for="s in services" :key="s.id">
                        <button type="button" @click="pickService(s)"
                                :class="service?.id === s.id ? 'border-gold bg-gold/10' : 'border-white/10 hover:border-white/30'"
                                class="relative text-left p-5 rounded-2xl border bg-ink-800 transition-colors">
                            <span x-show="s.featured" class="absolute -top-2.5 right-4 bg-gold text-ink text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 rounded-full">Mais pedido</span>
                            <div class="flex justify-between gap-3">
                                <span class="font-semibold text-white" x-text="s.name"></span>
                                <span class="text-gold font-bold shrink-0" x-text="money(s.price)"></span>
                            </div>
                            <p class="text-sm text-neutral-400 mt-1.5" x-show="s.description" x-text="s.description"></p>
                            <p class="text-xs text-neutral-500 mt-3" x-text="s.duration_minutes + ' min'"></p>
                        </button>
                    </template>
                </div>
                <p x-show="services.length === 0" class="text-neutral-500">Nenhum serviço disponível no momento.</p>
            </section>

            {{-- 2. Profissional --}}
            <section x-show="step === 2" x-cloak>
                <h2 class="text-lg font-semibold text-white mb-4">Com quem?</h2>
                <div class="grid sm:grid-cols-2 gap-3">
                    <template x-for="h in hairdressers" :key="h.id">
                        <button type="button" @click="pickHairdresser(h)"
                                :class="hairdresser?.id === h.id ? 'border-gold bg-gold/10' : 'border-white/10 hover:border-white/30'"
                                class="text-left p-5 rounded-2xl border bg-ink-800 transition-colors flex items-center gap-4">
                            <div class="w-12 h-12 rounded-full bg-gradient-to-br from-gold to-gold-700 text-ink font-display font-bold flex items-center justify-center shrink-0" x-text="initials(h.name)"></div>
                            <div class="min-w-0">
                                <div class="font-semibold text-white" x-text="h.name"></div>
                                <div class="text-sm text-neutral-400 truncate" x-text="h.specialty || 'Barbeiro'"></div>
                            </div>
                        </button>
                    </template>
                </div>
            </section>

            {{-- 3. Data e hora --}}
            <section x-show="step === 3" x-cloak>
                <h2 class="text-lg font-semibold text-white mb-4">Quando?</h2>
                <div class="-mx-4 px-4 sm:mx-0 sm:px-0 overflow-x-auto pb-2">
                    <div class="flex gap-2 w-max">
                        <template x-for="d in days" :key="d.date">
                            <button type="button" @click="pickDate(d.date)"
                                    :class="date === d.date ? 'bg-gold text-ink border-gold' : 'bg-ink-800 border-white/10 text-neutral-300 hover:border-white/30'"
                                    class="w-16 shrink-0 py-3 rounded-xl border text-center transition-colors">
                                <div class="text-[11px] font-medium" x-text="d.weekday"></div>
                                <div class="text-xl font-bold leading-tight" x-text="d.day"></div>
                                <div class="text-[11px] opacity-70" x-text="d.month"></div>
                            </button>
                        </template>
                    </div>
                </div>

                <div class="mt-6">
                    <div x-show="loadingSlots" class="grid grid-cols-4 sm:grid-cols-6 gap-2">
                        <template x-for="i in 12"><div class="h-11 rounded-lg bg-white/5 animate-pulse"></div></template>
                    </div>
                    <div x-show="!loadingSlots && date">
                        <div x-show="slots.filter(s => s.available).length" class="grid grid-cols-4 sm:grid-cols-6 gap-2">
                            <template x-for="s in slots" :key="s.time">
                                <button type="button" @click="time = s.time; step = 4; scrollTop()" :disabled="!s.available"
                                        :class="time === s.time ? 'bg-gold text-ink border-gold' : (s.available ? 'bg-ink-800 border-white/10 text-white hover:border-gold' : 'border-transparent text-neutral-700 line-through')"
                                        class="h-11 rounded-lg border text-sm font-semibold transition-colors disabled:cursor-not-allowed" x-text="s.time"></button>
                            </template>
                        </div>
                        <div x-show="!slots.filter(s => s.available).length" class="rounded-xl border border-dashed border-white/15 p-6 text-center text-neutral-400 text-sm">
                            Sem horários livres neste dia com <span class="text-white" x-text="hairdresser?.name"></span>.
                            <button type="button" class="text-gold underline ml-1" @click="nextDayWithSlots()">Ver próximo dia</button>
                        </div>
                    </div>
                </div>
            </section>

            {{-- 4. Dados e pagamento --}}
            <section x-show="step === 4" x-cloak>
                <h2 class="text-lg font-semibold text-white mb-4">Seus dados</h2>
                <div class="space-y-4">
                    <div>
                        <label for="name" class="block text-sm text-neutral-400 mb-1.5">Nome</label>
                        <input id="name" type="text" x-model.trim="client.name" autocomplete="name" placeholder="Como podemos te chamar?"
                               class="w-full bg-ink-800 border border-white/10 rounded-xl px-4 py-3.5 text-white placeholder-neutral-600 focus:border-gold focus:ring-0 outline-none">
                    </div>
                    <div>
                        <label for="phone" class="block text-sm text-neutral-400 mb-1.5">WhatsApp</label>
                        <input id="phone" type="tel" inputmode="tel" x-model="client.phone" @input="client.phone = maskPhone($event.target.value)" autocomplete="tel" placeholder="(11) 99999-9999"
                               class="w-full bg-ink-800 border border-white/10 rounded-xl px-4 py-3.5 text-white placeholder-neutral-600 focus:border-gold focus:ring-0 outline-none">
                        <p class="text-xs text-neutral-500 mt-1.5">Para confirmação, lembrete e seus pontos de fidelidade.</p>
                    </div>
                    <div>
                        <label for="email" class="block text-sm text-neutral-400 mb-1.5">E-mail <span class="text-neutral-600">(opcional)</span></label>
                        <input id="email" type="email" x-model.trim="client.email" autocomplete="email" placeholder="voce@email.com"
                               class="w-full bg-ink-800 border border-white/10 rounded-xl px-4 py-3.5 text-white placeholder-neutral-600 focus:border-gold focus:ring-0 outline-none">
                    </div>
                    <div>
                        <label for="notes" class="block text-sm text-neutral-400 mb-1.5">Observação <span class="text-neutral-600">(opcional)</span></label>
                        <input id="notes" type="text" x-model.trim="client.notes" maxlength="500" placeholder="Ex.: quero degradê baixo"
                               class="w-full bg-ink-800 border border-white/10 rounded-xl px-4 py-3.5 text-white placeholder-neutral-600 focus:border-gold focus:ring-0 outline-none">
                    </div>
                </div>

                <h2 class="text-lg font-semibold text-white mt-10 mb-4">Pagamento</h2>
                <div class="grid sm:grid-cols-3 gap-3">
                    @if ($paymentsEnabled)
                        <label :class="payment === 'pix' ? 'border-gold bg-gold/10' : 'border-white/10'" class="cursor-pointer p-4 rounded-xl border bg-ink-800 transition-colors">
                            <input type="radio" value="pix" x-model="payment" class="sr-only">
                            <div class="font-semibold text-white">PIX</div>
                            <div class="text-xs text-neutral-400 mt-1">Confirmação imediata</div>
                        </label>
                        <label :class="payment === 'credit_card' ? 'border-gold bg-gold/10' : 'border-white/10'" class="cursor-pointer p-4 rounded-xl border bg-ink-800 transition-colors">
                            <input type="radio" value="credit_card" x-model="payment" class="sr-only">
                            <div class="font-semibold text-white">Cartão de crédito</div>
                            <div class="text-xs text-neutral-400 mt-1">À vista</div>
                        </label>
                    @endif
                    @if ($allowPayLater || ! $paymentsEnabled)
                        <label :class="payment === 'pay_later' ? 'border-gold bg-gold/10' : 'border-white/10'" class="cursor-pointer p-4 rounded-xl border bg-ink-800 transition-colors">
                            <input type="radio" value="pay_later" x-model="payment" class="sr-only">
                            <div class="font-semibold text-white">Pagar na barbearia</div>
                            <div class="text-xs text-neutral-400 mt-1">Dinheiro, PIX ou cartão no local</div>
                        </label>
                    @endif
                </div>

                @if ($paymentsEnabled)
                <div x-show="payment === 'credit_card'" x-cloak x-transition class="mt-5 grid grid-cols-2 gap-3">
                    <div class="col-span-2">
                        <label class="block text-xs text-neutral-400 mb-1.5">Número do cartão</label>
                        <input type="text" inputmode="numeric" autocomplete="cc-number" x-model="card.number" @input="card.number = maskCard($event.target.value)" placeholder="0000 0000 0000 0000"
                               class="w-full bg-ink-800 border border-white/10 rounded-xl px-4 py-3 text-white font-mono focus:border-gold focus:ring-0 outline-none">
                    </div>
                    <div class="col-span-2">
                        <label class="block text-xs text-neutral-400 mb-1.5">Nome impresso no cartão</label>
                        <input type="text" autocomplete="cc-name" x-model="card.name" placeholder="JOÃO A SILVA"
                               class="w-full bg-ink-800 border border-white/10 rounded-xl px-4 py-3 text-white uppercase focus:border-gold focus:ring-0 outline-none">
                    </div>
                    <div>
                        <label class="block text-xs text-neutral-400 mb-1.5">Validade</label>
                        <input type="text" inputmode="numeric" autocomplete="cc-exp" x-model="card.expiry" @input="card.expiry = maskExpiry($event.target.value)" placeholder="MM/AA"
                               class="w-full bg-ink-800 border border-white/10 rounded-xl px-4 py-3 text-white font-mono focus:border-gold focus:ring-0 outline-none">
                    </div>
                    <div>
                        <label class="block text-xs text-neutral-400 mb-1.5">CVV</label>
                        <input type="text" inputmode="numeric" autocomplete="cc-csc" x-model="card.cvv" @input="card.cvv = $event.target.value.replace(/\D/g, '').slice(0, 4)" placeholder="123"
                               class="w-full bg-ink-800 border border-white/10 rounded-xl px-4 py-3 text-white font-mono focus:border-gold focus:ring-0 outline-none">
                    </div>
                    <div class="col-span-2">
                        <label class="block text-xs text-neutral-400 mb-1.5">CPF do titular</label>
                        <input type="text" inputmode="numeric" x-model="card.cpf" @input="card.cpf = maskCpf($event.target.value)" placeholder="000.000.000-00"
                               class="w-full bg-ink-800 border border-white/10 rounded-xl px-4 py-3 text-white font-mono focus:border-gold focus:ring-0 outline-none">
                    </div>
                    <p class="col-span-2 text-xs text-neutral-500 flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/></svg>
                        Pagamento processado com segurança pelo Mercado Pago. Não armazenamos dados do cartão.
                    </p>
                </div>
                <p x-show="payment === 'pix'" x-cloak class="mt-4 text-sm text-neutral-400">Após confirmar, mostraremos o QR Code e o código copia-e-cola. O horário é garantido assim que o pagamento cair.</p>
                @endif

                <p class="text-xs text-neutral-500 mt-6">Ao confirmar, você concorda com a nossa <a href="{{ route('privacy') }}" target="_blank" class="underline">política de privacidade</a>.</p>
            </section>

            <div class="hidden lg:flex mt-8 items-center gap-4">
                <button type="button" x-show="step > 1" @click="back()" class="text-neutral-400 hover:text-white px-2 py-3">← Voltar</button>
                <button type="button" x-show="step === 4" @click="submit()" :disabled="sending || !formValid()"
                        class="ml-auto bg-gold text-ink font-semibold px-8 py-4 rounded-full disabled:opacity-40 hover:bg-gold-300 transition-colors">
                    <span x-show="!sending" x-text="submitLabel()"></span>
                    <span x-show="sending">Processando…</span>
                </button>
            </div>
        </div>

        {{-- Resumo (desktop) --}}
        <aside class="hidden lg:block sticky top-24 bg-ink-800 border border-white/10 rounded-2xl p-6">
            <div class="text-xs text-neutral-500 uppercase tracking-widest font-semibold mb-4">Resumo</div>
            <dl class="space-y-3 text-sm">
                <div class="flex justify-between gap-3"><dt class="text-neutral-500">Serviço</dt><dd class="text-white text-right" x-text="service?.name || '—'"></dd></div>
                <div class="flex justify-between gap-3"><dt class="text-neutral-500">Profissional</dt><dd class="text-white text-right" x-text="hairdresser?.name || '—'"></dd></div>
                <div class="flex justify-between gap-3"><dt class="text-neutral-500">Data</dt><dd class="text-white text-right" x-text="dateLabel() || '—'"></dd></div>
                <div class="flex justify-between gap-3"><dt class="text-neutral-500">Horário</dt><dd class="text-white text-right" x-text="time || '—'"></dd></div>
            </dl>
            <div class="border-t border-white/10 mt-5 pt-5 flex justify-between items-baseline">
                <span class="text-neutral-400">Total</span>
                <span class="text-2xl font-bold text-gold" x-text="service ? money(service.price) : 'R$ 0,00'"></span>
            </div>
        </aside>
    </div>

    {{-- Barra inferior (celular) --}}
    <div class="lg:hidden fixed bottom-0 inset-x-0 z-30 bg-ink/95 backdrop-blur-xl border-t border-white/10 px-4 pt-3 pb-safe">
        <div class="flex items-center gap-3">
            <button type="button" x-show="step > 1" @click="back()" class="w-11 h-11 shrink-0 rounded-full border border-white/15 text-neutral-300 flex items-center justify-center" aria-label="Voltar">←</button>
            <div class="flex-1 min-w-0">
                <div class="text-sm text-white truncate" x-text="[service?.name, hairdresser?.name, time && (dateLabel() + ' ' + time)].filter(Boolean).join(' · ') || 'Escolha um serviço'"></div>
                <div class="text-gold font-bold" x-text="service ? money(service.price) : ''"></div>
            </div>
            <button type="button" x-show="step === 4" @click="submit()" :disabled="sending || !formValid()"
                    class="bg-gold text-ink font-semibold px-5 py-3 rounded-full disabled:opacity-40 shrink-0">
                <span x-show="!sending">Confirmar</span>
                <span x-show="sending">Aguarde…</span>
            </button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
@php
    $servicesJson = $services->map(fn ($s) => $s->only(['id', 'name', 'description', 'price', 'duration_minutes', 'featured']))->values();
    $hairdressersJson = $hairdressers->map(fn ($h) => $h->only(['id', 'name', 'specialty']))->values();
@endphp
<script>
function booking() {
    return {
        step: 1,
        services: @json($servicesJson),
        hairdressers: @json($hairdressersJson),
        days: @json($days),
        service: null,
        hairdresser: null,
        date: null,
        time: null,
        slots: [],
        loadingSlots: false,
        client: { name: '', phone: '', email: '', notes: '' },
        payment: @json($paymentsEnabled ? 'pix' : 'pay_later'),
        card: { number: '', name: '', expiry: '', cvv: '', cpf: '' },
        sending: false,
        error: null,
        mpPublicKey: @json($mpPublicKey),

        init() {
            // Lembra os dados do cliente para a próxima vez
            try {
                const saved = JSON.parse(localStorage.getItem('booking-client') || 'null');
                if (saved) this.client = { ...this.client, ...saved, notes: '' };
            } catch (e) {}

            const pre = @json($preselectedService);
            const preH = @json($preselectedHairdresser);
            const s = this.services.find(x => x.id === pre);
            const h = this.hairdressers.find(x => x.id === preH);
            if (s) { this.service = s; this.step = 2; }
            if (h) { this.hairdresser = h; if (s) this.toDateStep(); }
            if (!s && this.services.length === 1) this.pickService(this.services[0]);
        },

        pickService(s) { this.service = s; this.time = null; this.hairdresser ? this.toDateStep() : (this.step = 2); this.scrollTop(); },
        pickHairdresser(h) {
            this.hairdresser = h; this.time = null;
            this.toDateStep();
            this.scrollTop();
        },
        toDateStep() {
            this.step = 3;
            if (!this.date && this.days.length) this.date = this.days[0].date;
            this.loadSlots();
        },
        pickDate(d) { this.date = d; this.time = null; this.loadSlots(); },

        async loadSlots() {
            if (!this.date || !this.hairdresser || !this.service) return;
            this.loadingSlots = true;
            try {
                const params = new URLSearchParams({ date: this.date, hairdresser_id: this.hairdresser.id, service_id: this.service.id });
                const r = await fetch(`{{ route('booking.availability') }}?${params}`, { headers: { Accept: 'application/json' } });
                this.slots = r.ok ? await r.json() : [];
            } catch (e) {
                this.slots = [];
                this.error = 'Não foi possível carregar os horários. Verifique sua conexão.';
            } finally {
                this.loadingSlots = false;
            }
        },
        async nextDayWithSlots() {
            const idx = this.days.findIndex(d => d.date === this.date);
            for (let i = idx + 1; i < this.days.length; i++) {
                this.date = this.days[i].date;
                await this.loadSlots();
                if (this.slots.some(s => s.available)) return;
            }
        },

        canGoTo(n) {
            if (n === 1) return true;
            if (n === 2) return !!this.service;
            if (n === 3) return !!this.service && !!this.hairdresser;
            return !!this.service && !!this.hairdresser && !!this.time;
        },
        goTo(n) { if (this.canGoTo(n)) { this.step = n; if (n === 3) this.loadSlots(); } },
        back() { if (this.step > 1) this.step--; if (this.step === 3) this.loadSlots(); this.scrollTop(); },
        scrollTop() { window.scrollTo({ top: 0, behavior: 'smooth' }); },

        formValid() {
            const phone = this.client.phone.replace(/\D/g, '');
            if (this.client.name.length < 2 || phone.length < 10 || !this.time) return false;
            if (this.payment === 'credit_card') {
                return this.card.number.replace(/\D/g, '').length >= 13 && this.card.name.length > 2
                    && /^\d{2}\/\d{2}$/.test(this.card.expiry) && this.card.cvv.length >= 3
                    && this.card.cpf.replace(/\D/g, '').length === 11;
            }
            return true;
        },
        submitLabel() {
            return { pix: 'Gerar PIX e confirmar', credit_card: 'Pagar e confirmar', pay_later: 'Confirmar agendamento' }[this.payment];
        },

        async tokenizeCard() {
            const [mm, yy] = this.card.expiry.split('/');
            const number = this.card.number.replace(/\D/g, '');
            const cpf = this.card.cpf.replace(/\D/g, '');
            const r = await fetch(`https://api.mercadopago.com/v1/card_tokens?public_key=${encodeURIComponent(this.mpPublicKey)}`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    card_number: number,
                    cardholder: { name: this.card.name, identification: { type: 'CPF', number: cpf } },
                    expiration_month: parseInt(mm, 10),
                    expiration_year: 2000 + parseInt(yy, 10),
                    security_code: this.card.cvv,
                }),
            });
            const data = await r.json();
            if (!r.ok || !data.id) {
                throw new Error('Confira os dados do cartão. ' + (data.cause?.[0]?.description || ''));
            }
            let methodId = null;
            if (window.MercadoPago) {
                const mp = new MercadoPago(this.mpPublicKey, { locale: 'pt-BR' });
                const methods = await mp.getPaymentMethods({ bin: number.slice(0, 6) });
                methodId = methods?.results?.[0]?.id || null;
            }
            return { token: data.id, methodId, cpf };
        },

        async submit() {
            if (this.sending || !this.formValid()) return;
            this.sending = true;
            this.error = null;
            try {
                try { localStorage.setItem('booking-client', JSON.stringify({ name: this.client.name, phone: this.client.phone, email: this.client.email })); } catch (e) {}

                let card = {};
                if (this.payment === 'credit_card') card = await this.tokenizeCard();

                const r = await fetch(`{{ route('booking.process') }}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                    },
                    body: JSON.stringify({
                        client_name: this.client.name,
                        client_phone: this.client.phone,
                        email: this.client.email || null,
                        notes: this.client.notes || null,
                        service_id: this.service.id,
                        hairdresser_id: this.hairdresser.id,
                        scheduled_at: `${this.date} ${this.time}:00`,
                        payment_method: this.payment,
                        token: card.token || null,
                        payment_method_id: card.methodId || null,
                        card_cpf: card.cpf || null,
                    }),
                });

                if (r.status === 419) throw new Error('Sua sessão expirou. Atualize a página e tente novamente.');
                const result = await r.json();
                if (!result.success) {
                    this.error = result.message || 'Não foi possível concluir. Tente novamente.';
                    if (r.status === 409) { this.time = null; this.step = 3; this.loadSlots(); }
                    this.scrollTop();
                    return;
                }
                window.location.href = result.redirect;
            } catch (e) {
                this.error = e.message || 'Erro de conexão. Tente novamente.';
                this.scrollTop();
            } finally {
                this.sending = false;
            }
        },

        // Utilitários
        money(v) { return Number(v).toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' }); },
        initials(n) { return n.split(' ').slice(0, 2).map(p => p[0]).join('').toUpperCase(); },
        dateLabel() {
            const d = this.days.find(x => x.date === this.date);
            return d ? `${d.weekday}, ${d.day}/${this.date.slice(5, 7)}` : '';
        },
        maskPhone(v) {
            v = v.replace(/\D/g, '').slice(0, 11);
            if (v.length > 10) return v.replace(/(\d{2})(\d{5})(\d{0,4})/, '($1) $2-$3');
            if (v.length > 6) return v.replace(/(\d{2})(\d{4})(\d{0,4})/, '($1) $2-$3');
            if (v.length > 2) return v.replace(/(\d{2})(\d{0,5})/, '($1) $2');
            return v;
        },
        maskCard(v) { return v.replace(/\D/g, '').slice(0, 16).replace(/(\d{4})(?=\d)/g, '$1 '); },
        maskExpiry(v) { v = v.replace(/\D/g, '').slice(0, 4); return v.length > 2 ? v.slice(0, 2) + '/' + v.slice(2) : v; },
        maskCpf(v) {
            v = v.replace(/\D/g, '').slice(0, 11);
            return v.replace(/(\d{3})(\d)/, '$1.$2').replace(/(\d{3})(\d)/, '$1.$2').replace(/(\d{3})(\d{1,2})$/, '$1-$2');
        },
    };
}
</script>
@endpush
