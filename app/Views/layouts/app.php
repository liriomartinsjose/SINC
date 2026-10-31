
<?php
$pageTitle = $pageTitle ?? 'Painel';
$user = $user ?? [];
$baseUrl = rtrim((string) ($baseUrl ?? '/sinc'), '/');

$pageStyles = $pageStyles ?? [];
$pageScripts = $pageScripts ?? [];

if (is_string($pageStyles) && $pageStyles !== '') {
    $pageStyles = [$pageStyles];
}

if (is_string($pageScripts) && $pageScripts !== '') {
    $pageScripts = [$pageScripts];
}

$pageStyles = is_array($pageStyles) ? $pageStyles : [];
$pageScripts = is_array($pageScripts) ? $pageScripts : [];
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#173B57">

    <title><?= Security::escape($pageTitle) ?> | SINC</title>

    <link
    rel="stylesheet"
    href="<?= Security::escape($baseUrl) ?>/public/assets/css/header-app.css"
    >

    <link rel="stylesheet"
          href="<?= Security::escape($baseUrl) ?>/public/assets/css/denuncias-detalhes.css">

    <link rel="stylesheet" href="<?= $baseUrl ?>/public/assets/css/minhas_denuncias.css">

    <?php foreach ($pageStyles as $style): ?>
        <?php
        $style = trim((string) $style);

        if ($style === '') {
            continue;
        }

        $styleUrl = str_starts_with($style, 'http://')
            || str_starts_with($style, 'https://')
            ? $style
            : $baseUrl . '/' . ltrim($style, '/');
        ?>
        <link rel="stylesheet"
              href="<?= Security::escape($styleUrl) ?>">
    <?php endforeach; ?>
</head>


<body class="app-page">

    <header class="sinc-topbar">
        <div class="sinc-topbar-inner">

            <!-- Marca SINC -->
            <a
                class="sinc-brand"
                href="<?= Security::escape($baseUrl) ?>/cidadao/dashboard"
                aria-label="SINC — Página inicial"
            >
                <span class="sinc-brand-symbol" aria-hidden="true">
                    <svg viewBox="0 0 48 48" fill="none">
                        <path
                            d="M24 4.5 41 14v20L24 43.5 7 34V14L24 4.5Z"
                            stroke="currentColor"
                            stroke-width="1.6"
                        />
                        <path
                            d="M32 16.5a12 12 0 1 0 3.4 10.8"
                            stroke="currentColor"
                            stroke-width="3"
                            stroke-linecap="round"
                        />
                        <path
                            d="M24 15v9l6 3"
                            stroke="currentColor"
                            stroke-width="2.6"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                        />
                        <circle cx="24" cy="24" r="2.1" fill="currentColor"/>
                    </svg>
                </span>

                <span class="sinc-brand-copy">
                    <strong>SINC<span>.</span></strong>
                    <small>Inteligência cívica</small>
                </span>
            </a>

            <!-- Navegação principal -->
            <nav class="sinc-main-nav" aria-label="Navegação principal">
                <a
                    href="<?= Security::escape($baseUrl) ?>/cidadao/dashboard"
                    class="sinc-nav-link"
                >
                    <svg viewBox="0 0 24 24" aria-hidden="true">
                        <rect x="3.5" y="3.5" width="7" height="7" rx="1.5"/>
                        <rect x="13.5" y="3.5" width="7" height="4" rx="1.5"/>
                        <rect x="13.5" y="11.5" width="7" height="9" rx="1.5"/>
                        <rect x="3.5" y="13.5" width="7" height="7" rx="1.5"/>
                    </svg>
                    <span>Painel</span>
                </a>

                <a
                    href="<?= Security::escape($baseUrl) ?>/denuncias"
                    class="sinc-nav-link"
                >
                    <svg viewBox="0 0 24 24" aria-hidden="true">
                        <path d="M7 3.5h7l4 4v13H7a2 2 0 0 1-2-2v-13a2 2 0 0 1 2-2Z"/>
                        <path d="M14 3.5v5h5M9 13h6M9 16.5h6"/>
                    </svg>
                    <span>Minhas denúncias</span>
                </a>

                <a
                    href="<?= Security::escape($baseUrl) ?>/denuncias/criar"
                    class="sinc-nav-link sinc-nav-link-action"
                >
                    <span class="sinc-nav-plus" aria-hidden="true">+</span>
                    <span>Nova denúncia</span>
                </a>
            </nav>

            <!-- Área do utilizador -->
            <div class="sinc-topbar-account">

                <div class="sinc-account-avatar" aria-hidden="true">
                    <?php
                    $nomeUtilizador = trim(
                        (string) ($user['nome_completo'] ?? 'Utilizador')
                    );
                    $partesNome = preg_split('/\s+/', $nomeUtilizador);
                    $iniciais = mb_strtoupper(
                        mb_substr($partesNome[0] ?? 'U', 0, 1, 'UTF-8')
                        . (
                            count($partesNome) > 1
                                ? mb_substr(
                                    $partesNome[count($partesNome) - 1],
                                    0,
                                    1,
                                    'UTF-8'
                                )
                                : ''
                        ),
                        'UTF-8'
                    );
                    ?>
                    <?= Security::escape($iniciais) ?>
                </div>

                <div class="sinc-account-info">
                    <strong>
                        <?= Security::escape($nomeUtilizador) ?>
                    </strong>
                    <span>
                        <?= Security::escape(
                            $user['papel_nome'] ?? 'Cidadão'
                        ) ?>
                    </span>
                </div>

                <span class="sinc-account-divider" aria-hidden="true"></span>

                <form
                    method="POST"
                    action="<?= Security::escape($baseUrl) ?>/logout"
                    class="sinc-logout-form"
                >
                    <input
                        type="hidden"
                        name="_csrf_token"
                        value="<?= Security::escape(Security::csrfToken()) ?>"
                    >

                    <button
                        type="submit"
                        class="sinc-logout-button"
                        aria-label="Terminar sessão"
                        title="Terminar sessão"
                    >
                        <svg viewBox="0 0 24 24" aria-hidden="true">
                            <path d="M10 4H5.5A1.5 1.5 0 0 0 4 5.5v13A1.5 1.5 0 0 0 5.5 20H10"/>
                            <path d="M14 8l4 4-4 4M18 12H9"/>
                        </svg>
                        <span>Sair</span>
                    </button>
                </form>

            </div>
        </div>
    </header>

    <main class="app-content">
        <?= $content ?>
    </main>

    <footer class="app-footer">
        <span class="sinc-footer-brand">SINC<span>.</span></span>
        <span>Participação cidadã e transparência</span>
    </footer>

    <?php foreach ($pageScripts as $script): ?>
        <?php
        $script = trim((string) $script);

        if ($script === '') {
            continue;
        }

        $scriptUrl = str_starts_with($script, 'http://')
            || str_starts_with($script, 'https://')
            ? $script
            : $baseUrl . '/' . ltrim($script, '/');
        ?>
        <script
            src="<?= Security::escape($scriptUrl) ?>"
            defer
        ></script>
    <?php endforeach; ?>

</body>
</html>
