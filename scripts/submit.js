document.addEventListener('DOMContentLoaded', function () {
    const token = localStorage.getItem('token');
    const userId = localStorage.getItem('user_id');
    if (!token || !userId) {
        window.location.href = 'login.html';
        return;
    }

    const form = document.getElementById('article-form');
    const MAX_FILE_SIZE = 50 * 1024 * 1024; // 50 MB

    form.addEventListener('submit', function(event) {
        event.preventDefault();

        const mainImageInput = document.querySelector('input[name="main_image"]');
        if (mainImageInput.files[0] && mainImageInput.files[0].size > MAX_FILE_SIZE) {
            alert("Die Datei ist zu groß. Maximale Größe: 50 MB");
            mainImageInput.value = ''; // Datei-Auswahl zurücksetzen
            return;
        }

        const formData = new FormData(form);
        formData.append('content', tinymce.get('content').getContent());

        fetch('api/save_article.php', {
            method: 'POST',
            headers: {
                'Authorization': token,
                'User-ID': userId
            },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                console.error("Fehler beim Speichern des Artikels:", data.error);
            } else {
                console.log("Artikel erfolgreich gespeichert:", data);
                alert('Artikel erfolgreich gespeichert.');
                form.reset();
                tinymce.get('content').setContent('');
            }
        })
        .catch(error => {
            console.error("Fehler beim Speichern des Artikels:", error);
            alert('Fehler beim Speichern des Artikels: ' + error.message);
        });
    });
});