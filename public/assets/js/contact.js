
document.addEventListener("DOMContentLoaded", () => {
    const form = document.getElementById("contact-form");
    if (!form) return;

    const status = document.getElementById("contact-status");
    const button = form.querySelector('[type="submit"]');

    const fields = [
        ...form.querySelectorAll('[name^="contact_message_"]')
    ];

    let pending = false;

    // Utiliser nos propres messages de validation
    form.noValidate = true;

    // Vérifier un champ et afficher son erreur
    function validateField(field) {
        const value = field.value.trim();
        let message = "";

        if (!value) {
            message = "Ce champ est obligatoire.";
        } else if (
            field.type === "email" &&
            field.validity.typeMismatch
        ) {
            message = "Adresse email invalide.";
        } else if (
            field.maxLength > 0 &&
            Array.from(value).length > field.maxLength
        ) {
            message = `Maximum ${field.maxLength} caractères.`;
        }

        const error = document.getElementById(
            `${field.name}-error`
        );

        if (error) {
            error.textContent = message;
        }

        field.setAttribute(
            "aria-invalid",
            String(Boolean(message))
        );

        return message === "";
    }

    // Vérifier les champs pendant la saisie
    fields.forEach(field => {
        field.addEventListener("input", () => {
            validateField(field);
        });
    });

    // Envoyer le formulaire
    form.addEventListener("submit", async event => {
        event.preventDefault();

        if (pending) return;

        // Valider tous les champs
        const results = fields.map(validateField);

        if (results.some(valid => !valid)) {
            status.textContent =
                "Corrigez les champs indiqués.";

            fields[results.indexOf(false)].focus();
            return;
        }

        pending = true;
        button.disabled = true;
        status.textContent = "Envoi en cours...";

        try {
            const response = await fetch(form.action, {
                method: "POST",
                credentials: "same-origin",
                body: new FormData(form),
                headers: {
                    Accept: "application/json"
                }
            });

            const result = await response.json();

            if (!response.ok || !result.success) {
                throw new Error(
                    result.message || "Erreur lors de l'envoi."
                );
            }

            form.reset();

            // Afficher la confirmation
            document.getElementById("contact-layout")
                .classList.add("hidden");

            document.getElementById("contact-success")
                .classList.remove("hidden");

        } catch (error) {
            status.textContent =
                error.message || "Une erreur est survenue.";
        } finally {
            pending = false;
            button.disabled = false;
        }
    });
});
