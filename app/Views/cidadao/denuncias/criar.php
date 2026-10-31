
<?php
$pageTitle = 'Nova denúncia';

$tipos = $tipos ?? [];
$bairros = $bairros ?? [];
$erros = $erros ?? [];
$dados = $dados ?? [];

$valor = static function (string $campo, string $padrao = '') use ($dados): string {
    return Security::escape((string) ($dados[$campo] ?? $padrao));
};

$baseUrl = $baseUrl ?? '/sinc';
?>

<link rel="stylesheet" href="<?= $baseUrl ?>/public/assets/css/criar-denuncias.css">

<link
    rel="stylesheet"
    href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
>

<section class="report-page">

    <div class="report-hero">
        <div class="report-hero-copy">
            <span class="report-eyebrow">
                <span class="eyebrow-line"></span>
                PARTICIPAÇÃO CIDADÃ
            </span>

            <h1>O teu olhar pode<br>mudar <span>a cidade.</span></h1>

            <p>
                Identifica um problema, partilha a sua localização
                e ajuda a transformar uma preocupação numa acção concreta.
            </p>

            <div class="hero-meta">
                <span><span class="meta-icon">◎</span> Localização precisa</span>
                <span><span class="meta-icon">↗</span> Participação activa</span>
            </div>
        </div>

        <div class="hero-visual" aria-label="Ilustração de participação cívica">
            <div class="visual-orbit orbit-one"></div>
            <div class="visual-orbit orbit-two"></div>

            <div class="visual-coordinate coordinate-top">
                <span>COORDENADAS</span>
                <strong>AO VIVO</strong>
            </div>

            <div class="visual-center">
                <div class="visual-center-ring">
                    <span class="visual-pin">⌖</span>
                </div>
            </div>

            <div class="visual-floating visual-float-left">
                <span class="float-symbol">+</span>
                <span>
                    <strong>Uma ocorrência</strong>
                    <small>Um problema identificado</small>
                </span>
            </div>

            <div class="visual-floating visual-float-right">
                <span class="float-check">✓</span>
                <span>
                    <strong>Impacto colectivo</strong>
                    <small>Uma comunidade mais activa</small>
                </span>
            </div>

            <div class="visual-grid"></div>
        </div>
    </div>

    <div class="report-progress">
        <div class="progress-heading">
            <div>
                <span class="progress-kicker">NOVA PARTICIPAÇÃO</span>
                <h2>Vamos dar voz ao problema.</h2>
            </div>

            <span class="progress-counter">
                <strong id="reportProgressNumber">01</strong>
                <span>/ 03</span>
            </span>
        </div>

        <div class="progress-track" aria-label="Progresso do formulário">
            <span class="progress-segment is-active" data-progress="1"></span>
            <span class="progress-segment" data-progress="2"></span>
            <span class="progress-segment" data-progress="3"></span>
        </div>

        <div class="progress-labels">
            <span>O problema</span>
            <span>Localização</span>
            <span>Submissão</span>
        </div>
    </div>

    <?php if ($erros !== []): ?>
        <div class="report-alert" role="alert">
            <span class="alert-symbol">!</span>
            <div>
                <strong>Precisamos de rever alguns dados.</strong>
                <ul>
                    <?php foreach ($erros as $erro): ?>
                        <li><?= Security::escape((string) $erro) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    <?php endif; ?>

    <form
        id="denunciaForm"
        class="report-form"
        method="POST"
        action="<?= $baseUrl ?>/denuncias/criar"
        enctype="multipart/form-data"
    >
        <input
            type="hidden"
            name="_csrf_token"
            value="<?= Security::escape(Security::csrfToken()) ?>"
        >

        <div class="report-layout">

            <div class="report-main-column">

                <section class="report-card" id="problemSection">
                    <div class="card-heading">
                        <div class="section-icon">01</div>
                        <div>
                            <span class="section-overline">IDENTIFICAÇÃO</span>
                            <h2>O que está a acontecer?</h2>
                            <p>Descreve o problema de forma clara e objectiva.</p>
                        </div>
                    </div>

                    <div class="field-group">
                        <label for="titulo">Título da denúncia <span>*</span></label>
                        <input
                            id="titulo"
                            name="titulo"
                            type="text"
                            maxlength="200"
                            placeholder="Ex.: Buraco perigoso na estrada principal"
                            value="<?= $valor('titulo') ?>"
                            required
                        >
                        <div class="field-foot">
                            <span>Um título curto facilita a identificação.</span>
                            <span id="titleCounter">0/200</span>
                        </div>
                    </div>

                    <div class="field-group">
                        <label for="tipo_ocorrencia_id">Categoria do problema <span>*</span></label>
                        <div class="select-wrap">
                            <select
                                id="tipo_ocorrencia_id"
                                name="tipo_ocorrencia_id"
                                required
                            >
                                <option value="">Selecciona o tipo de ocorrência</option>
                                <?php foreach ($tipos as $tipo): ?>
                                    <option
                                        value="<?= (int) $tipo['id'] ?>"
                                        <?= (string) ($dados['tipo_ocorrencia_id'] ?? '') === (string) $tipo['id'] ? 'selected' : '' ?>
                                    >
                                        <?= Security::escape($tipo['nome']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <span class="select-chevron">⌄</span>
                        </div>
                    </div>

                    <div class="field-group">
                        <label for="descricao">Descrição do problema <span>*</span></label>
                        <textarea
                            id="descricao"
                            name="descricao"
                            rows="5"
                            maxlength="20000"
                            placeholder="O que aconteceu? Desde quando? Que consequências está a causar à comunidade?"
                            required
                        ><?= $valor('descricao') ?></textarea>
                        <div class="field-foot">
                            <span>Inclui detalhes úteis para compreender a situação.</span>
                            <span id="descriptionCounter">0/20000</span>
                        </div>
                    </div>
                </section>

                <section class="report-card" id="photosSection">
                    <div class="card-heading">
                        <div class="section-icon">02</div>
                        <div>
                            <span class="section-overline">PROVA VISUAL</span>
                            <h2>Mostra-nos o que viste.</h2>
                            <p>Uma fotografia pode ajudar a compreender melhor o problema.</p>
                        </div>
                    </div>

                    <label class="upload-zone" for="imagens">
                        <input
                            id="imagens"
                            name="imagens[]"
                            type="file"
                            accept="image/jpeg,image/png,image/webp"
                            multiple
                        >

                        <span class="upload-graphic">
                            <svg viewBox="0 0 48 48" fill="none" aria-hidden="true">
                                <rect x="5" y="8" width="38" height="32" rx="7"
                                    stroke="currentColor" stroke-width="1.8"/>
                                <circle cx="17" cy="19" r="3.5"
                                    stroke="currentColor" stroke-width="1.8"/>
                                <path d="M7 34L18 25L25 31L31 26L41 35"
                                    stroke="currentColor" stroke-width="1.8"
                                    stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M35 5V15M30 10H40"
                                    stroke="currentColor" stroke-width="1.8"
                                    stroke-linecap="round"/>
                            </svg>
                        </span>

                        <strong>Arrasta as imagens para aqui</strong>
                        <span>ou <span class="upload-link">selecciona do teu dispositivo</span></span>
                        <small>JPEG, PNG ou WebP · Máximo de 5 imagens · 5 MB por imagem</small>
                    </label>

                    <div id="imagePreview" class="image-preview" aria-live="polite"></div>

                    <p class="upload-note">
                        <span>ⓘ</span>
                        Evita fotografar rostos, documentos pessoais ou outros dados privados.
                    </p>
                </section>

                <section class="report-card" id="locationSection">
                    <div class="card-heading">
                        <div class="section-icon">03</div>
                        <div>
                            <span class="section-overline">LOCALIZAÇÃO</span>
                            <h2>Onde está o problema?</h2>
                            <p>Usa o GPS ou selecciona directamente o ponto no mapa.</p>
                        </div>
                    </div>

                    <div class="location-status" id="locationStatus" aria-live="polite">
                        <span class="location-status-icon">⌖</span>
                        <span>
                            <strong id="locationStatusTitle">Localização por confirmar</strong>
                            <small id="locationStatusText">Clica no mapa ou usa a localização do dispositivo.</small>
                        </span>
                        <span class="status-pill" id="locationPill">PENDENTE</span>
                    </div>

                    <div class="map-toolbar">
                        <span><span class="map-live-dot"></span> MAPA INTERACTIVO</span>

                        <button
                            class="gps-button"
                            type="button"
                            id="gpsButton"
                        >
                            <span>⌖</span> Usar o meu GPS
                        </button>
                    </div>

                    <div
                        id="reportMap"
                        class="report-map"
                        role="application"
                        aria-label="Mapa interactivo. Clica para seleccionar a localização do problema."
                    ></div>

                    <p class="map-help">
                        <span>↗</span>
                        Clica no mapa para colocar o marcador exactamente onde está o problema.
                    </p>

                    <input
                        type="hidden"
                        name="latitude"
                        id="latitude"
                        value="<?= $valor('latitude') ?>"
                        required
                    >

                    <input
                        type="hidden"
                        name="longitude"
                        id="longitude"
                        value="<?= $valor('longitude') ?>"
                        required
                    >

                    <div class="coordinates-readout">
                        <div>
                            <span>LATITUDE</span>
                            <strong id="latitudeDisplay">—</strong>
                        </div>
                        <div>
                            <span>LONGITUDE</span>
                            <strong id="longitudeDisplay">—</strong>
                        </div>
                        <span class="coordinate-icon">◎</span>
                    </div>

                    <div class="location-divider">
                        <span></span>
                        <small>DETALHES DO LOCAL</small>
                        <span></span>
                    </div>

                    <div class="field-group">
                        <label for="bairro_id">Bairro <span>*</span></label>
                        <div class="select-wrap">
                            <select id="bairro_id" name="bairro_id" required>
                                <option value="">Selecciona o bairro</option>
                                <?php foreach ($bairros as $bairro): ?>
                                    <option
                                        value="<?= (int) $bairro['id'] ?>"
                                        <?= (string) ($dados['bairro_id'] ?? '') === (string) $bairro['id'] ? 'selected' : '' ?>
                                    >
                                        <?= Security::escape($bairro['nome']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <span class="select-chevron">⌄</span>
                        </div>
                    </div>

                    <div class="field-row">
                        <div class="field-group">
                            <label for="rua">Rua ou avenida</label>
                            <input
                                id="rua"
                                name="rua"
                                type="text"
                                maxlength="200"
                                placeholder="Nome da rua"
                                value="<?= $valor('rua') ?>"
                            >
                        </div>

                        <div class="field-group">
                            <label for="numero">N.º da porta</label>
                            <input
                                id="numero"
                                name="numero"
                                type="text"
                                maxlength="50"
                                placeholder="Opcional"
                                value="<?= $valor('numero') ?>"
                            >
                        </div>
                    </div>

                    <div class="field-group">
                        <label for="referencia">Ponto de referência</label>
                        <input
                            id="referencia"
                            name="referencia"
                            type="text"
                            placeholder="Ex.: junto à escola ou ao mercado"
                            value="<?= $valor('referencia') ?>"
                        >
                        <small class="field-hint">
                            Uma referência conhecida ajuda a localizar o problema no terreno.
                        </small>
                    </div>
                </section>

            </div>

            <aside class="report-side-column">

                <section class="side-card side-summary">
                    <span class="side-card-kicker">RESUMO DA PARTICIPAÇÃO</span>

                    <div class="summary-emblem">
                        <span>◎</span>
                    </div>

                    <h3>Cada detalhe<br>faz a diferença.</h3>
                    <p>
                        Informação clara e uma localização correcta ajudam a
                        encaminhar o problema.
                    </p>

                    <div class="summary-divider"></div>

                    <div class="summary-row">
                        <span class="summary-check">✓</span>
                        <span>
                            <strong>Descrição objectiva</strong>
                            <small>Explica o que precisa de atenção.</small>
                        </span>
                    </div>

                    <div class="summary-row">
                        <span class="summary-check">✓</span>
                        <span>
                            <strong>Localização confirmada</strong>
                            <small>Ajuda a identificar o local certo.</small>
                        </span>
                    </div>

                    <div class="summary-row">
                        <span class="summary-check">✓</span>
                        <span>
                            <strong>Fotografias úteis</strong>
                            <small>Acrescentam contexto visual.</small>
                        </span>
                    </div>
                </section>

                <section class="side-card privacy-card">
                    <div class="privacy-icon">⌑</div>
                    <div>
                        <h3>A tua privacidade importa.</h3>
                        <p>
                            A tua conta é utilizada para registar a participação.
                            Podes indicar que a denúncia deve ser anónima.
                        </p>
                    </div>

                    <label class="privacy-toggle" for="anonima">
                        <span class="toggle-copy">
                            <strong>Denúncia anónima</strong>
                            <small>Não mostrar a tua identidade publicamente.</small>
                        </span>

                        <input
                            type="checkbox"
                            name="anonima"
                            id="anonima"
                            value="1"
                            <?= !empty($dados['anonima']) ? 'checked' : '' ?>
                        >

                        <span class="toggle-track"></span>
                    </label>

                    <p class="privacy-disclaimer">
                        A opção anónima não significa que a conta deixe de estar
                        associada internamente ao registo.
                    </p>
                </section>

                <section class="side-card impact-card">
                    <span class="impact-star">✳</span>
                    <span class="side-card-kicker">CIDADANIA ACTIVA</span>
                    <h3>Uma cidade melhor começa com alguém que não fica indiferente.</h3>
                    <span class="impact-signature">SINC <span>—</span> A tua voz conta.</span>
                </section>

                <div class="submit-card">
                    <div class="submit-secure">
                        <span>✓</span> Revê os dados antes de enviar
                    </div>

                    <button type="submit" id="submitReport" class="submit-button">
                        <span>Submeter denúncia</span>
                        <span class="submit-arrow">↗</span>
                    </button>

                    <p>
                        Ao submeter, confirmas que as informações fornecidas
                        correspondem ao que observaste.
                    </p>
                </div>

            </aside>

        </div>
    </form>

    <footer class="report-footer">
        <span>© <?= date('Y') ?> SINC — Sistema de Inteligência Cívica</span>
        <span>Construído para dar voz à comunidade <span class="footer-heart">♥</span></span>
    </footer>
</section>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
(() => {
    'use strict';

    const form = document.getElementById('denunciaForm');
    const title = document.getElementById('titulo');
    const description = document.getElementById('descricao');
    const titleCounter = document.getElementById('titleCounter');
    const descriptionCounter = document.getElementById('descriptionCounter');
    const imageInput = document.getElementById('imagens');
    const imagePreview = document.getElementById('imagePreview');

    const latitudeInput = document.getElementById('latitude');
    const longitudeInput = document.getElementById('longitude');
    const latitudeDisplay = document.getElementById('latitudeDisplay');
    const longitudeDisplay = document.getElementById('longitudeDisplay');

    const statusTitle = document.getElementById('locationStatusTitle');
    const statusText = document.getElementById('locationStatusText');
    const locationPill = document.getElementById('locationPill');
    const locationStatus = document.getElementById('locationStatus');
    const gpsButton = document.getElementById('gpsButton');

    let marker = null;
    let map = null;
    let previews = [];

    function updateCounter(input, output) {
        output.textContent = `${input.value.length}/${input.maxLength}`;
    }

    title.addEventListener('input', () => updateCounter(title, titleCounter));
    description.addEventListener('input', () => updateCounter(description, descriptionCounter));

    updateCounter(title, titleCounter);
    updateCounter(description, descriptionCounter);

    function actualizarProgresso() {
        const problemComplete =
            title.value.trim() !== '' &&
            document.getElementById('tipo_ocorrencia_id').value !== '' &&
            description.value.trim() !== '';

        const locationComplete =
            latitudeInput.value !== '' &&
            longitudeInput.value !== '' &&
            document.getElementById('bairro_id').value !== '';

        const step = !problemComplete ? 1 : !locationComplete ? 2 : 3;

        document.getElementById('reportProgressNumber').textContent =
            String(step).padStart(2, '0');

        document.querySelectorAll('[data-progress]').forEach((element) => {
            const number = Number(element.dataset.progress);
            element.classList.toggle('is-active', number <= step);
            element.classList.toggle('is-complete', number < step);
        });
    }

    form.addEventListener('input', actualizarProgresso);
    form.addEventListener('change', actualizarProgresso);

    function definirLocalizacao(lat, lng, zoom = 17) {
        latitudeInput.value = Number(lat).toFixed(8);
        longitudeInput.value = Number(lng).toFixed(8);

        latitudeDisplay.textContent = Number(lat).toFixed(6);
        longitudeDisplay.textContent = Number(lng).toFixed(6);

        statusTitle.textContent = 'Localização seleccionada';
        statusText.textContent = 'Confirma se o marcador está no local correcto.';
        locationPill.textContent = 'CONFIRMADA';
        locationPill.classList.add('is-confirmed');
        locationStatus.classList.add('is-located');

        if (map && window.L) {
            const point = [Number(lat), Number(lng)];

            if (!marker) {
                marker = L.marker(point).addTo(map);
            } else {
                marker.setLatLng(point);
            }

            map.setView(point, zoom);
        }

        actualizarProgresso();
    }

    if (window.L) {
        map = L.map('reportMap', {
            zoomControl: false,
            scrollWheelZoom: false
        }).setView([-8.8383, 13.2344], 12);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; OpenStreetMap'
        }).addTo(map);

        L.control.zoom({ position: 'bottomright' }).addTo(map);

        map.on('click', (event) => {
            definirLocalizacao(event.latlng.lat, event.latlng.lng);
        });

        const initialLat = Number(latitudeInput.value);
        const initialLng = Number(longitudeInput.value);

        if (
            latitudeInput.value !== '' &&
            longitudeInput.value !== '' &&
            Number.isFinite(initialLat) &&
            Number.isFinite(initialLng)
        ) {
            definirLocalizacao(initialLat, initialLng);
        }

        setTimeout(() => map.invalidateSize(), 200);
    } else {
        document.getElementById('reportMap').innerHTML =
            '<p class="map-unavailable">Não foi possível carregar o mapa. Verifica a ligação à Internet e tenta novamente.</p>';
    }

    gpsButton.addEventListener('click', () => {
        if (!navigator.geolocation) {
            statusTitle.textContent = 'GPS indisponível';
            statusText.textContent = 'O teu navegador não disponibiliza geolocalização. Selecciona o ponto no mapa.';
            return;
        }

        gpsButton.disabled = true;
        gpsButton.innerHTML = '<span>⌖</span> A obter localização…';

        navigator.geolocation.getCurrentPosition(
            (position) => {
                definirLocalizacao(
                    position.coords.latitude,
                    position.coords.longitude
                );

                gpsButton.disabled = false;
                gpsButton.innerHTML = '<span>✓</span> Localização obtida';
            },
            (error) => {
                const mensagens = {
                    1: 'Permite o acesso à localização no navegador ou selecciona o ponto no mapa.',
                    2: 'Não foi possível determinar a posição. Tenta novamente ou usa o mapa.',
                    3: 'A localização demorou demasiado. Tenta novamente ou usa o mapa.'
                };

                statusTitle.textContent = 'Não foi possível obter o GPS';
                statusText.textContent = mensagens[error.code] || 'Selecciona o local no mapa.';

                gpsButton.disabled = false;
                gpsButton.innerHTML = '<span>⌖</span> Tentar novamente';
            },
            {
                enableHighAccuracy: true,
                timeout: 15000,
                maximumAge: 60000
            }
        );
    });

    function limparPreviews() {
        previews.forEach((url) => URL.revokeObjectURL(url));
        previews = [];
        imagePreview.replaceChildren();
    }

    imageInput.addEventListener('change', () => {
        limparPreviews();

        const files = Array.from(imageInput.files || []);
        const permitidos = ['image/jpeg', 'image/png', 'image/webp'];
        const maxSize = 5 * 1024 * 1024;

        if (files.length > 5) {
            alert('Podes seleccionar no máximo cinco imagens.');
            imageInput.value = '';
            return;
        }

        for (const file of files) {
            if (!permitidos.includes(file.type) || file.size > maxSize) {
                alert('Selecciona imagens JPEG, PNG ou WebP com até 5 MB cada.');
                imageInput.value = '';
                limparPreviews();
                return;
            }
        }

        files.forEach((file) => {
            const url = URL.createObjectURL(file);
            previews.push(url);

            const card = document.createElement('div');
            card.className = 'preview-card';

            const image = document.createElement('img');
            image.src = url;
            image.alt = 'Pré-visualização de ' + file.name;

            const caption = document.createElement('div');
            caption.className = 'preview-caption';

            const name = document.createElement('span');
            name.textContent = file.name;

            const size = document.createElement('small');
            size.textContent = `${(file.size / (1024 * 1024)).toFixed(2)} MB`;

            caption.append(name, size);
            card.append(image, caption);
            imagePreview.append(card);
        });
    });

    form.addEventListener('submit', (event) => {
        if (!latitudeInput.value || !longitudeInput.value) {
            event.preventDefault();

            document.getElementById('locationSection').scrollIntoView({
                behavior: 'smooth',
                block: 'center'
            });

            statusTitle.textContent = 'Falta confirmar a localização';
            statusText.textContent = 'Selecciona o ponto no mapa ou usa o GPS antes de submeter.';

            locationStatus.classList.add('location-error');
            return;
        }

        const button = document.getElementById('submitReport');
        button.disabled = true;
        button.querySelector('span:first-child').textContent = 'A submeter…';
    });

    actualizarProgresso();
})();
</script>
