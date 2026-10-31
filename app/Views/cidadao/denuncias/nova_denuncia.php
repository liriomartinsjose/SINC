```php
<?php
$esc = static fn($v): string => htmlspecialchars(
    (string) ($v ?? ''),
    ENT_QUOTES,
    'UTF-8'
);

$old = $old ?? [];
$errors = $errors ?? [];
$tiposOcorrencia = $tiposOcorrencia ?? [];
$bairros = $bairros ?? [];

$valor = static fn(string $campo, $padrao = '') =>
    $old[$campo] ?? $padrao;

$urlMinhas = base_url('minhas-denu$baseUrl = rtrim((string) ($baseUrl ?? '/sinc'), '/');
$urlMinhas = $baseUrl . '/denuncias';
$urlCriar = $baseUrl . '/denuncias/criar';ncias');
?>

<section class="sinc-page sinc-denuncia-page">
    <div class="sinc-page-container">

        <a class="sinc-back-link" href="<?= $esc($urlMinhas) ?>">
            <span aria-hidden="true">&larr;</span>
            Voltar às minhas denúncias
        </a>

        <header class="sinc-page-header">
            <span class="sinc-eyebrow">PARTICIPAÇÃO CÍVICA</span>
            <h1>Comunicar um problema</h1>
            <p>
                A tua participação pode ajudar a identificar problemas
                na comunidade. Descreve a situação com clareza e indica
                o local onde acontece.
            </p>
        </header>

        <?php if ($errors): ?>
            <div class="sinc-alert sinc-alert-error" role="alert">
                <strong>Não foi possível concluir a operação.</strong>
                <ul>
                    <?php foreach ($errors as $erro): ?>
                        <li><?= $esc($erro) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <?php if (!$tiposOcorrencia || !$bairros): ?>
            <div class="sinc-alert sinc-alert-warning" role="alert">
                O formulário não pode ser submetido enquanto não existirem
                tipos de problema e bairros disponíveis.
            </div>
        <?php endif; ?>

        <form
            method="POST"
            action="<?= $esc($urlCriar) ?>"
            class="sinc-form"
            id="formNovaDenuncia"
        >
            <section class="sinc-form-section">
                <div class="sinc-section-heading">
                    <span class="sinc-section-number">01</span>
                    <div>
                        <h2>O que aconteceu?</h2>
                        <p>Identifica o problema que pretendes comunicar.</p>
                    </div>
                </div>

                <div class="sinc-field">
                    <label for="titulo">Título da denúncia *</label>
                    <input
                        type="text"
                        id="titulo"
                        name="titulo"
                        maxlength="200"
                        minlength="5"
                        required
                        value="<?= $esc($valor('titulo')) ?>"
                        placeholder="Ex.: Buraco na estrada junto ao mercado"
                    >
                    <small>Entre 5 e 200 caracteres.</small>
                </div>

                <div class="sinc-field">
                    <label for="tipo_ocorrencia_id">Tipo de problema *</label>
                    <select
                        id="tipo_ocorrencia_id"
                        name="tipo_ocorrencia_id"
                        required
                    >
                        <option value="">Selecciona uma categoria</option>

                        <?php foreach ($tiposOcorrencia as $tipo): ?>
                            <option
                                value="<?= $esc($tipo['id']) ?>"
                                <?= (string) $valor('tipo_ocorrencia_id') ===
                                    (string) $tipo['id']
                                    ? 'selected' : '' ?>
                            >
                                <?= $esc($tipo['nome']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="sinc-field">
                    <label for="descricao">Descrição do problema *</label>
                    <textarea
                        id="descricao"
                        name="descricao"
                        rows="6"
                        minlength="10"
                        required
                        placeholder="Explica o que aconteceu, desde quando existe o problema e como afecta a comunidade."
                    ><?= $esc($valor('descricao')) ?></textarea>
                    <small>Inclui apenas informações relevantes e que consigas descrever com exactidão.</small>
                </div>
            </section>

            <section class="sinc-form-section">
                <div class="sinc-section-heading">
                    <span class="sinc-section-number">02</span>
                    <div>
                        <h2>Onde aconteceu?</h2>
                        <p>Uma localização correcta facilita a verificação.</p>
                    </div>
                </div>

                <div class="sinc-field">
                    <label for="bairro_id">Bairro *</label>
                    <select id="bairro_id" name="bairro_id" required>
                        <option value="">Selecciona o bairro</option>

                        <?php foreach ($bairros as $bairro): ?>
                            <option
                                value="<?= $esc($bairro['id']) ?>"
                                <?= (string) $valor('bairro_id') ===
                                    (string) $bairro['id']
                                    ? 'selected' : '' ?>
                            >
                                <?= $esc($bairro['nome']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="sinc-field-grid">
                    <div class="sinc-field">
                        <label for="rua">Rua ou avenida</label>
                        <input
                            type="text"
                            id="rua"
                            name="rua"
                            maxlength="200"
                            value="<?= $esc($valor('rua')) ?>"
                            placeholder="Nome da rua"
                        >
                    </div>

                    <div class="sinc-field">
                        <label for="numero">Número ou identificação</label>
                        <input
                            type="text"
                            id="numero"
                            name="numero"
                            maxlength="50"
                            value="<?= $esc($valor('numero')) ?>"
                            placeholder="N.º ou referência do local"
                        >
                    </div>
                </div>

                <div class="sinc-field">
                    <label for="referencia">Ponto de referência</label>
                    <textarea
                        id="referencia"
                        name="referencia"
                        rows="3"
                        placeholder="Ex.: perto da escola ou em frente ao centro de saúde"
                    ><?= $esc($valor('referencia')) ?></textarea>
                </div>

                <div class="sinc-field-grid">
                    <div class="sinc-field">
                        <label for="latitude">Latitude *</label>
                        <input
                            type="number"
                            id="latitude"
                            name="latitude"
                            min="-90"
                            max="90"
                            step="any"
                            required
                            value="<?= $esc($valor('latitude')) ?>"
                            placeholder="-8.8383"
                        >
                    </div>

                    <div class="sinc-field">
                        <label for="longitude">Longitude *</label>
                        <input
                            type="number"
                            id="longitude"
                            name="longitude"
                            min="-180"
                            max="180"
                            step="any"
                            required
                            value="<?= $esc($valor('longitude')) ?>"
                            placeholder="13.2344"
                        >
                    </div>
                </div>

                <p class="sinc-field-help">
                    Introduz as coordenadas geográficas do local exacto.
                    Os valores apresentados nos exemplos são ilustrativos.
                </p>
            </section>

            <section class="sinc-form-section">
                <div class="sinc-section-heading">
                    <span class="sinc-section-number">03</span>
                    <div>
                        <h2>Privacidade</h2>
                        <p>Escolhe como pretendes apresentar a denúncia.</p>
                    </div>
                </div>

                <label class="sinc-checkbox-card" for="anonima">
                    <input
                        type="checkbox"
                        id="anonima"
                        name="anonima"
                        value="1"
                        <?= (string) $valor('anonima') === '1'
                            ? 'checked' : '' ?>
                    >
                    <span>
                        <strong>Apresentar anonimamente</strong>
                        <small>
                            A denúncia continuará associada à conta para
                            controlo interno. A aplicação deverá impedir
                            a divulgação pública da identidade.
                        </small>
                    </span>
                </label>
            </section>

            <footer class="sinc-form-footer">
                <p>
                    <span aria-hidden="true">*</span>
                    Campos obrigatórios.
                </p>

                <div class="sinc-form-actions">
                    <a
                        href="<?= $esc($urlMinhas) ?>"
                        class="sinc-button sinc-button-secondary"
                    >
                        Cancelar
                    </a>

                    <button
                        type="submit"
                        class="sinc-button sinc-button-primary"
                        <?= !$tiposOcorrencia || !$bairros
                            ? 'disabled' : '' ?>
                    >
                        Submeter denúncia
                        <span aria-hidden="true">&rarr;</span>
                    </button>
                </div>
            </footer>
        </form>
    </div>
</section>
```