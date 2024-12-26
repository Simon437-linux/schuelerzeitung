const form = document.getElementById("article-form");

document.addEventListener("DOMContentLoaded", () => {
    // Überprüfen Sie, ob der Benutzer eingeloggt ist
    const author = localStorage.getItem('author');
    const password = localStorage.getItem('password');
    if (!author || !password || password !== 'Schülerzeitung') {
        window.location.href = 'login.html';
        return;
    }

    // Initialisieren Sie die Schrittweise Anzeige der Felder
    document.getElementById('step-2').style.display = 'none';
    document.getElementById('step-3').style.display = 'none';
    document.getElementById('step-4').style.display = 'none';
});

form.addEventListener("submit", async (e) => {
    e.preventDefault();
    const formData = new FormData(form);

    // Get the content from the TinyMCE editor
    const editor = tinymce.get(tinymce.activeEditor.id);
    if (editor) {
        const content = editor.getContent();
        formData.set('content', content);
    } else {
        console.error('TinyMCE editor not found');
        return;
    }

    // Set the author and password from local storage
    const author = localStorage.getItem('author');
    const password = localStorage.getItem('password');
    formData.set('author', author);
    formData.set('password', password);

    try {
        const response = await fetch("api/save_article.php", {
            method: "POST",
            body: formData,
        });

        const result = await response.json();
        console.log("Artikel gespeichert:", result);

        if (result.success) {
            alert("Artikel erfolgreich gespeichert!");
            form.reset();
            editor.setContent(''); // Clear the TinyMCE editor
            // Zurück zum ersten Schritt
            document.getElementById('step-1').style.display = 'block';
            document.getElementById('step-2').style.display = 'none';
            document.getElementById('step-3').style.display = 'none';
            document.getElementById('step-4').style.display = 'none';
        } else {
            alert("Fehler beim Speichern des Artikels: " + result.error);
        }
    } catch (error) {
        console.error("Fehler:", error);
        alert("Ein unerwarteter Fehler ist aufgetreten.");
    }
});

function showNextStep(currentStep, nextStep) {
    document.getElementById(currentStep).style.display = 'none';
    document.getElementById(nextStep).style.display = 'block';
}