async function saveComment(event) {
    event.preventDefault();

    const commentInput = document.querySelector('#comment');
    const comment = commentInput.value;
    const articleId = new URLSearchParams(window.location.search).get("id");

    if (comment.trim() === '') {
        alert('Der Kommentar darf nicht leer sein.');
        return;
    }

    const response = await fetch(`api/save_comment.php?article_id=${articleId}`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ comment: comment })
    });

    const result = await response.json();

    if (result.success) {
        commentInput.value = '';
        loadComments();
    } else {
        alert('Fehler beim Speichern: ' + result.message);
    }
}

async function loadComments() {
    const articleId = new URLSearchParams(window.location.search).get("id");
    const response = await fetch(`api/save_comment.php?article_id=${articleId}`);
    const comments = await response.json();

    // Sortiere die Kommentare nach Timestamp, neueste zuerst
    comments.sort((a, b) => new Date(b.timestamp) - new Date(a.timestamp));

    const commentsList = document.querySelector('#comments-list');
    commentsList.innerHTML = '';

    comments.forEach((entry, index) => {
        const listItem = document.createElement('li');
        listItem.innerHTML = `
            <strong>${entry.timestamp}:</strong> ${entry.comment} 
            <br>
            <button onclick="updateLike(${index}, 'like')">Gefällt mir</button> 
            <button onclick="updateLike(${index}, 'dislike')">Gefällt mir nicht</button>
            <span id="likes-${index}">👍 ${entry.likes || 0}</span>
            <span id="dislikes-${index}">👎 ${entry.dislikes || 0}</span>
        `;
        commentsList.appendChild(listItem);
    });
}


async function updateLike(index, type) {
    const articleId = new URLSearchParams(window.location.search).get("id");

    const response = await fetch(`api/update_like.php?article_id=${articleId}`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ index: index, type: type })
    });

    const result = await response.json();

    if (result.success) {
        loadComments();
    } else {
        alert('Fehler beim Aktualisieren: ' + result.message);
    }
}
