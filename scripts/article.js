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
                <p class="article-meta"><strong>Von:</strong> ${article.author} | <strong>Veröffentlicht am:</strong> ${article.date}</p>
                <div class="article-image">
                    <img src="articles/${article.image}" alt="${article.title}">
                </div>
                <p>${article.content}</p>
                <button class="like-button" onclick="updateArticleLike('like')">👍</button>
                <span id="article-likes">${article.likes || 0}</span>
                <button class="dislike-button" onclick="updateArticleLike('dislike')">👎</button>
                <span id="article-dislikes">${article.dislikes || 0}</span>
                <div class="comments-section">
                    <h2>Kommentare</h2>
                    <div id="comments-container"></div>
                </div>
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
                    <p class="article-meta"><strong>Von:</strong> ${article.author} | <strong>Veröffentlicht am:</strong> ${article.date}</p>
                    <div class="article-image">
                        <img src="articles/${article.image}" alt="${article.title}">
                    </div>
                    <p>${article.content}</p>
                    <button class="like-button" onclick="updateArticleLike('like')">👍</button>
                    <span id="article-likes">${article.likes || 0}</span>
                    <button class="dislike-button" onclick="updateArticleLike('dislike')">👎</button>
                    <span id="article-dislikes">${article.dislikes || 0}</span>
                    <div class="comments-section">
                        <h2>Kommentare</h2>
                        <div id="comments-container"></div>
                    </div>
                `;
            })
            .catch(error => {
                articleContainer.innerHTML = `<p>Fehler beim Laden des Artikels: ${error.message}</p>`;
            });
    } else {
        alert('Fehler beim Aktualisieren: ' + result.message);
    }
}

function loadComments() {
    const articleId = new URLSearchParams(window.location.search).get("id");
    const commentsContainer = document.getElementById("comments-container");

    fetch(`api/load_comments.php?article_id=${articleId}`)
        .then(response => response.json())
        .then(comments => {
            commentsContainer.innerHTML = comments.map(comment => `
                <div class="comment">
                    <p class="comment-author">${comment.author}</p>
                    <p class="comment-date">${comment.date}</p>
                    <p class="comment-content">${comment.content}</p>
                    <button class="like-button" onclick="updateCommentLike(${comment.id}, 'like')">👍</button>
                    <span id="comment-likes-${comment.id}">${comment.likes || 0}</span>
                    <button class="dislike-button" onclick="updateCommentLike(${comment.id}, 'dislike')">👎</button>
                    <span id="comment-dislikes-${comment.id}">${comment.dislikes || 0}</span>
                </div>
            `).join('');
        })
        .catch(error => {
            commentsContainer.innerHTML = `<p>Fehler beim Laden der Kommentare: ${error.message}</p>`;
        });
}

async function updateCommentLike(commentId, type) {
    const response = await fetch(`api/update_comment_like.php?comment_id=${commentId}`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ type: type })
    });

    const result = await response.json();

    if (result.success) {
        // Update the like/dislike counts for the comment
        const likesSpan = document.getElementById(`comment-likes-${commentId}`);
        const dislikesSpan = document.getElementById(`comment-dislikes-${commentId}`);
        likesSpan.textContent = result.likes;
        dislikesSpan.textContent = result.dislikes;
    } else {
        alert('Fehler beim Aktualisieren: ' + result.message);
    }
}