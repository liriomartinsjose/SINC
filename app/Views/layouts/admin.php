<?php

declare(strict_types=1);

$pageTitle = $pageTitle ?? 'Painel administrativo';
$user = $user ?? [];
$baseUrl = rtrim($baseUrl ?? '/sinc', '/');
$pageStyles = $pageStyles ?? [];
$pageScripts = $pageScripts ?? [];

$e = static fn($value): string =>
    Security::escape((string) ($value ?? ''));

$currentPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$basePath = '/' . trim($baseUrl, '/');

if ($basePath !== '/' && str_starts_with($currentPath, $basePath . '/')) {
    $currentPath = substr($currentPath, strlen($basePath));
} elseif ($currentPath === $basePath) {
    $currentPath = '/';
}

$currentPath = '/' . trim($currentPath, '/');
if ($currentPath === '//') {
    $currentPath = '/';
}

$isActive = static function (string $path) use ($currentPath): bool {
    $path = '/' . trim($path, '/');
    return $currentPath === $path;
};

$isGroupActive = static function (array $paths) use ($currentPath): bool {
    foreach ($paths as $path) {
        $path = '/' . trim($path, '/');

        if ($currentPath === $path || str_starts_with($currentPath, $path . '/')) {
            return true;
        }
    }

    return false;
};

$inicial = mb_strtoupper(
    mb_substr((string) ($user['nome_completo'] ?? 'A'), 0, 1, 'UTF-8'),
    'UTF-8'
);
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#f5f7fa">

    <title><?= $e($pageTitle) ?> | SINC</title>

        <!-- Estilos gerais da administração -->
    <link
        rel="stylesheet"
        href="<?= $e($baseUrl . '/public/assets/css/admin/admin.css') ?>"
    >
    <link rel="stylesheet"
      href="/sinc/public/assets/css/admin/dashboard.css">

    <link rel="stylesheet"
      href="<?= htmlspecialchars(rtrim($baseUrl, '/'), ENT_QUOTES, 'UTF-8') ?>/assets/css/admin-denuncias.css">

    <!-- Estilos adicionais específicos da página -->
    <?php foreach (array_unique($pageStyles) as $stylesheet): ?>
        <link
            rel="stylesheet"
            href="<?= $e(
                $baseUrl . '/' . ltrim((string) $stylesheet, '/')
            ) ?>"
        >
    <?php endforeach; ?>

    <!-- JavaScript administrativo: sidebar e dropdowns -->
    <script
        src="<?= $e($baseUrl . '/public/assets/js/admin/admin.js') ?>"
        defer
    ></script>
</head>

<body class="admin-shell">

    <div class="admin-backdrop" data-sidebar-backdrop></div>

    <aside class="admin-sidebar"
           id="admin-sidebar"
           aria-label="Navegação administrativa">

        <a class="admin-brand"
           href="<?= $e($baseUrl) ?>/admin/dashboard">
            <span class="admin-brand__mark">S</span>
            <span class="admin-brand__text">
                <strong>SINC</strong>
                <small>Inteligência cívica</small>
            </span>
        </a>

        <div class="admin-sidebar__caption">ESPAÇO DE GESTÃO</div>

        <nav class="admin-nav" aria-label="Menu principal">

            <!-- PAINEL -->
            <a class="admin-nav__link <?= $isActive('/admin/dashboard') ? 'is-active' : '' ?>"
               href="<?= $e($baseUrl) ?>/admin/dashboard"
               <?= $isActive('/admin/dashboard') ? 'aria-current="page"' : '' ?>>
                <span class="admin-nav__icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24">
                        <rect x="3" y="3" width="7" height="7" rx="1.5"/>
                        <rect x="14" y="3" width="7" height="7" rx="1.5"/>
                        <rect x="3" y="14" width="7" height="7" rx="1.5"/>
                        <rect x="14" y="14" width="7" height="7" rx="1.5"/>
                    </svg>
                </span>
                <span class="admin-nav__label">Visão geral</span>
            </a>

            <div class="admin-nav__group-label">ADMINISTRAÇÃO</div>

            <!-- UTILIZADORES -->
            <section class="admin-nav__section">
                <button class="admin-nav__toggle"
                        type="button"
                        data-nav-toggle
                        aria-expanded="<?= $isGroupActive(['/admin/utilizadores', '/admin/papeis', '/admin/permissoes']) ? 'true' : 'false' ?>"
                        aria-controls="nav-utilizadores">
                    <span class="admin-nav__icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24">
                            <path d="M16 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                            <circle cx="10" cy="7" r="4"/>
                            <path d="M20 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/>
                        </svg>
                    </span>
                    <span class="admin-nav__label">Utilizadores e acessos</span>
                    <span class="admin-nav__chevron" aria-hidden="true">⌄</span>
                </button>

                <div class="admin-nav__submenu"
                     id="nav-utilizadores"
                     <?= $isGroupActive(['/admin/utilizadores', '/admin/papeis', '/admin/permissoes']) ? '' : 'hidden' ?>>
                    <span class="admin-nav__sublink is-disabled"
                          aria-disabled="true">Listar utilizadores</span>
                    <span class="admin-nav__sublink is-disabled"
                          aria-disabled="true">Registar utilizador</span>
                    <span class="admin-nav__sublink is-disabled"
                          aria-disabled="true">Papéis e permissões</span>
                </div>
            </section>

            <!-- ORGANIZAÇÕES -->
            <section class="admin-nav__section">
                <button class="admin-nav__toggle"
                        type="button"
                        data-nav-toggle
                        aria-expanded="<?= $isGroupActive(['/admin/organizacoes']) ? 'true' : 'false' ?>"
                        aria-controls="nav-organizacoes">
                    <span class="admin-nav__icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24">
                            <rect x="3" y="3" width="18" height="18" rx="2"/>
                            <path d="M9 21V9h6v12M7 7h.01M17 7h.01M7 12h.01M17 12h.01"/>
                        </svg>
                    </span>
                    <span class="admin-nav__label">Organizações</span>
                    <span class="admin-nav__chevron" aria-hidden="true">⌄</span>
                </button>

                <div class="admin-nav__submenu"
                     id="nav-organizacoes"
                     <?= $isGroupActive(['/admin/organizacoes']) ? '' : 'hidden' ?>>
                    <a class="admin-nav__sublink <?= $isActive('/admin/organizacoes') ? 'is-active' : '' ?>"
                       href="<?= $e($baseUrl) ?>/admin/organizacoes">
                        Listar organizações
                    </a>
                    <span class="admin-nav__sublink is-disabled"
                          aria-disabled="true">Registar organização</span>
                    <span class="admin-nav__sublink is-disabled"
                          aria-disabled="true">Responsáveis</span>
                </div>
            </section>

            <div class="admin-nav__group-label">OPERAÇÕES</div>

            <!-- OCORRÊNCIAS -->
            <section class="admin-nav__section">
                <button class="admin-nav__toggle"
                        type="button"
                        data-nav-toggle
                        aria-expanded="<?= $isGroupActive(['/ocorrencias']) ? 'true' : 'false' ?>"
                        aria-controls="nav-ocorrencias">
                    <span class="admin-nav__icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24">
                            <path d="M8 6h13M8 12h13M8 18h13"/>
                            <path d="M3 6h.01M3 12h.01M3 18h.01"/>
                        </svg>
                    </span>
                    <span class="admin-nav__label">Ocorrências</span>
                    <span class="admin-nav__chevron" aria-hidden="true">⌄</span>
                </button>

                <div class="admin-nav__submenu"
                     id="nav-ocorrencias"
                     <?= $isGroupActive(['/ocorrencias']) ? '' : 'hidden' ?>>
                    <a class="admin-nav__sublink <?= $isActive('/ocorrencias') ? 'is-active' : '' ?>"
                       href="<?= $e($baseUrl) ?>/ocorrencias">
                        Todas as ocorrências
                    </a>
                    <span class="admin-nav__sublink is-disabled"
                          aria-disabled="true">Em análise</span>
                    <span class="admin-nav__sublink is-disabled"
                          aria-disabled="true">Encaminhadas</span>
                    <span class="admin-nav__sublink is-disabled"
                          aria-disabled="true">Em execução</span>
                    <span class="admin-nav__sublink is-disabled"
                          aria-disabled="true">Resolvidas</span>
                </div>
            </section>

            <!-- PROCESSOS -->
            <section class="admin-nav__section">
                <button class="admin-nav__toggle"
                        type="button"
                        data-nav-toggle
                        aria-expanded="false"
                        aria-controls="nav-processos">
                    <span class="admin-nav__icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24">
                            <path d="M8 6h13M8 12h13M8 18h13"/>
                            <path d="M3 5h2v2H3zM3 11h2v2H3zM3 17h2v2H3z"/>
                        </svg>
                    </span>
                    <span class="admin-nav__label">Processos</span>
                    <span class="admin-nav__chevron" aria-hidden="true">⌄</span>
                </button>

                <div class="admin-nav__submenu" id="nav-processos" hidden>
                    <span class="admin-nav__sublink is-disabled"
                          aria-disabled="true">Processos ativos</span>
                    <span class="admin-nav__sublink is-disabled"
                          aria-disabled="true">Processos concluídos</span>
                </div>
            </section>

            <div class="admin-nav__group-label">PARTICIPAÇÃO CIDADÃ</div>

            <!-- PARTICIPAÇÃO -->
            <section class="admin-nav__section">
                <button class="admin-nav__toggle"
                        type="button"
                        data-nav-toggle
                        aria-expanded="false"
                        aria-controls="nav-participacao">
                    <span class="admin-nav__icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24">
                            <path d="M21 11.5a8.4 8.4 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.4 8.4 0 0 1-3.8-.9L3 21l1.9-5.7a8.4 8.4 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.4 8.4 0 0 1 3.8-.9h.5a8.5 8.5 0 0 1 8 8z"/>
                        </svg>
                    </span>
                    <span class="admin-nav__label">Participação cidadã</span>
                    <span class="admin-nav__chevron" aria-hidden="true">⌄</span>
                </button>

                <div class="admin-nav__submenu" id="nav-participacao" hidden>
                    <span class="admin-nav__sublink is-disabled"
                          aria-disabled="true">Comentários</span>
                    <span class="admin-nav__sublink is-disabled"
                          aria-disabled="true">Confirmações</span>
                    <span class="admin-nav__sublink is-disabled"
                          aria-disabled="true">Votos de prioridade</span>
                </div>
            </section>

            <!-- LOCALIZAÇÕES -->
            <section class="admin-nav__section">
                <button class="admin-nav__toggle"
                        type="button"
                        data-nav-toggle
                        aria-expanded="false"
                        aria-controls="nav-localizacoes">
                    <span class="admin-nav__icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24">
                            <path d="M20 10c0 5-8 11-8 11S4 15 4 10a8 8 0 1 1 16 0Z"/>
                            <circle cx="12" cy="10" r="2.5"/>
                        </svg>
                    </span>
                    <span class="admin-nav__label">Localizações</span>
                    <span class="admin-nav__chevron" aria-hidden="true">⌄</span>
                </button>

                <div class="admin-nav__submenu" id="nav-localizacoes" hidden>
                    <span class="admin-nav__sublink is-disabled"
                          aria-disabled="true">Províncias</span>
                    <span class="admin-nav__sublink is-disabled"
                          aria-disabled="true">Municípios</span>
                    <span class="admin-nav__sublink is-disabled"
                          aria-disabled="true">Comunas e bairros</span>
                </div>
            </section>

            <div class="admin-nav__group-label">ANÁLISE E SISTEMA</div>

            <!-- INDICADORES -->
            <section class="admin-nav__section">
                <button class="admin-nav__toggle"
                        type="button"
                        data-nav-toggle
                        aria-expanded="false"
                        aria-controls="nav-indicadores">
                    <span class="admin-nav__icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24">
                            <path d="M3 3v18h18"/>
                            <path d="m19 9-5 5-4-4-4 4"/>
                        </svg>
                    </span>
                    <span class="admin-nav__label">Indicadores e relatórios</span>
                    <span class="admin-nav__chevron" aria-hidden="true">⌄</span>
                </button>

                <div class="admin-nav__submenu" id="nav-indicadores" hidden>
                    <span class="admin-nav__sublink is-disabled"
                          aria-disabled="true">Painel de indicadores</span>
                    <span class="admin-nav__sublink is-disabled"
                          aria-disabled="true">Relatórios</span>
                </div>
            </section>

            <!-- AUDITORIA E CONFIGURAÇÕES -->
            <section class="admin-nav__section">
                <button class="admin-nav__toggle"
                        type="button"
                        data-nav-toggle
                        aria-expanded="false"
                        aria-controls="nav-sistema">
                    <span class="admin-nav__icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24">
                            <path d="M12 8v4l3 2"/>
                            <circle cx="12" cy="12" r="9"/>
                        </svg>
                    </span>
                    <span class="admin-nav__label">Auditoria e sistema</span>
                    <span class="admin-nav__chevron" aria-hidden="true">⌄</span>
                </button>

                <div class="admin-nav__submenu" id="nav-sistema" hidden>
                    <span class="admin-nav__sublink is-disabled"
                          aria-disabled="true">Histórico de atividades</span>
                    <span class="admin-nav__sublink is-disabled"
                          aria-disabled="true">Tipos de ocorrência</span>
                    <span class="admin-nav__sublink is-disabled"
                          aria-disabled="true">Estados e categorias</span>
                    <span class="admin-nav__sublink is-disabled"
                          aria-disabled="true">Configurações</span>
                </div>
            </section>

        </nav>

        <div class="admin-sidebar__bottom">
            <div class="admin-system-note">
                <span class="admin-system-note__dot"></span>
                <span class="admin-nav__label">Ambiente de desenvolvimento</span>
            </div>

            <button class="admin-collapse"
                    type="button"
                    data-sidebar-collapse
                    aria-label="Recolher menu lateral"
                    aria-expanded="true">
                <span aria-hidden="true">⇤</span>
                <span class="admin-nav__label">Recolher menu</span>
            </button>
        </div>
    </aside>

    <div class="admin-main">

        <header class="admin-topbar">
            <div class="admin-topbar__left">
                <button class="admin-icon-button"
                        type="button"
                        data-sidebar-toggle
                        aria-controls="admin-sidebar"
                        aria-expanded="true"
                        aria-label="Alternar menu lateral">
                    <svg viewBox="0 0 24 24" aria-hidden="true">
                        <path d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                </button>

                <div class="admin-breadcrumb">
                    <span>Administração</span>
                    <span aria-hidden="true">/</span>
                    <strong><?= $e($pageTitle) ?></strong>
                </div>
            </div>

            <div class="admin-topbar__right">
                <span class="admin-topbar__environment">SINC · LOCAL</span>

                <div class="admin-profile">
                    <span class="admin-profile__avatar" aria-hidden="true">
                        <?= $e($inicial) ?>
                    </span>
                    <span class="admin-profile__text">
                        <strong><?= $e($user['nome_completo'] ?? 'Administrador') ?></strong>
                        <small><?= $e($user['papel_nome'] ?? 'Administrador') ?></small>
                    </span>
                </div>

                <form method="POST" action="<?= $e($baseUrl) ?>/logout">
                    <input type="hidden"
                           name="_csrf_token"
                           value="<?= $e(Security::csrfToken()) ?>">

                    <button class="admin-logout"
                            type="submit"
                            title="Terminar sessão">
                        <svg viewBox="0 0 24 24" aria-hidden="true">
                            <path d="M10 17l5-5-5-5M15 12H3"/>
                            <path d="M12 3h6a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-6"/>
                        </svg>
                        <span>Sair</span>
                    </button>
                </form>
            </div>
        </header>

        <main class="admin-content">
            <?= $content ?>
        </main>

        <footer class="admin-footer">
            <span>SINC — Sistema de Inteligência Cívica</span>
            <span>Área administrativa</span>
        </footer>

    </div>
    <?php foreach ($pageScripts as $script): ?>
    <script
        src="<?= $e(
            $baseUrl . '/' . ltrim((string) $script, '/')
        ) ?>"
        defer></script>
    <?php endforeach; ?>
</body>
</html>