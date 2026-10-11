
document.addEventListener("DOMContentLoaded", () => {
    const section = document.getElementById("recipe-rating");
    if (!section) return;

    const recipeId = Number(section.dataset.recipeId);
    if (!Number.isInteger(recipeId) || recipeId <= 0) return;

    const controls = section.querySelector("[data-rating-controls]");
    const guest = section.querySelector("[data-rating-guest]");
    const stars = [...section.querySelectorAll("[data-rate-value]")];

    const average = document.getElementById("rating-average");
    const fill = document.getElementById("rating-average-fill");
    const count = document.getElementById("rating-count");
    const personal = document.getElementById("rating-personal");
    const status = document.getElementById("rating-status");
    const header = document.getElementById("recipe-header-rating");

    let userRating = Number(section.dataset.userRating) || 0;
    let csrfToken = null;
    let connected = false;
    let pending = false;

    // Afficher la sélection ou le survol
    function renderStars(value = userRating) {
        stars.forEach(star => {
            const starValue = Number(star.dataset.rateValue);

            star.classList.toggle(
                "text-[#b68d32]",
                starValue <= value
            );

            star.classList.toggle(
                "text-[#929985]",
                starValue > value
            );

            star.setAttribute(
                "aria-pressed",
                String(starValue === userRating)
            );
        });
    }

    function renderPersonalRating() {
        personal.textContent = userRating > 0
            ? `Votre note : ${userRating}/5`
            : "Sélectionnez une étoile";
    }

    // Mettre à jour les deux moyennes affichées
    function updateAverage(averageRating, voteCount) {
        const score = Number(averageRating);
        const votes = Number(voteCount);

        if (
            !Number.isFinite(score) ||
            score < 0 ||
            score > 5 ||
            !Number.isInteger(votes) ||
            votes < 0
        ) {
            throw new Error("Données de notation invalides.");
        }

        const formatted = score.toLocaleString("fr-BE", {
            minimumFractionDigits: 1,
            maximumFractionDigits: 1
        });

        const label = votes > 0
            ? `${formatted}/5`
            : "Pas encore de note";

        average.textContent = label;
        count.textContent = `${votes} avis`;

        // Remplissage des étoiles
        fill.style.width = votes > 0
            ? `${score * 20}%`
            : "0%";

        // Moyenne dans l'en-tête de la recette
        if (header) {
            header.textContent = votes > 0
                ? `★ ${label} (${votes} avis)`
                : "Pas encore de note";
        }
    }

    // Vérifier la session et récupérer le token CSRF
    async function loadUser() {
        try {
            const response = await fetch("?pg=api-me", {
                credentials: "same-origin",
                cache: "no-store",
                headers: {
                    Accept: "application/json"
                }
            });

            const result = await response.json();

            if (!response.ok || !result.success || !result.data) {
                throw new Error("Session indisponible.");
            }

            csrfToken = result.data.csrf_token;
            connected = Boolean(result.data.user);

            controls.classList.toggle("hidden", !connected);
            guest.classList.toggle("hidden", connected);

            if (!connected) {
                userRating = 0;
            }

            renderStars();
            renderPersonalRating();

        } catch (error) {
            connected = false;
            csrfToken = null;

            controls.classList.add("hidden");
            guest.classList.remove("hidden");

            status.textContent =
                "Impossible de vérifier votre session.";
        }
    }

    // Envoyer la note à l'API PHP
    async function sendRating(value) {
        if (pending || !connected) return;
        if (!Number.isInteger(value) || value < 1 || value > 5) return;

        pending = true;
        stars.forEach(star => star.disabled = true);
        status.textContent = "Enregistrement...";

        try {
            if (!csrfToken) {
                await loadUser();

                if (!connected || !csrfToken) {
                    throw new Error("Connectez-vous pour voter.");
                }
            }

            const formData = new FormData();
            formData.append("recipe_id", String(recipeId));
            formData.append("note_value", String(value));
            formData.append("_csrf", csrfToken);

            const response = await fetch("?pg=api-note-rate", {
                method: "POST",
                credentials: "same-origin",
                headers: {
                    Accept: "application/json"
                },
                body: formData
            });

            let result;

            try {
                result = await response.json();
            } catch {
                throw new Error("Réponse du serveur invalide.");
            }

            if (!response.ok || !result.success) {
                if (response.status === 401 || response.status === 403) {
                    await loadUser();
                }

                throw new Error(
                    result.message || "Impossible d'enregistrer la note."
                );
            }

            const nextRating = Number(result.data?.note);
            const nextAverage = Number(result.data?.average_rating);
            const nextCount = Number(result.data?.vote_count);

            if (
                !Number.isInteger(nextRating) ||
                nextRating < 1 ||
                nextRating > 5 ||
                !Number.isFinite(nextAverage) ||
                nextAverage < 1 ||
                nextAverage > 5 ||
                !Number.isInteger(nextCount) ||
                nextCount < 1
            ) {
                throw new Error("Réponse de notation invalide.");
            }

            // Actualiser la moyenne avant de confirmer le vote
            updateAverage(nextAverage, nextCount);

            userRating = nextRating;
            renderStars();
            renderPersonalRating();

            status.textContent = "Votre note a bien été enregistrée !";

        } catch (error) {
            renderStars();
            status.textContent = error.message ||
                "Une erreur est survenue.";

        } finally {
            pending = false;
            stars.forEach(star => star.disabled = false);
        }
    }

    // Survol, clavier et clic
    stars.forEach(star => {
        const value = Number(star.dataset.rateValue);

        star.addEventListener("mouseenter", () => renderStars(value));
        star.addEventListener("mouseleave", () => renderStars());
        star.addEventListener("focus", () => renderStars(value));
        star.addEventListener("blur", () => renderStars());
        star.addEventListener("click", () => sendRating(value));
    });

    // Actualiser les étoiles après connexion/déconnexion
    document.addEventListener("auth:changed", () => {
        connected = false;
        csrfToken = null;
        userRating = 0;

        // Masquer temporairement les boutons de vote
        controls.classList.add("hidden");
        guest.classList.remove("hidden");

        renderStars();
        renderPersonalRating();

        // Récupérer la nouvelle session
        loadUser();
    });

    renderStars();
    renderPersonalRating();
    loadUser();
});
