<!DOCTYPE html>
<html lang="pt_BR" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agendamento | Barbearia Elite</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <!-- PWA -->
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#D4AF37">
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/sw.js');
            });
        }
    </script>
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #0a0a0a; color: #e5e5e5; }
        .font-luxury { font-family: 'Playfair Display', serif; }
        .text-gold { color: #D4AF37; }
        .bg-gold { background-color: #D4AF37; }
        .border-gold { border-color: #D4AF37; }
        .glass { background: rgba(26, 26, 26, 0.8); backdrop-filter: blur(10px); }
        .hero-bg { background-image: linear-gradient(to bottom, rgba(10,10,10,0.7), #0a0a0a), url('/images/hero-booking.png'); background-size: cover; background-position: center; }
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="min-h-screen hero-bg">

    <div class="max-w-4xl mx-auto px-4 py-12" x-data="fluxoAgendamento()">
        <!-- Header -->
        <header class="text-center mb-12">
            <h1 class="font-luxury text-5xl text-gold mb-2">Barbearia Elite</h1>
            <p class="text-gray-400 uppercase tracking-widest text-sm">Reserva de Horário & Tradição</p>
        </header>

        <!-- Progress Bar -->
        <div class="mb-12 flex justify-between items-center relative px-8">
            <div class="absolute h-0.5 bg-gray-800 w-full left-0 top-1/2 -z-10"></div>
            <template x-for="n in 5">
                <div :class="etapa >= n ? 'bg-gold' : 'bg-gray-800'" class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold transition-all duration-500">
                    <span x-text="n" :class="etapa >= n ? 'text-black' : 'text-gray-500'"></span>
                </div>
            </template>
        </div>

        <!-- Steps Container -->
        <div class="glass p-8 rounded-2xl border border-gray-800 shadow-2xl">
            
            <!-- Step 1: Services -->
            <div x-show="etapa === 1" x-transition>
                <h2 class="text-2xl font-luxury mb-6 border-b border-gray-800 pb-4">Escolha o Serviço</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @foreach($services as $service)
                        <div @click="selecionarServico({{ json_encode($service) }})" 
                             :class="servicoSelecionado?.id === {{ $service->id }} ? 'border-gold bg-gold/10' : 'border-gray-800 hover:border-gray-600'"
                             class="p-4 border rounded-xl cursor-pointer transition-all">
                            <div class="flex justify-between items-start">
                                <div>
                                    <h3 class="font-bold text-lg">{{ $service->name }}</h3>
                                    <p class="text-sm text-gray-400">{{ $service->duration_minutes }} min</p>
                                </div>
                                <span class="text-gold font-bold">R$ {{ number_format($service->price, 2, ',', '.') }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
            
            <!-- Step 2: Hairdresser -->
            <div x-show="etapa === 2" x-cloak x-transition>
                <h2 class="text-2xl font-luxury mb-6 border-b border-gray-800 pb-4">Escolha o Profissional</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @foreach($hairdressers as $hairdresser)
                        <div @click="selecionarProfissional({{ json_encode($hairdresser) }})" 
                             :class="profissionalSelecionado?.id === {{ $hairdresser->id }} ? 'border-gold bg-gold/10' : 'border-gray-800 hover:border-gray-600'"
                             class="p-4 border rounded-xl cursor-pointer transition-all">
                            <div class="flex items-center gap-4">
                                <div class="w-12 h-12 rounded-full bg-gold/20 flex items-center justify-center text-gold font-bold">
                                    {{ substr($hairdresser->name, 0, 1) }}
                                </div>
                                <div>
                                    <h3 class="font-bold text-lg text-white">{{ $hairdresser->name }}</h3>
                                    <p class="text-xs text-gray-400">Especialista em Corte & Barba</p>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Step 3: Date & Time -->
            <div x-show="etapa === 3" x-cloak x-transition>
                <h2 class="text-2xl font-luxury mb-6 border-b border-gray-800 pb-4">Data e Horário</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div>
                        <label class="block text-xs uppercase text-gray-500 font-bold mb-2">Data</label>
                        <input type="date" x-model="dataAgendamento" @change="buscarDisponibilidade()" class="w-full bg-gray-900 border-gray-800 rounded-lg text-white focus:ring-gold focus:border-gold">
                    </div>
                    <div>
                        <label class="block text-xs uppercase text-gray-500 font-bold mb-2">Horário Disponíveis</label>
                        <div class="grid grid-cols-3 gap-2">
                            <template x-for="horario in horariosDisponiveis">
                                <button @click="horaAgendamento = horario" 
                                        :disabled="horariosOcupados.includes(horario)"
                                        :class="horaAgendamento === horario ? 'bg-gold text-black border-gold' : (horariosOcupados.includes(horario) ? 'bg-gray-800 border-transparent text-gray-600 cursor-not-allowed opacity-30' : 'bg-gray-900 border-gray-800 text-gray-400')"
                                        class="py-2 text-sm border rounded hover:border-gold transition-all" 
                                        x-text="horario"></button>
                            </template>
                        </div>
                        <div x-show="carregandoHorarios" class="mt-2 text-xs text-gold animate-pulse">Sincronizando horários...</div>
                    </div>
                </div>
            </div>

            <!-- Step 4: Client Info -->
            <div x-show="etapa === 4" x-cloak x-transition>
                <h2 class="text-2xl font-luxury mb-6 border-b border-gray-800 pb-4">Seus Dados</h2>
                <div class="space-y-6">
                    <div>
                        <label class="block text-xs uppercase text-gray-400 font-bold mb-2">Nome Completo <span class="text-red-500">*</span></label>
                        <input type="text" x-model="nomeCliente" placeholder="Como deseja ser chamado?" class="w-full bg-gray-900 border-gray-800 rounded-xl text-white focus:ring-gold focus:border-gold py-3 px-4">
                    </div>
                    <div>
                        <label class="block text-xs uppercase text-gray-400 font-bold mb-2">WhatsApp / Celular <span class="text-red-500">*</span></label>
                        <input type="tel" x-model="telefoneCliente" placeholder="(00) 00000-0000" class="w-full bg-gray-900 border-gray-800 rounded-xl text-white focus:ring-gold focus:border-gold py-3 px-4">
                        <p class="text-[10px] text-gray-500 mt-2 flex items-center">
                            <svg class="w-3 h-3 mr-1 text-green-500" fill="currentColor" viewBox="0 0 20 20"><path d="M2 10a8 8 0 018-8v8h8a8 8 0 11-16 0z"/><path d="M12 2.252A8.014 8.014 0 0117.748 8H12V2.252z"/></svg>
                            Usaremos este número para enviar o lembrete via WhatsApp/SMS.
                        </p>
                    </div>
                    <div class="pt-2">
                        <label class="block text-xs uppercase text-gray-400 font-bold mb-2">E-mail (Opcional)</label>
                        <input type="email" x-model="emailCliente" placeholder="Para receber o lembrete por e-mail" class="w-full bg-gray-900 border-gray-800 rounded-xl text-white focus:ring-gold focus:border-gold py-3 px-4">
                        <p class="text-[10px] text-gray-500 mt-2 italic">Fique tranquilo, o e-mail não é obrigatório.</p>
                    </div>
                </div>
            </div>

            <!-- Step 5: Payment -->
            <div x-show="etapa === 5" x-cloak x-transition>
                <h2 class="text-2xl font-luxury mb-6 border-b border-gray-800 pb-4">Pagamento Antecipado</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-4">
                        <label class="flex items-center p-4 border rounded-xl cursor-pointer transition-all" :class="metodoPagamento === 'pix' ? 'border-gold bg-gold/10' : 'border-gray-800'">
                            <input type="radio" name="pay" value="pix" x-model="metodoPagamento" class="text-gold focus:ring-gold">
                            <span class="ml-3 font-bold">PIX (Confirmação Imediata)</span>
                        </label>
                        <label class="flex items-center p-4 border rounded-xl cursor-pointer transition-all" :class="metodoPagamento === 'credit_card' ? 'border-gold bg-gold/10' : 'border-gray-800'">
                            <input type="radio" name="pay" value="credit_card" x-model="metodoPagamento" class="text-gold focus:ring-gold">
                            <span class="ml-3 font-bold">Cartão de Crédito</span>
                        </label>
                        <label class="flex items-center p-4 border rounded-xl cursor-pointer transition-all" :class="metodoPagamento === 'pay_later' ? 'border-gold bg-gold/10' : 'border-gray-800'">
                            <input type="radio" name="pay" value="pay_later" x-model="metodoPagamento" class="text-gold focus:ring-gold">
                            <span class="ml-3 font-bold">Pagar na Barbearia</span>
                        </label>
                    </div>
                    
                    <div class="bg-black/40 p-6 rounded-xl border border-dashed border-gray-700">
                        <div x-show="metodoPagamento === 'pix'" class="text-center">
                            <p class="text-xs mb-4 text-gray-400">Escaneie o QR Code abaixo para pagar via PIX:</p>
                            <div class="bg-white p-4 inline-block rounded-lg shadow-inner mb-4">
                                <img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=BarbeariaElitePaymentPix" alt="QR Code PIX">
                            </div>
                            <p class="text-sm font-bold text-gold">R$ <span x-text="servicoSelecionado?.price"></span></p>
                        </div>
                        <div x-show="metodoPagamento === 'credit_card'">
                             <p class="text-xs mb-4 text-gray-400">Simulação de Cartão de Crédito:</p>
                             <div class="space-y-3">
                                 <input type="text" placeholder="Número do Cartão" class="w-full bg-gray-900/50 border-gray-800 rounded text-xs p-2">
                                 <div class="grid grid-cols-2 gap-2">
                                     <input type="text" placeholder="Validade" class="w-full bg-gray-900/50 border-gray-800 rounded text-xs p-2">
                                     <input type="text" placeholder="CVV" class="w-full bg-gray-900/50 border-gray-800 rounded text-xs p-2">
                                 </div>
                             </div>
                        </div>
                        <div x-show="metodoPagamento === 'pay_later'" class="text-center py-4">
                            <div class="inline-block p-3 rounded-full bg-gold/20 mb-4">
                                <svg class="w-8 h-8 text-gold" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                </svg>
                            </div>
                            <h3 class="font-bold text-lg mb-2">Pague no Local</h3>
                            <p class="text-sm text-gray-400">Você pode realizar o pagamento diretamente em nossa barbearia no dia do seu atendimento.</p>
                            <p class="mt-4 text-gold font-bold">Total: R$ <span x-text="servicoSelecionado?.price"></span></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Navigation Buttons -->
            <div class="mt-12 flex justify-between">
                <button x-show="etapa > 1" @click="etapa--" class="text-gray-400 hover:text-white transition-colors">Voltar</button>
                <div class="flex-grow"></div>
                <button x-show="etapa < 5" :disabled="!etapaValida()" @click="etapa++" 
                        class="bg-gold text-black font-bold py-3 px-8 rounded-full disabled:opacity-50 transition-all hover:scale-105">
                    Próximo Passo
                </button>
                <button x-show="etapa === 5" @click="finalizarAgendamento()" :disabled="enviando" 
                        class="bg-green-600 hover:bg-green-700 text-white font-bold py-3 px-12 rounded-full transition-all flex items-center shadow-lg">
                    <span x-show="!enviando" x-text="metodoPagamento === 'pay_later' ? 'FINALIZAR AGENDAMENTO' : 'FINALIZAR E PAGAR'"></span>
                    <span x-show="enviando" class="animate-pulse">PROCESSANDO...</span>
                </button>
            </div>
        </div>

        <!-- Footer -->
        <footer class="mt-12 text-center text-gray-600 text-xs">
            <p>&copy; 2026 Barbearia Elite. Todos os direitos reservados.</p>
        </footer>
    </div>

    <script>
        function fluxoAgendamento() {
            return {
                etapa: 1,
                servicoSelecionado: null,
                profissionalSelecionado: null,
                dataAgendamento: '',
                horaAgendamento: '',
                nomeCliente: '',
                telefoneCliente: '',
                emailCliente: '',
                horariosDisponiveis: [
                    '09:00', '09:30', '10:00', '10:30', '11:00', '11:30', 
                    '14:00', '14:30', '15:00', '15:30', '16:00', '16:30', 
                    '17:00', '17:30', '18:00', '18:30', '19:00'
                ],
                horariosOcupados: [],
                carregandoHorarios: false,
                enviando: false,
                metodoPagamento: 'pix',
                
                selecionarServico(servico) {
                    this.servicoSelecionado = servico;
                    this.etapa = 2;
                },

                selecionarProfissional(profissional) {
                    this.profissionalSelecionado = profissional;
                    this.etapa = 3;
                    this.buscarDisponibilidade();
                },

                async buscarDisponibilidade() {
                    if (!this.dataAgendamento || !this.profissionalSelecionado) return;
                    
                    this.carregandoHorarios = true;
                    try {
                        const resp = await fetch(`{{ route('booking.availability') }}?date=${this.dataAgendamento}&hairdresser_id=${this.profissionalSelecionado.id}`);
                        this.horariosOcupados = await resp.json();
                    } catch (e) {
                        console.error("Erro ao buscar horários", e);
                    } finally {
                        this.carregandoHorarios = false;
                    }
                },

                etapaValida() {
                    if (this.etapa === 1) return this.servicoSelecionado !== null;
                    if (this.etapa === 2) return this.profissionalSelecionado !== null;
                    if (this.etapa === 3) return this.dataAgendamento !== '' && this.horaAgendamento !== '';
                    if (this.etapa === 4) return this.nomeCliente.length > 2 && this.telefoneCliente.length > 8;
                    return true;
                },

                async finalizarAgendamento() {
                    this.enviando = true;
                    
                    const scheduled_at = `${this.dataAgendamento} ${this.horaAgendamento}:00`;
                    
                    try {
                        const targetUrl = '{{ route("booking.process") }}';
                        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                        
                        console.log('Iniciando fetch para:', targetUrl);
                        console.log('CSRF Token presente:', !!csrfToken);

                        const response = await fetch(targetUrl, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': csrfToken,
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({
                                client_name: this.nomeCliente,
                                client_phone: this.telefoneCliente,
                                email: this.emailCliente,
                                service_id: this.servicoSelecionado.id,
                                hairdresser_id: this.profissionalSelecionado.id,
                                scheduled_at: scheduled_at,
                                payment_method: this.metodoPagamento
                            })
                        });

                        const result = await response.json();
                        
                        if (result.success) {
                            window.location.href = `/agendar/sucesso/${result.appointment_id}`;
                        } else {
                            alert('Erro ao processar agendamento: ' + result.message);
                        }
                    } catch (error) {
                        console.error('Erro:', error);
                        alert('Ocorreu um erro no processamento: ' + error.message);
                    } finally {
                        this.enviando = false;
                    }
                }
            }
        }
    </script>
</body>
</html>
