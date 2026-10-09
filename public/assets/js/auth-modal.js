document.addEventListener('DOMContentLoaded', () => {
    const modal = document.getElementById('auth-modal');
    if (!modal) return;

    const forms = {
        login: document.getElementById('login-form'),
        register: document.getElementById('register-form'),
    };

    let lastTrigger = null;

    function showForm(name) {
        Object.entries(forms).forEach(([key, form]) => {
            form.classList.toggle('hidden', key !== name);
        });
    }

    function openModal(formName = 'login') {
        showForm(formName);
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        document.body.classList.add('overflow-hidden');

        const firstInput = forms[formName].querySelector('input:not([type="hidden"])');
        if (firstInput) firstInput.focus();
    }

    function closeModal() {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        document.body.classList.remove('overflow-hidden');

        if (lastTrigger) lastTrigger.focus();
    }

    document.addEventListener('click', (event) => {
        // Ouverture : <button data-auth-open="login"> ou "register"
        const opener = event.target.closest('[data-open-login]');
        if (opener) {
            event.preventDefault();
            lastTrigger = opener;
            openModal('login');
            return;
        }

        // Fermeture : croix
        if (event.target.closest('[data-auth-close]')) {
            closeModal();
            return;
        }

        // Changement de formulaire
        const switcher = event.target.closest('[data-auth-switch]');
        if (switcher) {
            showForm(switcher.dataset.authSwitch);
            return;
        }

        // Fermeture : clic sur le fond sombre
        if (event.target === modal) {
            closeModal();
        }
    });

    // Fermeture : touche Échap
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && !modal.classList.contains('hidden')) {
            closeModal();
        }
    });
});