<?php

namespace Tests\Feature;

use App\Console\Commands\AiFixCommand;
use App\Services\AI\CorrectionService;
use App\Services\AI\ErrorVerificationService;
use Illuminate\Support\Facades\File;
use Mockery\MockInterface;
use Tests\TestCase;

class AiFixCommandTest extends TestCase
{
    public function test_it_proposes_a_fix_when_error_is_found()
    {
        $filePath = app_path('Http/Controllers/TestController.php');
        $error = "Error: syntax error, unexpected token \";\" in $filePath:10";
        $suggestedCode = "<?php\n\nnamespace App\Http\Controllers;\n\nclass TestController {}\n";

        // Mock Verifier
        $this->mock(ErrorVerificationService::class, function (MockInterface $mock) use ($error) {
            $mock->shouldReceive('getLatestErrors')->once()->andReturn([$error]);
        });

        // Mock Corrector
        $this->mock(CorrectionService::class, function (MockInterface $mock) use ($suggestedCode) {
            $mock->shouldReceive('getFixSuggestion')->once()->andReturn($suggestedCode);
        });

        // Mock File System
        File::shouldReceive('exists')->with($filePath)->andReturn(true);
        File::shouldReceive('get')->with($filePath)->andReturn("<?php\n\nnamespace App\Http\Controllers;\n\nclass TestController {} ;\n");
        File::shouldReceive('put')->with($filePath, $suggestedCode)->once();

        $this->artisan('ai:fix')
            ->expectsOutputToContain('❌ Erros encontrados nos logs:')
            ->expectsOutputToContain("🔍 Analisando arquivo: $filePath")
            ->expectsConfirmation("Deseja aplicar esta correção em $filePath?", 'yes')
            ->expectsOutput('✅ Arquivo corrigido!')
            ->assertExitCode(0);
    }
}
