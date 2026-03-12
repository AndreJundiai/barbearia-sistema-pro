<?php

namespace App\Services\AI;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Process;

class ErrorVerificationService
{
    public function getLatestErrors(): array
    {
        $logPath = config('ai_corrector.log_path');
        
        if (!File::exists($logPath)) {
            return [];
        }

        $content = File::get($logPath);
        $lines = explode("\n", $content);
        $lastLines = array_slice($lines, -200); // Get last 200 lines

        $errors = [];
        foreach ($lastLines as $line) {
            if (str_contains($line, '.ERROR:') || str_contains($line, '.CRITICAL:')) {
                $errors[] = $line;
            }
        }

        return array_unique($errors);
    }

    public function runTests(): string
    {
        $result = Process::run('php artisan test --stop-on-failure');
        return $result->output() . $result->errorOutput();
    }

    public function checkSyntax(string $filePath): bool
    {
        $result = Process::run("php -l $filePath");
        return $result->successful();
    }
}
