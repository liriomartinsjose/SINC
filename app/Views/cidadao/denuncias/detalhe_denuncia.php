<?php
$esc = static fn($v): string => htmlspecialchars(
    (string) ($v ?? ''),
    ENT_QUOTES,
    'UTF-8'
);

$denuncia = $denuncia ?? [];

$estado = strtoupper(
    (string) ($denuncia['status'] ?? 'PENDENTE')
);

$estados = [
    'PENDENTE' => 'Pendente de validação',
    'VALIDADA' => 'Validada',
    'REJEITADA' => 'Rejeitada',
    'DUPLICADA' => 'Identificada como duplicada',
];

$classeEstado = match ($estado) {
    'VALIDADA' => 'status-validada',
    'REJEITADA' => 'status-rejeitada',
    'DUPLICADA' => 'status-duplicada',
    default => 'status-pendente',
};

$id = (int) ($denuncia['id'] ?? 0);
?>

<section class="sinc-page sinc-detail-page">
    <div class="sinc-page-container">

        <a
            href="<?= $esc(base_url('minhas-denuncias')) ?>"
            class="sinc-back-link"
        >
            <span aria-hidden="true">&larr;</span>
            Voltar às minhas denúncias
        </a>

        <header class="sinc-detail-header">
            <div>
                <span class="sinc-eyebrow">DETALHE DA PARTICIPAÇÃO</span>
                <h1>Denúncia #<?= $id ?></h1>
                <p>Consulta os dados e o estado actual da tua participação.</p>
            </div>

            <span class="sinc-status <?= $esc($classeEstado) ?>">
                <?= $esc($estados[$estado] ?? $estado) ?>
            </span>
        </header>

        <article class="sinc-detail-card">
            <div class="sinc-detail-section">
                <h2>Informações da denúncia</h2>

                <dl class="sinc-detail-grid">
                    <div>
                        <dt>Título</dt>
                        <dd><?= $esc($denuncia['titulo'] ?? '') ?></dd>
                    </div>

                    <div>
                        <dt>Tipo de problema</dt>
                        <dd>
                            <?= $esc(
                                $denuncia['tipo_ocorrencia_nome']
                                ?? 'Não indicado'
                            ) ?>
                        </dd>
                    </div>

                    <div>
                        <dt>Data de submissão</dt>
                        <dd><?= $esc($denuncia['criado_em'] ?? '') ?></dd>
                    </div>

                    <div>
                        <dt>Apresentação anónima</dt>
                        <dd>
                            <?= !empty($denuncia['anonima'])
                                ? 'Sim'
                                : 'Não' ?>
                        </dd>
                    </div>
                </dl>

                <div class="sinc-detail-description">
                    <h3>Descrição</h3>
                    <p><?= nl2br($esc($denuncia['descricao'] ?? '')) ?></p>
                </div>
            </div>

            <div class="sinc-detail-section">
                <h2>Localização</h2>

                <dl class="sinc-detail-grid">
                    <div>
                        <dt>Bairro</dt>
                        <dd>
                            <?= $esc(
                                $denuncia['bairro_nome'] ?? 'Não indicado'
                            ) ?>
                        </dd>
                    </div>

                    <div>
                        <dt>Rua</dt>
                        <dd>
                            <?= $esc($denuncia['rua'] ?: 'Não indicada') ?>
                        </dd>
                    </div>

                    <div>
                        <dt>Número ou identificação</dt>
                        <dd>
                            <?= $esc($denuncia['numero'] ?: 'Não indicado') ?>
                        </dd>
                    </div>

                    <div>
                        <dt>Referência</dt>
                        <dd>
                            <?= $esc(
                                $denuncia['referencia'] ?: 'Não indicada'
                            ) ?>
                        </dd>
                    </div>

                    <div>
                        <dt>Latitude</dt>
                        <dd><?= $esc($denuncia['latitude'] ?? '') ?></dd>
                    </div>

                    <div>
                        <dt>Longitude</dt>
                        <dd><?= $esc($denuncia['longitude'] ?? '') ?></dd>
                    </div>
                </dl>
            </div>

            <div class="sinc-detail-section">
                <h2>Estado da participação</h2>

                <p>
                    <?php if ($estado === 'PENDENTE'): ?>
                        A tua denúncia foi registada e aguarda validação.
                        Ainda não está confirmada como uma ocorrência
                        operacional.
                    <?php elseif ($estado === 'VALIDADA'): ?>
                        A denúncia foi validada. A associação a uma
                        ocorrência pode ser consultada abaixo, quando existir.
                    <?php elseif ($estado === 'REJEITADA'): ?>
                        A denúncia foi rejeitada. Para conheceres o motivo,
                        o sistema deverá disponibilizar a justificação
                        registada pelo responsável pela validação.
                    <?php elseif ($estado === 'DUPLICADA'): ?>
                        A denúncia foi identificada como duplicada.
                        Consulta a ocorrência associada, caso tenha sido
                        registada.
                    <?php endif; ?>
                </p>

                <?php if (!empty($denuncia['ocorrencia_id'])): ?>
                    <div class="sinc-linked-occurrence">
                        <strong>Ocorrência associada</strong>
                        <p>
                            Esta denúncia está associada à ocorrência
                            #<?= (int) $denuncia['ocorrencia_id'] ?>.
                        </p>
                    </div>
                <?php else: ?>
                    <p class="sinc-field-help">
                        Ainda não existe uma ocorrência associada a esta
                        denúncia.
                    </p>
                <?php endif; ?>
            </div>
        </article>

        <footer class="sinc-detail-footer">
            <a
                href="<?= $esc(base_url('minhas-denuncias')) ?>"
                class="sinc-button sinc-button-secondary"
            >
                Voltar à lista
            </a>
        </footer>
    </div>
</section>