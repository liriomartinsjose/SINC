
"use strict";

document.addEventListener("DOMContentLoaded", () => {
    const menuButton = document.querySelector(".menu-toggle");
    const navigation = document.querySelector(".site-navigation");

    if (menuButton && navigation) {
        menuButton.addEventListener("click", () => {
            const isOpen =
                menuButton.getAttribute("aria-expanded") === "true";

            menuButton.setAttribute("aria-expanded", String(!isOpen));
            menuButton.setAttribute(
                "aria-label",
                isOpen ? "Abrir menu" : "Fechar menu"
            );

            navigation.classList.toggle("is-open", !isOpen);
        });

        navigation.querySelectorAll("a").forEach((link) => {
            link.addEventListener("click", () => {
                menuButton.setAttribute("aria-expanded", "false");
                menuButton.setAttribute("aria-label", "Abrir menu");
                navigation.classList.remove("is-open");
            });
        });

        document.addEventListener("keydown", (event) => {
            if (event.key === "Escape") {
                menuButton.setAttribute("aria-expanded", "false");
                menuButton.setAttribute("aria-label", "Abrir menu");
                navigation.classList.remove("is-open");
                menuButton.focus();
            }
        });

        window.addEventListener("resize", () => {
            if (window.innerWidth > 760) {
                menuButton.setAttribute("aria-expanded", "false");
                menuButton.setAttribute("aria-label", "Abrir menu");
                navigation.classList.remove("is-open");
            }
        });
    }

    // Permite que o link "Voltar ao início" regresse ao cabeçalho.
    const header = document.querySelector(".site-header");

    if (header) {
        header.id = "top";
    }
});
