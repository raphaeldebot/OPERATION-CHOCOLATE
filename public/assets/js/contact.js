const form = document.getElementById("contact-form");

if (form) {
    const status = document.getElementById("contact-status");
    const button = form.querySelector('button[type="submit"]');

    form.addEventListener("submit", async (event) => {
        event.preventDefault();

        if (!form.reportValidity()) return;

        button.disabled = true;
        status.textContent = "Envoi en cours...";

        try {
            const response = await fetch(form.action, {
                method: "POST",
                body: new FormData(form),
                headers: {
                    Accept: "application/json"
                }
            });

            const result = await response.json();

            if (!response.ok || !result.success) {
                status.textContent =
                    result.message || "Erreur lors de l'envoi.";
                return;
            }

            form.reset();

            document.getElementById("contact-layout")
                .classList.add("hidden");

            document.getElementById("contact-success")
                .classList.remove("hidden");

        } catch (error) {
            status.textContent =
                "Une erreur est survenue. Réessayez.";
        } finally {
            button.disabled = false;
        }
    });
}