<?php

declare(strict_types=1);

$config = require __DIR__ . '/config/app.php';

date_default_timezone_set($config['timezone']);

$isHttps = isset($_SERVER['HTTPS'])
    && $_SERVER['HTTPS'] !== ''
    && $_SERVER['HTTPS'] !== 'off';

session_name($config['session_name']);

ini_set('session.use_strict_mode', '1');
ini_set('session.use_only_cookies', '1');
ini_set('session.use_trans_sid', '0');

session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'secure' => $isHttps,
    'httponly' => true,
    'samesite' => 'Lax',
]);

session_start();

require_once __DIR__ . '/core/Database.php';
require_once __DIR__ . '/core/Security.php';
require_once __DIR__ . '/core/Router.php';
require_once __DIR__ . '/core/Controller.php';
require_once __DIR__ . '/core/Auth.php';

require_once __DIR__ . '/app/Models/Utilizador.php';
require_once __DIR__ . '/app/Models/Denuncia.php';
require_once __DIR__ . '/app/Models/Ocorrencia.php';
require_once __DIR__ . '/app/Models/Organizacao.php';
require_once __DIR__ . '/app/Controllers/AuthController.php';
require_once __DIR__ . '/app/Controllers/DashboardAdminController.php';
require_once __DIR__ . '/app/Controllers/DashboardCidadaoController.php';
require_once __DIR__ . '/app/Controllers/DenunciaController.php';
require_once __DIR__ . '/app/Controllers/OcorrenciaController.php';
require_once __DIR__ . '/app/Controllers/OrganizacaoController.php';
require_once __DIR__ . '/app/Controllers/PaginaInicialController.php';
require_once __DIR__ . '/app/Controllers/AdminDenunciaController.php';
$router = new Router();

$authController = new AuthController();
$dashboardAdminController = new DashboardAdminController();
$dashboardCidadaoController = new DashboardCidadaoController();
$denunciaController = new DenunciaController();
$ocorrenciaController = new OcorrenciaController();
$organizacaoController = new OrganizacaoController();
$paginaInicialController = new PaginaInicialController();
$adminDenunciaController = new AdminDenunciaController();


// Página inicial
$router->get('/', function () use ($config): void {
    $destination = Auth::check() ? '/dashboard' : '/login';

    header(
        'Location: ' . rtrim($config['base_url'], '/') . $destination,
        true,
        302
    );

    exit;
});


 // Painel do cidadão — rota principal
$router->get(
    '/dashboard',
    [$dashboardCidadaoController, 'index']
);

// Painel do cidadão — rota utilizada após o login
$router->get(
    '/cidadao/dashboard',
    [$dashboardCidadaoController, 'index']
);


// Painel do administrador
$router->get(
    '/admin/dashboard',
    [$dashboardAdminController, 'index']
);

// Registo público de cidadãos
$router->get('/registo', [$authController, 'mostrarRegisto']);
$router->post('/registo', [$authController, 'registar']);

// Autenticação
$router->get('/login', [$authController, 'mostrarLogin']);
$router->post('/login', [$authController, 'entrar']);
$router->post('/logout', [$authController, 'sair']);


// Módulo de denúncias
$router->get('/denuncias', [$denunciaController, 'index']);

$router->get(
    '/denuncias/criar',
    [$denunciaController, 'criar']
);

$router->post(
    '/denuncias/criar',
    [$denunciaController, 'criar']
);

$router->get(
    '/denuncias/detalhes',
    [$denunciaController, 'detalhes']
);


// Gestão de organizações
$router->get(
    '/admin/organizacoes',
    [$organizacaoController, 'index']
);

$router->get(
    '/admin/organizacoes/criar',
    [$organizacaoController, 'criar']
);

$router->post(
    '/admin/organizacoes/criar',
    [$organizacaoController, 'criar']
);

$router->get(
    '/admin/organizacoes/editar',
    [$organizacaoController, 'editar']
);

$router->post(
    '/admin/organizacoes/editar',
    [$organizacaoController, 'editar']
);

$router->post(
    '/admin/organizacoes/estado',
    [$organizacaoController, 'alternarEstado']
);

// Gestão de ocorrências
$router->get(
    '/ocorrencias',
    [$ocorrenciaController, 'index']
);

$router->get(
    '/ocorrencias/show',
    [$ocorrenciaController, 'show']
);

$router->post(
    '/ocorrencias/estado',
    [$ocorrenciaController, 'updateEstado']
);


// Página inicial pública do SINC
$router->get(
    '/',
    [$paginaInicialController, 'index']
);


$router->get(
    '/admin/denuncias',
    [$adminDenunciaController, 'index']
);

$router->post(
    '/admin/denuncias/estado',
    [$adminDenunciaController, 'alterarEstado']
);








/*
 * Despachar o pedido e registar erros não tratados.
 */
try {
    $router->dispatch($config['base_url']);
} catch (Throwable $exception) {
    error_log(
        '[SINC] Exceção não tratada'
        . ' | Tipo: ' . get_class($exception)
        . ' | Mensagem: ' . $exception->getMessage()
        . ' | Ficheiro: ' . $exception->getFile()
        . ' | Linha: ' . $exception->getLine()
    );

    http_response_code(500);

    echo '<!DOCTYPE html>';
    echo '<html lang="pt">';
    echo '<head>';
    echo '<meta charset="UTF-8">';
    echo '<meta name="viewport" content="width=device-width, initial-scale=1">';
    echo '<title>SINC | Erro</title>';
    echo '</head>';
    echo '<body style="font-family:Arial,sans-serif;padding:40px">';
    echo '<h1>Ocorreu um erro inesperado.</h1>';
    echo '<p>Tenta novamente. Se o problema persistir, verifica o registo '
        . 'de erros do PHP.</p>';
    echo '</body>';
    echo '</html>';
}
