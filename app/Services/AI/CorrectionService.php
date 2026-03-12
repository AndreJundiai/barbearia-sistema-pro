<?php

namespace App\Services\AI;

use Illuminate\Support\Facades\Http;

class CorrectionService
{
    protected string $apiKey;

    public function __construct()
    {
        $this->apiKey = config('ai_corrector.api_key') ?? '';
    }

    public function getFixSuggestion(string $errorContext, string $fileContent, string $filePath): ?string
    {
        if (empty($this->apiKey) || $this->apiKey === 'INSIRA_SUA_CHAVE_AQUI') {
            return "Erro: Chave de API Gemini não configurada ou inválida. Por favor, adicione uma GEMINI_API_KEY válida ao seu arquivo .env.";
        }

        $prompt = "Você é um desenvolvedor sênior PHP/Laravel. Analise o seguinte erro e o código, e retorne APENAS o código corrigido para o arquivo. Não inclua explicações, apenas o conteúdo completo do arquivo corrigido.\n\n" .
                  "Contexto do Erro:\n$errorContext\n\n" .
                  "Caminho do Arquivo: $filePath\n\n" .
                  "Código Original:\n$fileContent";

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
                $suggestedCode = $data['candidates'][0]['content']['parts'][0]['text'] ?? null;
                
                // Limpa o markdown se a IA o incluiu
                if ($suggestedCode) {
                    $suggestedCode = preg_replace('/^```php\s+/i', '', $suggestedCode);
                    $suggestedCode = preg_replace('/^```\s+/i', '', $suggestedCode);
                    $suggestedCode = preg_replace('/\s+```$/i', '', $suggestedCode);
                }

                return $suggestedCode;
            }
            
            $lastError = $response->body();
            \Illuminate\Support\Facades\Log::warning("Gemini AI ($model) falhou: " . $lastError);
        }

        return "Erro: Falha ao conectar à API Gemini. Todas as tentativas falharam. Último erro: " . $lastError;
    }
}
