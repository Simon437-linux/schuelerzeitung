document.addEventListener("DOMContentLoaded", () => {
    const articleId = new URLSearchParams(window.location.search).get("id");
    const articleContainer = document.getElementById("article-container");

    fetch(`api/load_article.php?id=${articleId}`)
        .then(response => response.json())
        .then(article => {
            if (article.error) {
                articleContainer.innerHTML = `<p>${article.error}</p>`;
                return;
            }

            articleContainer.innerHTML = `
                <h1>${article.title}</h1>
                <p><strong>Von:</strong> ${article.author}</p>
                <img src="articles/${article.image}" alt="${article.title}">
                <p>${article.content}</p>
                <p><strong>Veröffentlicht am:</strong> ${article.date}</p>
                <button onclick="updateArticleLike('like')">Gefällt mir</button>
                <button onclick="updateArticleLike('dislike')">Gefällt mir nicht</button>
                <span id="article-likes">👍 ${article.likes || 0}</span>
                <span id="article-dislikes">👎 ${article.dislikes || 0}</span>
            `;

            // Load comments after the article is loaded
            loadComments();
        })
        .catch(error => {
            articleContainer.innerHTML = `<p>Fehler beim Laden des Artikels: ${error.message}</p>`;
        });
});

async function updateArticleLike(type) {
    const articleId = new URLSearchParams(window.location.search).get("id");

    const response = await fetch(`api/update_article_like.php?article_id=${articleId}`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ type: type })
    });

    const result = await response.json();

    if (result.success) {
        // Reload the article to update the like/dislike counts
        const articleContainer = document.getElementById("article-container");
        fetch(`api/load_article.php?id=${articleId}`)
            .then(response => response.json())
            .then(article => {
                if (article.error) {
                    articleContainer.innerHTML = `<p>${article.error}</p>`;
                    return;
                }

                articleContainer.innerHTML = `
                    <h1>${article.title}</h1>
                    <p><strong>Von:</strong> ${article.author}</p>
                    <p>${article.content}</p>
                    <img src="articles/${article.image}" alt="${article.title}">
                    <p><strong>Veröffentlicht am:</strong> ${article.date}</p>
                    <button onclick="updateArticleLike('like')">Gefällt mir</button>
                    <button onclick="updateArticleLike('dislike')">Gefällt mir nicht</button>
                    <span id="article-likes">👍 ${article.likes || 0}</span>
                    <span id="article-dislikes">👎 ${article.dislikes || 0}</span>
                `;

                // Load comments after the article is loaded
                loadComments();
            })
            .catch(error => {
                articleContainer.innerHTML = `<p>Fehler beim Laden des Artikels: ${error.message}</p>`;
            });
    } else {
        alert('Fehler beim Aktualisieren: ' + result.message);
    }
}