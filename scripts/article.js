document.addEventListener("DOMContentLoaded", () => {
    // Check if the user is authenticated
    const token = localStorage.getItem('token');
    const userId = localStorage.getItem('user_id');

    // Check the current page
    const currentPage = window.location.pathname.split('/').pop();

    if (currentPage === 'submit.html' && (!token || !userId)) {
        // Redirect to login page for submitting articles
        window.location.href = 'login.html';
        return;
    }

    loadArticle();
});

function loadArticle() {
    const articleId = new URLSearchParams(window.location.search).get("id");
    const articleContainer = document.getElementById("article-container");
    const galleryContainer = document.getElementById("gallery-container");

    fetch(`api/load_article.php?id=${articleId}`)
        .then(response => response.json())
        .then(article => {
            if (article.error) {
                articleContainer.innerHTML = `<p>${article.error}</p>`;
                return;
            }

            articleContainer.innerHTML = `
                <div class="title-container">
                <h1>${article.title}</h1>
                <a href="index.html" class="back-link"><h3 class="back-link">ᐊ- Zurück zu den Artikeln</h3></a>
                </div>
                <img src="articles/${article.image}" alt="${article.title}" class="main-article-image">
                <p class="article-category"><strong>Kategorie:</strong> ${article.category}</p>
                <p class="article-meta"><strong>Von:</strong> ${article.author} | <strong>Veröffentlicht am:</strong> ${article.date}</p><br>
                <p>${article.content}</p>
                <button class="like-button" onclick="updateArticleLike('like')">👍</button>
                <span id="article-likes">${article.likes || 0}</span>
                <button class="dislike-button" onclick="updateArticleLike('dislike')">👎</button>
                <span id="article-dislikes">${article.dislikes || 0}</span>
                <div class="comments-section">
                    <div id="comments-container"></div>
                </div>
            `;

            // Galerie anzeigen
            if (article.images && article.images.length > 0) {
                galleryContainer.innerHTML = `
                    <div class="gallery">
                        ${article.images.map(image => `<a href="articles/${image}" data-fancybox="gallery" data-caption="${article.title}"><img src="articles/${image}" alt="${article.title}"></a>`).join('')}
                    </div>
                `;

                // Initialize Fancybox
                $('[data-fancybox="gallery"]').fancybox({
                    buttons: [
                        "slideShow",
                        "thumbs",
                        "zoom",
                        "fullScreen",
                        "share",
                        "close"
                    ],
                    loop: true,
                    protect: true,
                    thumbs: {
                        autoStart: false
                    }
                });
            }

            // Load comments after the article is loaded
            loadComments();
        })
        .catch(error => {
            articleContainer.innerHTML = `<p>Fehler beim Laden des Artikels: ${error.message}</p>`;
        });
}

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
        fetch(`api/load_article.php?id=${articleId}`)
            .then(response => response.json())
            .then(article => {
                if (article.error) {
                    console.error(article.error);
                    return;
                }

                // Update only the like/dislike counts
                document.getElementById("article-likes").textContent = article.likes || 0;
                document.getElementById("article-dislikes").textContent = article.dislikes || 0;
            })
            .catch(error => {
                console.error('Fehler beim Aktualisieren der Likes/Dislikes:', error.message);
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
            // Sortiere Kommentare nach Datum in absteigender Reihenfolge (neueste zuerst)
            comments.sort((a, b) => {
                const dateA = parseDate(a.date);
                const dateB = parseDate(b.date);
                return dateB - dateA;
            });

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

function parseDate(dateString) {
    console.log("Parsing date:", dateString);
    const formats = [
        // DD.MM.YYYY
        /(\d{2})\.(\d{2})\.(\d{4})/,
        // YYYY-MM-DD
        /(\d{4})-(\d{2})-(\d{2})/,
        // MM/DD/YYYY
        /(\d{2})\/(\d{2})\/(\d{4})/
    ];

    for (let format of formats) {
        const match = dateString.match(format);
        if (match) {
            const [_, year, month, day] = match[1].length === 4 
                ? [null, match[1], match[2], match[3]]
                : [null, match[3], match[2], match[1]];
            return new Date(year, month - 1, day).getTime();
        }
    }

    // Fallback: Versuche, das Datum direkt zu parsen
    return new Date(dateString).getTime();
}

async function updateCommentLike(commentId, type) {
    const articleId = new URLSearchParams(window.location.search).get("id");
    const response = await fetch(`api/update_like.php?article_id=${articleId}`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ index: commentId, type: type })
    });

    const result = await response.json();

    if (result.success) {
        loadComments();
    } else {
        alert('Fehler beim Aktualisieren: ' + result.message);
    }
}