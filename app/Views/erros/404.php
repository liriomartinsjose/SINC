<?php
declare(strict_types=1);

$e = static fn($valor): string => Security::escape((string) ($valor ?? ''));
?>

<section class="empty-state">
    <h1>Denúncia não encontrada</h1>
    <p>
        O registo solicitado não existe, não está disponível
        ou não tens autorização para o consultar.
    </p>

    <a class="btn btn-primary" href="<?= $e($baseUrl) ?>/denuncias">
        Voltar às minhas denúncias
    </a>
</section>