<!DOCTYPE html>
<html lang="pt_BR" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sucesso | Barbearia Elite</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #0a0a0a; color: #e5e5e5; }
        .font-luxury { font-family: 'Playfair Display', serif; }
        .text-gold { color: #D4AF37; }
        .bg-gold { background-color: #D4AF37; }
        .glass { background: rgba(26, 26, 26, 0.8); backdrop-filter: blur(10px); }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-4">

    <div class="max-w-md w-full glass p-10 rounded-3xl border border-gray-800 text-center shadow-2xl transition-all hover:border-gold">
        <div class="w-20 h-20 bg-green-900/30 text-green-500 rounded-full flex items-center justify-center mx-auto mb-6 border border-green-500/30">
            <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
        </div>
        
        <h1 class="font-luxury text-3xl text-gold mb-4">Agendamento Confirmado!</h1>
        <p class="text-gray-400 mb-8 leading-relaxed">
            @if($appointment->payment_method === 'pay_later')
                Seu agendamento foi realizado com sucesso. O pagamento será feito diretamente na barbearia. Estamos esperando por você!
            @else
                Seu pagamento via <span class="text-white font-bold uppercase tracking-tighter">
                    {{ $appointment->payment_method === 'pix' ? 'PIX' : 'Cartão de Crédito' }}
                </span> foi processado com sucesso. Estamos esperando por você!
            @endif
        </p>

        <div class="bg-black/40 p-6 rounded-2xl border border-gray-800 text-left mb-8">
            <div class="flex justify-between items-center mb-4 pb-4 border-b border-gray-800">
                <span class="text-xs text-gray-500 uppercase tracking-widest font-bold">Serviço</span>
                <span class="font-bold">{{ $appointment->service->name }}</span>
            </div>
            <div class="flex justify-between items-center mb-4 pb-4 border-b border-gray-800">
                <span class="text-xs text-gray-500 uppercase tracking-widest font-bold">Data/Hora</span>
                <span class="font-bold">{{ \Carbon\Carbon::parse($appointment->scheduled_at)->format('d/m/Y H:i') }}</span>
            </div>
            <div class="flex justify-between items-center">
                <span class="text-xs text-gray-500 uppercase tracking-widest font-bold">Cliente</span>
                <span class="font-bold">{{ $appointment->client_name }}</span>
            </div>
        </div>

        <a href="/" class="inline-block bg-gold text-black font-bold py-3 px-10 rounded-full transition-all hover:scale-105 shadow-lg">
            VOLTAR AO INÍCIO
        </a>
        
        <p class="mt-8 text-[10px] text-gray-600 uppercase tracking-widest">Barbearia Elite &bull; Tradição & Estilo</p>
    </div>

</body>
</html>
