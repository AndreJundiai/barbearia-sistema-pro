<x-app-layout>
    <div class="py-12 bg-[#0a0a0b] min-h-screen text-gray-100">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">
            
            <!-- Header Section -->
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-gray-800 pb-8">
                <div>
                    <h2 class="text-4xl font-extrabold tracking-tight text-white font-luxury">
                        Gestão <span class="text-gold">Financeira</span>
                    </h2>
                    <p class="text-gray-500 mt-2 text-sm uppercase tracking-widest font-bold">Controle total de fluxo e comissões</p>
                </div>
                <div class="flex items-center gap-3">
                    <button onclick="document.getElementById('importModal').classList.remove('hidden')" class="px-6 py-2 bg-gray-800 hover:bg-gray-700 border border-gray-700 rounded-xl text-xs font-black text-white uppercase tracking-widest transition-all">
                        Importar Planilha
                    </button>
                    <span class="px-4 py-2 bg-gray-900 border border-gray-800 rounded-full text-xs font-bold text-gray-400">
                        {{ now()->translatedFormat('M Y') }}
                    </span>
                </div>
            </div>

            @if(session('status'))
                <div class="bg-green-500/10 border border-green-500/50 text-green-500 p-4 rounded-xl text-sm animate-pulse">
                    {{ session('status') }}
                </div>
            @endif

            <!-- Glassmorphism Summary Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Receitas -->
                <div class="relative group overflow-hidden bg-gray-900/50 backdrop-blur-xl border border-gray-800 p-6 rounded-3xl transition-all hover:border-green-500/50">
                    <div class="absolute -right-4 -top-4 w-24 h-24 bg-green-500/10 rounded-full blur-3xl group-hover:bg-green-500/20 transition-all"></div>
                    <p class="text-xs font-bold text-gray-500 uppercase tracking-widest mb-1">Total Receitas</p>
                    <p class="text-3xl font-black text-green-500 tracking-tight">R$ {{ number_format($totalIncome, 2, ',', '.') }}</p>
                    <div class="mt-4 flex items-center gap-2 text-[10px] text-green-500 font-bold bg-green-500/10 w-fit px-2 py-1 rounded-full">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 10l7-7m0 0l7 7m-7-7v18"></path></svg>
                        FLUXO POSITIVO
                    </div>
                </div>

                <!-- Despesas -->
                <div class="relative group overflow-hidden bg-gray-900/50 backdrop-blur-xl border border-gray-800 p-6 rounded-3xl transition-all hover:border-red-500/50">
                    <div class="absolute -right-4 -top-4 w-24 h-24 bg-red-500/10 rounded-full blur-3xl group-hover:bg-red-500/20 transition-all"></div>
                    <p class="text-xs font-bold text-gray-500 uppercase tracking-widest mb-1">Total Despesas</p>
                    <p class="text-3xl font-black text-red-500 tracking-tight">R$ {{ number_format($totalExpense, 2, ',', '.') }}</p>
                    <div class="mt-4 flex items-center gap-2 text-[10px] text-red-500 font-bold bg-red-500/10 w-fit px-2 py-1 rounded-full">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M19 14l-7 7m0 0l-7-7m7 7V3"></path></svg>
                        SAÍDAS REGISTRADAS
                    </div>
                </div>

                <!-- Lucro Real -->
                <div class="relative group overflow-hidden bg-gradient-to-br from-gray-900 to-black border border-gold/30 p-6 rounded-3xl transition-all hover:scale-[1.02]">
                    <div class="absolute -right-4 -top-4 w-24 h-24 bg-gold/10 rounded-full blur-3xl"></div>
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-1">Saldo em Caixa</p>
                    <p class="text-4xl font-black text-gold tracking-tighter">R$ {{ number_format($balance, 2, ',', '.') }}</p>
                    <div class="mt-4 bg-gold text-black text-[10px] font-black px-3 py-1 rounded-full w-fit uppercase">
                        Patrimônio Atual
                    </div>
                </div>
            </div>

            <!-- Charts Section -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <div class="bg-gray-900/50 p-6 rounded-3xl border border-gray-800">
                    <h3 class="text-sm font-bold text-gray-400 uppercase tracking-widest mb-6">Fluxo de Caixa (7 dias)</h3>
                    <canvas id="flowChart" height="200"></canvas>
                </div>
                <div class="bg-gray-900/50 p-6 rounded-3xl border border-gray-800">
                    <h3 class="text-sm font-bold text-gray-400 uppercase tracking-widest mb-6">Despesas vs Receitas</h3>
                    <canvas id="distributionChart" height="200"></canvas>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Left: Transactions List -->
                <div class="lg:col-span-2 space-y-6">
                    <div class="bg-gray-900/50 border border-gray-800 rounded-3xl overflow-hidden backdrop-blur-sm">
                        <div class="p-6 border-b border-gray-800 flex justify-between items-center bg-gray-800/20">
                            <h3 class="text-lg font-bold text-white flex items-center gap-2 uppercase tracking-tighter">
                                <span class="w-2 h-6 bg-gold rounded-full"></span>
                                Histórico Recente
                            </h3>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full text-left border-collapse">
                                <thead>
                                    <tr class="bg-black/20 text-[10px] uppercase font-black text-gray-500 tracking-widest">
                                        <th class="px-6 py-4">Data</th>
                                        <th class="px-6 py-4">Descrição</th>
                                        <th class="px-6 py-4 text-center">Tipo</th>
                                        <th class="px-6 py-4 text-right">Valor</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-800">
                                    @forelse($transactions as $transaction)
                                    <tr class="hover:bg-gray-800/30 transition-colors group">
                                        <td class="px-6 py-4 text-xs text-gray-400">
                                            {{ \Carbon\Carbon::parse($transaction->transaction_date)->format('d/m/Y') }}
                                        </td>
                                        <td class="px-6 py-4 text-sm font-medium text-gray-200">
                                            {{ $transaction->description }}
                                        </td>
                                        <td class="px-6 py-4 text-center">
                                            <span class="px-3 py-1 rounded-full text-[10px] font-black uppercase {{ $transaction->type == 'income' ? 'bg-green-500/10 text-green-500 border border-green-500/20' : 'bg-red-500/10 text-red-500 border border-red-500/20' }}">
                                                {{ $transaction->type == 'income' ? 'Entrada' : 'Saída' }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-right">
                                            <span class="text-sm font-bold {{ $transaction->type == 'income' ? 'text-green-500' : 'text-red-500' }}">
                                                {{ $transaction->type == 'income' ? '+' : '-' }} R$ {{ number_format($transaction->amount, 2, ',', '.') }}
                                            </span>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="4" class="px-6 py-12 text-center text-gray-500 italic">Nenhuma transação encontrada</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Right: Quick Action & Commissions Summary -->
                <div class="space-y-6">
                    <!-- Quick New Transaction -->
                    <div class="bg-gray-900 shadow-2xl rounded-3xl p-6 border border-gray-800">
                        <h3 class="text-md font-bold text-white mb-6 flex items-center gap-2 uppercase tracking-tighter">
                            <svg class="w-5 h-5 text-gold" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                            Novo Lançamento
                        </h3>
                        <form action="{{ route('finance.store') }}" method="POST" class="space-y-4">
                            @csrf
                            <div>
                                <label class="text-[10px] text-gray-500 uppercase font-black tracking-widest mb-2 block">Tipo de Movimento</label>
                                <select name="type" class="w-full bg-black border-gray-800 rounded-xl text-white focus:ring-gold focus:border-gold text-sm transition-all">
                                    <option value="income">Receita (Entrada)</option>
                                    <option value="expense">Despesa (Saída)</option>
                                </select>
                            </div>
                            <div>
                                <label class="text-[10px] text-gray-500 uppercase font-black tracking-widest mb-2 block">O que comprou/recebeu?</label>
                                <input type="text" name="description" placeholder="Ex: Venda de Produto" class="w-full bg-black border-gray-800 rounded-xl text-white focus:ring-gold focus:border-gold text-sm transition-all">
                            </div>
                            <div>
                                <label class="text-[10px] text-gray-500 uppercase font-black tracking-widest mb-2 block">Valor Total</label>
                                <div class="relative">
                                    <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gold font-bold">R$</span>
                                    <input type="number" step="0.01" name="amount" placeholder="0,00" class="w-full bg-black border-gray-800 rounded-xl text-white pl-12 focus:ring-gold focus:border-gold text-sm transition-all font-bold">
                                </div>
                            </div>
                            <div>
                                <label class="text-[10px] text-gray-500 uppercase font-black tracking-widest mb-2 block">Data da Transação</label>
                                <input type="date" name="transaction_date" value="{{ date('Y-m-d') }}" class="w-full bg-black border-gray-800 rounded-xl text-white focus:ring-gold focus:border-gold text-sm transition-all">
                            </div>
                            <button type="submit" class="w-full bg-gold hover:bg-gold-dark text-black font-black py-4 rounded-xl transition-all shadow-lg shadow-gold/10 uppercase tracking-widest text-xs">
                                Registrar Movimento
                            </button>
                        </form>
                    </div>

                    <!-- Simplified Commission Summary (Quick View) -->
                    <div class="bg-gray-900/40 p-6 rounded-3xl border border-gold/10">
                        <h3 class="text-gray-400 text-xs font-bold uppercase tracking-widest mb-6">Comissões Pendentes</h3>
                        <div class="space-y-4">
                            @foreach($commissions as $comm)
                            <div class="flex items-center justify-between border-b border-gray-800 pb-3 last:border-0 last:pb-0">
                                <span class="text-sm font-medium text-gray-300">{{ $comm['name'] }}</span>
                                <span class="bg-red-500/10 text-red-500 text-[10px] font-black px-2 py-1 rounded-full">R$ {{ number_format($comm['pending'], 2, ',', '.') }}</span>
                            </div>
                            @endforeach
                        </div>
                        <a href="#comissoes-detalhado" class="text-gold text-[10px] font-black uppercase mt-6 block hover:underline">Ver Gestão Completa ↓</a>
                    </div>
                </div>
            </div>

            <!-- Detailed Commission Management Section -->
            <div id="comissoes-detalhado" class="mt-12 pt-12 border-t border-gray-800">
                <div class="mb-10 text-center">
                    <h3 class="text-3xl font-luxury text-white">Gestão <span class="text-gold">Pró</span> de Comissões</h3>
                    <p class="text-gray-500 text-xs uppercase tracking-[0.3em] font-bold mt-2">Liquidação e histórico de profissionais</p>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    @foreach($commissions as $comm)
                    <div class="group bg-[#111112] border border-gray-800 p-8 rounded-[2rem] transition-all hover:border-gold/30 hover:-translate-y-2 relative overflow-hidden">
                        <div class="absolute top-0 right-0 p-4 opacity-5 group-hover:opacity-10 transition-opacity">
                            <svg class="w-16 h-16 text-gold" fill="currentColor" viewBox="0 0 20 20"><path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"></path><path fill-rule="evenodd" d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3zm-3 4a1 1 0 100 2h.01a1 1 0 100-2H7zm3 0a1 1 0 100 2h3a1 1 0 100-2h-3z" clip-rule="evenodd"></path></svg>
                        </div>
                        
                        <div class="flex items-center gap-4 mb-8">
                            <div class="w-14 h-14 bg-gradient-to-br from-gold to-gold-dark rounded-2xl flex items-center justify-center text-black font-black text-xl shadow-lg shadow-gold/20">
                                {{ substr($comm['name'], 0, 1) }}
                            </div>
                            <div>
                                <h4 class="text-xl font-bold text-white tracking-tight">{{ $comm['name'] }}</h4>
                                <div class="flex items-center gap-1.5">
                                    <span class="w-2 h-2 rounded-full bg-green-500"></span>
                                    <p class="text-[10px] text-gray-500 uppercase font-black">Profissional Ativo</p>
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4 mb-8">
                            <div class="bg-black/40 p-4 rounded-xl border border-gray-800/50">
                                <p class="text-[9px] text-gray-500 uppercase font-black mb-1">A Pagar</p>
                                <p class="text-xl font-bold text-red-500 tracking-tighter">R$ {{ number_format($comm['pending'], 2, ',', '.') }}</p>
                            </div>
                            <div class="bg-black/40 p-4 rounded-xl border border-gray-800/50">
                                <p class="text-[9px] text-gray-500 uppercase font-black mb-1">Total Pago</p>
                                <p class="text-xl font-bold text-green-600 tracking-tighter">R$ {{ number_format($comm['paid'], 2, ',', '.') }}</p>
                            </div>
                        </div>

                        @if($comm['pending'] > 0)
                            <form action="{{ route('finance.pay-commissions', $comm['id']) }}" method="POST">
                                @csrf
                                <button type="submit" class="w-full bg-white text-black font-black py-4 rounded-2xl hover:bg-gold transition-all uppercase tracking-widest text-[11px] flex items-center justify-center gap-2 group/btn shadow-xl">
                                    <svg class="w-4 h-4 transition-transform group-hover/btn:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                                    Liquidar Ganhos
                                </button>
                            </form>
                        @else
                            <div class="w-full bg-gray-900/50 text-gray-600 font-bold py-4 rounded-2xl text-[11px] text-center border border-gray-800 uppercase tracking-widest">
                                Pagamento em dia
                            </div>
                        @endif

                        <div class="mt-6 pt-6 border-t border-gray-800 flex items-center justify-between">
                            <span class="text-[10px] text-gray-500 font-bold uppercase tracking-widest">Total Acumulado histórico:</span>
                            <span class="text-sm font-black text-gold">R$ {{ number_format($comm['total'], 2, ',', '.') }}</span>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

        </div>
    </div>

    <!-- Import Modal -->
    <div id="importModal" class="hidden fixed inset-0 bg-black/80 backdrop-blur-sm z-50 flex items-center justify-center p-4">
        <div class="bg-gray-900 border border-gray-800 p-8 rounded-[2rem] max-w-md w-full relative">
            <button onclick="document.getElementById('importModal').classList.add('hidden')" class="absolute top-6 right-6 text-gray-500 hover:text-white">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
            <h3 class="text-2xl font-luxury text-white mb-4">Importar <span class="text-gold">Dados</span></h3>
            <p class="text-gray-500 text-xs mb-6 uppercase tracking-widest font-bold">Formato CSV (Data;Descrição;Tipo;Valor)</p>
            
            <form action="{{ route('finance.import') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                @csrf
                <div class="border-2 border-dashed border-gray-800 rounded-2xl p-8 text-center hover:border-gold/50 cursor-pointer transition-all">
                    <input type="file" name="csv_file" class="hidden" id="csvFileInput" onchange="document.getElementById('fileName').innerText = this.files[0].name">
                    <label for="csvFileInput" class="cursor-pointer">
                        <svg class="w-12 h-12 text-gray-700 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path></svg>
                        <p id="fileName" class="text-sm text-gray-400 font-bold">Clique para selecionar sua planilha</p>
                    </label>
                </div>
                <button type="submit" class="w-full bg-gold text-black font-black py-4 rounded-xl uppercase tracking-widest text-xs shadow-lg shadow-gold/10">
                    Processar Migração
                </button>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const ctxFlow = document.getElementById('flowChart').getContext('2d');
        const ctxDist = document.getElementById('distributionChart').getContext('2d');

        const dates = @json($chartData->keys());
        const incomes = @json($chartData->map(fn($day) => $day->where('type', 'income')->sum('total'))->values());
        const expenses = @json($chartData->map(fn($day) => $day->where('type', 'expense')->sum('total'))->values());

        new Chart(ctxFlow, {
            type: 'line',
            data: {
                labels: dates,
                datasets: [
                    {
                        label: 'Entradas',
                        data: incomes,
                        borderColor: '#10b981',
                        backgroundColor: 'rgba(16, 185, 129, 0.1)',
                        fill: true,
                        tension: 0.4
                    },
                    {
                        label: 'Saídas',
                        data: expenses,
                        borderColor: '#ef4444',
                        backgroundColor: 'rgba(239, 68, 68, 0.1)',
                        fill: true,
                        tension: 0.4
                    }
                ]
            },
            options: {
                plugins: { legend: { display: false } },
                scales: { y: { display: false }, x: { grid: { display: false }, ticks: { color: '#4b5563', font: { size: 10 } } } }
            }
        });

        new Chart(ctxDist, {
            type: 'doughnut',
            data: {
                labels: ['Receitas', 'Despesas'],
                datasets: [{
                    data: [{{ $totalIncome }}, {{ $totalExpense }}],
                    backgroundColor: ['#D4AF37', '#1f1f23'],
                    borderWidth: 0
                }]
            },
            options: {
                plugins: { legend: { position: 'bottom', labels: { color: '#9ca3af', font: { size: 10, weight: 'bold' } } } },
                cutout: '80%'
            }
        });
    </script>
    
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@700&family=Outfit:wght@300;400;700;900&display=swap');
        
        body {
            font-family: 'Outfit', sans-serif;
        }
        
        .font-luxury {
            font-family: 'Cormorant Garamond', serif;
        }
        
        .text-gold { color: #D4AF37; }
        .bg-gold { background-color: #D4AF37; }
        .bg-gold-dark { background-color: #B8860B; }
        .border-gold { border-color: #D4AF37; }

        ::-webkit-scrollbar { width: 8px; }
        ::-webkit-scrollbar-track { background: #0a0a0b; }
        ::-webkit-scrollbar-thumb { background: #1f1f23; border-radius: 10px; }
        ::-webkit-scrollbar-thumb:hover { background: #D4AF37; }
    </style>
</x-app-layout>
