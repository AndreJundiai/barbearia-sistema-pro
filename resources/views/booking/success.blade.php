@extends('layouts.site')

@section('title', 'Meu horário | '.$shop['shop_name'])
@push('head')<meta name="robots" content="noindex">@endpush

@section('content')
@php
    $a = $appointment;
    $awaitingPix = $a->status === 'pending_payment' && $a->payment_method === 'pix';
    $points = $a->customer?->loyalty_points ?? 0;
    $progress = $loyaltyTarget ? $points % $loyaltyTarget : 0;
    $cancelled = in_array($a->status, ['cancelled', 'no_show']);
@endphp
<div class="max-w-xl mx-auto px-4 sm:px-6 pt-24 pb-16" x-data="appointmentPage()">

    @if (session('status'))
        <div class="mb-6 rounded-xl border border-emerald-500/40 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-200">{{ session('status') }}</div>
    @endif
    @if (session('error'))
        <div class="mb-6 rounded-xl border border-red-500/40 bg-red-500/10 px-4 py-3 text-sm text-red-200">{{ session('error') }}</div>
    @endif

    <div class="text-center mb-8">
        @if ($cancelled)
            <div class="w-16 h-16 mx-auto rounded-full bg-red-500/15 text-red-400 flex items-center justify-center mb-5">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" d="M6 6l12 12M18 6L6 18"/></svg>
            </div>
            <h1 class="font-display text-3xl font-bold text-white">Agendamento cancelado</h1>
        @elseif ($awaitingPix)
            <div x-show="!paid" class="w-16 h-16 mx-auto rounded-full bg-gold/15 text-gold flex items-center justify-center mb-5">
                <svg class="w-8 h-8 animate-pulse" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path stroke-linecap="round" d="M12 7v5l3 2"/></svg>
            </div>
            <h1 x-show="!paid" class="font-display text-3xl font-bold text-white">Falta só o PIX</h1>
            <p x-show="!paid" class="text-neutral-400 mt-2">Pague para garantir seu horário. Esta página atualiza sozinha.</p>
            <h1 x-show="paid" x-cloak class="font-display text-3xl font-bold text-white">Pagamento confirmado! 🎉</h1>
        @else
            <div class="w-16 h-16 mx-auto rounded-full bg-emerald-500/15 text-emerald-400 flex items-center justify-center mb-5">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
            </div>
            <h1 class="font-display text-3xl font-bold text-white">{{ $a->status === 'completed' ? 'Atendimento concluído' : 'Horário confirmado!' }}</h1>
            <p class="text-neutral-400 mt-2">Te esperamos, {{ \Illuminate\Support\Str::before($a->client_name, ' ') }}.</p>
        @endif
    </div>

    @if ($awaitingPix && $a->pix_copy_paste)
        <div x-show="!paid" class="bg-ink-800 border border-gold/30 rounded-2xl p-6 text-center mb-6">
            @if ($a->pix_qr_code)
                <div class="bg-white p-3 rounded-xl inline-block mb-4">
                    <img src="data:image/png;base64,{{ $a->pix_qr_code }}" alt="QR Code PIX" class="w-52 h-52">
                </div>
            @endif
            <div class="text-xs text-neutral-500 uppercase tracking-widest mb-2">PIX copia e cola</div>
            <div class="flex gap-2">
                <input type="text" readonly value="{{ $a->pix_copy_paste }}" class="flex-1 min-w-0 bg-ink border border-white/10 rounded-lg px-3 py-2.5 text-xs text-neutral-300 font-mono">
                <button type="button" @click="copy(@js($a->pix_copy_paste))" class="bg-gold text-ink font-semibold text-sm px-4 rounded-lg shrink-0" x-text="copied ? 'Copiado!' : 'Copiar'"></button>
            </div>
            <div class="text-gold font-bold text-xl mt-5">R$ {{ number_format($a->total_price, 2, ',', '.') }}</div>
        </div>
    @endif

    <div class="bg-ink-800 border border-white/10 rounded-2xl divide-y divide-white/10">
        @foreach ([
            ['Serviço', $a->service->name ?? '—'],
            ['Profissional', $a->hairdresser->name ?? '—'],
            ['Data', ucfirst($a->scheduled_at->translatedFormat('l, d \d\e F'))],
            ['Horário', $a->scheduled_at->format('H:i')],
            ['Valor', 'R$ '.number_format($a->total_price, 2, ',', '.')],
            ['Pagamento', $a->paymentLabel().' · '.($a->payment_status === 'paid' ? 'pago' : 'pendente')],
        ] as [$label, $value])
            <div class="flex justify-between gap-4 px-5 py-3.5 text-sm">
                <span class="text-neutral-500">{{ $label }}</span>
                <span class="text-white text-right">{{ $value }}</span>
            </div>
        @endforeach
    </div>

    @unless ($cancelled)
        <div class="grid grid-cols-2 gap-3 mt-6">
            <a href="{{ $a->googleCalendarLink() }}" target="_blank" rel="noopener" class="text-center border border-white/15 rounded-xl px-4 py-3 text-sm text-white hover:border-gold">Salvar na agenda</a>
            @if ($maps = \App\Models\Setting::mapsUrl())
                <a href="{{ $maps }}" target="_blank" rel="noopener" class="text-center border border-white/15 rounded-xl px-4 py-3 text-sm text-white hover:border-gold">Como chegar</a>
            @else
                <a href="{{ route('home') }}" class="text-center border border-white/15 rounded-xl px-4 py-3 text-sm text-white hover:border-gold">Página inicial</a>
            @endif
        </div>
    @endunless

    {{-- Fidelidade --}}
    @if ($a->customer && $loyaltyTarget)
        <div class="mt-6 rounded-2xl border border-gold/30 bg-gradient-to-br from-ink-700 to-ink p-5">
            <div class="flex justify-between items-center mb-3">
                <div class="text-sm font-semibold text-white">Seu cartão fidelidade</div>
                <div class="text-sm text-gold font-bold">{{ $progress }}/{{ $loyaltyTarget }}</div>
            </div>
            <div class="h-2 rounded-full bg-white/10 overflow-hidden">
                <div class="h-full bg-gold rounded-full" style="width: {{ $loyaltyTarget ? ($progress / $loyaltyTarget) * 100 : 0 }}%"></div>
            </div>
            <p class="text-xs text-neutral-400 mt-3">
                @if ($points > 0 && $progress === 0)
                    Você ganhou um atendimento cortesia! Avise na recepção. 🎁
                @else
                    Faltam {{ $loyaltyTarget - $progress }} atendimento(s) para ganhar um cortesia.
                @endif
            </p>
        </div>
    @endif

    <div class="mt-8 text-center space-y-4">
        <p class="text-xs text-neutral-500">Guarde este link: é por ele que você acompanha ou cancela seu horário.</p>
        <div class="flex flex-col sm:flex-row gap-3 justify-center">
            <button type="button" @click="share()" class="border border-white/15 rounded-full px-6 py-3 text-sm text-white hover:border-gold">Compartilhar / copiar link</button>
            @if ($wa = \App\Models\Setting::whatsappUrl('Olá! Tenho um horário em '.$a->scheduled_at->format('d/m H:i').' ('.$a->client_name.').'))
                <a href="{{ $wa }}" target="_blank" rel="noopener" class="border border-white/15 rounded-full px-6 py-3 text-sm text-white hover:border-gold">Falar no WhatsApp</a>
            @endif
        </div>

        @if ($a->canBeCancelledByClient())
            <form method="POST" action="{{ route('booking.cancel', $a->token) }}" onsubmit="return confirm('Cancelar este agendamento?')">
                @csrf
                <button class="text-sm text-red-400 hover:text-red-300 underline mt-2">Cancelar agendamento</button>
            </form>
        @endif

        <a href="{{ route('booking.index') }}" class="block text-gold text-sm font-semibold mt-4">Fazer outro agendamento →</a>
    </div>
</div>
@endsection

@push('scripts')
<script>
function appointmentPage() {
    return {
        paid: false,
        copied: false,
        init() {
            @if ($awaitingPix)
                const poll = setInterval(async () => {
                    try {
                        const r = await fetch(@js(route('booking.status', $a->token)), { headers: { Accept: 'application/json' } });
                        const s = await r.json();
                        if (s.payment_status === 'paid') {
                            this.paid = true;
                            clearInterval(poll);
                            setTimeout(() => window.location.reload(), 1500);
                        }
                    } catch (e) {}
                }, 5000);
            @endif
        },
        async copy(text) {
            try { await navigator.clipboard.writeText(text); this.copied = true; setTimeout(() => this.copied = false, 2000); } catch (e) {}
        },
        async share() {
            const data = { title: @js($shop['shop_name']), text: 'Meu horário', url: window.location.href };
            if (navigator.share) { try { await navigator.share(data); return; } catch (e) {} }
            this.copy(window.location.href);
            alert('Link copiado!');
        },
    };
}
</script>
@endpush
