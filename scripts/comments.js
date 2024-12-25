async function saveComment(event) {
    event.preventDefault();

    const nameInput = document.querySelector('#name');
    const commentInput = document.querySelector('#comment');
    const name = nameInput.value;
    const comment = commentInput.value;
    const articleId = new URLSearchParams(window.location.search).get("id");

    if (comment.trim() === '') {
        alert('Der Kommentar darf nicht leer sein.');
        return;
    }

    const response = await fetch(`api/save_comment.php?article_id=${articleId}`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ name: name, comment: comment })
    });

    const result = await response.json();

    if (result.success) {
        nameInput.value = '';
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

    const commentsList = document.querySelector('#comments-list');
    commentsList.innerHTML = '';

    comments.forEach((entry, index) => {
        const listItem = document.createElement('li');
        listItem.innerHTML = `
            <strong>${entry.name}:</strong> ${entry.comment} 
            <br>
            <span class="comment-date">${entry.timestamp}</span>
            <br>
            <button class="like" onclick="updateLike(${index}, 'like')"><span id="likes-${index}">👍 ${entry.likes || 0}</span></button> 
            <button class="dislike" onclick="updateLike(${index}, 'dislike')"><span id="dislikes-${index}">👎 ${entry.dislikes || 0}</span></button>
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