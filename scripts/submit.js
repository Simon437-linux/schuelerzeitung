const form = document.getElementById("article-form");

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
            tinymce.activeEditor.setContent(''); // Clear the TinyMCE editor
        } else {
            alert("Fehler beim Speichern des Artikels: " + result.error);
        }
    } catch (error) {
        console.error("Fehler:", error);
        alert("Ein unerwarteter Fehler ist aufgetreten.");
    }
});