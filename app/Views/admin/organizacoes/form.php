<?php
declare(strict_types=1);

$esc = static fn (mixed $valor): string => Security::escape((string) ($valor ?? ''));
$editar = ($modo ?? 'criar') === 'editar';
$org = $organizacao ?? [];
$action = $esc($baseUrl) . '/admin/organizacoes/' . ($editar ? 'editar?id=' . (int) ($org['id'] ?? 0) : 'criar');
?>

<section class="org-page">
    <header class="org-header">
        <div>
            <p class="org-eyebrow">GESTÃO DE ENTIDADES</p>
            <h1><?= $editar ? 'Editar organização' : 'Nova organização' ?></h1>
            <p>Preenche os dados da entidade. Os campos assinalados com * são obrigatórios.</p>
        </div>
        <a class="org-button" href="<?= $esc($baseUrl) ?>/admin/organizacoes">
            Voltar à lista
        </a>
    </header>

    <?php if (!empty($erro)): ?>
        <div class="org-error" role="alert"><?= $esc($erro) ?></div>
    <?php endif; ?>

    <form class="org-form" method="post" action="<?= $action ?>">
        <input type="hidden" name="_csrf_token"
               value="<?= $esc(Security::csrfToken()) ?>">

        <?php if ($editar): ?>
            <input type="hidden" name="id" value="<?= (int) ($org['id'] ?? 0) ?>">
        <?php endif; ?>

        <div class="org-form-section">
            <h2>Identificação</h2>
            <div class="org-form-grid">
                <div class="org-field org-field-wide">
                    <label for="nome">Nome oficial *</label>
                    <input id="nome" name="nome" required maxlength="200"
                           value="<?= $esc($org['nome'] ?? '') ?>">
                </div>

                <div class="org-field">
                    <label for="sigla">Sigla</label>
                    <input id="sigla" name="sigla" maxlength="30"
                           value="<?= $esc($org['sigla'] ?? '') ?>">
                </div>

                <div class="org-field">
                    <label for="codigo_oficial">Código oficial</label>
                    <input id="codigo_oficial" name="codigo_oficial" maxlength="50"
                           value="<?= $esc($org['codigo_oficial'] ?? '') ?>">
                </div>

                <div class="org-field">
                    <label for="tipo">Tipo *</label>
                    <select id="tipo" name="tipo" required>
                        <?php foreach ([
                            'PUBLICA' => 'Pública',
                            'PRIVADA' => 'Privada',
                            'MISTA' => 'Mista',
                        ] as $valor => $rotulo): ?>
                            <option value="<?= $valor ?>"
                                <?= ($org['tipo'] ?? 'PUBLICA') === $valor ? 'selected' : '' ?>>
                                <?= $rotulo ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="org-field">
                    <label for="nivel_administrativo">Nível administrativo *</label>
                    <select id="nivel_administrativo" name="nivel_administrativo" required>
                        <?php foreach ([
                            'COMUNAL' => 'Comunal',
                            'MUNICIPAL' => 'Municipal',
                            'PROVINCIAL' => 'Provincial',
                            'NACIONAL' => 'Nacional',
                        ] as $valor => $rotulo): ?>
                            <option value="<?= $valor ?>"
                                <?= ($org['nivel_administrativo'] ?? '') === $valor ? 'selected' : '' ?>>
                                <?= $rotulo ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </div>

        <div class="org-form-section">
            <h2>Contactos</h2>
            <div class="org-form-grid">
                <div class="org-field">
                    <label for="email">E-mail</label>
                    <input id="email" name="email" type="email" maxlength="150"
                           value="<?= $esc($org['email'] ?? '') ?>">
                </div>

                <div class="org-field">
                    <label for="telefone">Telefone</label>
                    <input id="telefone" name="telefone" maxlength="30"
                           value="<?= $esc($org['telefone'] ?? '') ?>">
                </div>

                <div class="org-field org-field-wide">
                    <label for="site">Website</label>
                    <input id="site" name="site" type="url" maxlength="255"
                           placeholder="https://..."
                           value="<?= $esc($org['site'] ?? '') ?>">
                </div>
            </div>
        </div>

        <div class="org-form-section">
            <h2>Localização</h2>
            <div class="org-form-grid">
                <div class="org-field">
                    <label for="provincia_id">Província</label>
                    <select id="provincia_id" name="provincia_id">
                        <option value="">Selecionar província</option>
                        <?php foreach ($provincias as $provincia): ?>
                            <option value="<?= (int) $provincia['id'] ?>"
                                <?= (string) ($org['provincia_id'] ?? '') === (string) $provincia['id'] ? 'selected' : '' ?>>
                                <?= $esc($provincia['nome']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="org-field">
                    <label for="municipio_id">Município</label>
                    <select id="municipio_id" name="municipio_id">
                        <option value="">Selecionar município</option>
                        <?php foreach ($municipios as $municipio): ?>
                            <option value="<?= (int) $municipio['id'] ?>"
                                <?= (string) ($org['municipio_id'] ?? '') === (string) $municipio['id'] ? 'selected' : '' ?>>
                                <?= $esc($municipio['nome']) ?>
                                <?php if (!empty($municipio['provincia_nome'])): ?>
                                    — <?= $esc($municipio['provincia_nome']) ?>
                                <?php endif; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <small>Os municípios apresentados dependem dos dados existentes na base de dados.</small>
                </div>

                <div class="org-field org-field-wide">
                    <label for="endereco">Endereço</label>
                    <input id="endereco" name="endereco" maxlength="255"
                           value="<?= $esc($org['endereco'] ?? '') ?>">
                </div>

                <div class="org-field">
                    <label for="latitude">Latitude</label>
                    <input id="latitude" name="latitude" type="number"
                           min="-90" max="90" step="any"
                           value="<?= $esc($org['latitude'] ?? '') ?>">
                </div>

                <div class="org-field">
                    <label for="longitude">Longitude</label>
                    <input id="longitude" name="longitude" type="number"
                           min="-180" max="180" step="any"
                           value="<?= $esc($org['longitude'] ?? '') ?>">
                </div>
            </div>
        </div>

        <div class="org-form-section">
            <h2>Descrição</h2>
            <div class="org-field">
                <label for="descricao">Informações adicionais</label>
                <textarea id="descricao" name="descricao" rows="4"><?= $esc($org['descricao'] ?? '') ?></textarea>
            </div>
        </div>

        <div class="org-form-footer">
            <a class="org-button" href="<?= $esc($baseUrl) ?>/admin/organizacoes">Cancelar</a>
            <button class="org-button org-button-primary" type="submit">
                <?= $editar ? 'Guardar alterações' : 'Criar organização' ?>
            </button>
        </div>
    </form>
</section>
