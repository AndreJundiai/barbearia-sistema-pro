<?php

namespace App\Http\Controllers;

use App\Services\AI\ErrorVerificationService;
use App\Services\AI\CorrectionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class AiCorrectorController extends Controller
{
    public function brainstorm(Request $request, \App\Services\AI\ArchitectService $architect)
    {
        $idea = $request->input('idea');
        $suggestion = null;

        if ($idea) {
            $suggestion = $architect->suggestModule($idea);
        }

        return view('ai_corrector.dashboard', [
            'errors' => (new \App\Services\AI\ErrorVerificationService())->getLatestErrors(),
            'apiKeySet' => !empty(config('ai_corrector.api_key')),
            'architectSuggestion' => $suggestion,
            'activeTab' => 'architect'
        ]);
    }

    public function createModule(Request $request)
    {
        $files = $request->input('files', []);
        
        if (empty($files)) {
            \Illuminate\Support\Facades\Log::error("createModule chamado sem arquivos.");
            return back()->with('error', "Nenhum arquivo foi recebido para criação.");
        }

        $created = [];

        foreach ($files as $file) {
            $path = base_path($file['path']);
            $directory = dirname($path);

            if (!File::isDirectory($directory)) {
                File::makeDirectory($directory, 0755, true);
            }

            File::put($path, $file['content']);
            $created[] = $file['path'];
            \Illuminate\Support\Facades\Log::info("Arquivo criado: " . $path);
        }

        return back()->with('status', "Módulo criado com sucesso! Arquivos gerados: " . implode(', ', $created));
    }

    public function index(ErrorVerificationService $verifier)
    {
        $errors = $verifier->getLatestErrors();
        $apiKeySet = !empty(config('ai_corrector.api_key'));

        return view('ai_corrector.dashboard', compact('errors', 'apiKeySet'));
    }

    public function analyze(Request $request, CorrectionService $corrector)
    {
        $rawPath = $request->input('file');
        $errorContext = $request->input('error');

        // Normalização de caminho para Windows/Encoding
        // Tenta capturar o caminho a partir de marcos conhecidos (vendor ou pastas do app)
        if (preg_match('#(?:^|/|\\\\)(vendor|app|config|database|routes|resources|public)[/\\\\].*#i', $rawPath, $pathMatches)) {
            $relativePath = $pathMatches[0];
            $filePath = base_path(ltrim($relativePath, '/\\'));
        } else {
            $filePath = $rawPath;
        }

        if (!File::exists($filePath)) {
            return back()->with('error', "Arquivo não encontrado:\n$filePath\n\nIsso pode ocorrer devido ao encoding do Windows. Tente abrir o arquivo manualmente.");
        }

        $content = File::get($filePath);
        
        // Se for um arquivo do vendor, avisamos que não é recomendável editar
        $isVendor = str_contains($filePath, 'vendor');
        
        $suggestion = $corrector->getFixSuggestion($errorContext, $content, $filePath);

        return view('ai_corrector.dashboard', [
            'errors' => [],
            'apiKeySet' => true,
            'suggestion' => $suggestion,
            'targetFile' => $filePath,
            'originalContent' => $content,
            'isVendor' => $isVendor
        ]);
    }

    public function apply(Request $request)
    {
        $filePath = $request->input('file');
        $content = $request->input('content');

        if (File::exists($filePath)) {
            File::put($filePath, $content);
            return redirect()->route('ai-corrector.index')->with('status', 'Correção aplicada com sucesso!');
        }

        return back()->with('error', 'Falha ao aplicar correção.');
    }

    public function updateKey(Request $request)
    {
        $request->validate([
            'api_key' => 'required|string'
        ]);

        $key = $request->input('api_key');
        $envPath = base_path('.env');

        if (File::exists($envPath)) {
            $content = File::get($envPath);
            
            if (str_contains($content, 'GEMINI_API_KEY=')) {
                $content = preg_replace('/GEMINI_API_KEY=.*/', 'GEMINI_API_KEY=' . $key, $content);
            } else {
                $content .= "\nGEMINI_API_KEY=" . $key;
            }

            File::put($envPath, $content);
            
            return back()->with('status', 'Chave API atualizada com sucesso!');
        }

        return back()->with('error', 'Arquivo .env não encontrado.');
    }
}
