document.addEventListener('DOMContentLoaded', function () {
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
            body: formData
        })
        .then(response => response.text())
        .then(data => {
            try {
                const jsonData = JSON.parse(data); // Versuchen Sie, die Antwort zu parsen
                if (jsonData.error) {
                    console.error("Fehler beim Speichern des Artikels:", jsonData.error);
                } else {
                    console.log("Artikel erfolgreich gespeichert:", jsonData);
                    alert('Artikel erfolgreich gespeichert.');
                    form.reset();
                    tinymce.get('content').setContent('');
                }
            } catch (error) {
                console.error("Fehler beim Parsen der JSON-Antwort:", error);
                console.error("Serverantwort:", data); // Geben Sie die Serverantwort aus
                alert('Fehler beim Speichern des Artikels: ' + data); // Geben Sie die Serverantwort in der Fehlermeldung aus
            }
        })
        .catch(error => {
            console.error("Fehler beim Speichern des Artikels:", error);
            alert('Fehler beim Speichern des Artikels: ' + error.message);
        });
    });
});