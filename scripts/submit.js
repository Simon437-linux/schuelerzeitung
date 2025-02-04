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

    const headers = new Headers();
    headers.append("Authorization", token || '');
    headers.append("User-ID", userId || '');

    // Debugging: Überprüfen Sie die Header vor dem Senden
    console.log("Authorization Header:", token);
    console.log("User-ID Header:", userId);

    fetch('api/save_article.php', {
        method: 'POST',
        headers: headers,
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