document.addEventListener("DOMContentLoaded", async () => {
    const rating = document.getElementById("recipe-rating");

    if (!rating) return;

    const recipeId = Number(rating.dataset.recipeId);
    const controls = rating.querySelector("[data-rating-controls]");
    const guest = rating.querySelector("[data-rating-guest]");
    const stars = rating.querySelectorAll("[data-rate-value]");

    const average = document.getElementById("rating-average");
    const count = document.getElementById("rating-count");
    const personal = document.getElementById("rating-personal");
    const status = document.getElementById("rating-status");

    let csrfToken = null;
    let userRating = Number(rating.dataset.userRating || 0);

    function updateStars(value) {
        stars.forEach((star) => {
            const starValue = Number(star.dataset.rateValue);
            const selected = starValue <= value;

            star.classList.toggle("text-[#b68d32]", selected);
            star.classList.toggle("text-[#929985]", !selected);

            star.setAttribute(
                "aria-pressed",
                starValue === value ? "true" : "false"
            );
        });
    }

    async function loadUser() {
        try {
            const response = await fetch("?pg=api-me", {
                headers: {
                    Accept: "application/json",
                },
            });

            const result = await response.json();

            if (!response.ok || !result.success) {
                throw new Error("Impossible de récupérer l'utilisateur");
            }

            csrfToken = result.data.csrf_token;

            if (result.data.user) {
                controls.classList.remove("hidden");
                guest.classList.add("hidden");

                updateStars(userRating);
            } else {
                controls.classList.add("hidden");
                guest.classList.remove("hidden");
            }
        } catch (error) {
            console.error(error);

            status.textContent =
                "Impossible de charger le système de notation.";
        }
    }

    async function sendRating(value) {
        status.textContent = "Enregistrement...";

        const formData = new FormData();

        formData.append("recipe_id", recipeId);
        formData.append("note_value", value);
        formData.append("_csrf", csrfToken);

        try {
            const response = await fetch("?pg=api-note-rate", {
                method: "POST",
                body: formData,
                headers: {
                    Accept: "application/json",
                },
            });

            const result = await response.json();

            if (!response.ok || !result.success) {
                status.textContent =
                    result.message || "Impossible d'enregistrer la note.";

                return;
            }

            userRating = Number(result.data.note);

            updateStars(userRating);

            personal.textContent =
                `Votre note : ${userRating}/5`;

            average.textContent =
                `${Number(result.data.average_rating)
                    .toFixed(1)
                    .replace(".", ",")}/5`;

            const voteCount = Number(result.data.vote_count);

            count.textContent =
                `${voteCount} ${voteCount > 1 ? "avis" : "avis"}`;

            status.textContent = result.message;
        } catch (error) {
            console.error(error);

            status.textContent =
                "Une erreur est survenue. Réessayez.";
        }
    }

    stars.forEach((star) => {
        star.addEventListener("click", () => {
            const value = Number(star.dataset.rateValue);

            if (value < 1 || value > 5) return;

            sendRating(value);
        });
    });

    await loadUser();
});