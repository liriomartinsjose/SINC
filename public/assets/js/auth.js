
/* =========================================================
   SINC — Interações das páginas de autenticação
   Ficheiro: public/assets/js/auth.js
   ========================================================= */

'use strict';

document.addEventListener('DOMContentLoaded', () => {
    const passwordInputs = document.querySelectorAll(
        '.auth-page input[type="password"]'
    );

    passwordInputs.forEach((input) => {
        const wrapper = input.closest('.password-control');

        if (!wrapper) {
            return;
        }

        const toggle = document.createElement('button');

        toggle.type = 'button';
        toggle.className = 'password-toggle';
        toggle.textContent = 'Mostrar';
        toggle.setAttribute('aria-label', 'Mostrar palavra-passe');
        toggle.setAttribute('aria-pressed', 'false');

        wrapper.appendChild(toggle);

        toggle.addEventListener('click', () => {
            const showing = input.type === 'text';

            input.type = showing ? 'password' : 'text';
            toggle.textContent = showing ? 'Mostrar' : 'Ocultar';

            toggle.setAttribute(
                'aria-label',
                showing ? 'Mostrar palavra-passe' : 'Ocultar palavra-passe'
            );

            toggle.setAttribute('aria-pressed', String(!showing));
            input.focus();
        });
    });
});
