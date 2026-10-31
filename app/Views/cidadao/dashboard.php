<?php
declare(strict_types=1);

$e = static fn($value): string =>
    Security::escape((string) ($value ?? ''));

$userName = trim((string) (
    $user['nome'] ?? $user['nome_completo'] ?? 'Cidadão'
));

$primeiroNome = explode(' ', $userName)[0] ?: 'Cidadão';

$resumo = $resumo ?? [];
$estatisticasPublicas = $estatisticasPublicas ?? [];
$denunciasRecentes = $denunciasRecentes ?? [];
$ocorrenciasPublicas = $ocorrenciasPublicas ?? [];
$provincias = $provincias ?? [];
$categorias = $categorias ?? [];

$mapaJson = json_encode(
    $ocorrenciasPublicas,
    JSON_UNESCAPED_UNICODE
        | JSON_HEX_TAG
        | JSON_HEX_APOS
        | JSON_HEX_QUOT
        | JSON_HEX_AMP
) ?: '[]';

$inicial = mb_strtoupper(
    mb_substr($primeiroNome, 0, 1, 'UTF-8'),
    'UTF-8'
);
?>

<link
    rel="stylesheet"
    href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
    integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY="
    crossorigin=""
>

<div class="civic-dashboard">

    <header class="civic-welcome">
        <div class="civic-welcome-copy">
            <span class="civic-eyebrow">
                <span class="civic-live-dot"></span>
                PARTICIPAÇÃO CÍVICA
            </span>

            <h1>Olá, <?= $e($primeiroNome) ?>.</h1>

            <p>
                Angola transforma-se quando cada cidadão participa.
                Descobre o que acontece à tua volta.
            </p>
        </div>

        <a class="civic-primary-button"
           href="<?= $e(rtrim($baseUrl ?? '/sinc', '/') . '/denuncias/criar') ?>">
            <span aria-hidden="true">＋</span>
            Comunicar ocorrência
        </a>
    </header>

    <?php if (!empty($erroConsulta)): ?>
        <div class="civic-notice" role="status">
            <strong>Alguns dados não estão disponíveis.</strong>
            <span>
                Não foi possível carregar toda a informação.
                Tenta novamente mais tarde.
            </span>
        </div>
    <?php endif; ?>

    <section class="civic-stat-grid" aria-label="Indicadores do SINC">
        <article class="civic-stat-card civic-stat-featured">
            <span class="civic-stat-icon" aria-hidden="true">◎</span>
            <span class="civic-stat-label">Ocorrências no mapa</span>
            <strong><?= number_format((int) ($estatisticasPublicas['total'] ?? 0), 0, ',', '.') ?></strong>
            <small>Registos públicos com localização válida</small>
        </article>

        <article class="civic-stat-card">
            <span class="civic-stat-icon" aria-hidden="true">↗</span>
            <span class="civic-stat-label">Em acompanhamento</span>
            <strong><?= number_format((int) ($estatisticasPublicas['em_execucao'] ?? 0), 0, ',', '.') ?></strong>
            <small>Encaminhadas ou em execução</small>
        </article>

        <article class="civic-stat-card">
            <span class="civic-stat-icon" aria-hidden="true">✓</span>
            <span class="civic-stat-label">Resolvidas</span>
            <strong><?= number_format((int) ($estatisticasPublicas['resolvidas'] ?? 0), 0, ',', '.') ?></strong>
            <small>Entre os registos carregados</small>
        </article>

        <article class="civic-stat-card">
            <span class="civic-stat-icon" aria-hidden="true">⌖</span>
            <span class="civic-stat-label">Províncias representadas</span>
            <strong><?= number_format((int) ($estatisticasPublicas['provincias'] ?? 0), 0, ',', '.') ?></strong>
            <small>Com ocorrências geolocalizadas</small>
        </article>
    </section>

    <section class="civic-explorer" aria-labelledby="civic-explorer-title">
        <div class="civic-section-heading">
            <div>
                <span class="civic-eyebrow">EXPLORAR ANGOLA</span>
                <h2 id="civic-explorer-title">O país, visto de perto.</h2>
                <p>Pesquisa uma ocorrência ou explora o mapa.</p>
            </div>

            <span class="civic-map-caption">
                <span class="civic-live-dot"></span>
                Dados registados no SINC
            </span>
        </div>

        <form class="civic-filter-bar" id="civic-filters">
            <label class="civic-search-field">
                <span class="civic-sr-only">Pesquisar ocorrências</span>
                <span aria-hidden="true">⌕</span>
                <input
                    type="search"
                    id="civic-search"
                    placeholder="Pesquisar problema, local ou palavra-chave..."
                    autocomplete="off"
                >
            </label>

            <label>
                <span class="civic-sr-only">Filtrar por província</span>
                <select id="civic-province">
                    <option value="">Todas as províncias</option>
                    <?php foreach ($provincias as $provincia): ?>
                        <option value="<?= $e($provincia['id'] ?? '') ?>">
                            <?= $e($provincia['nome'] ?? '') ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </label>

            <label>
                <span class="civic-sr-only">Filtrar por categoria</span>
                <select id="civic-category">
                    <option value="">Todas as categorias</option>
                    <?php foreach ($categorias as $categoria): ?>
                        <option value="<?= $e($categoria['id'] ?? '') ?>">
                            <?= $e($categoria['nome'] ?? '') ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </label>

            <label>
                <span class="civic-sr-only">Filtrar por gravidade</span>
                <select id="civic-severity">
                    <option value="">Todas as gravidades</option>
                    <option value="CRITICA">Crítica</option>
                    <option value="ALTA">Alta</option>
                    <option value="MEDIA">Média</option>
                    <option value="BAIXA">Baixa</option>
                </select>
            </label>

            <button type="button" id="civic-reset-filters"
                    class="civic-reset-button">
                Limpar
            </button>
        </form>

        <div class="civic-map-layout">
            <div class="civic-map-panel">
                <div id="civic-map"
                     role="application"
                     aria-label="Mapa interactivo de Angola com ocorrências públicas"></div>

                <div class="civic-map-legend">
                    <span><i class="civic-legend-dot civic-legend-critical"></i>Crítica</span>
                    <span><i class="civic-legend-dot civic-legend-high"></i>Alta</span>
                    <span><i class="civic-legend-dot civic-legend-medium"></i>Média</span>
                    <span><i class="civic-legend-dot civic-legend-low"></i>Baixa</span>
                </div>
            </div>

            <aside class="civic-map-aside">
                <div class="civic-aside-top">
                    <span class="civic-eyebrow">NO TERRITÓRIO</span>
                    <h3>O que está a acontecer?</h3>
                    <p>Explora os registos públicos e acompanha o estado de cada ocorrência.</p>
                </div>

                <div class="civic-result-count">
                    <strong id="civic-visible-count">0</strong>
                    <span>ocorrências visíveis</span>
                </div>

                <div class="civic-mini-feed" id="civic-mini-feed">
                    <p class="civic-loading-message">
                        A preparar as ocorrências...
                    </p>
                </div>
            </aside>
        </div>
    </section>

    <section class="civic-feed-section" aria-labelledby="civic-feed-title">
        <div class="civic-section-heading civic-feed-heading">
            <div>
                <span class="civic-eyebrow">COMUNIDADE</span>
                <h2 id="civic-feed-title">Em destaque no país</h2>
                <p>Informação pública para compreender e acompanhar os problemas.</p>
            </div>

            <label class="civic-sort-control">
                <span>Ordenar</span>
                <select id="civic-sort">
                    <option value="recent">Mais recentes</option>
                    <option value="severity">Maior gravidade</option>
                </select>
            </label>
        </div>

        <div id="civic-feed" class="civic-feed-grid">
            <p class="civic-loading-message">A carregar o feed...</p>
        </div>

        <div id="civic-empty-state" class="civic-empty-state" hidden>
            <span aria-hidden="true">⌕</span>
            <h3>Não encontrámos resultados</h3>
            <p>Experimenta alterar a pesquisa ou limpar alguns filtros.</p>
            <button type="button" id="civic-empty-reset" class="civic-secondary-button">
                Ver todas as ocorrências
            </button>
        </div>
    </section>

    <section class="civic-personal-section">
        <div class="civic-section-heading">
            <div>
                <span class="civic-eyebrow">A TUA PARTICIPAÇÃO</span>
                <h2>As tuas ocorrências</h2>
                <p>Acompanha o progresso dos problemas que comunicaste.</p>
            </div>

            <a class="civic-text-link"
            href="<?= $e(rtrim($baseUrl ?? '/sinc', '/') . '/denuncias') ?>">
            Ver o meu histórico <span aria-hidden="true">→</span>
            </a>
        </div>

        <div class="civic-personal-summary">
            <div>
                <span>Total comunicado</span>
                <strong><?= number_format((int) ($resumo['total'] ?? 0), 0, ',', '.') ?></strong>
            </div>
            <div>
                <span>Em aberto</span>
                <strong><?= number_format((int) ($resumo['em_aberto'] ?? 0), 0, ',', '.') ?></strong>
            </div>
            <div>
                <span>Em execução</span>
                <strong><?= number_format((int) ($resumo['em_execucao'] ?? 0), 0, ',', '.') ?></strong>
            </div>
            <div>
                <span>Resolvidas</span>
                <strong><?= number_format((int) ($resumo['resolvidas'] ?? 0), 0, ',', '.') ?></strong>
            </div>
        </div>

        <?php if (empty($denunciasRecentes)): ?>
            <div class="civic-personal-empty">
                <div class="civic-personal-empty-icon" aria-hidden="true">✳</div>
                <div>
                    <h3>A tua participação começa com uma voz.</h3>
                    <p>Ainda não tens ocorrências comunicadas. Quando registares uma, poderás acompanhar o seu progresso aqui.</p>
                </div>
                <a class="civic-secondary-button"
                   href="<?= $e(rtrim($baseUrl ?? '/sinc', '/') . '/denuncias/criar') ?>">
                    Comunicar a primeira ocorrência
                </a>
            </div>
        <?php else: ?>
            <div class="civic-personal-list">
                <?php foreach ($denunciasRecentes as $denuncia): ?>
                    <article class="civic-personal-item">
                        <div class="civic-personal-item-icon" aria-hidden="true">↗</div>
                        <div class="civic-personal-item-copy">
                            <h3><?= $e($denuncia['titulo'] ?? 'Ocorrência sem título') ?></h3>
                            <p>
                                <?= $e($denuncia['tipo_nome'] ?? 'Ocorrência') ?>
                                <span aria-hidden="true">·</span>
                                <?= $e($denuncia['criado_em'] ?? '') ?>
                            </p>
                        </div>
                        <span class="civic-status-pill">
                            <?= $e($denuncia['estado_nome'] ?? 'Em acompanhamento') ?>
                        </span>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>

    <footer class="civic-dashboard-footer">
        <span class="civic-footer-mark">SINC<span>.</span></span>
        <p>Uma comunidade informada pode construir soluções melhores.</p>
        <span>Participar. Acompanhar. Transformar.</span>
    </footer>
</div>

<script
    src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
    integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo="
    crossorigin=""
    defer
></script>

<script>
    window.SINC_DASHBOARD_DATA = <?= $mapaJson ?>;
</script>
