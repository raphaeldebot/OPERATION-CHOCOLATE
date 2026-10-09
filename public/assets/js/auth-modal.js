document.addEventListener("DOMContentLoaded", () => {
    const modal = document.getElementById("auth-modal");

    if (!modal) return;

    const loginForm = document.getElementById("login-form");
    const registerForm = document.getElementById("register-form");

    const forms = {
        login: loginForm,
        register: registerForm,
    };

    let lastTrigger = null;
    let csrfToken = null;

    async function loadCsrfToken() {
        try {
            const response = await fetch("?pg=api-me", {
                method: "GET",
                headers: {
                    Accept: "application/json",
                },
            });

            const result = await response.json();

            if (!response.ok || !result.success) {
                throw new Error("Impossible de récupérer le token CSRF");
            }

            csrfToken = result.data.csrf_token;

            document
                .querySelectorAll('input[name="_csrf"]')
                .forEach((input) => {
                    input.value = csrfToken;
                });

            return true;
        } catch (error) {
            console.error(error);
            return false;
        }
    }

    function showForm(name) {
        Object.entries(forms).forEach(([key, form]) => {
            form.classList.toggle("hidden", key !== name);
        });
    }

    async function openModal(formName = "login") {
        await loadCsrfToken();

        showForm(formName);

        modal.classList.remove("hidden");
        modal.classList.add("flex");

        document.body.classList.add("overflow-hidden");

        const firstInput = forms[formName]
            .querySelector('input:not([type="hidden"])');

        if (firstInput) {
            firstInput.focus();
        }
    }

    function closeModal() {
        modal.classList.add("hidden");
        modal.classList.remove("flex");

        document.body.classList.remove("overflow-hidden");

        if (lastTrigger) {
            lastTrigger.focus();
        }
    }

    async function submitAuthForm(form, statusElement) {
        if (!form.reportValidity()) return;

        if (!csrfToken) {
            const loaded = await loadCsrfToken();

            if (!loaded) {
                statusElement.textContent =
                    "Impossible d'envoyer le formulaire.";
                return;
            }
        }

        const button = form.querySelector('button[type="submit"]');

        button.disabled = true;
        statusElement.textContent = "Chargement...";

        try {
            const response = await fetch(form.action, {
                method: "POST",
                body: new FormData(form),
                headers: {
                    Accept: "application/json",
                },
            });

            const result = await response.json();

            statusElement.textContent = result.message;

            if (!response.ok || !result.success) {
                return;
            }

            form.reset();

            closeModal();

            window.location.reload();
        } catch (error) {
            console.error(error);

            statusElement.textContent =
                "Une erreur est survenue. Réessayez.";
        } finally {
            button.disabled = false;
        }
    }

    loginForm.addEventListener("submit", (event) => {
        event.preventDefault();

        submitAuthForm(
            loginForm,
            document.getElementById("login-status")
        );
    });

    registerForm.addEventListener("submit", (event) => {
        event.preventDefault();

        submitAuthForm(
            registerForm,
            document.getElementById("register-status")
        );
    });

    document.addEventListener("click", (event) => {
        const opener = event.target.closest("[data-open-login]");

        if (opener) {
            event.preventDefault();

            lastTrigger = opener;

            openModal("login");

            return;
        }

        const closer = event.target.closest("[data-auth-close]");

        if (closer) {
            closeModal();

            return;
        }

        const switcher = event.target.closest("[data-auth-switch]");

        if (switcher) {
            showForm(switcher.dataset.authSwitch);
        }
    });

    document.addEventListener("keydown", (event) => {
        if (
            event.key === "Escape" &&
            !modal.classList.contains("hidden")
        ) {
            closeModal();
        }
    });
});