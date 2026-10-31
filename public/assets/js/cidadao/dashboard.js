(() => {
    'use strict';

    const root = document.querySelector('.civic-dashboard');

    if (!root) return;

    const data = Array.isArray(window.SINC_DASHBOARD_DATA)
        ? window.SINC_DASHBOARD_DATA
        : [];

    const elements = {
        search: document.getElementById('civic-search'),
        province: document.getElementById('civic-province'),
        category: document.getElementById('civic-category'),
        severity: document.getElementById('civic-severity'),
        reset: document.getElementById('civic-reset-filters'),
        emptyReset: document.getElementById('civic-empty-reset'),
        sort: document.getElementById('civic-sort'),
        map: document.getElementById('civic-map'),
        feed: document.getElementById('civic-feed'),
        miniFeed: document.getElementById('civic-mini-feed'),
        visibleCount: document.getElementById('civic-visible-count'),
        emptyState: document.getElementById('civic-empty-state')
    };

    const severityRank = {
        CRITICA: 4,
        ALTA: 3,
        MEDIA: 2,
        BAIXA: 1
    };

    const severityColor = {
        CRITICA: '#b83f4b',
        ALTA: '#df8138',
        MEDIA: '#c6a044',
        BAIXA: '#4787ad'
    };

    let map = null;
    let markerLayer = null;
    let markersById = new Map();
    let filteredOccurrences = [...data];

    const normalize = (value) => String(value ?? '')
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '')
        .toLocaleLowerCase('pt');

    const safeText = (value, fallback = '') => {
        const text = String(value ?? '').trim();
        return text || fallback;
    };

    const severityName = (value) => {
        const names = {
            CRITICA: 'Crítica',
            ALTA: 'Alta',
            MEDIA: 'Média',
            BAIXA: 'Baixa'
        };

        return names[String(value ?? '').toUpperCase()] || 'Não indicada';
    };

    const formatDate = (value) => {
        if (!value) return 'Data não disponível';

        const normalizedDate = String(value).replace(' ', 'T');
        const date = new Date(normalizedDate);

        if (Number.isNaN(date.getTime())) {
            return String(value);
        }

        return new Intl.DateTimeFormat('pt-PT', {
            day: '2-digit',
            month: 'short',
            year: 'numeric'
        }).format(date);
    };

    const locationName = (item) => {
        return [
            item.bairro_nome,
            item.comuna_nome,
            item.municipio_nome,
            item.provincia_nome
        ]
            .map((value) => safeText(value))
            .filter(Boolean)
            .filter((value, index, array) =>
                array.indexOf(value) === index
            )
            .join(', ') || 'Localização não especificada';
    };

    const detailsUrl = (item) => {
        const basePath = window.location.pathname.startsWith('/sinc')
            ? '/sinc'
            : '';

        return `${basePath}/denuncias/detalhes?id=${encodeURIComponent(item.id)}`;
    };

    const createElement = (tag, className, text) => {
        const element = document.createElement(tag);

        if (className) {
            element.className = className;
        }

        if (text !== undefined && text !== null) {
            element.textContent = String(text);
        }

        return element;
    };

    function initializeMap() {
        if (!elements.map) return;

        if (!window.L) {
            elements.map.replaceChildren(
                createElement(
                    'p',
                    'civic-loading-message',
                    'Não foi possível carregar o mapa. Verifica a ligação à Internet.'
                )
            );
            return;
        }

        // Centro aproximado de Angola. Não representa uma ocorrência.
        map = L.map(elements.map, {
            zoomControl: true,
            scrollWheelZoom: false
        }).setView([-12.5, 17.5], 5);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 18,
            attribution: '&copy; OpenStreetMap contributors'
        }).addTo(map);

        markerLayer = L.layerGroup().addTo(map);

        // Evita zoom acidental ao percorrer a página.
        elements.map.addEventListener('focusin', () => {
            map.scrollWheelZoom.enable();
        });

        elements.map.addEventListener('focusout', () => {
            map.scrollWheelZoom.disable();
        });
    }

    function buildPopup(item) {
        const container = createElement('div', 'civic-popup');

        const title = createElement(
            'div',
            'civic-popup-title',
            safeText(item.titulo, 'Ocorrência sem título')
        );

        const category = createElement(
            'div',
            '',
            safeText(item.categoria_nome, 'Categoria não indicada')
        );

        const location = createElement(
            'div',
            'civic-popup-meta',
            locationName(item)
        );

        const status = createElement(
            'div',
            'civic-popup-meta',
            `Estado: ${safeText(item.estado_nome, 'Não indicado')}`
        );

        const link = createElement('a', 'civic-card-link', 'Ver detalhes →');
        link.href = detailsUrl(item);

        container.append(title, category, location, status, link);

        return container;
    }

    function updateMarkers(items) {
        if (!map || !markerLayer) return;

        markerLayer.clearLayers();
        markersById = new Map();

        items.forEach((item) => {
            const lat = Number(item.latitude);
            const lng = Number(item.longitude);

            if (
                !Number.isFinite(lat) ||
                !Number.isFinite(lng) ||
                lat < -90 || lat > 90 ||
                lng < -180 || lng > 180
            ) {
                return;
            }

            const gravity = String(item.gravidade ?? 'MEDIA').toUpperCase();
            const color = severityColor[gravity] || '#4787ad';

            const marker = L.circleMarker([lat, lng], {
                radius: gravity === 'CRITICA' ? 9 : 7,
                color,
                weight: 2,
                opacity: 1,
                fillColor: color,
                fillOpacity: 0.78
            });

            marker.bindPopup(buildPopup(item), {
                maxWidth: 270
            });

            marker.on('click', () => {
                highlightOccurrence(item.id, false);
            });

            marker.addTo(markerLayer);
            markersById.set(String(item.id), marker);
        });
    }

    function highlightOccurrence(id, openPopup = true) {
        const marker = markersById.get(String(id));

        if (!marker || !map) return;

        map.setView(marker.getLatLng(), Math.max(map.getZoom(), 10), {
            animate: true
        });

        if (openPopup) {
            marker.openPopup();
        }
    }

    function createMiniItem(item) {
        const button = createElement('button', 'civic-mini-item');
        button.type = 'button';

        const topLine = createElement('div', 'civic-mini-topline');

        const category = createElement(
            'span',
            'civic-mini-type',
            safeText(item.tipo_nome, 'Ocorrência')
        );

        const gravity = createElement(
            'span',
            'civic-severity-label',
            severityName(item.gravidade)
        );

        gravity.style.setProperty(
            '--severity-color',
            severityColor[String(item.gravidade ?? '').toUpperCase()] || '#74857c'
        );

        topLine.append(category, gravity);

        const title = createElement(
            'h4',
            '',
            safeText(item.titulo, 'Ocorrência sem título')
        );

        const location = createElement(
            'span',
            'civic-mini-location',
            locationName(item)
        );

        button.append(topLine, title, location);

        button.addEventListener('click', () => {
            highlightOccurrence(item.id);
        });

        return button;
    }

    function createOccurrenceCard(item) {
        const article = createElement('article', 'civic-occurrence-card');

        const meta = createElement('div', 'civic-card-meta');

        meta.append(
            createElement(
                'span',
                'civic-category-tag',
                safeText(item.categoria_nome, 'Geral')
            ),
            createElement(
                'span',
                'civic-gravity-tag',
                severityName(item.gravidade)
            )
        );

        const title = createElement(
            'h3',
            '',
            safeText(item.titulo, 'Ocorrência sem título')
        );

        const description = createElement(
            'p',
            'civic-card-description',
            safeText(item.descricao, 'Não foi disponibilizada uma descrição pública.')
        );

        const location = createElement('div', 'civic-card-location');
        location.append(
            createElement('span', '', '⌖'),
            createElement('span', '', locationName(item))
        );

        const divider = createElement('div', 'civic-card-divider');

        const footer = createElement('div', 'civic-card-footer');
        footer.append(
            createElement(
                'span',
                'civic-status-pill',
                safeText(item.estado_nome, 'Estado não indicado')
            ),
            createElement(
                'span',
                'civic-card-date',
                formatDate(item.criado_em)
            )
        );

        const link = createElement('a', 'civic-card-link', 'Consultar ocorrência →');
        link.href = detailsUrl(item);

        article.append(meta, title, description, location, divider, footer, link);

        return article;
    }

    function applyFilters() {
        const searchTerm = normalize(elements.search?.value ?? '');
        const provinceId = elements.province?.value ?? '';
        const categoryId = elements.category?.value ?? '';
        const severity = elements.severity?.value ?? '';

        filteredOccurrences = data.filter((item) => {
            const searchableText = normalize([
                item.titulo,
                item.descricao,
                item.tipo_nome,
                item.categoria_nome,
                item.estado_nome,
                item.bairro_nome,
                item.comuna_nome,
                item.municipio_nome,
                item.provincia_nome
            ].join(' '));

            if (searchTerm && !searchableText.includes(searchTerm)) {
                return false;
            }

            if (
                provinceId &&
                String(item.provincia_id) !== String(provinceId)
            ) {
                return false;
            }

            if (
                categoryId &&
                String(item.categoria_id) !== String(categoryId)
            ) {
                return false;
            }

            if (
                severity &&
                String(item.gravidade ?? '').toUpperCase() !== severity
            ) {
                return false;
            }

            return true;
        });

        renderResults();
    }

    function renderResults() {
        const sorted = [...filteredOccurrences];

        if (elements.sort?.value === 'severity') {
            sorted.sort((a, b) => {
                const rankA = severityRank[String(a.gravidade ?? '').toUpperCase()] || 0;
                const rankB = severityRank[String(b.gravidade ?? '').toUpperCase()] || 0;

                return rankB - rankA;
            });
        } else {
            sorted.sort((a, b) => {
                const dateA = new Date(String(a.criado_em ?? '').replace(' ', 'T')).getTime() || 0;
                const dateB = new Date(String(b.criado_em ?? '').replace(' ', 'T')).getTime() || 0;

                return dateB - dateA;
            });
        }

        elements.visibleCount.textContent = String(sorted.length);

        elements.feed.replaceChildren();
        elements.miniFeed.replaceChildren();

        if (sorted.length === 0) {
            elements.emptyState.hidden = false;

            elements.miniFeed.append(
                createElement(
                    'p',
                    'civic-loading-message',
                    'Nenhuma ocorrência corresponde aos filtros seleccionados.'
                )
            );
        } else {
            elements.emptyState.hidden = true;

            sorted.forEach((item) => {
                elements.feed.append(createOccurrenceCard(item));
            });

            // O painel lateral mostra no máximo cinco itens.
            sorted.slice(0, 5).forEach((item) => {
                elements.miniFeed.append(createMiniItem(item));
            });
        }

        updateMarkers(sorted);
    }

    function resetFilters() {
        if (elements.search) elements.search.value = '';
        if (elements.province) elements.province.value = '';
        if (elements.category) elements.category.value = '';
        if (elements.severity) elements.severity.value = '';

        applyFilters();
    }

    function registerEvents() {
        elements.search?.addEventListener('input', applyFilters);
        elements.province?.addEventListener('change', applyFilters);
        elements.category?.addEventListener('change', applyFilters);
        elements.severity?.addEventListener('change', applyFilters);
        elements.sort?.addEventListener('change', renderResults);

        elements.reset?.addEventListener('click', resetFilters);
        elements.emptyReset?.addEventListener('click', resetFilters);
    }

    function start() {
        initializeMap();
        registerEvents();
        renderResults();

        // O mapa pode precisar de recalcular as dimensões após a renderização.
        if (map) {
            window.setTimeout(() => map.invalidateSize(), 150);
        }
    }

    start();
})();
