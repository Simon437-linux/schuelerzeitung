document.addEventListener("DOMContentLoaded", () => {
    const articleId = new URLSearchParams(window.location.search).get("id");

    // Prüfe, ob eine gültige ID vorhanden ist
    if (!articleId) {
        document.getElementById("article-container").innerHTML = "<p>Artikel nicht gefunden.</p>";
        return;
    }

    fetch(`api/load_article.php?id=${articleId}`)
        .then(response => response.json())
        .then(article => {
            if (!article) {
                document.getElementById("article-container").innerHTML = "<p>Artikel nicht gefunden.</p>";
                return;
            }

            document.getElementById("article-container").innerHTML = `
                <h1>${article.title}</h1>
                <p><strong>Autor:</strong> ${article.author}</p>
                <img src="articles/${article.image}" alt="${article.title}">
                <p>${article.content}</p>
            `;
        })
        .catch(error => {
            console.error(error);
            document.getElementById("article-container").innerHTML = "<p>Fehler beim Laden des Artikels.</p>";
        });
});
