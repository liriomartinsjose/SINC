<?php
$esc = static fn($v): string => htmlspecialchars(
    (string) ($v ?? ''),
    ENT_QUOTES,
    'UTF-8'
);

$denuncias = $denuncias ?? [];
$baseUrl = rtrim((string) ($baseUrl ?? '/sinc'), '/');
$urlNova = $baseUrl . '/denuncias/criar';

$estados = [
    'PENDENTE' => 'Pendente',
    'VALIDADA' => 'Validada',
    'REJEITADA' => 'Rejeitada',
    'DUPLICADA' => 'Duplicada',
];
?>
<link
    rel="stylesheet"
    href="<?= $baseUrl ?>/public/assets/css/minhas_denuncias.css"
>

<link
    rel="stylesheet"
    href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
>

<section class="sinc-page sinc-list-page">
    <div class="sinc-page-container">

        <header class="sinc-list-header">
            <div>
                <span class="sinc-eyebrow">ÁREA DO CIDADÃO</span>
                <h1>Minhas denúncias</h1>
                <p>
                    Acompanha as participações que submeteste à plataforma.
                </p>
            </div>

            <a
                href="<?= $esc($urlNova) ?>"
                class="sinc-button sinc-button-primary"
            >
                <span aria-hidden="true">+</span>
                Nova denúncia
            </a>
        </header>

        <?php if (!empty($sucesso)): ?>
            <div class="sinc-alert sinc-alert-success" role="status">
                Denúncia submetida com sucesso. A participação aguarda
                validação.
            </div>
        <?php endif; ?>

        <?php if (!empty($eliminada)): ?>
            <div class="sinc-alert sinc-alert-success" role="status">
                A denúncia pendente foi eliminada da tua lista.
            </div>
        <?php endif; ?>

        <?php if (!empty($erro)): ?>
            <div class="sinc-alert sinc-alert-error" role="alert">
                Não foi possível concluir a operação. A denúncia pode
                já não estar pendente ou não pertencer à tua conta.
            </div>
        <?php endif; ?>

        <div class="sinc-list-summary">
            <div>
                <span class="sinc-summary-number">
                    <?= count($denuncias) ?>
                </span>
                <span class="sinc-summary-label">
                    <?= count($denuncias) === 1
                        ? 'denúncia registada'
                        : 'denúncias registadas' ?>
                </span>
            </div>
        </div>

        <?php if (empty($denuncias)): ?>
            <div class="sinc-empty-state">
                <div class="sinc-empty-icon" aria-hidden="true">◎</div>
                <h2>Ainda não tens denúncias</h2>
                <p>
                    Quando comunicares um problema, poderás acompanhar
                    aqui o estado da tua participação.
                </p>
                <a
                    href="<?= $esc($urlNova) ?>"
                    class="sinc-button sinc-button-primary"
                >
                    Registar a primeira denúncia
                </a>
            </div>
        <?php else: ?>

            <div class="sinc-denuncia-list">
                <?php foreach ($denuncias as $denuncia): ?>
                    <?php
                    $estado = strtoupper(
                        (string) ($denuncia['status'] ?? 'PENDENTE')
                    );

                    $classeEstado = match ($estado) {
                        'VALIDADA' => 'status-validada',
                        'REJEITADA' => 'status-rejeitada',
                        'DUPLICADA' => 'status-duplicada',
                        default => 'status-pendente',
                    };

                    $id = (int) ($denuncia['id'] ?? 0);
                    ?>

                    <article class="sinc-denuncia-card">
                        <div class="sinc-denuncia-card-main">
                            <div class="sinc-card-meta">
                                <span class="sinc-reference">
                                    Denúncia #<?= $id ?>
                                </span>

                                <span class="sinc-status <?= $esc($classeEstado) ?>">
                                    <?= $esc($estados[$estado] ?? $estado) ?>
                                </span>
                            </div>

                            <h2><?= $esc($denuncia['titulo'] ?? '') ?></h2>

                            <p class="sinc-card-category">
                                <?= $esc(
                                    $denuncia['tipo_ocorrencia_nome']
                                    ?? 'Tipo não indicado'
                                ) ?>
                            </p>

                            <p class="sinc-card-description">
                                <?= $esc($denuncia['descricao'] ?? '') ?>
                            </p>

                            <div class="sinc-card-details">
                                <span>
                                    <strong>Local:</strong>
                                    <?= $esc(
                                        $denuncia['localizacao']
                                        ?? $denuncia['bairro_nome']
                                        ?? 'Não indicado'
                                    ) ?>
                                </span>

                                <span>
                                    <strong>Submetida:</strong>
                                    <?= $esc($denuncia['criado_em'] ?? '') ?>
                                </span>
                            </div>

                            <?php if (!empty($denuncia['ocorrencia_id'])): ?>
                                <p class="sinc-linked-occurrence">
                                    Associada à ocorrência
                                    #<?= (int) $denuncia['ocorrencia_id'] ?>
                                </p>
                            <?php endif; ?>
                        </div>

                        <div class="sinc-denuncia-card-actions">
                            <a
                                href="<?= $esc(
                                    $baseUrl . '/denuncias/detalhes?id=' . $id
                                ) ?>"
                                class="sinc-button sinc-button-secondary"
                            >
                                Consultar detalhes
                            </a>

                            <?php if ($estado === 'PENDENTE'): ?>
                                <form
                                    method="POST"
                                    action="<?= $esc($baseUrl . '/eliminar-denuncia') ?>"
                                    onsubmit="return confirm('Tens a certeza de que pretendes eliminar esta denúncia pendente?');"
                                >
                                    <input
                                        type="hidden"
                                        name="id"
                                        value="<?= $id ?>"
                                    >

                                    <button
                                        type="submit"
                                        class="sinc-button sinc-button-danger"
                                    >
                                        Eliminar
                                    </button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>