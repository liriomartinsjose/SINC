<?php
declare(strict_types=1);

$e = static fn($valor): string =>
    Security::escape((string) ($valor ?? ''));

$statusLabels = [
    'PENDENTE' => 'Pendente',
    'VALIDADA' => 'Validada',
    'REJEITADA' => 'Rejeitada',
    'DUPLICADA' => 'Duplicada',
];

$statusDescriptions = [
    'PENDENTE' => 'A tua participação foi registada e aguarda análise.',
    'VALIDADA' => 'A participação foi validada e pode avançar no processo.',
    'REJEITADA' => 'A participação foi rejeitada após análise.',
    'DUPLICADA' => 'Esta participação foi identificada como duplicada.',
];

$status = $denuncia['status'] ?? 'PENDENTE';
$statusClass = strtolower((string) $status);

$dataSubmissao = !empty($denuncia['criado_em'])
    ? date('d/m/Y · H:i', strtotime($denuncia['criado_em']))
    : 'Não disponível';

$gravidade = strtoupper(
    (string) ($denuncia['gravidade_padrao'] ?? 'MEDIA')
);

$gravidadeLabels = [
    'BAIXA' => 'Baixa',
    'MEDIA' => 'Média',
    'ALTA' => 'Alta',
    'CRITICA' => 'Crítica',
];

$gravidadeClass = strtolower($gravidade);

$latitude = $denuncia['latitude'] ?? null;
$longitude = $denuncia['longitude'] ?? null;

$temCoordenadas = is_numeric($latitude)
    && is_numeric($longitude);

$googleMapsUrl = $temCoordenadas
    ? 'https://www.google.com/maps?q='
        . rawurlencode((string) $latitude . ',' . (string) $longitude)
    : null;

?>

<div class="denuncia-detail-page">

    <a
        class="detail-back"
        href="<?= $e($baseUrl) ?>/denuncias"
    >
        <span aria-hidden="true">←</span>
        Voltar às minhas denúncias
    </a>

    <header class="detail-hero">
        <div class="detail-hero-content">
            <p class="detail-eyebrow">
                <span class="detail-eyebrow-mark"></span>
                Detalhes da participação
            </p>

            <h1><?= $e($denuncia['titulo'] ?? 'Denúncia') ?></h1>

            <p class="detail-hero-description">
                Consulta os dados, a localização e o estado actual
                desta participação no SINC.
            </p>
        </div>

        <div class="detail-reference-box">
            <div class="detail-reference-icon" aria-hidden="true">⌕</div>

            <div>
                <span class="detail-reference-label">Referência</span>
                <span class="detail-reference-number">
                    #<?= (int) ($denuncia['id'] ?? 0) ?>
                </span>
            </div>
        </div>
    </header>

    <section class="detail-status-panel" aria-label="Estado da denúncia">
        <div class="detail-status-main">
            <div class="detail-status-icon" aria-hidden="true">◷</div>

            <div class="detail-status-copy">
                <strong>Estado da participação</strong>
                <p>
                    <?= $e(
                        $statusDescriptions[$status]
                        ?? 'Consulta o estado actual da participação.'
                    ) ?>
                </p>
            </div>
        </div>

        <span class="detail-status-badge status-<?= $e($statusClass) ?>">
            <?= $e($statusLabels[$status] ?? $status) ?>
        </span>
    </section>

    <div class="detail-content-grid">

        <div class="detail-main-column">

            <section class="detail-card">
                <div class="detail-card-heading">
                    <div class="detail-card-icon" aria-hidden="true">▤</div>

                    <div>
                        <h2>Descrição da ocorrência</h2>
                        <p>Informações fornecidas na submissão.</p>
                    </div>
                </div>

                <p class="detail-description"><?= nl2br(
                    $e($denuncia['descricao'] ?? '')
                ) ?></p>
            </section>

            <section class="detail-card">
                <div class="detail-card-heading">
                    <div class="detail-card-icon" aria-hidden="true">▦</div>

                    <div>
                        <h2>Classificação</h2>
                        <p>Categoria e dados de registo.</p>
                    </div>
                </div>

                <dl class="detail-info-grid">
                    <div class="detail-info-item">
                        <dt>Tipo de ocorrência</dt>
                        <dd><?= $e($denuncia['tipo_ocorrencia'] ?? 'Não indicado') ?></dd>
                    </div>

                    <div class="detail-info-item">
                        <dt>Gravidade padrão</dt>
                        <dd>
                            <span class="detail-gravity gravity-<?= $e($gravidadeClass) ?>">
                                <?= $e($gravidadeLabels[$gravidade] ?? $gravidade) ?>
                            </span>
                        </dd>
                    </div>

                    <div class="detail-info-item">
                        <dt>Data de submissão</dt>
                        <dd><?= $e($dataSubmissao) ?></dd>
                    </div>

                    <div class="detail-info-item">
                        <dt>Origem</dt>
                        <dd>
                            <?= ($denuncia['origem'] ?? '') === 'WEB'
                                ? 'Plataforma Web'
                                : $e($denuncia['origem'] ?? 'Não indicada') ?>
                        </dd>
                    </div>
                </dl>
            </section>

            <section class="detail-card">
                <div class="detail-card-heading">
                    <div class="detail-card-icon" aria-hidden="true">⌖</div>

                    <div>
                        <h2>Localização</h2>
                        <p>Local indicado para a ocorrência.</p>
                    </div>
                </div>

                <dl class="detail-location-list">
                    <div class="detail-location-item">
                        <div class="detail-location-icon" aria-hidden="true">⌖</div>
                        <div class="detail-location-copy">
                            <dt>Bairro</dt>
                            <dd><?= $e($denuncia['bairro'] ?? 'Não indicado') ?></dd>
                        </div>
                    </div>

                    <div class="detail-location-item">
                        <div class="detail-location-icon" aria-hidden="true">⌁</div>
                        <div class="detail-location-copy">
                            <dt>Rua ou avenida</dt>
                            <dd><?= $e(
                                !empty($denuncia['rua'])
                                    ? $denuncia['rua']
                                    : 'Não indicada'
                            ) ?></dd>
                        </div>
                    </div>

                    <div class="detail-location-item">
                        <div class="detail-location-icon" aria-hidden="true">№</div>
                        <div class="detail-location-copy">
                            <dt>Número da porta ou edifício</dt>
                            <dd><?= $e(
                                !empty($denuncia['numero'])
                                    ? $denuncia['numero']
                                    : 'Não indicado'
                            ) ?></dd>
                        </div>
                    </div>

                    <div class="detail-location-item">
                        <div class="detail-location-icon" aria-hidden="true">☷</div>
                        <div class="detail-location-copy">
                            <dt>Ponto de referência</dt>
                            <dd><?= $e(
                                !empty($denuncia['referencia'])
                                    ? $denuncia['referencia']
                                    : 'Não indicada'
                            ) ?></dd>
                        </div>
                    </div>
                </dl>

                <?php if ($temCoordenadas): ?>
                    <div class="detail-coordinates">
                        <div class="detail-coordinate">
                            <span>Latitude</span>
                            <strong><?= $e($latitude) ?></strong>
                        </div>

                        <div class="detail-coordinate">
                            <span>Longitude</span>
                            <strong><?= $e($longitude) ?></strong>
                        </div>
                    </div>

                    <a
                        class="detail-map-link"
                        href="<?= $e($googleMapsUrl) ?>"
                        target="_blank"
                        rel="noopener noreferrer"
                    >
                        <span aria-hidden="true">↗</span>
                        Ver localização no Google Maps
                    </a>
                <?php else: ?>
                    <p class="detail-empty-attachments">
                        Não existem coordenadas válidas registadas para esta
                        localização.
                    </p>
                <?php endif; ?>
            </section>

        </div>

        <aside class="detail-side-column">

            <section class="detail-card">
                <div class="detail-card-heading">
                    <div class="detail-card-icon" aria-hidden="true">◉</div>

                    <div>
                        <h2>Privacidade</h2>
                        <p>Visibilidade da participação.</p>
                    </div>
                </div>

                <div class="detail-anonymous">
                    <div class="detail-anonymous-icon" aria-hidden="true">
                        <?= (int) ($denuncia['anonima'] ?? 0) === 1
                            ? '◉'
                            : '◎' ?>
                    </div>

                    <div>
                        <strong>
                            <?= (int) ($denuncia['anonima'] ?? 0) === 1
                                ? 'Participação anónima'
                                : 'Participação identificada' ?>
                        </strong>

                        <p>
                            <?= (int) ($denuncia['anonima'] ?? 0) === 1
                                ? 'A participação foi submetida com a opção de anonimato activada.'
                                : 'A participação foi submetida sem activar a opção de anonimato.' ?>
                        </p>
                    </div>
                </div>
            </section>

            <section class="detail-card">
                <div class="detail-card-heading">
                    <div class="detail-card-icon" aria-hidden="true">◷</div>

                    <div>
                        <h2>Acompanhamento</h2>
                        <p>Resumo do estado actual.</p>
                    </div>
                </div>

                <div class="detail-timeline">
                    <div class="detail-timeline-point">
                        <strong>Participação registada</strong>
                        <p>A denúncia foi submetida na plataforma SINC.</p>
                        <time><?= $e($dataSubmissao) ?></time>
                    </div>

                    <div class="detail-timeline-point">
                        <strong>
                            <?= $e($statusLabels[$status] ?? $status) ?>
                        </strong>
                        <p>
                            <?= $e(
                                $statusDescriptions[$status]
                                ?? 'Estado actual da participação.'
                            ) ?>
                        </p>
                    </div>
                </div>
            </section>

            <section class="detail-card">
                <div class="detail-card-heading">
                    <div class="detail-card-icon" aria-hidden="true">▧</div>

                    <div>
                        <h2>Anexos</h2>
                        <p>Ficheiros associados à participação.</p>
                    </div>
                </div>

                <?php if (!empty($anexos)): ?>
                    <div class="detail-attachments">
                        <?php foreach ($anexos as $anexo): ?>
                            <div class="detail-attachment">
                                <div class="detail-attachment-icon" aria-hidden="true">
                                    ▧
                                </div>

                                <div class="detail-attachment-copy">
                                    <strong>
                                        <?= $e($anexo['nome_original'] ?? 'Ficheiro') ?>
                                    </strong>

                                    <span>
                                        <?= $e(strtoupper(
                                            (string) ($anexo['extensao'] ?? 'FICHEIRO')
                                        )) ?>

                                        <?php if (isset($anexo['tamanho_bytes'])): ?>
                                            ·
                                            <?= number_format(
                                                (int) $anexo['tamanho_bytes'] / 1024,
                                                0,
                                                ',',
                                                '.'
                                            ) ?> KB
                                        <?php endif; ?>
                                    </span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="detail-empty-attachments">
                        Esta participação não tem anexos registados.
                    </div>
                <?php endif; ?>
            </section>

        </aside>
    </div>

    <footer class="detail-footer-actions">
        <p class="detail-footer-note">
            Referência da participação:
            <strong>#<?= (int) ($denuncia['id'] ?? 0) ?></strong>
        </p>

        <a
            class="btn btn-secondary"
            href="<?= $e($baseUrl) ?>/denuncias"
        >
            <span aria-hidden="true">←</span>
            Voltar às minhas denúncias
        </a>
    </footer>

</div>
