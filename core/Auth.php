<?php

declare(strict_types=1);

final class Auth
{
    public static function id(): ?int
    {
        $id = $_SESSION['user_id'] ?? null;

        return is_numeric($id) ? (int) $id : null;
    }

    public static function check(): bool
    {
        return self::id() !== null;
    }

    public static function user(): ?array
    {
        $id = self::id();

        if ($id === null) {
            return null;
        }

        $model = new Utilizador();
        $user = $model->findById($id);

        if ($user === null) {
            self::logout();

            return null;
        }

        return $user;
    }

    public static function login(array $user): void
    {
        session_regenerate_id(true);

        $_SESSION['user_id'] = (int) $user['id'];
        $_SESSION['authenticated_at'] = time();

        unset($_SESSION['_csrf_token']);
    }

    public static function logout(): void
    {
        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();

            setcookie(session_name(), '', [
                'expires' => time() - 42000,
                'path' => $params['path'],
                'domain' => $params['domain'],
                'secure' => $params['secure'],
                'httponly' => $params['httponly'],
                'samesite' => 'Lax',
            ]);
        }

        session_destroy();
    }

    public static function requireAuth(): void
    {
        if (!self::check() || self::user() === null) {
            $config = require __DIR__ . '/../config/app.php';

            header(
                'Location: ' . rtrim($config['base_url'], '/') . '/login'
            );

            exit;
        }
    }


    public static function requireRole(int ...$papelIds): array
{
    $user = self::user();

    if ($user === null) {
        $config = require __DIR__ . '/../config/app.php';

        header(
            'Location: ' . rtrim($config['base_url'], '/') . '/login',
            true,
            303
        );
        exit;
    }

    $papelId = (int) ($user['papel_id'] ?? 0);

    if (!in_array($papelId, $papelIds, true)) {
        http_response_code(403);
        echo 'Não tens permissão para aceder a esta área.';
        exit;
    }

    return $user;
}

    public static function requireGuest(): void
    {
        if (self::check() && self::user() !== null) {
            $config = require __DIR__ . '/../config/app.php';

            header(
                'Location: ' . rtrim($config['base_url'], '/') . '/dashboard'
            );

            exit;
        }
    }
}

