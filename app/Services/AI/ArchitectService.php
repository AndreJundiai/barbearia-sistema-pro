<?php

namespace App\Services\AI;

use Illuminate\Support\Facades\Http;

class ArchitectService
{
    protected string $apiKey;

    public function __construct()
    {
        $this->apiKey = config('ai_corrector.api_key') ?? '';
    }

    public function suggestModule(string $description): ?array
    {
        if (empty($this->apiKey) || $this->apiKey === 'INSIRA_SUA_CHAVE_AQUI') {
            return ['error' => "Erro: Chave de API Gemini não configurada."];
        }

        $prompt = "Você é um Product Owner e Arquiteto de Sistemas sênior, especialista em sistemas para Barbearias de Luxo.\n" .
                  "O usuário quer criar um novo módulo: '$description'.\n\n" .
                  "Sua tarefa é gerar a estrutura completa para o 'Melhor App de Barbearia do Mundo'. Gere um JSON com a seguinte estrutura:\n" .
                  "{\n" .
                  "  'module_name': 'Nome',\n" .
                  "  'vision': 'Visão premium',\n" .
                  "  'features': ['f1', 'f2', 'f3'],\n" .
                  "  'files': [\n" .
                  "    {'path': 'database/migrations/YYYY_MM_DD_HHMMSS_create_table_name.php', 'content': '... código completo da migration ...'},\n" .
                  "    {'path': 'app/Http/Controllers/ModuleNameController.php', 'content': '... código completo do controller ...'},\n" .
                  "    {'path': 'resources/views/module/index.blade.php', 'content': '... código completo da view premium ...'}\n" .
                  "  ],\n" .
                  "  'ui_suggestion': 'Descrição visual'\n" .
                  "}\n" .
                  "IMPORTANTE: Retorne APENAS o JSON puro. O código deve ser funcional e seguir o padrão Laravel 11.";

        // Lista de modelos disponíveis conforme nossa verificação anterior
        $models = ['gemini-2.0-flash', 'gemini-flash-latest', 'gemini-pro-latest'];
        $lastError = "";

        foreach ($models as $model) {
            $response = Http::withoutVerifying()->withHeaders([
                'Content-Type' => 'application/json',
            ])->post("https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$this->apiKey}", [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => $prompt]
                        ]
                    ]
                ]
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $text = $data['candidates'][0]['content']['parts'][0]['text'] ?? null;
                
                // Tenta limpar o JSON se a IA incluiu markdown
                $text = preg_replace('/^```json\s+/i', '', $text);
                $text = preg_replace('/^```\s+/i', '', $text);
                $text = preg_replace('/\s+```$/i', '', $text);

                return json_decode($text, true) ?: ['error' => "Falha ao processar sugestão da IA.", 'raw' => $text];
            }
            
            $lastError = $response->body();
            \Illuminate\Support\Facades\Log::warning("Architect AI ($model) falhou: " . $lastError);
        }

        return ['error' => "Falha ao conectar à API Gemini (Todas as tentativas): " . $lastError];
    }
}
