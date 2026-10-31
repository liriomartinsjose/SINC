
<?php

declare(strict_types=1);

$ocorrencias = $ocorrencias ?? [];
$pesquisa = $pesquisa ?? '';
$estadoSelecionado = $estadoSelecionado ?? '';
$tipoSelecionado = $tipoSelecionado ?? '';
$papelId = (int) ($papelId ?? 0);
$baseUrl = rtrim($baseUrl ?? '/sinc', '/');

$e = static fn($value): string =>
    Security::escape((string) ($value ?? ''));

$estados = [
    'Criada',
    'Em análise',
    'Validada',
    'Encaminhada',
    'Em execução',
    'Resolvida',
    'Arquivada',
];

$tipos = [
    'Buraco na via',
    'Poste apagado',
    'Falta de água',
    'Falta de medicamentos',
    'Problema estrutural escolar',
];

$totalVisivel = count($ocorrencias);

$emAnalise = 0;
$emExecucao = 0;
$resolvidas = 0;

foreach ($ocorrencias as $item) {
    $nomeEstado = (string) ($item['estado_nome'] ?? '');

    if ($nomeEstado === 'Em análise') {
        $emAnalise++;
    }

    if ($nomeEstado === 'Em execução') {
        $emExecucao++;
    }

    if ($nomeEstado === 'Resolvida') {
        $resolvidas++;
    }
}
?>

<section class="occ-page">

    <header class="occ-heading">
        <div>
            <span class="occ-eyebrow">SINC / GESTÃO OPERACIONAL</span>
            <h1>Ocorrências</h1>
            <p>
                Acompanha os problemas reportados e consulta o seu
                estado de tratamento.
            </p>
        </div>

        <div class="occ-heading__badge">
            <span class="occ-heading__dot"></span>
            Sistema de ocorrências
        </div>
    </header>

    <section class="occ-stats" aria-label="Resumo das ocorrências">

        <article class="occ-stat">
            <span class="occ-stat__label">Registos visíveis</span>
            <strong><?= $totalVisivel ?></strong>
            <span class="occ-stat__note">Resultado da consulta actual</span>
        </article>

        <article class="occ-stat">
            <span class="occ-stat__label">Em análise</span>
            <strong><?= $emAnalise ?></strong>
            <span class="occ-stat__note">Aguardam avaliação</span>
        </article>

        <article class="occ-stat">
            <span class="occ-stat__label">Em execução</span>
            <strong><?= $emExecucao ?></strong>
            <span class="occ-stat__note">Em tratamento operacional</span>
        </article>

        <article class="occ-stat">
            <span class="occ-stat__label">Resolvidas</span>
            <strong><?= $resolvidas ?></strong>
            <span class="occ-stat__note">Com resolução registada</span>
        </article>

    </section>

    <section class="occ-panel">

        <div class="occ-panel__heading">
            <div>
                <h2>Lista de ocorrências</h2>
                <p>Pesquisa e consulta os registos disponíveis.</p>
            </div>

            <span class="occ-count">
                <?= $totalVisivel ?>
                <?= $totalVisivel === 1 ? 'registo' : 'registos' ?>
            </span>
        </div>

        <form
            class="occ-filters"
            method="GET"
            action="<?= $e($baseUrl) ?>/ocorrencias"
        >
            <div class="occ-field occ-field--search">
                <label for="occ-pesquisa">Pesquisar</label>
                <input
                    type="search"
                    id="occ-pesquisa"
                    name="pesquisa"
                    value="<?= $e($pesquisa) ?>"
                    placeholder="Título, descrição ou autor..."
                >
            </div>

            <?php if ($papelId !== 1): ?>

                <div class="occ-field">
                    <label for="occ-estado">Estado</label>
                    <select id="occ-estado" name="estado">
                        <option value="">Todos os estados</option>

                        <?php foreach ($estados as $nomeEstado): ?>
                            <option
                                value="<?= $e($nomeEstado) ?>"
                                <?= $estadoSelecionado === $nomeEstado
                                    ? 'selected'
                                    : '' ?>
                            >
                                <?= $e($nomeEstado) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="occ-field">
                    <label for="occ-tipo">Tipo</label>
                    <select id="occ-tipo" name="tipo">
                        <option value="">Todos os tipos</option>

                        <?php foreach ($tipos as $nomeTipo): ?>
                            <option
                                value="<?= $e($nomeTipo) ?>"
                                <?= $tipoSelecionado === $nomeTipo
                                    ? 'selected'
                                    : '' ?>
                            >
                                <?= $e($nomeTipo) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

            <?php endif; ?>

            <div class="occ-filter-actions">
                <button class="occ-btn occ-btn--primary" type="submit">
                    Pesquisar
                </button>

                <a
                    class="occ-btn occ-btn--secondary"
                    href="<?= $e($baseUrl) ?>/ocorrencias"
                >
                    Limpar
                </a>
            </div>
        </form>

        <?php if ($totalVisivel > 0): ?>

            <div class="occ-table-wrap">
                <table class="occ-table">
                    <thead>
                        <tr>
                            <th>Ocorrência</th>
                            <?php if ($papelId !== 1): ?>
                                <th>Autor</th>
                            <?php endif; ?>
                            <th>Estado</th>
                            <th>Gravidade</th>
                            <th>Data de registo</th>
                            <th>Ação</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php foreach ($ocorrencias as $item): ?>
                            <?php
                            $estadoNome = (string) (
                                $item['estado_nome'] ?? 'Desconhecido'
                            );

                            $estadoClasse = match ($estadoNome) {
                                'Criada' => 'is-created',
                                'Em análise' => 'is-review',
                                'Validada' => 'is-validated',
                                'Encaminhada' => 'is-forwarded',
                                'Em execução' => 'is-progress',
                                'Resolvida' => 'is-resolved',
                                'Arquivada' => 'is-archived',
                                default => 'is-unknown',
                            };

                            $gravidade = (string) (
                                $item['gravidade'] ?? 'MEDIA'
                            );

                            $gravidadeClasse = match ($gravidade) {
                                'BAIXA' => 'is-low',
                                'MEDIA' => 'is-medium',
                                'ALTA' => 'is-high',
                                'CRITICA' => 'is-critical',
                                default => 'is-unknown',
                            };
                            ?>

                            <tr>
                                <td>
                                    <div class="occ-record">
                                        <span class="occ-record__icon"
                                              aria-hidden="true">
                                            <svg viewBox="0 0 24 24">
                                                <path d="M12 22s8-4 8-11V5l-8-3-8 3v6c0 7 8 11 8 11Z"/>
                                                <path d="M12 8v4"/>
                                                <path d="M12 16h.01"/>
                                            </svg>
                                        </span>

                                        <div>
                                            <strong>
                                                <?= $e($item['titulo'] ?? '') ?>
                                            </strong>
                                            <small>
                                                REF. #<?= (int) ($item['id'] ?? 0) ?>
                                                ·
                                                <?= $e(
                                                    $item['tipo_ocorrencia_nome']
                                                    ?? 'Sem categoria'
                                                ) ?>
                                            </small>
                                        </div>
                                    </div>
                                </td>

                                <?php if ($papelId !== 1): ?>
                                    <td>
                                        <?= $e(
                                            $item['autor_nome']
                                            ?? 'Utilizador'
                                        ) ?>
                                    </td>
                                <?php endif; ?>

                                <td>
                                    <span class="occ-status <?= $e($estadoClasse) ?>">
                                        <span></span>
                                        <?= $e($estadoNome) ?>
                                    </span>
                                </td>

                                <td>
                                    <span class="occ-severity <?= $e($gravidadeClasse) ?>">
                                        <?= $e($gravidade) ?>
                                    </span>
                                </td>

                                <td class="occ-date">
                                    <?php
                                    $dataRegisto = $item['criado_em'] ?? null;

                                    if ($dataRegisto !== null) {
                                        $timestamp = strtotime((string) $dataRegisto);
                                    } else {
                                        $timestamp = false;
                                    }
                                    ?>

                                    <?= $timestamp !== false
                                        ? date('d/m/Y · H:i', $timestamp)
                                        : '—' ?>
                                </td>

                                <td>
                                    <a
                                        class="occ-action"
                                        href="<?= $e($baseUrl) ?>/ocorrencias/show?id=<?= (int) ($item['id'] ?? 0) ?>"
                                        aria-label="Consultar ocorrência <?= (int) ($item['id'] ?? 0) ?>"
                                    >
                                        Consultar
                                        <span aria-hidden="true">→</span>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

        <?php else: ?>

            <div class="occ-empty">
                <span class="occ-empty__icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24">
                        <circle cx="11" cy="11" r="7"/>
                        <path d="m16 16 4 4"/>
                        <path d="M8 11h6"/>
                    </svg>
                </span>

                <h3>Não existem ocorrências para apresentar</h3>

                <p>
                    Não foram encontrados registos com os critérios
                    seleccionados. Experimenta limpar os filtros.
                </p>

                <a
                    class="occ-btn occ-btn--secondary"
                    href="<?= $e($baseUrl) ?>/ocorrencias"
                >
                    Limpar filtros
                </a>
            </div>

        <?php endif; ?>

    </section>

    <p class="occ-disclaimer">
        Os indicadores apresentados correspondem aos registos visíveis
        nesta consulta, não necessariamente ao total global do sistema.
    </p>

</section>
