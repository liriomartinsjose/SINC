
"use strict";

document.addEventListener("DOMContentLoaded", () => {
    const body = document.body;
    const sidebar = document.querySelector("#admin-sidebar");
    const toggleButton = document.querySelector("[data-sidebar-toggle]");
    const collapseButton = document.querySelector("[data-sidebar-collapse]");
    const backdrop = document.querySelector("[data-sidebar-backdrop]");

    if (!sidebar || !toggleButton || !collapseButton || !backdrop) {
        return;
    }

    const mobileQuery = window.matchMedia("(max-width: 760px)");

    const updateExpandedState = (expanded) => {
        toggleButton.setAttribute("aria-expanded", String(expanded));
        collapseButton.setAttribute("aria-expanded", String(expanded));

        collapseButton.setAttribute(
            "aria-label",
            expanded ? "Recolher menu lateral" : "Expandir menu lateral"
        );
    };

    const closeMobileSidebar = () => {
        body.classList.remove("admin-sidebar-open");
        toggleButton.setAttribute("aria-expanded", "false");
    };

    toggleButton.addEventListener("click", () => {
        if (mobileQuery.matches) {
            const isOpen = body.classList.toggle("admin-sidebar-open");
            toggleButton.setAttribute("aria-expanded", String(isOpen));
            return;
        }

        const isCollapsed = body.classList.toggle("admin-sidebar-collapsed");
        updateExpandedState(!isCollapsed);
    });

    collapseButton.addEventListener("click", () => {
        if (mobileQuery.matches) {
            closeMobileSidebar();
            return;
        }

        const isCollapsed = body.classList.toggle("admin-sidebar-collapsed");
        updateExpandedState(!isCollapsed);
    });

    backdrop.addEventListener("click", closeMobileSidebar);

    document.addEventListener("keydown", (event) => {
        if (event.key === "Escape") {
            closeMobileSidebar();
        }
    });

    mobileQuery.addEventListener("change", (event) => {
        body.classList.remove("admin-sidebar-open");

        if (event.matches) {
            body.classList.remove("admin-sidebar-collapsed");
        }

        updateExpandedState(!body.classList.contains(
            "admin-sidebar-collapsed"
        ));
    });

    updateExpandedState(
        !body.classList.contains("admin-sidebar-collapsed")
    );
});


/* Dropdowns da navegação administrativa do SINC */
(() => {
    'use strict';

    const toggles = document.querySelectorAll('[data-nav-toggle]');

    toggles.forEach((toggle) => {
        const submenuId = toggle.getAttribute('aria-controls');
        const submenu = submenuId
            ? document.getElementById(submenuId)
            : null;

        if (!submenu) {
            return;
        }

        // Sincroniza o estado inicial do botão com o submenu.
        const inicialmenteAberto =
            toggle.getAttribute('aria-expanded') === 'true';

        toggle.setAttribute(
            'aria-expanded',
            inicialmenteAberto ? 'true' : 'false'
        );

        submenu.hidden = !inicialmenteAberto;

        toggle.addEventListener('click', () => {
            const estaAberto =
                toggle.getAttribute('aria-expanded') === 'true';

            toggle.setAttribute(
                'aria-expanded',
                estaAberto ? 'false' : 'true'
            );

            submenu.hidden = estaAberto;
        });
    });
})();
