// Mise en évidence de la page courante (aucun appel API ni modification des routes).
document.addEventListener('DOMContentLoaded', () => {
    const page = new URLSearchParams(window.location.search).get('pg') || 'home';
    const current = page === 'recipe' ? 'recipes' : page;
    document.querySelectorAll('[data-nav-page]').forEach(link => {
        if (link.dataset.navPage === current) {
            link.classList.add('is-active');
            if (link.tagName === 'A') link.setAttribute('aria-current', 'page');
        }
    });
});
