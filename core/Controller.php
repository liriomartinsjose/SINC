<?php

declare(strict_types=1);

abstract class Controller
{
    protected function render(
        string $view,
        array $data = [],
        string $layout = 'guest'
    ): void {
        $viewFile = __DIR__ . '/../app/Views/' . $view . '.php';
        $layoutFile = __DIR__ . '/../app/Views/layouts/' . $layout . '.php';

        if (!is_file($viewFile) || !is_file($layoutFile)) {
            http_response_code(500);
            throw new RuntimeException(
                'A vista ou o layout solicitado não existe.'
            );
        }

        $config = require __DIR__ . '/../config/app.php';

        $data['baseUrl'] = rtrim($config['base_url'], '/');

        extract($data, EXTR_SKIP);

        ob_start();
        require $viewFile;
        $content = ob_get_clean();

        require $layoutFile;
    }

    protected function redirect(string $path): void
    {
        $config = require __DIR__ . '/../config/app.php';

        $url = rtrim($config['base_url'], '/')
            . '/'
            . ltrim($path, '/');

        header('Location: ' . $url, true, 303);
        exit;
    }
}


