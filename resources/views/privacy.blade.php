@extends('layouts.site')

@section('title', 'Política de privacidade | '.$shop['shop_name'])

@section('content')
<article class="max-w-3xl mx-auto px-4 sm:px-6 pt-28 pb-20 text-neutral-300 leading-relaxed space-y-5">
    <h1 class="font-display text-4xl font-bold text-white mb-6">Política de privacidade</h1>
    <p>Esta política explica como a <strong class="text-white">{{ $shop['shop_name'] }}</strong> trata os seus dados pessoais no site e no aplicativo, em conformidade com a Lei Geral de Proteção de Dados (Lei nº 13.709/2018 — LGPD).</p>

    <h2 class="text-xl font-semibold text-white pt-4">Quais dados coletamos</h2>
    <ul class="list-disc pl-6 space-y-1">
        <li>Nome, telefone/WhatsApp e, se você informar, e-mail;</li>
        <li>Dados do agendamento (serviço, profissional, data, horário e observações);</li>
        <li>Status do pagamento. Os dados do cartão são enviados diretamente ao Mercado Pago e <strong class="text-white">não são armazenados</strong> por nós.</li>
    </ul>

    <h2 class="text-xl font-semibold text-white pt-4">Para que usamos</h2>
    <ul class="list-disc pl-6 space-y-1">
        <li>Realizar e gerenciar o seu agendamento;</li>
        <li>Enviar confirmação e lembrete do horário;</li>
        <li>Controlar o programa de fidelidade;</li>
        <li>Cumprir obrigações legais e fiscais.</li>
    </ul>

    <h2 class="text-xl font-semibold text-white pt-4">Compartilhamento</h2>
    <p>Compartilhamos dados apenas com prestadores necessários para o serviço: processamento de pagamento (Mercado Pago), envio de mensagens (SMS/WhatsApp/e-mail) e hospedagem. Não vendemos seus dados.</p>

    <h2 class="text-xl font-semibold text-white pt-4">Seus direitos</h2>
    <p>Você pode pedir acesso, correção ou exclusão dos seus dados a qualquer momento
        @if ($wa = \App\Models\Setting::whatsappUrl('Olá! Gostaria de falar sobre meus dados pessoais.'))
            pelo nosso <a href="{{ $wa }}" class="text-gold underline" target="_blank" rel="noopener">WhatsApp</a>
        @else
            diretamente na barbearia
        @endif
        .</p>

    <h2 class="text-xl font-semibold text-white pt-4">Armazenamento local</h2>
    <p>Para agilizar seus próximos agendamentos, o navegador pode guardar seu nome e telefone no próprio aparelho. Você pode apagar isso limpando os dados do site no navegador.</p>

    <p class="text-sm text-neutral-500 pt-6">Última atualização: {{ now()->translatedFormat('F \d\e Y') }}.</p>
</article>
@endsection
