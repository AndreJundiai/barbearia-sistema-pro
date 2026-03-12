<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Chave da API Gemini
    |--------------------------------------------------------------------------
    |
    | Esta chave é usada para interagir com a API Google Gemini para
    | análise de código e sugestões de correção.
    |
    */
    'api_key' => env('GEMINI_API_KEY'),

    /*
    |--------------------------------------------------------------------------
    | Caminhos de Lógica
    |--------------------------------------------------------------------------
    |
    | Os caminhos e logs que o módulo irá monitorar em busca de erros.
    |
    */
    'log_path' => storage_path('logs/laravel.log'),
    
    'verification_paths' => [
        app_path(),
        resource_path('views'),
        base_path('routes'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Configurações de Auto-Correção
    |--------------------------------------------------------------------------
    |
    | Configurações sobre como a IA deve se comportar ao sugerir correções.
    |
    */
    'auto_apply' => false, // Defina como true para aplicar correções sem confirmação manual (Cuidado!)
];
