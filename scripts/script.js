document.addEventListener("DOMContentLoaded", () => {
    const articlesContainer = document.getElementById("articles-container");
    const latestArticleContainer = document.createElement("div");
    const olderArticlesContainer = document.createElement("div");

    // Klassen für die neuen Container hinzufügen
    latestArticleContainer.classList.add("latest-article");
    olderArticlesContainer.classList.add("older-articles");

    // Container in den Haupt-Container einfügen
    articlesContainer.appendChild(latestArticleContainer);
    articlesContainer.appendChild(olderArticlesContainer);

    fetch("api/load_articles.php")
        .then(response => response.json())
        .then(articles => {
            if (articles.length === 0) {
                articlesContainer.innerHTML = "<p>Keine Artikel verfügbar.</p>";
                return;
            }

            // Artikel nach Datum sortieren (neueste zuerst)
            articles.sort((a, b) => new Date(b.date) - new Date(a.date));

            // Neuester Artikel hervorheben
            const latestArticle = articles.shift(); // Entfernt den ersten Artikel aus dem Array
            latestArticleContainer.innerHTML = `
                <img src="articles/${latestArticle.image}" alt="${latestArticle.title}">
                <h1>${latestArticle.title}</h1>
                <p>${latestArticle.content.substring(0, 200)}...</p>
                <p><strong>Autor:</strong> ${latestArticle.author}</p>
                <a href="article.html?id=${latestArticle.id}">Weiterlesen</a>
            `;

            // Ältere Artikel darstellen
            articles.forEach(article => {
                const articleElement = document.createElement("div");
                articleElement.classList.add("article");
                articleElement.innerHTML = `
                    <img src="articles/${article.image}" alt="${article.title}">
                    <div>
                        <h2>${article.title}</h2>
                        <p>${article.content.substring(0, 100)}...</p>
                        <p><strong>Autor:</strong> ${article.author}</p>
                        <a href="article.html?id=${article.id}">Weiterlesen</a>
                    </div>
                `;
                olderArticlesContainer.appendChild(articleElement);
            });
        })
        .catch(error => console.error("Fehler beim Laden der Artikel:", error));
});
