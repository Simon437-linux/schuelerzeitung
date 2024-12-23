const form = document.getElementById("article-form");

form.addEventListener("submit", async (e) => {
    e.preventDefault();
    const formData = new FormData(form);

    try {
        const response = await fetch("api/save_article.php", {
            method: "POST",
            body: formData,
        });

        const result = await response.json();
        console.log("Artikel gespeichert:", result);

        if (result.success) {
            alert("Artikel erfolgreich gespeichert!");
            form.reset();
        } else {
            alert("Fehler beim Speichern des Artikels: " + result.error);
        }
    } catch (error) {
        console.error("Fehler:", error);
        alert("Ein unerwarteter Fehler ist aufgetreten.");
    }
});