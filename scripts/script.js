document.addEventListener("DOMContentLoaded", () => {
    // Referenzen auf wichtige DOM-Elemente
    const articlesContainer = document.getElementById("articles-container");
    const latestArticleContainer = document.createElement("div");
    const olderArticlesContainer = document.createElement("div");
    const searchBar = document.getElementById("search-bar");
    const categoryMenu = document.getElementById("category-menu");
    const menuToggle = document.getElementById("menu-toggle");
    const latestCommentsList = document.getElementById("latest-comments-list");
    const popularArticlesList = document.getElementById("popular-articles-list");

    // Klassen für die neuen Container hinzufügen
    latestArticleContainer.classList.add("latest-article");
    olderArticlesContainer.classList.add("older-articles");

    // Container in den Haupt-Container einfügen
    articlesContainer.appendChild(latestArticleContainer);
    articlesContainer.appendChild(olderArticlesContainer);

    let articles = []; // Variable zum Speichern der geladenen Artikel

    // Artikel von der API laden
    fetch("api/load_articles.php")
        .then(response => response.json())
        .then(data => {
            articles = data; // Speichere die geladenen Artikel
            displayArticles(articles); // Zeige alle Artikel initial an
            updateSidebar(articles); // Aktualisiere die Seitenleiste
        })
        .catch(error => {
            console.error("Fehler beim Laden der Artikel:", error);
            articlesContainer.innerHTML = "<p>Fehler beim Laden der Artikel. Bitte versuchen Sie es später erneut.</p>";
        });

    // Suchleiste: Filterung bei Benutzereingabe
    if (searchBar) {
        searchBar.addEventListener("input", () => {
            const query = searchBar.value.toLowerCase();
            filterAndDisplayArticles(query, "");
        });
    }

    // Kategorienmenü: Filterung bei Klick auf eine Kategorie
    if (categoryMenu) {
        categoryMenu.addEventListener("click", (event) => {
            if (event.target.tagName === "A") {
                event.preventDefault();
                const category = event.target.getAttribute("data-category").toLowerCase();
                filterAndDisplayArticles("", category);
            }
        });
    }

    /**
     * Filter- und Anzeige-Funktion
     * @param {string} query Suchanfrage (Text)
     * @param {string} category Kategorie (aus dem Dropdown)
     */
    function filterAndDisplayArticles(query, category) {
        const filteredArticles = articles.filter(article => {
            const matchesQuery = article.title.toLowerCase().includes(query);
            const matchesCategory = category === "" || article.category.toLowerCase() === category.toLowerCase();
            return matchesQuery && matchesCategory;
        });

        displayArticles(filteredArticles);
    }

    /**
     * Funktion zum Anzeigen der Artikel
     * @param {Array} articlesToDisplay Die anzuzeigenden Artikel
     */
    function displayArticles(articlesToDisplay) {
        latestArticleContainer.innerHTML = ""; // Container leeren
        olderArticlesContainer.innerHTML = ""; // Container leeren

        if (articlesToDisplay.length === 0) {
            articlesContainer.innerHTML = "<p>Keine Artikel verfügbar.</p>";
            return;
        }

        // Artikel nach Datum sortieren (neueste zuerst)
        articlesToDisplay.sort((a, b) => new Date(b.date) - new Date(a.date));

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

    function updateSidebar(articles) {
        // Beliebteste Artikel anzeigen (basierend auf Likes)
        const popularArticles = articles.sort((a, b) => b.likes - a.likes).slice(0, 5);

        popularArticlesList.innerHTML = popularArticles.map(article => `
            <li>
                <a href="article.html?id=${article.id}">${article.title} <br> <a class="popular_date"> vom: ${article.date}</a></a>
                <hr>
            </li>
        `).join('');
    }
});