<?php
$e = static fn($valor) => Security::escape((string) ($valor ?? ''));

$estadosDisponiveis = [
    'PENDENTE' => 'Pendente',
    'VALIDADA' => 'Validada',
    'REJEITADA' => 'Rejeitada',
    'DUPLICADA' => 'Duplicada',
];

$base = rtrim($baseUrl, '/');
?>
<link
    rel="stylesheet"
    href="<?= $baseUrl ?>/public/assets/css/admin-denuncias.css"
>

<div class="admin-denuncias">
    <header class="pagina-cabecalho">
        <div>
            <h1>Gestão de denúncias</h1>
            <p>Consulta, analisa e atualiza as denúncias submetidas pelos cidadãos.</p>
        </div>
    </header>

    <?php if (!empty($flash)): ?>
        <div class="alerta alerta-<?= $e($flash['tipo'] ?? 'erro') ?>">
            <?= $e($flash['mensagem'] ?? '') ?>
        </div>
    <?php endif; ?>

    <section class="estatisticas-denuncias">
        <article>
            <span>Total</span>
            <strong><?= (int) ($estatisticas['total'] ?? 0) ?></strong>
        </article>
        <article>
            <span>Pendentes</span>
            <strong><?= (int) ($estatisticas['pendentes'] ?? 0) ?></strong>
        </article>
        <article>
            <span>Validadas</span>
            <strong><?= (int) ($estatisticas['validadas'] ?? 0) ?></strong>
        </article>
        <article>
            <span>Rejeitadas</span>
            <strong><?= (int) ($estatisticas['rejeitadas'] ?? 0) ?></strong>
        </article>
        <article>
            <span>Duplicadas</span>
            <strong><?= (int) ($estatisticas['duplicadas'] ?? 0) ?></strong>
        </article>
    </section>

    <form method="GET"
          action="<?= $e($base) ?>/admin/denuncias"
          class="filtros-denuncias">

        <input
            type="search"
            name="pesquisa"
            value="<?= $e($pesquisa) ?>"
            placeholder="Pesquisar título, descrição ou UUID"
        >

        <select name="estado">
            <option value="">Todos os estados</option>

            <?php foreach ($estadosDisponiveis as $valor => $rotulo): ?>
                <option
                    value="<?= $e($valor) ?>"
                    <?= $estado === $valor ? 'selected' : '' ?>
                >
                    <?= $e($rotulo) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <button type="submit">Pesquisar</button>

        <a href="<?= $e($base) ?>/admin/denuncias">Limpar filtros</a>
    </form>

    <div class="tabela-responsiva">
        <table class="tabela-denuncias">
            <thead>
                <tr>
                    <th>Denúncia</th>
                    <th>Tipo</th>
                    <th>Localização</th>
                    <th>Autor</th>
                    <th>Data</th>
                    <th>Estado</th>
                    <th>Atualizar</th>
                </tr>
            </thead>

            <tbody>
            <?php if (empty($denuncias)): ?>
                <tr>
                    <td colspan="7">Não foram encontradas denúncias.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($denuncias as $d): ?>
                    <tr>
                        <td>
                            <strong><?= $e($d['titulo']) ?></strong>
                            <small><?= $e($d['uuid']) ?></small>
                        </td>

                        <td><?= $e($d['tipo_ocorrencia']) ?></td>

                        <td>
                            <?= $e($d['bairro'] ?? 'Bairro não indicado') ?>
                            <?php if (!empty($d['rua'])): ?>
                                <small><?= $e($d['rua']) ?></small>
                            <?php endif; ?>
                        </td>

                        <td>
                            <?= !empty($d['anonima'])
                                ? 'Anónima'
                                : $e($d['nome_cidadao'] ?? 'Utilizador indisponível') ?>
                        </td>

                        <td><?= $e($d['criado_em']) ?></td>

                        <td>
                            <span class="estado-denuncia estado-<?= strtolower($e($d['status'])) ?>">
                                <?= $e($estadosDisponiveis[$d['status']] ?? $d['status']) ?>
                            </span>
                        </td>

                        <td>
                            <form method="POST"
                                  action="<?= $e($base) ?>/admin/denuncias/estado"
                                  class="form-estado-denuncia">

                                <input
                                    type="hidden"
                                    name="_csrf_token"
                                    value="<?= $e($csrfToken) ?>"
                                >

                                <input
                                    type="hidden"
                                    name="id"
                                    value="<?= (int) $d['id'] ?>"
                                >

                                <select name="status" aria-label="Novo estado">
                                    <?php foreach ($estadosDisponiveis as $valor => $rotulo): ?>
                                        <option
                                            value="<?= $e($valor) ?>"
                                            <?= $d['status'] === $valor ? 'selected' : '' ?>
                                        >
                                            <?= $e($rotulo) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>

                                <button type="submit">Guardar</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
