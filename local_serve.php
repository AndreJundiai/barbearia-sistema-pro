<?php

/**
 * Script de inicialização customizado para contornar erro de encoding no Windows (caractere "é").
 * Este script emula o comportamento do servidor interno do PHP sem depender do router do Laravel que falha com paths especiais.
 */

$uri = urldecode(
    parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH)
);

// Tenta servir arquivos estáticos do diretório /public
$publicFile = __DIR__ . '/public' . $uri;

if ($uri !== '/' && file_exists($publicFile) && !is_dir($publicFile)) {
    return false;
}

require_once __DIR__ . '/public/index.php';

