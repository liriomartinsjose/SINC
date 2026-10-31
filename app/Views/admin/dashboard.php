<?php

declare(strict_types=1);

$user = $user ?? [];
$estatisticas = $estatisticas ?? [];
$porEstado = $porEstado ?? [];
$porGravidade = $porGravidade ?? [];
$evolucao = $evolucao ?? [];
$denuncias = $denuncias ?? [];
$atividade = $atividade ?? [];
$filtros = $filtros ?? [];
$totalFiltrado = (int) ($totalFiltrado ?? 0);
$erroConsulta = (bool) ($erroConsulta ?? false);
$baseUrl = rtrim($baseUrl ?? '/sinc', '/');

$e = static fn($value): string =>
    Security::escape((string) ($value ?? ''));

$estados = [
    1 => 'Criada',
    2 => 'Em análise',
    3 => 'Validada',
    4 => 'Encaminhada',
    5 => 'Em execução',
    6 => 'Resolvida',
    7 => 'Arquivada',
];

$estadoClasse = [
    1 => 'created',
    2 => 'analysis',
    3 => 'validated',
    4 => 'forwarded',
    5 => 'progress',
    6 => 'resolved',
    7 => 'archived',
];

$gravidadeClasse = [
    'BAIXA' => 'low',
    'MEDIA' => 'medium',
    'ALTA' => 'high',
    'CRITICA' => 'critical',
];

$totalGrafico = max(
    1,
    ...array_map(
        static fn(array $item): int => (int) ($item['total'] ?? 0),
        $porEstado
    )
);

$totalGravidade = max(
    1,
    ...array_map(
        static fn(array $item): int => (int) ($item['total'] ?? 0),
        $porGravidade
    )
);

$maxEvolucao = max(
    1,
    ...array_map(
        static fn(array $item): int => (int) ($item['total'] ?? 0),
        $evolucao
    )
);

$nome = (string) ($user['nome_completo'] ?? 'Administrador');
$inicial = function_exists('mb_substr')
    ? mb_strtoupper(mb_substr($nome, 0, 1, 'UTF-8'), 'UTF-8')
    : strtoupper(substr($nome, 0, 1));

$periodos = [
    '7' => 'Últimos 7 dias',
    '30' => 'Últimos 30 dias',
    '90' => 'Últimos 90 dias',
    '365' => 'Últimos 12 meses',
    'todos' => 'Todo o período',
];

?>
<head>
    <link rel="stylesheet" href="../../../public/assets/css/admin/dashboard.css">    
</head>
<section class="dashboard-admin" aria-labelledby="dashboard-title">

    <header class="dash-header">
        <div class="dash-header__intro">
            <span class="dash-eyebrow">
                <span class="dash-eyebrow__dot"></span>
                CENTRO DE INTELIGÊNCIA CÍVICA
            </span>

            <h1 id="dashboard-title">Painel de controlo</h1>

            <p>
                Monitoriza as ocorrências, acompanha a resposta operacional
                e identifica os problemas que exigem maior atenção.
            </p>
        </div>

        <div class="dash-header__actions">
            <div class="dash-date">
                <span class="dash-date__label">DATA DE CONSULTA</span>
                <strong data-current-date>—</strong>
            </div>

            <a class="dash-button dash-button--primary"
               href="<?= $e($baseUrl) ?>/ocorrencias">
                <svg viewBox="0 0 24 24" aria-hidden="true">
                    <path d="M4 5h16v14H4z"/>
                    <path d="M8 9h8M8 13h8M8 17h4"/>
                </svg>
                Gerir ocorrências
            </a>
        </div>
    </header>

    <?php if ($erroConsulta): ?>
        <div class="dash-alert" role="alert">
            <svg viewBox="0 0 24 24" aria-hidden="true">
                <circle cx="12" cy="12" r="9"/>
                <path d="M12 8v4M12 16h.01"/>
            </svg>
            <div>
                <strong>Não foi possível carregar todos os indicadores.</strong>
                <p>Verifica o registo de erros do PHP e a ligação à base de dados.</p>
            </div>
        </div>
    <?php endif; ?>

    <section class="dash-filters" aria-label="Pesquisa e filtros de ocorrências">
        <div class="dash-section-caption">
            <div>
                <span class="dash-section-caption__overline">EXPLORAÇÃO DE DADOS</span>
                <h2>Pesquisar ocorrências</h2>
            </div>
            <span class="dash-section-caption__hint">
                Filtra a lista de resultados
            </span>
        </div>

        <form class="dash-filter-form"
              method="get"
              action="<?= $e($baseUrl) ?>/admin/dashboard">

            <label class="dash-search">
                <svg viewBox="0 0 24 24" aria-hidden="true">
                    <circle cx="11" cy="11" r="7"/>
                    <path d="m16 16 4 4"/>
                </svg>
                <span class="dash-visually-hidden">Pesquisar por título ou referência</span>
                <input
                    type="search"
                    name="pesquisa"
                    placeholder="Título ou referência da ocorrência..."
                    value="<?= $e($filtros['pesquisa'] ?? '') ?>"
                >
            </label>

            <label class="dash-filter-control">
                <span>Estado</span>
                <select name="estado">
                    <option value="0">Todos os estados</option>
                    <?php foreach ($estados as $id => $estado): ?>
                        <option
                            value="<?= $id ?>"
                            <?= (int) ($filtros['estado'] ?? 0) === $id ? 'selected' : '' ?>
                        >
                            <?= $e($estado) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </label>

            <label class="dash-filter-control">
                <span>Gravidade</span>
                <select name="gravidade">
                    <option value="">Todas</option>
                    <?php foreach (['BAIXA', 'MEDIA', 'ALTA', 'CRITICA'] as $gravidade): ?>
                        <option
                            value="<?= $e($gravidade) ?>"
                            <?= ($filtros['gravidade'] ?? '') === $gravidade ? 'selected' : '' ?>
                        >
                            <?= $e(ucfirst(mb_strtolower($gravidade))) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </label>

            <label class="dash-filter-control">
                <span>Período</span>
                <select name="periodo">
                    <?php foreach ($periodos as $valor => $rotulo): ?>
                        <option
                            value="<?= $e($valor) ?>"
                            <?= ($filtros['periodo'] ?? '30') === $valor ? 'selected' : '' ?>
                        >
                            <?= $e($rotulo) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </label>

            <button class="dash-button dash-button--search" type="submit">
                Aplicar
            </button>

            <a class="dash-button dash-button--reset"
               href="<?= $e($baseUrl) ?>/admin/dashboard">
                Limpar
            </a>
        </form>
    </section>

    <section class="dash-kpis" aria-label="Indicadores globais">
        <article class="dash-kpi dash-kpi--blue">
            <div class="dash-kpi__top">
                <span class="dash-kpi__icon">
                    <svg viewBox="0 0 24 24" aria-hidden="true">
                        <path d="M5 3h14v18H5z"/>
                        <path d="M8 7h8M8 11h8M8 15h5"/>
                    </svg>
                </span>
                <span class="dash-kpi__label">VOLUME GLOBAL</span>
            </div>
            <strong class="dash-kpi__value">
                <?= number_format((int) ($estatisticas['total'] ?? 0), 0, ',', '.') ?>
            </strong>
            <span class="dash-kpi__description">Ocorrências registadas</span>
            <span class="dash-kpi__foot">Total ativo na plataforma</span>
        </article>

        <article class="dash-kpi dash-kpi--amber">
            <div class="dash-kpi__top">
                <span class="dash-kpi__icon">
                    <svg viewBox="0 0 24 24" aria-hidden="true">
                        <circle cx="12" cy="12" r="9"/>
                        <path d="M12 7v5l3 2"/>
                    </svg>
                </span>
                <span class="dash-kpi__label">A REQUERER ATENÇÃO</span>
            </div>
            <strong class="dash-kpi__value">
                <?= number_format((int) ($estatisticas['pendentes'] ?? 0), 0, ',', '.') ?>
            </strong>
            <span class="dash-kpi__description">Criadas ou em análise</span>
            <span class="dash-kpi__foot">Estados 1 e 2</span>
        </article>

        <article class="dash-kpi dash-kpi--violet">
            <div class="dash-kpi__top">
                <span class="dash-kpi__icon">
                    <svg viewBox="0 0 24 24" aria-hidden="true">
                        <path d="M4 19V5M4 19h17"/>
                        <path d="m7 15 4-5 3 2 5-7"/>
                    </svg>
                </span>
                <span class="dash-kpi__label">EM EXECUÇÃO</span>
            </div>
            <strong class="dash-kpi__value">
                <?= number_format((int) ($estatisticas['execucao'] ?? 0), 0, ',', '.') ?>
            </strong>
            <span class="dash-kpi__description">Trabalhos em curso</span>
            <span class="dash-kpi__foot">Estado operacional</span>
        </article>

        <article class="dash-kpi dash-kpi--green">
            <div class="dash-kpi__top">
                <span class="dash-kpi__icon">
                    <svg viewBox="0 0 24 24" aria-hidden="true">
                        <circle cx="12" cy="12" r="9"/>
                        <path d="m8 12 3 3 5-6"/>
                    </svg>
                </span>
                <span class="dash-kpi__label">RESOLUÇÃO</span>
            </div>
            <strong class="dash-kpi__value">
                <?= number_format((int) ($estatisticas['resolvidas'] ?? 0), 0, ',', '.') ?>
            </strong>
            <span class="dash-kpi__description">Ocorrências resolvidas</span>
            <span class="dash-kpi__foot">
                Taxa global: <?= $e($estatisticas['taxa_resolucao'] ?? 0) ?>%
            </span>
        </article>
    </section>

    <section class="dash-analytics" aria-label="Análise estatística">
        <article class="dash-panel dash-panel--trend">
            <header class="dash-panel__header">
                <div>
                    <span class="dash-panel__eyebrow">SÉRIE TEMPORAL</span>
                    <h2>Evolução das ocorrências</h2>
                    <p>Novos registos nos últimos sete dias</p>
                </div>
                <span class="dash-panel__icon">
                    <svg viewBox="0 0 24 24" aria-hidden="true">
                        <path d="M3 3v18h18"/>
                        <path d="m7 14 4-4 3 3 6-7"/>
                    </svg>
                </span>
            </header>

            <?php if (array_sum(array_column($evolucao, 'total')) === 0): ?>
                <div class="dash-chart-empty">
                    <span class="dash-chart-empty__symbol">∿</span>
                    <strong>Sem novos registos neste período</strong>
                    <p>Quando existirem ocorrências, a evolução será apresentada aqui.</p>
                </div>
            <?php else: ?>
                <div class="dash-trend-chart" role="img"
                     aria-label="Gráfico de barras com ocorrências registadas por dia">
                    <?php foreach ($evolucao as $dia): ?>
                        <?php
                        $valor = (int) ($dia['total'] ?? 0);
                        $altura = $valor > 0
                            ? max(5, (int) round(($valor / $maxEvolucao) * 100))
                            : 0;
                        ?>
                        <div class="dash-trend-column"
                             title="<?= $e($dia['rotulo'] ?? '') ?>: <?= $valor ?> ocorrência(s)">
                            <span class="dash-trend-column__value"><?= $valor ?></span>
                            <div class="dash-trend-column__track">
                                <span class="dash-trend-column__bar"
                                      style="height: <?= $altura ?>%"></span>
                            </div>
                            <span class="dash-trend-column__label">
                                <?= $e($dia['rotulo'] ?? '') ?>
                            </span>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </article>

        <article class="dash-panel">
            <header class="dash-panel__header">
                <div>
                    <span class="dash-panel__eyebrow">DISTRIBUIÇÃO</span>
                    <h2>Por estado</h2>
                    <p>Volume atual por etapa de tratamento</p>
                </div>
                <span class="dash-panel__icon">
                    <svg viewBox="0 0 24 24" aria-hidden="true">
                        <path d="M4 19V5M4 19h17"/>
                        <path d="M7 15h3v2H7zM12 10h3v7h-3zM17 6h3v11h-3z"/>
                    </svg>
                </span>
            </header>

            <div class="dash-distribution">
                <?php foreach ($porEstado as $item): ?>
                    <?php
                    $valor = (int) ($item['total'] ?? 0);
                    $percentagem = (int) round(($valor / $totalGrafico) * 100);
                    ?>
                    <div class="dash-distribution__item">
                        <div class="dash-distribution__meta">
                            <span><?= $e($item['nome'] ?? '') ?></span>
                            <strong><?= $valor ?></strong>
                        </div>
                        <div class="dash-progress">
                            <span class="dash-progress__bar dash-progress__bar--state"
                                  style="width: <?= min(100, $percentagem) ?>%"></span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </article>

        <article class="dash-panel">
            <header class="dash-panel__header">
                <div>
                    <span class="dash-panel__eyebrow">NÍVEL DE RISCO</span>
                    <h2>Por gravidade</h2>
                    <p>Classificação das ocorrências ativas</p>
                </div>
                <span class="dash-panel__icon dash-panel__icon--alert">
                    <svg viewBox="0 0 24 24" aria-hidden="true">
                        <path d="M12 3 2.8 20h18.4L12 3Z"/>
                        <path d="M12 9v5M12 17h.01"/>
                    </svg>
                </span>
            </header>

            <div class="dash-distribution">
                <?php foreach ($porGravidade as $item): ?>
                    <?php
                    $gravidade = (string) ($item['nome'] ?? '');
                    $valor = (int) ($item['total'] ?? 0);
                    $percentagem = (int) round(($valor / $totalGravidade) * 100);
                    ?>
                    <div class="dash-distribution__item">
                        <div class="dash-distribution__meta">
                            <span class="dash-severity dash-severity--<?= $e($gravidadeClasse[$gravidade] ?? 'low') ?>">
                                <span></span>
                                <?= $e(ucfirst(mb_strtolower($gravidade))) ?>
                            </span>
                            <strong><?= $valor ?></strong>
                        </div>
                        <div class="dash-progress">
                            <span class="dash-progress__bar dash-progress__bar--<?= $e($gravidadeClasse[$gravidade] ?? 'low') ?>"
                                  style="width: <?= min(100, $percentagem) ?>%"></span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="dash-critical-note">
                <span class="dash-critical-note__dot"></span>
                <span>
                    <strong><?= (int) ($estatisticas['criticas'] ?? 0) ?></strong>
                    ocorrências críticas ativas
                </span>
            </div>
        </article>
    </section>

    <section class="dash-panel dash-recent">
        <header class="dash-panel__header dash-panel__header--table">
            <div>
                <span class="dash-panel__eyebrow">ACOMPANHAMENTO OPERACIONAL</span>
                <h2>Ocorrências recentes</h2>
                <p>
                    <?= number_format($totalFiltrado, 0, ',', '.') ?>
                    resultado(s) com os filtros atuais
                </p>
            </div>

            <a class="dash-text-link" href="<?= $e($baseUrl) ?>/ocorrencias">
                Ver todas
                <span aria-hidden="true">↗</span>
            </a>
        </header>

        <?php if ($denuncias === []): ?>
            <div class="dash-empty">
                <span class="dash-empty__icon">
                    <svg viewBox="0 0 24 24" aria-hidden="true">
                        <circle cx="10.5" cy="10.5" r="6.5"/>
                        <path d="m16 16 5 5M8 10h5"/>
                    </svg>
                </span>
                <strong>Nenhuma ocorrência encontrada</strong>
                <p>Experimenta alterar os filtros ou limpar a pesquisa.</p>
                <a class="dash-button dash-button--reset"
                   href="<?= $e($baseUrl) ?>/admin/dashboard">
                    Limpar filtros
                </a>
            </div>
        <?php else: ?>
            <div class="dash-table-wrap">
                <table class="dash-table">
                    <thead>
                        <tr>
                            <th scope="col">Ocorrência</th>
                            <th scope="col">Referência</th>
                            <th scope="col">Gravidade</th>
                            <th scope="col">Estado</th>
                            <th scope="col">Registada em</th>
                            <th scope="col"><span class="dash-visually-hidden">Ações</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($denuncias as $denuncia): ?>
                            <?php
                            $estadoId = (int) ($denuncia['estado_id'] ?? 0);
                            $gravidade = (string) ($denuncia['gravidade'] ?? 'BAIXA');
                            $referencia = strtoupper(substr(
                                str_replace('-', '', (string) ($denuncia['uuid'] ?? '')),
                                0,
                                8
                            ));
                            ?>
                            <tr>
                                <td>
                                    <div class="dash-table__title">
                                        <span class="dash-table__document">
                                            <svg viewBox="0 0 24 24" aria-hidden="true">
                                                <path d="M6 3h9l4 4v14H6z"/>
                                                <path d="M14 3v5h5M9 12h7M9 16h7"/>
                                            </svg>
                                        </span>
                                        <span>
                                            <strong><?= $e($denuncia['titulo'] ?? 'Sem título') ?></strong>
                                            <small>
                                                Tipo #<?= (int) ($denuncia['tipo_ocorrencia_id'] ?? 0) ?>
                                                · Local #<?= (int) ($denuncia['localizacao_id'] ?? 0) ?>
                                            </small>
                                        </span>
                                    </div>
                                </td>
                                <td>
                                    <code class="dash-reference"><?= $e($referencia) ?></code>
                                </td>
                                <td>
                                    <span class="dash-severity dash-severity--<?= $e($gravidadeClasse[$gravidade] ?? 'low') ?>">
                                        <span></span>
                                        <?= $e(ucfirst(mb_strtolower($gravidade))) ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="dash-status dash-status--<?= $e($estadoClasse[$estadoId] ?? 'created') ?>">
                                        <?= $e($estados[$estadoId] ?? 'Desconhecido') ?>
                                    </span>
                                </td>
                                <td class="dash-table__date">
                                    <?= !empty($denuncia['criado_em'])
                                        ? $e(date('d/m/Y H:i', strtotime((string) $denuncia['criado_em'])))
                                        : '—' ?>
                                </td>
                                <td>
                                    <a class="dash-table__action"
                                       href="<?= $e($baseUrl) ?>/ocorrencias/show?id=<?= (int) ($denuncia['id'] ?? 0) ?>"
                                       aria-label="Consultar ocorrência <?= $e($referencia) ?>">
                                        <svg viewBox="0 0 24 24" aria-hidden="true">
                                            <path d="M5 12s2.5-6 7-6 7 6 7 6-2.5 6-7 6-7-6-7-6Z"/>
                                            <circle cx="12" cy="12" r="2"/>
                                        </svg>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </section>

    <section class="dash-bottom-grid">
        <article class="dash-panel">
            <header class="dash-panel__header">
                <div>
                    <span class="dash-panel__eyebrow">AÇÃO PRIORITÁRIA</span>
                    <h2>Requer atenção</h2>
                    <p>Ocorrências críticas ou de alta gravidade na lista filtrada</p>
                </div>
            </header>

            <?php
            $prioritarias = array_values(array_filter(
                $denuncias,
                static fn(array $item): bool =>
                    in_array((string) ($item['gravidade'] ?? ''), ['CRITICA', 'ALTA'], true)
            ));
            ?>

            <?php if ($prioritarias === []): ?>
                <div class="dash-inline-empty">
                    <span class="dash-inline-empty__check">✓</span>
                    <p>Não existem ocorrências críticas ou de alta gravidade entre os resultados apresentados.</p>
                </div>
            <?php else: ?>
                <div class="dash-priority-list">
                    <?php foreach (array_slice($prioritarias, 0, 4) as $item): ?>
                        <?php $g = (string) ($item['gravidade'] ?? 'ALTA'); ?>
                        <a class="dash-priority-item"
                           href="<?= $e($baseUrl) ?>/ocorrencias/show?id=<?= (int) ($item['id'] ?? 0) ?>">
                            <span class="dash-priority-item__signal dash-priority-item__signal--<?= $e($gravidadeClasse[$g] ?? 'high') ?>"></span>
                            <span class="dash-priority-item__content">
                                <strong><?= $e($item['titulo'] ?? 'Sem título') ?></strong>
                                <small><?= $e(ucfirst(mb_strtolower($g))) ?> · <?= $e($estados[(int) ($item['estado_id'] ?? 0)] ?? 'Desconhecido') ?></small>
                            </span>
                            <span class="dash-priority-item__arrow" aria-hidden="true">↗</span>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </article>

        <article class="dash-panel">
            <header class="dash-panel__header">
                <div>
                    <span class="dash-panel__eyebrow">RASTREABILIDADE</span>
                    <h2>Atividade recente</h2>
                    <p>Últimos eventos registados no histórico</p>
                </div>
            </header>

            <?php if ($atividade === []): ?>
                <div class="dash-inline-empty">
                    <span class="dash-inline-empty__check">i</span>
                    <p>Ainda não existem eventos disponíveis para apresentar, ou o histórico não está acessível.</p>
                </div>
            <?php else: ?>
                <div class="dash-activity-list">
                    <?php foreach ($atividade as $evento): ?>
                        <div class="dash-activity-item">
                            <span class="dash-activity-item__marker"></span>
                            <div>
                                <strong><?= $e($evento['acao'] ?? 'Atividade') ?></strong>
                                <p><?= $e($evento['descricao'] ?? '') ?></p>
                                <time>
                                    <?= !empty($evento['data_evento'])
                                        ? $e(date('d/m/Y H:i', strtotime((string) $evento['data_evento'])))
                                        : 'Data indisponível' ?>
                                </time>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </article>
    </section>

    <footer class="dash-footer">
        <span>SINC · Sistema de Inteligência Cívica</span>
        <span>Informação operacional baseada nos registos disponíveis.</span>
    </footer>
</section>