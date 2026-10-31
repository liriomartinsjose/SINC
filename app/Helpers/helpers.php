<?php
/**
 * Funções auxiliares para escape de saída e redireccionamento.
 */

function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

function redirect(string $path): void
{
    header('Location: ' . $path);
    exit;
}

function base_url(string $path = ''): string
{
    $base = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
    return $base . '/' . ltrim($path, '/');
}

function old(string $key, array $data = []): string
{
    return e($data[$key] ?? '');
}

function estadoLabel(string $estado): string
{
    $labels = [
        'pendente'   => 'Pendente',
        'em_analise' => 'Em análise',
        'resolvido'  => 'Resolvido',
        'rejeitado'  => 'Rejeitado',
    ];
    return $labels[$estado] ?? ucfirst($estado);
}
