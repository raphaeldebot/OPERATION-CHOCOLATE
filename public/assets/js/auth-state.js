
document.addEventListener("DOMContentLoaded", () => {
    const guestBlocks = document.querySelectorAll("[data-auth-guest]");
    const userBlocks = document.querySelectorAll("[data-auth-user]");
    const names = document.querySelectorAll("[data-auth-name]");
    const statuses = document.querySelectorAll("[data-auth-status]");
    const logoutButtons = document.querySelectorAll("[data-auth-logout]");

    let pending = false;
    let requestId = 0;

    // Afficher les messages dans la navigation
    function showStatus(message = "") {
        statuses.forEach(element => {
            element.textContent = message;
        });
    }

    // Récupérer l'utilisateur connecté et le token CSRF
    async function getSession() {
        const response = await fetch("?pg=api-me", {
            credentials: "same-origin",
            cache: "no-store",
            headers: {
                Accept: "application/json"
            }
        });

        const result = await response.json();

        if (!response.ok || !result.success || !result.data) {
            throw new Error("Impossible de vérifier la session.");
        }

        return result.data;
    }

    // Mettre à jour les menus mobile et ordinateur
    function renderNavigation(user) {
        const connected = Boolean(user);

        guestBlocks.forEach(element => {
            element.classList.toggle("hidden", connected);
        });

        userBlocks.forEach(element => {
            element.classList.toggle("hidden", !connected);
        });

        names.forEach(element => {
            element.textContent = connected
                ? `Bonjour, ${user.user_login}`
                : "";
        });
    }

    // Actualiser la navigation
    async function refreshNavigation() {
        const currentRequest = ++requestId;

        try {
            const session = await getSession();

            // Ignorer une réponse devenue obsolète
            if (currentRequest !== requestId) return;

            renderNavigation(session.user);
            showStatus("");

        } catch (error) {
            if (currentRequest === requestId) {
                showStatus(error.message);
            }
        }
    }

    // Réagir aux changements de connexion
    document.addEventListener("auth:changed", () => {
        refreshNavigation();
    });

    // Gérer la déconnexion
    document.addEventListener("click", async event => {
        const button = event.target.closest("[data-auth-logout]");

        if (!button || pending) return;

        pending = true;
        ++requestId;

        logoutButtons.forEach(element => {
            element.disabled = true;
        });

        showStatus("Déconnexion en cours...");

        try {
            const session = await getSession();

            if (session.user) {
                const data = new FormData();
                data.append("_csrf", session.csrf_token);

                const response = await fetch("?pg=api-logout", {
                    method: "POST",
                    credentials: "same-origin",
                    headers: {
                        Accept: "application/json"
                    },
                    body: data
                });

                const result = await response.json();

                if (!response.ok || !result.success) {
                    throw new Error(
                        result.message || "Déconnexion impossible."
                    );
                }
            }

            // Réafficher le bouton Connexion
            renderNavigation(null);
            showStatus("");

            // Prévenir les commentaires et les notes
            document.dispatchEvent(new Event("auth:changed"));

        } catch (error) {
            showStatus(
                error.message || "Erreur lors de la déconnexion."
            );
            refreshNavigation();

        } finally {
            pending = false;

            logoutButtons.forEach(element => {
                element.disabled = false;
            });
        }
    });

    // Vérifier la session au chargement
    refreshNavigation();
});
