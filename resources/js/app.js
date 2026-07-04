import './bootstrap';

/* ────────────────────────────────────────────────────────────────
   Tema Claro / Oscuro
   Persiste la preferencia en localStorage y la aplica a <html>.
   Inyecta un botón flotante de cambio en todas las páginas.
   ──────────────────────────────────────────────────────────────── */
(function () {
    const STORAGE_KEY = 'alitas-theme';

    function getTheme() {
        return localStorage.getItem(STORAGE_KEY) || 'dark';
    }

    function applyTheme(theme) {
        document.documentElement.setAttribute('data-theme', theme);
    }

    function iconFor(theme) {
        return theme === 'light' ? '🌙' : '☀️';
    }

    // Aplicar cuanto antes para evitar parpadeo
    applyTheme(getTheme());

    function injectToggle() {
        if (document.getElementById('theme-toggle')) return;

        const btn = document.createElement('button');
        btn.id = 'theme-toggle';
        btn.type = 'button';
        btn.title = 'Cambiar tema claro / oscuro';
        btn.setAttribute('aria-label', 'Cambiar tema');
        btn.textContent = iconFor(getTheme());

        btn.addEventListener('click', () => {
            const next = getTheme() === 'light' ? 'dark' : 'light';
            localStorage.setItem(STORAGE_KEY, next);
            applyTheme(next);
            btn.textContent = iconFor(next);
        });

        document.body.appendChild(btn);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', injectToggle);
    } else {
        injectToggle();
    }
})();
