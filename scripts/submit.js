document.addEventListener('DOMContentLoaded', function () {
    // Authentifizierungsprüfung nur für submit.html
    if (window.location.pathname.endsWith("submit.html")) {
        const token = localStorage.getItem('token');
        const userId = localStorage.getItem('user_id');
        if (!token || !userId) {
            window.location.href = 'login.html';
            return;
        }
    }
});

    const form = document.getElementById('article-form');
    const token = localStorage.getItem('token');
    const userId = localStorage.getItem('user_id');
    const MAX_FILE_SIZE = 50 * 1024 * 1024; // 50 MB

    form.addEventListener('submit', function(event) {
        event.preventDefault();

        if (!token || !userId) {
            alert('Kein gültiger Token gefunden. Bitte erneut einloggen.');
            window.location.href = 'login.html';
            return;
        }

        document.querySelector('input[name="main_image"]').addEventListener('change', function(event) {
            if (event.target.files[0] && event.target.files[0].size > MAX_FILE_SIZE) {
                alert("Die Datei ist zu groß. Maximale Größe: 50 MB");
                event.target.value = ''; // Datei-Auswahl zurücksetzen
            }
        });

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
        .then(response => response.text())
        .then(data => {
            console.log('Server response:', data);
            try {
                const jsonData = JSON.parse(data);
                if (jsonData.error) {
                    throw new Error(jsonData.error);
                }
                alert('Artikel erfolgreich gespeichert.');
                form.reset();
                tinymce.get('content').setContent('');
            } catch (error) {
                throw new Error('Unerwartete Serverantwort: ' + data);
            }
        })
        .catch(error => {
            console.error('Fehler:', error);
            alert('Fehler beim Speichern des Artikels: ' + error.message);
        });
    });