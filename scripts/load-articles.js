document.addEventListener("DOMContentLoaded", () => {
    // Artikel von der API laden
    fetch("api/load_articles.php")
        .then(response => response.json())
        .then(data => {
            if (Array.isArray(data)) {
                articles = data; // Speichere die geladenen Artikel
                displayArticles(articles); // Zeige alle Artikel initial an
            } else {
                throw new Error('Unexpected data format');
            }
        })
        .catch(error => {
            console.error("Fehler beim Laden der Artikel:", error);
            articlesContainer.innerHTML = "<p>Fehler beim Laden der Artikel. Bitte versuchen Sie es später erneut.</p>";
        });

    function displayArticles(articlesToDisplay) {
        latestArticleContainer.innerHTML = ""; // Container leeren
        olderArticlesContainer.innerHTML = ""; // Container leeren

        if (articlesToDisplay.length === 0) {
            articlesContainer.innerHTML = "<p>Keine Artikel verfügbar.</p>";
            return;
        }

        // Artikel sind bereits nach Datum sortiert, kein erneutes Sortieren nötig

        // Neuester Artikel hervorheben
        const latestArticle = articlesToDisplay.shift(); // Entfernt den ersten Artikel aus dem Array
        latestArticleContainer.innerHTML = `
            <div class="latest-article-image">
                <img src="articles/${latestArticle.image}" alt="${latestArticle.title}">
            </div>
            <div class="latest-article-content">
                <h1>${latestArticle.title}</h1>
                <p>${latestArticle.content.substring(0, 200)}...</p>
                <p><strong>Von:</strong> ${latestArticle.author}</p>
                <p><strong>Kategorie:</strong> ${latestArticle.category}</p>
                <p><strong>Likes:</strong> ${latestArticle.likes} | <strong>Kommentare:</strong> ${latestArticle.commentCount}</p>
                <a href="article.html?id=${latestArticle.id}">Weiterlesen</a>
                <hr>
            </div>
        `;

        // Ältere Artikel darstellen
        articlesToDisplay.forEach(article => {
            const articleElement = document.createElement("div");
            articleElement.classList.add("article");
            articleElement.innerHTML = `
                <div class="article-image">
                    <img src="articles/${article.image}" alt="${article.title}">
                </div>
                <div class="article-content">
                    <h2>${article.title}</h2>
                    <p>${article.content.substring(0, 150)}...</p>
                    <p><strong>Von:</strong> ${article.author}</p>
                    <p><strong>Kategorie:</strong> ${article.category}</p>
                    <p><strong>Likes:</strong> ${article.likes} | <strong>Kommentare:</strong> ${article.commentCount}</p>
                    <a href="article.html?id=${article.id}">Weiterlesen</a>
                </div>
            `;
            olderArticlesContainer.appendChild(articleElement);
        });
    }
});