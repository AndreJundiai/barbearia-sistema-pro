@extends('layouts.site')

@push('head')
    {{-- Dados estruturados: ajudam a barbearia a aparecer no Google com horário, endereço e preços --}}
    <script type="application/ld+json">
        {!! json_encode(array_filter([
            '@context' => 'https://schema.org',
            '@type' => 'BarberShop',
            'name' => $shop['shop_name'],
            'description' => $shop['about'],
            'url' => url('/'),
            'image' => asset('images/hero.jpg'),
            'telephone' => $shop['whatsapp'] ? '+'.(strlen($shop['whatsapp']) <= 11 ? '55' : '').$shop['whatsapp'] : null,
            'address' => $shop['address'] ? ['@type' => 'PostalAddress', 'streetAddress' => $shop['address'], 'addressLocality' => $shop['city'], 'addressCountry' => 'BR'] : null,
            'openingHours' => $shop['open_time'].'-'.$shop['close_time'],
            'priceRange' => $services->isNotEmpty() ? 'R$ '.number_format($services->min('price'), 0, ',', '.').' - R$ '.number_format($services->max('price'), 0, ',', '.') : null,
            'sameAs' => $shop['instagram'] ? ['https://instagram.com/'.$shop['instagram']] : null,
            'makesOffer' => $services->map(fn ($s) => ['@type' => 'Offer', 'name' => $s->name, 'price' => number_format($s->price, 2, '.', ''), 'priceCurrency' => 'BRL'])->values(),
        ]), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}
    </script>
@endpush

@section('content')
    {{-- HERO --}}
    <section class="relative min-h-[92svh] flex items-end sm:items-center overflow-hidden">
        <img src="/images/hero.jpg" alt="" class="absolute inset-0 w-full h-full object-cover opacity-60" fetchpriority="high">
        <div class="absolute inset-0 bg-gradient-to-t from-ink via-ink/70 to-ink/30"></div>
        <div class="relative max-w-6xl mx-auto px-4 sm:px-6 pt-28 pb-16 sm:py-0 w-full">
            <div class="max-w-2xl rise">
                <div class="inline-flex items-center gap-2 border border-gold/30 bg-black/30 backdrop-blur rounded-full px-3 py-1 text-xs text-gold-200 mb-6">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                    Agenda online aberta · {{ \App\Models\Setting::openDaysLabel() }}, {{ $shop['open_time'] }}–{{ $shop['close_time'] }}
                </div>
                <h1 class="font-display text-5xl sm:text-7xl font-bold leading-[1.05] text-gradient-gold">{{ $shop['slogan'] }}</h1>
                <p class="mt-6 text-lg text-neutral-300 leading-relaxed max-w-xl">{{ $shop['about'] }}</p>
                <div class="mt-9 flex flex-col sm:flex-row gap-3">
                    <a href="{{ route('booking.index') }}" class="inline-flex justify-center items-center gap-2 bg-gold text-ink font-semibold text-base px-8 py-4 rounded-full hover:bg-gold-300 transition-colors shadow-lg shadow-gold/20">
                        Agendar meu horário
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5-5 5M5 12h13"/></svg>
                    </a>
                    <a href="#servicos" class="inline-flex justify-center items-center border border-white/20 text-white font-medium px-8 py-4 rounded-full hover:border-gold hover:text-gold transition-colors">
                        Ver serviços e preços
                    </a>
                </div>
                <ul class="mt-10 grid grid-cols-3 gap-4 max-w-md text-center sm:text-left">
                    <li>
                        <div class="text-2xl font-bold text-white">1 min</div>
                        <div class="text-xs text-neutral-400 mt-1">para agendar</div>
                    </li>
                    <li>
                        <div class="text-2xl font-bold text-white">{{ $hairdressers->count() }}</div>
                        <div class="text-xs text-neutral-400 mt-1">{{ $hairdressers->count() === 1 ? 'profissional' : 'profissionais' }}</div>
                    </li>
                    <li>
                        <div class="text-2xl font-bold text-white">{{ $stats['completed'] >= 50 ? number_format($stats['completed'], 0, ',', '.').'+' : 'PIX' }}</div>
                        <div class="text-xs text-neutral-400 mt-1">{{ $stats['completed'] >= 50 ? 'atendimentos' : 'ou cartão online' }}</div>
                    </li>
                </ul>
            </div>
        </div>
    </section>

    {{-- COMO FUNCIONA --}}
    <section class="border-y hairline bg-ink-800/60">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 py-10 grid sm:grid-cols-3 gap-6">
            @foreach ([
                ['1', 'Escolha o serviço', 'Corte, barba, combo — preço e duração na hora.'],
                ['2', 'Escolha o profissional e o horário', 'Só aparecem horários realmente livres.'],
                ['3', 'Confirmado!', 'Você recebe a confirmação e um lembrete antes do horário.'],
            ] as [$n, $title, $text])
                <div class="flex gap-4">
                    <div class="w-10 h-10 shrink-0 rounded-full border border-gold/40 text-gold font-display font-bold flex items-center justify-center">{{ $n }}</div>
                    <div>
                        <div class="font-semibold text-white">{{ $title }}</div>
                        <div class="text-sm text-neutral-400 mt-1">{{ $text }}</div>
                    </div>
                </div>
            @endforeach
        </div>
    </section>

    {{-- SERVIÇOS --}}
    <section id="servicos" class="scroll-mt-20 py-20 sm:py-28">
        <div class="max-w-6xl mx-auto px-4 sm:px-6">
            <div class="max-w-2xl mb-12">
                <div class="text-gold text-xs font-semibold uppercase tracking-[0.25em] mb-3">Serviços e preços</div>
                <h2 class="font-display text-4xl sm:text-5xl font-bold text-white">Escolha o seu e agende agora</h2>
                <p class="text-neutral-400 mt-4">Preço fechado, sem surpresa. Toque no serviço para reservar.</p>
            </div>

            @if ($services->isEmpty())
                <p class="text-neutral-500">Em breve nosso catálogo estará disponível.</p>
            @else
                <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach ($services as $service)
                        <a href="{{ route('booking.index', ['servico' => $service->id]) }}"
                           class="group relative flex flex-col bg-ink-800 border {{ $service->featured ? 'border-gold/60' : 'hairline' }} rounded-2xl p-6 hover:border-gold transition-colors">
                            @if ($service->featured)
                                <span class="absolute -top-3 left-6 bg-gold text-ink text-[11px] font-bold uppercase tracking-wider px-3 py-1 rounded-full">Mais pedido</span>
                            @endif
                            <div class="flex items-start justify-between gap-4">
                                <h3 class="font-semibold text-lg text-white">{{ $service->name }}</h3>
                                <div class="text-right shrink-0">
                                    <div class="text-gold font-bold text-xl">R$ {{ number_format($service->price, 2, ',', '.') }}</div>
                                </div>
                            </div>
                            @if ($service->description)
                                <p class="text-sm text-neutral-400 mt-2 leading-relaxed">{{ $service->description }}</p>
                            @endif
                            <div class="mt-auto pt-6 flex items-center justify-between text-sm">
                                <span class="inline-flex items-center gap-1.5 text-neutral-500">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path stroke-linecap="round" d="M12 7v5l3 2"/></svg>
                                    {{ $service->duration_minutes }} min
                                </span>
                                <span class="text-gold font-semibold group-hover:translate-x-1 transition-transform">Agendar →</span>
                            </div>
                        </a>
                    @endforeach
                </div>
            @endif
        </div>
    </section>

    {{-- EQUIPE --}}
    @if ($hairdressers->isNotEmpty())
    <section id="equipe" class="scroll-mt-20 py-20 sm:py-28 bg-ink-800/50 border-y hairline">
        <div class="max-w-6xl mx-auto px-4 sm:px-6">
            <div class="max-w-2xl mb-12">
                <div class="text-gold text-xs font-semibold uppercase tracking-[0.25em] mb-3">Nossa equipe</div>
                <h2 class="font-display text-4xl sm:text-5xl font-bold text-white">Mãos de quem entende</h2>
            </div>
            <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach ($hairdressers as $h)
                    <div class="bg-ink border hairline rounded-2xl p-6 flex flex-col">
                        <div class="flex items-center gap-4">
                            <div class="w-14 h-14 rounded-full bg-gradient-to-br from-gold to-gold-700 text-ink font-display font-bold text-xl flex items-center justify-center">{{ $h->initials() }}</div>
                            <div>
                                <div class="font-semibold text-white text-lg">{{ $h->name }}</div>
                                <div class="text-sm text-gold">{{ $h->specialty ?: 'Barbeiro' }}</div>
                            </div>
                        </div>
                        @if ($h->bio)
                            <p class="text-sm text-neutral-400 mt-4 leading-relaxed">{{ $h->bio }}</p>
                        @endif
                        <div class="mt-auto pt-6 flex items-center justify-between">
                            <a href="{{ route('booking.index', ['profissional' => $h->id]) }}" class="text-sm font-semibold text-gold hover:underline">Agendar com {{ \Illuminate\Support\Str::before($h->name, ' ') }} →</a>
                            @if ($h->instagram)
                                <a href="https://instagram.com/{{ $h->instagram }}" target="_blank" rel="noopener" class="text-xs text-neutral-500 hover:text-white">&#64;{{ $h->instagram }}</a>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
    @endif

    {{-- GALERIA --}}
    @if ($gallery->isNotEmpty())
    <section class="py-20 sm:py-28">
        <div class="max-w-6xl mx-auto px-4 sm:px-6">
            <div class="flex items-end justify-between gap-4 mb-10">
                <div>
                    <div class="text-gold text-xs font-semibold uppercase tracking-[0.25em] mb-3">Galeria</div>
                    <h2 class="font-display text-4xl sm:text-5xl font-bold text-white">Nossos trabalhos</h2>
                </div>
                <a href="{{ route('gallery.index') }}" class="text-sm text-gold font-semibold shrink-0">Ver todos →</a>
            </div>
            <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
                @foreach ($gallery as $img)
                    <div class="aspect-square overflow-hidden rounded-xl bg-ink-800">
                        <img src="{{ Storage::url($img->path) }}" alt="{{ $img->title ?: 'Corte realizado na '.$shop['shop_name'] }}" loading="lazy" class="w-full h-full object-cover hover:scale-105 transition-transform duration-500">
                    </div>
                @endforeach
            </div>
        </div>
    </section>
    @endif

    {{-- FIDELIDADE --}}
    <section class="py-20 sm:py-24">
        <div class="max-w-6xl mx-auto px-4 sm:px-6">
            <div class="relative overflow-hidden rounded-3xl border border-gold/30 bg-gradient-to-br from-ink-700 to-ink p-8 sm:p-12 grid md:grid-cols-2 gap-8 items-center">
                <div>
                    <div class="text-gold text-xs font-semibold uppercase tracking-[0.25em] mb-3">Clube de fidelidade</div>
                    <h2 class="font-display text-3xl sm:text-4xl font-bold text-white">A cada {{ $loyaltyTarget }} atendimentos, o próximo é por nossa conta</h2>
                    <p class="text-neutral-400 mt-4">É automático: agende pelo site com o seu WhatsApp e os pontos são somados sozinhos. Sem cartão de papel, sem cadastro.</p>
                    <a href="{{ route('booking.index') }}" class="inline-block mt-7 bg-gold text-ink font-semibold px-7 py-3.5 rounded-full hover:bg-gold-300 transition-colors">Começar a pontuar</a>
                </div>
                <div class="grid grid-cols-5 gap-3 max-w-xs mx-auto md:ml-auto" aria-hidden="true">
                    @for ($i = 1; $i <= min($loyaltyTarget, 10); $i++)
                        <div class="aspect-square rounded-full border-2 {{ $i <= 6 ? 'border-gold bg-gold/15 text-gold' : 'border-white/15 text-neutral-600' }} flex items-center justify-center">
                            @if ($i === min($loyaltyTarget, 10))
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path d="M10 1.5l2.6 5.3 5.9.9-4.3 4.1 1 5.8L10 14.9l-5.2 2.7 1-5.8L1.5 7.7l5.9-.9z"/></svg>
                            @elseif ($i <= 6)
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                            @endif
                        </div>
                    @endfor
                </div>
            </div>
        </div>
    </section>

    {{-- FAQ --}}
    <section class="py-20 sm:py-24 bg-ink-800/50 border-y hairline">
        <div class="max-w-3xl mx-auto px-4 sm:px-6">
            <h2 class="font-display text-3xl sm:text-4xl font-bold text-white mb-8 text-center">Perguntas frequentes</h2>
            <div class="divide-y divide-white/10 border-y hairline" x-data="{ open: 0 }">
                @foreach ([
                    ['Preciso pagar antecipado?', $shop['allow_pay_later'] ? 'Não. Você pode pagar via PIX ou cartão pelo site, ou pagar direto na barbearia no dia.' : 'Sim, o pagamento online (PIX ou cartão) garante o seu horário.'],
                    ['Posso cancelar ou remarcar?', 'Sim. Na confirmação você recebe um link do seu horário, onde pode cancelar até '.$shop['cancel_notice_hours'].'h antes. Para remarcar, é só agendar de novo ou chamar no WhatsApp.'],
                    ['Vou receber lembrete?', 'Sim, enviamos a confirmação e um lembrete antes do seu horário.'],
                    ['Como funciona a fidelidade?', 'Cada atendimento concluído soma 1 ponto no seu número de WhatsApp. Ao completar '.$loyaltyTarget.', o próximo é cortesia.'],
                ] as $i => [$q, $a])
                    <div>
                        <button @click="open = open === {{ $i }} ? null : {{ $i }}" class="w-full flex justify-between items-center py-5 text-left text-white font-medium">
                            {{ $q }}
                            <svg :class="open === {{ $i }} && 'rotate-45'" class="w-5 h-5 text-gold transition-transform shrink-0 ml-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M12 5v14M5 12h14"/></svg>
                        </button>
                        <div x-show="open === {{ $i }}" x-transition.opacity x-cloak class="pb-5 text-neutral-400 leading-relaxed">{{ $a }}</div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- CONTATO --}}
    <section id="contato" class="scroll-mt-20 py-20 sm:py-28">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 grid lg:grid-cols-2 gap-10 items-center">
            <div>
                <div class="text-gold text-xs font-semibold uppercase tracking-[0.25em] mb-3">Visite a gente</div>
                <h2 class="font-display text-4xl sm:text-5xl font-bold text-white">Seu próximo corte começa aqui</h2>
                <dl class="mt-8 space-y-5">
                    <div class="flex gap-4">
                        <dt class="w-28 shrink-0 text-sm text-neutral-500">Horário</dt>
                        <dd class="text-neutral-200">{{ \App\Models\Setting::openDaysLabel() }} · {{ $shop['open_time'] }} às {{ $shop['close_time'] }}</dd>
                    </div>
                    @if ($shop['address'])
                    <div class="flex gap-4">
                        <dt class="w-28 shrink-0 text-sm text-neutral-500">Endereço</dt>
                        <dd class="text-neutral-200">{{ $shop['address'] }}@if($shop['city'])<br>{{ $shop['city'] }}@endif</dd>
                    </div>
                    @endif
                    @if ($shop['whatsapp'])
                    <div class="flex gap-4">
                        <dt class="w-28 shrink-0 text-sm text-neutral-500">WhatsApp</dt>
                        <dd><a href="{{ \App\Models\Setting::whatsappUrl() }}" target="_blank" rel="noopener" class="text-gold hover:underline">Chamar no WhatsApp</a></dd>
                    </div>
                    @endif
                </dl>
                <div class="mt-9 flex flex-col sm:flex-row gap-3">
                    <a href="{{ route('booking.index') }}" class="inline-flex justify-center bg-gold text-ink font-semibold px-8 py-4 rounded-full hover:bg-gold-300 transition-colors">Agendar horário</a>
                    @if ($maps = \App\Models\Setting::mapsUrl())
                        <a href="{{ $maps }}" target="_blank" rel="noopener" class="inline-flex justify-center border border-white/20 text-white px-8 py-4 rounded-full hover:border-gold hover:text-gold transition-colors">Como chegar</a>
                    @endif
                </div>
            </div>
            @if ($shop['address'])
                <div class="rounded-2xl overflow-hidden border hairline aspect-[4/3] bg-ink-800">
                    <iframe title="Mapa" loading="lazy" class="w-full h-full grayscale-[60%] invert-[90%] hue-rotate-180"
                            src="https://www.google.com/maps?q={{ rawurlencode($shop['address'].' '.$shop['city']) }}&output=embed"></iframe>
                </div>
            @endif
        </div>
    </section>
@endsection
