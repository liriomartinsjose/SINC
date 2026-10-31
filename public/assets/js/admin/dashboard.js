
'use strict';

(() => {
    const dashboard = document.querySelector('.dashboard-admin');

    if (!dashboard) {
        return;
    }

    // Data local do navegador, sem a necessidade de uma chamada ao servidor.
    const dateElement = dashboard.querySelector('[data-current-date]');

    if (dateElement) {
        dateElement.textContent = new Intl.DateTimeFormat('pt-PT', {
            day: '2-digit',
            month: 'long',
            year: 'numeric'
        }).format(new Date());
    }

    // Impede envios repetidos do formulário enquanto a pesquisa está a carregar.
    const filterForm = dashboard.querySelector('.dash-filter-form');
    const searchButton = filterForm?.querySelector('button[type="submit"]');

    if (filterForm && searchButton) {
        filterForm.addEventListener('submit', () => {
            searchButton.disabled = true;
            searchButton.setAttribute('aria-busy', 'true');
            searchButton.textContent = 'A filtrar…';
        });
    }

    // Permite iniciar uma pesquisa com "/" quando o utilizador não está
    // a escrever num campo de formulário.
    document.addEventListener('keydown', (event) => {
        const target = event.target;
        const isTyping = target instanceof HTMLElement && (
            target.isContentEditable ||
            ['INPUT', 'TEXTAREA', 'SELECT'].includes(target.tagName)
        );

        if (event.key === '/' && !isTyping && !event.ctrlKey && !event.metaKey) {
            const search = dashboard.querySelector('input[name="pesquisa"]');

            if (search) {
                event.preventDefault();
                search.focus();
            }
        }
    });

    // Anima as barras de distribuição respeitando a preferência de movimento.
    const bars = dashboard.querySelectorAll('.dash-progress__bar');

    if (!window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        bars.forEach((bar) => {
            const targetWidth = bar.style.width;
            bar.style.width = '0';

            requestAnimationFrame(() => {
                bar.style.width = targetWidth;
            });
        });
    }
})();
