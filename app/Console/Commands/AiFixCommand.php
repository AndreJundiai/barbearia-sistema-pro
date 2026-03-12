<?php

namespace App\Console\Commands;

use App\Services\AI\CorrectionService;
use App\Services\AI\ErrorVerificationService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class AiFixCommand extends Command
{
    protected $signature = 'ai:fix {--file= : Specific file to check}';
    protected $description = 'Detects errors and uses AI to suggest fixes.';

    public function handle(ErrorVerificationService $verifier, CorrectionService $corrector)
    {
        $this->info('🚀 Iniciando verificação de erros...');

        $errors = $verifier->getLatestErrors();
        
        if (empty($errors)) {
            $this->info('✅ Nenhum erro recente encontrado nos logs.');
        } else {
            $this->warn('❌ Erros encontrados nos logs:');
            foreach ($errors as $error) {
                $this->line("- $error");
            }
        }

        $targetFile = $this->option('file');
        
        if ($targetFile) {
            $this->processFile($targetFile, $corrector, "Manual check requested for this file.");
            return;
        }

        // Tenta identificar arquivos a partir dos erros
        foreach ($errors as $error) {
            // Regex mais flexível para capturar o caminho do arquivo
            if (preg_match('/(?:in|at)\s+(.*?\.php)(?::| on line )(\d+)/i', $error, $matches)) {
                $rawPath = $matches[1];
                
                // Normalização para Windows/Encoding: extrai o caminho a partir das pastas padrão do Laravel
                // Lida com caracteres corrompidos no caminho (ex: André -> Andr)
                if (preg_match('#(?:^|/|\\\\)(app|config|database|routes|resources|public)[/\\\\].*#i', $rawPath, $pathMatches)) {
                    $relativePath = $pathMatches[0];
                    $path = base_path(ltrim($relativePath, '/\\'));
                } else {
                    $path = $rawPath;
                }

                // Evita mexer em vendor
                if (str_contains($path, 'vendor')) {
                    continue;
                }

                if (File::exists($path)) {
                    $this->processFile($path, $corrector, $error);
                }
            }
        }
    }

    protected function processFile(string $filePath, CorrectionService $corrector, string $errorContext)
    {
        $this->info("🔍 Analisando arquivo: $filePath");
        $content = File::get($filePath);

        $this->info("🤖 Solicitando correção à IA...");
        
        try {
            $suggestion = $corrector->getFixSuggestion($errorContext, $content, $filePath);
        } catch (\Throwable $e) {
            $this->error("❌ Falha crítica ao processar arquivo: " . $e->getMessage());
            return;
        }

        if (str_starts_with($suggestion, 'Error:') || str_starts_with($suggestion, 'Erro:')) {
            $this->error("❌ A IA retornou um erro em vez de código:");
            $this->line($suggestion);
            return;
        }

        // Validação básica se parece código PHP
        if (!str_contains($suggestion, '<?php')) {
            $this->error("❌ A sugestão da IA não parece ser código PHP válido.");
            $this->line(substr($suggestion, 0, 200) . '...');
            return;
        }

        $this->line("\n--- Sugestão de Correção ---");
        $this->line($suggestion);
        $this->line("----------------------------\n");

        if ($this->confirm("Deseja aplicar esta correção em $filePath?", false)) {
            File::put($filePath, $suggestion);
            $this->info("✅ Arquivo corrigido!");
        } else {
            $this->info("⏭️ Correção ignorada.");
        }
    }
}
