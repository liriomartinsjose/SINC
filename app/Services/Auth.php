<?php
/**
 * Serviço de autenticação — gere sessões e verificação de utilizador.
 */

class Auth
{
    public static function init(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_set_cookie_params([
                'httponly'  => true,
                'samesite'  => 'Lax',
                'secure'    => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
            ]);
            session_start();
        }
    }

    public static function login(int $id, string $nome, string $email, string $papel): void
    {
        session_regenerate_id(true);
        $_SESSION['user'] = [
            'id'    => $id,
            'nome'  => $nome,
            'email' => $email,
            'papel' => $papel,
        ];
    }

    public static function logout(): void
    {
        $_SESSION = [];
        session_destroy();
    }

    public static function check(): bool
    {
        return isset($_SESSION['user']);
    }

    public static function user(): ?array
    {
        return $_SESSION['user'] ?? null;
    }

    public static function id(): ?int
    {
        return $_SESSION['user']['id'] ?? null;
    }

    public static function isAdmin(): bool
    {
        return ($_SESSION['user']['papel'] ?? '') === 'admin';
    }
}
