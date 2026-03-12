<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('AI Corrector - Inteligência de Manutenção') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                
                @if (session('status'))
                    <div class="mb-4 font-medium text-sm text-green-600">
                        {{ session('status') }}
                    </div>
                @endif

                @if (session('error'))
                    <div class="mb-4 font-medium text-sm text-red-600">
                        {{ session('error') }}
                    </div>
                @endif

                <!-- Gerenciamento de Chave API -->
                <div class="mb-8 p-4 bg-gray-50 dark:bg-gray-700 rounded-lg border border-gray-200 dark:border-gray-600">
                    <h3 class="text-sm font-bold mb-3 text-gold uppercase tracking-wider" style="color: #D4AF37;">Configuração da IA</h3>
                    <form action="{{ route('ai-corrector.update-key') }}" method="POST" class="flex gap-4">
                        @csrf
                        <div class="flex-grow">
                            <input type="password" name="api_key" placeholder="Insira sua GEMINI_API_KEY aqui" 
                                   class="w-full bg-white dark:bg-gray-800 border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:border-gold focus:ring focus:ring-gold focus:ring-opacity-50 text-sm"
                                   value="{{ config('ai_corrector.api_key') === 'INSIRA_SUA_CHAVE_AQUI' ? '' : config('ai_corrector.api_key') }}">
                        </div>
                        <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded text-sm transition appearance-none">
                            Salvar Chave
                        </button>
                    </form>
                    <p class="mt-2 text-xs text-gray-500">A chave será salva diretamente no seu arquivo <code>.env</code>.</p>
                </div>

                @unless($apiKeySet && config('ai_corrector.api_key') !== 'INSIRA_SUA_CHAVE_AQUI')
                    <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6" role="alert">
                        <p class="font-bold">Atenção!</p>
                        <p>A chave <code>GEMINI_API_KEY</code> não está configurada corretamente. Adicione-a acima para ativar a análise.</p>
                    </div>
                @endunless

                <!-- Sistema de Abas -->
                <div class="mb-6 border-b border-gray-200 dark:border-gray-700">
                    <ul class="flex flex-wrap -mb-px text-sm font-medium text-center" id="aiTab" role="tablist">
                        <li class="mr-2" role="presentation">
                            <button class="inline-block p-4 border-b-2 rounded-t-lg {{ ($activeTab ?? 'corrector') === 'corrector' ? 'border-indigo-600 text-indigo-600' : 'border-transparent hover:text-gray-600 hover:border-gray-300' }}" 
                                    onclick="window.location.hash = 'corrector'; location.reload();">
                                AI Corrector (Fix)
                            </button>
                        </li>
                        <li class="mr-2" role="presentation">
                            <button class="inline-block p-4 border-b-2 rounded-t-lg {{ ($activeTab ?? 'corrector') === 'architect' ? 'border-gold text-gold' : 'border-transparent hover:text-gray-600 hover:border-gray-300' }}"
                                    onclick="document.getElementById('architect-form').scrollIntoView();">
                                AI Architect (Criar Módulo)
                            </button>
                        </li>
                    </ul>
                </div>

                @if(($activeTab ?? 'corrector') === 'corrector')
                    <!-- Interface Original de Correção -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <!-- Erros Recentes -->
                        <div class="bg-gray-50 dark:bg-gray-700 p-6 rounded-lg border border-gray-200 dark:border-gray-600">
                            <h3 class="text-lg font-bold mb-4 text-gold" style="color: #D4AF37;">Erros Recentes nos Logs</h3>
                            
                            @if(count($errors) > 0)
                                <div class="space-y-4">
                                    @foreach($errors as $error)
                                        @php
                                            preg_match('/(?:in|at)\s+(.*?\.php)(?::| on line )(\d+)/i', $error, $matches);
                                            $rawFile = $matches[1] ?? null;
                                            $displayFile = $rawFile;
                                            if ($rawFile && preg_match('#(?:^|/|\\\\)(vendor|app|config|database|routes|resources|public)[/\\\\].*#i', $rawFile, $shortMatches)) {
                                                $displayFile = $shortMatches[0];
                                            }
                                        @endphp
                                        <div class="p-3 bg-white dark:bg-gray-800 border-l-4 border-red-500 rounded shadow-sm text-xs font-mono">
                                            <p class="text-xs text-red-600 font-bold mb-1">ERRO DETECTADO:</p>
                                            <p class="text-gray-900 dark:text-gray-100 mb-2 break-all">{{ $error }}</p>
                                            
                                            @if($rawFile)
                                                <div class="mt-2 pt-2 border-t border-gray-100 dark:border-gray-700 flex justify-between items-center">
                                                    <span class="text-[10px] text-gray-500 italic">Arquivo: {{ basename($rawFile) }}</span>
                                                    <form action="{{ route('ai-corrector.analyze') }}" method="POST">
                                                        @csrf
                                                        <input type="hidden" name="file" value="{{ $rawFile }}">
                                                        <input type="hidden" name="error" value="{{ $error }}">
                                                        <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-1 px-3 rounded text-[10px] uppercase transition shadow-sm">
                                                            VER SOLUÇÃO IA
                                                        </button>
                                                    </form>
                                                </div>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="flex flex-col items-center justify-center h-64 text-gray-400">
                                    <svg class="w-12 h-12 mb-2 opacity-20" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    <p>Tudo limpo! Nenhum erro recente.</p>
                                </div>
                            @endif
                        </div>

                        <!-- Sugestão da IA -->
                        <div class="bg-gray-50 dark:bg-gray-700 p-6 rounded-lg border border-gray-200 dark:border-gray-600 flex flex-col">
                            <h3 class="text-lg font-bold mb-4 text-gold" style="color: #D4AF37;">Solução Proposta</h3>
                            
                            @if(isset($suggestion))
                                <div class="flex-grow">
                                    <div class="bg-indigo-50 dark:bg-indigo-900/20 p-3 rounded mb-4 border-l-4 border-indigo-500">
                                        <p class="text-xs text-indigo-700 dark:text-indigo-300 font-bold">RECOMENDAÇÃO DA IA PARA:</p>
                                        <p class="text-[10px] font-mono text-gray-600 dark:text-gray-400">{{ $targetFile }}</p>
                                    </div>
                                    
                                    @if(isset($isVendor) && $isVendor)
                                        <div class="mb-4 bg-yellow-100 border-l-4 border-yellow-500 p-3 text-yellow-700 text-xs">
                                            <p class="font-bold">Aviso: Arquivo do Core (Vendor)</p>
                                            <p>Não é recomendado aplicar correções automáticas no <code>vendor</code>.</p>
                                        </div>
                                    @endif

                                    <textarea readonly class="w-full h-[400px] bg-gray-900 text-green-400 p-4 font-mono text-xs rounded border-none shadow-inner resize-none focus:ring-0">{{ $suggestion }}</textarea>
                                </div>
                                
                                <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-600">
                                    @if(!(isset($isVendor) && $isVendor))
                                        <form action="{{ route('ai-corrector.apply') }}" method="POST">
                                            @csrf
                                            <input type="hidden" name="file" value="{{ $targetFile }}">
                                            <input type="hidden" name="content" value="{{ $suggestion }}">
                                            <button type="submit" class="w-full bg-indigo-600 hover:bg-green-600 text-white font-bold py-2 px-4 rounded shadow transition-all text-xs uppercase tracking-widest">
                                                Aplicar Correção
                                            </button>
                                        </form>
                                    @else
                                        <button disabled class="w-full bg-gray-300 text-gray-500 font-bold py-2 px-4 rounded text-xs uppercase cursor-not-allowed">
                                            Edição Bloqueada
                                        </button>
                                    @endif
                                </div>
                            @else
                                <div class="flex flex-col items-center justify-center h-full text-gray-400 opacity-50">
                                    <svg class="w-16 h-16 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                                    <p class="text-sm">Aguardando análise de erro...</p>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif

                <!-- Seção AI Architect -->
                <div id="architect-section" class="mt-8 p-6 bg-gray-50 dark:bg-gray-700 rounded-xl border border-gold/30 shadow-inner">
                    <div class="flex items-center gap-3 mb-6">
                        <div class="p-2 bg-gold/10 rounded-lg">
                            <svg class="w-6 h-6 text-gold" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-gold" style="color: #D4AF37;">AI Architect</h3>
                            <p class="text-xs text-gray-500 italic">"Gere módulos para o melhor App de Barbearia do Mundo"</p>
                        </div>
                    </div>

                    <form action="{{ route('ai-corrector.brainstorm') }}" method="POST" id="architect-form">
                        @csrf
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">O que falta no seu sistema de barbearia?</label>
                            <textarea name="idea" rows="3" class="w-full bg-white dark:bg-gray-800 border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:ring-gold focus:border-gold p-4" 
                                      placeholder="Ex: Quero um módulo de Clube de Fidelidade onde o cliente ganha 1 corte grátis após 10 atendimentos."></textarea>
                        </div>
                        <button type="submit" class="bg-gold hover:bg-yellow-600 text-gray-900 font-bold py-3 px-8 rounded-lg shadow-lg transition-all transform hover:scale-105 uppercase tracking-wider text-sm" style="background-color: #D4AF37;">
                            Arquitetar Novo Módulo
                        </button>
                    </form>

                    @if(isset($architectSuggestion['error']))
                        <div class="mt-4 bg-red-100 border-l-4 border-red-500 p-4 text-red-700 text-xs">
                            <p class="font-bold">Falha na Arquitetura:</p>
                            <p>{{ $architectSuggestion['error'] }}</p>
                            @if(isset($architectSuggestion['raw']))
                                <details class="mt-2">
                                    <summary class="cursor-pointer">Ver detalhes técnicos</summary>
                                    <pre class="mt-2 bg-gray-900 text-gray-300 p-2 rounded overflow-x-auto">{{ $architectSuggestion['raw'] }}</pre>
                                </details>
                            @endif
                        </div>
                    @endif

                    @if(isset($architectSuggestion) && !isset($architectSuggestion['error']))
                        <div class="mt-8 grid grid-cols-1 lg:grid-cols-3 gap-6 animate-fade-in">
                            <div class="lg:col-span-1 space-y-4">
                                <div class="bg-white dark:bg-gray-800 p-4 rounded-lg shadow-sm border-t-4 border-gold">
                                    <h4 class="font-bold text-gold text-sm">{{ $architectSuggestion['module_name'] ?? 'Novo Módulo' }}</h4>
                                    <p class="text-xs text-gray-600 mt-2">{{ $architectSuggestion['vision'] ?? '' }}</p>
                                </div>
                                <div class="bg-white dark:bg-gray-800 p-4 rounded-lg shadow-sm border-l-4 border-indigo-500">
                                    <h5 class="font-bold text-xs mb-2">Funcionalidades Premium:</h5>
                                    <ul class="text-[10px] space-y-1 list-disc list-inside">
                                        @foreach($architectSuggestion['features'] ?? [] as $feature)
                                            <li>{{ $feature }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                                <div class="bg-white dark:bg-gray-800 p-4 rounded-lg shadow-sm">
                                    <h5 class="font-bold text-xs mb-2">Sugestão de Tela:</h5>
                                    <p class="text-[10px] italic text-gray-500">{{ $architectSuggestion['ui_suggestion'] ?? '' }}</p>
                                </div>
                            </div>
                            <div class="lg:col-span-2 space-y-6">
                                <div class="flex items-center justify-between mb-2">
                                    <h5 class="text-gold text-[10px] font-bold uppercase tracking-widest">Estrutura de Código Sugerida</h5>
                                    <form action="{{ route('ai-corrector.create-module') }}" method="POST">
                                        @csrf
                                        @foreach($architectSuggestion['files'] ?? [] as $index => $file)
                                            <input type="hidden" name="files[{{ $index }}][path]" value="{{ $file['path'] }}">
                                            <input type="hidden" name="files[{{ $index }}][content]" value="{{ $file['content'] }}">
                                        @endforeach
                                        <button type="submit" class="bg-green-600 hover:bg-green-500 text-white font-bold py-1 px-4 rounded-full text-[10px] uppercase shadow-lg transition-all flex items-center gap-2">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                                            Injetar Módulo no Sistema
                                        </button>
                                    </form>
                                </div>

                                <div class="space-y-4">
                                    @foreach($architectSuggestion['files'] ?? [] as $file)
                                        <div class="bg-gray-900 rounded-lg shadow-xl overflow-hidden border border-gray-800">
                                            <div class="bg-gray-800 px-4 py-2 border-b border-gray-700 flex justify-between items-center">
                                                <span class="text-[10px] font-mono text-indigo-400">{{ $file['path'] }}</span>
                                                <span class="text-[9px] text-gray-500 uppercase">Laravel File</span>
                                            </div>
                                            <textarea readonly class="w-full h-48 bg-transparent text-gray-300 font-mono text-xs border-none focus:ring-0 p-4">{{ $file['content'] }}</textarea>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endif
                </div>

            </div>
        </div>
    </div>
</x-app-layout>

<style>
    .text-gold { color: #D4AF37; }
</style>
