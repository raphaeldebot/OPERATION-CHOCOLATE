
document.addEventListener("DOMContentLoaded", () => {
    const section = document.getElementById("recipe-comments");
    if (!section) return;

    const form = document.getElementById("comment-form");
    const guest = document.getElementById("comments-guest");
    const list = document.getElementById("comments-list");
    const empty = document.getElementById("comments-empty");
    const loadMore = document.getElementById("comments-load-more");
    const formStatus = document.getElementById("comment-form-status");
    const listStatus = document.getElementById("comments-list-status");

    const recipeId = Number(section.dataset.recipeId);
    if (!Number.isInteger(recipeId) || recipeId <= 0) return;

    let offset = Number(section.dataset.commentsNextOffset) || 0;
    let user = null;
    let csrfToken = null;
    let pending = false;

    // Communication avec le backend PHP
    async function api(url, options = {}) {
        let response;

        try {
            response = await fetch(url, {
                credentials: "same-origin",
                headers: { Accept: "application/json" },
                ...options
            });
        } catch {
            throw new Error("Impossible de contacter le serveur.");
        }

        let result;

        try {
            result = await response.json();
        } catch {
            throw new Error("Réponse invalide du serveur.");
        }

        if (!response.ok || !result?.success) {
            const error = new Error(
                result?.message || "Une erreur est survenue."
            );
            error.status = response.status;
            throw error;
        }

        return result.data;
    }

    // Vérifier qui peut supprimer un commentaire
    function canDelete(userId) {
        return Boolean(user) && (
            Number(user.user_id) === Number(userId) ||
            user.user_role === "admin"
        );
    }

    function updateDeleteButtons() {
        list.querySelectorAll("article[data-comment-id]")
            .forEach(article => {
                const button = article.querySelector(
                    "[data-comment-delete]"
                );

                if (button) {
                    button.classList.toggle(
                        "hidden",
                        !canDelete(article.dataset.commentUserId)
                    );
                }
            });
    }

    // Actualiser les commentaires après connexion/déconnexion
    document.addEventListener("auth:changed", () => {
        user = null;
        csrfToken = null;

        form.classList.add("hidden");
        guest.classList.remove("hidden");

        updateDeleteButtons();

        refreshSession().catch(error => {
            listStatus.textContent = error.message;
        });
    });

    function updateEmptyState() {
        const hasComments = Boolean(
            list.querySelector("article[data-comment-id]")
        );

        empty.classList.toggle("hidden", hasComments);
    }

    // Récupérer l'utilisateur et le token CSRF
    async function refreshSession() {
        const data = await api("?pg=api-me", {
            cache: "no-store"
        });

        user = data.user;
        csrfToken = data.csrf_token;

        form.classList.toggle("hidden", !user);
        guest.classList.toggle("hidden", Boolean(user));

        updateDeleteButtons();
    }

    // Créer un élément HTML sans injection de code
    function createComment(comment) {
        const article = document.createElement("article");

        article.dataset.commentId = comment.id_comment;
        article.dataset.commentUserId = comment.user_id;
        article.className =
            "rounded-lg border border-[#b68d32]/40 " +
            "bg-[#eae6d8] p-6";

        const header = document.createElement("div");
        header.className =
            "flex flex-wrap items-center justify-between gap-3";

        const info = document.createElement("div");

        const author = document.createElement("h3");
        author.className = "font-serif text-xl";
        author.textContent = comment.user_login || "Utilisateur";

        const date = document.createElement("time");
        date.className = "text-sm opacity-70";

        const isoDate = String(comment.comment_date || "")
            .slice(0, 10);

        if (/^\d{4}-\d{2}-\d{2}$/.test(isoDate)) {
            const [year, month, day] = isoDate.split("-");
            date.dateTime = isoDate;
            date.textContent = `${day}/${month}/${year}`;
        }

        info.append(author, date);

        const deleteButton = document.createElement("button");
        deleteButton.type = "button";
        deleteButton.dataset.commentDelete = "";
        deleteButton.className =
            "hidden text-sm text-[#8c352d] underline";
        deleteButton.textContent = "Supprimer";

        header.append(info, deleteButton);
        article.append(header);

        if (comment.comment_subject) {
            const subject = document.createElement("h4");
            subject.className = "mt-4 font-semibold";
            subject.textContent = comment.comment_subject;
            article.append(subject);
        }

        const message = document.createElement("p");
        message.className =
            "mt-3 whitespace-pre-line leading-relaxed";
        message.textContent = comment.comment_message || "";

        article.append(message);

        deleteButton.classList.toggle(
            "hidden",
            !canDelete(comment.user_id)
        );

        return article;
    }

    // Publier un commentaire
    form.addEventListener("submit", async event => {
        event.preventDefault();

        if (pending || !form.reportValidity()) return;

        const message = form.querySelector(
            '[name="comment_message"]'
        ).value.trim();

        if (message.length < 3 || message.length > 500) {
            formStatus.textContent =
                "Le commentaire doit contenir 3 à 500 caractères.";
            return;
        }

        pending = true;
        const button = form.querySelector('[type="submit"]');
        button.disabled = true;
        formStatus.textContent = "Publication en cours...";

        try {
            if (!csrfToken) await refreshSession();

            if (!user) {
                throw new Error("Connexion requise.");
            }

            const data = new FormData(form);
            data.set("_csrf", csrfToken);

            const result = await api("?pg=api-comment-add", {
                method: "POST",
                body: data
            });

            if (!result?.comment) {
                throw new Error("Réponse du serveur incomplète.");
            }

            list.prepend(createComment(result.comment));

            // Un nouveau commentaire a été ajouté en base
            offset += 1;

            updateEmptyState();
            form.reset();

            formStatus.textContent =
                "Votre commentaire a été publié !";

        } catch (error) {
            formStatus.textContent = error.message;

            if (error.status === 401 || error.status === 403) {
                refreshSession().catch(() => {});
            }
        } finally {
            pending = false;
            button.disabled = false;
        }
    });

    // Supprimer un commentaire
    list.addEventListener("click", async event => {
        const button = event.target.closest(
            "[data-comment-delete]"
        );

        if (!button || pending) return;

        const article = button.closest(
            "article[data-comment-id]"
        );

        if (!article) return;
        if (!canDelete(article.dataset.commentUserId)) return;

        const commentId = Number(article.dataset.commentId);
        if (!Number.isInteger(commentId) || commentId <= 0) return;

        if (!confirm("Supprimer ce commentaire ?")) return;

        pending = true;
        button.disabled = true;
        listStatus.textContent = "Suppression en cours...";

        try {
            if (!csrfToken) await refreshSession();

            const data = new FormData();
            data.append("id_comment", String(commentId));
            data.append("_csrf", csrfToken);

            await api("?pg=api-comment-delete", {
                method: "POST",
                body: data
            });

            article.remove();

            // Corriger la position de pagination
            offset = Math.max(0, offset - 1);

            updateEmptyState();
            listStatus.textContent = "Commentaire supprimé.";

        } catch (error) {
            listStatus.textContent = error.message;

            if (error.status === 401 || error.status === 403) {
                refreshSession().catch(() => {});
            }
        } finally {
            pending = false;
            button.disabled = false;
        }
    });

    // Charger les commentaires suivants
    loadMore.addEventListener("click", async () => {
        if (pending) return;

        pending = true;
        loadMore.disabled = true;
        listStatus.textContent = "Chargement...";

        try {
            const params = new URLSearchParams({
                pg: "api-comments",
                recipe_id: String(recipeId),
                offset: String(offset)
            });

            const data = await api(`?${params}`);

            if (!Array.isArray(data?.comments)) {
                throw new Error("Liste de commentaires invalide.");
            }

            for (const comment of data.comments) {
                const exists = [...list.querySelectorAll(
                    "article[data-comment-id]"
                )].some(article =>
                    Number(article.dataset.commentId) ===
                    Number(comment.id_comment)
                );

                if (!exists) {
                    list.append(createComment(comment));
                }
            }

            offset += data.comments.length;
            updateEmptyState();

            if (data.comments.length < 10) {
                loadMore.classList.add("hidden");
                listStatus.textContent =
                    "Tous les commentaires sont affichés.";
            } else {
                listStatus.textContent = "";
            }

        } catch (error) {
            listStatus.textContent = error.message;
        } finally {
            pending = false;
            loadMore.disabled = false;
        }
    });

    // Initialiser l'interface
    updateEmptyState();

    refreshSession().catch(error => {
        form.classList.add("hidden");
        guest.classList.remove("hidden");
        listStatus.textContent = error.message;
    });
});
