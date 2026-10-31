<?php
declare(strict_types=1);

$esc = static fn (mixed $valor): string => Security::escape((string) ($valor ?? ''));

$mensagens = [
    'criada' => 'Organização criada com sucesso.',
    'atualizada' => 'Organização atualizada com sucesso.',
    'estado_atualizado' => 'Estado atualizado com sucesso.',
    'erro' => 'Não foi possível atualizar o estado.',
    'pedido_invalido' => 'O pedido enviado é inválido.',
];
?>

<section class="org-page">
    <header class="org-header">
        <div>
            <p class="org-eyebrow">ADMINISTRAÇÃO</p>
            <h1>Organizações</h1>
            <p>Gere os serviços e as entidades associados ao SINC.</p>
        </div>
        <a class="org-button org-button-primary"
           href="<?= $esc($baseUrl) ?>/admin/organizacoes/criar">
            + Nova organização
        </a>
    </header>

    <?php if (isset($mensagens[$mensagem ?? ''])): ?>
        <div class="org-notice" role="status">
            <?= $esc($mensagens[$mensagem]) ?>
        </div>
    <?php endif; ?>

    <form class="org-search" method="get"
          action="<?= $esc($baseUrl) ?>/admin/organizacoes">
        <label for="pesquisa">Pesquisar organizações</label>
        <div class="org-search-row">
            <input id="pesquisa" name="pesquisa" type="search"
                   value="<?= $esc($pesquisa ?? '') ?>"
                   placeholder="Nome, sigla ou código oficial">
            <button class="org-button" type="submit">Pesquisar</button>
        </div>
    </form>

    <div class="org-table-wrap">
        <table class="org-table">
            <thead>
                <tr>
                    <th>Organização</th>
                    <th>Tipo</th>
                    <th>Nível</th>
                    <th>Localização</th>
                    <th>Estado</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
            <?php if (empty($organizacoes)): ?>
                <tr>
                    <td colspan="6" class="org-empty">
                        Não existem organizações para apresentar.
                        Cria a primeira organização para começar.
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($organizacoes as $org): ?>
                    <tr>
                        <td>
                            <strong><?= $esc($org['nome'] ?? '') ?></strong>
                            <small>
                                <?= $esc($org['sigla'] ?? '') ?>
                                <?php if (!empty($org['codigo_oficial'])): ?>
                                    · <?= $esc($org['codigo_oficial']) ?>
                                <?php endif; ?>
                            </small>
                        </td>
                        <td><?= $esc($org['tipo'] ?? '') ?></td>
                        <td><?= $esc($org['nivel_administrativo'] ?? '') ?></td>
                        <td>
                            <?= $esc($org['municipio_nome'] ?? '') ?>
                            <?php if (!empty($org['provincia_nome'])): ?>
                                <?= !empty($org['municipio_nome']) ? ', ' : '' ?>
                                <?= $esc($org['provincia_nome']) ?>
                            <?php endif; ?>
                            <?php if (empty($org['municipio_nome']) && empty($org['provincia_nome'])): ?>
                                —
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="org-status <?= !empty($org['ativo']) ? 'is-active' : 'is-inactive' ?>">
                                <?= !empty($org['ativo']) ? 'Ativa' : 'Inativa' ?>
                            </span>
                        </td>
                        <td>
                            <div class="org-actions">
                                <a class="org-action-link"
                                   href="<?= $esc($baseUrl) ?>/admin/organizacoes/editar?id=<?= (int) $org['id'] ?>">
                                    Editar
                                </a>

                                <form method="post"
                                      action="<?= $esc($baseUrl) ?>/admin/organizacoes/estado">
                                    <input type="hidden" name="_csrf_token"
                                           value="<?= $esc(Security::csrfToken()) ?>">
                                    <input type="hidden" name="id"
                                           value="<?= (int) $org['id'] ?>">
                                    <input type="hidden" name="ativo"
                                           value="<?= !empty($org['ativo']) ? '0' : '1' ?>">
                                    <button class="org-action-link" type="submit">
                                        <?= !empty($org['ativo']) ? 'Desativar' : 'Ativar' ?>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>
