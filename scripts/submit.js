document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('article-form');
    const token = localStorage.getItem('token');
    const userId = localStorage.getItem('user_id');

    form.addEventListener('submit', function(event) {
        event.preventDefault();

        if (!token || !userId) {
            alert('Kein gültiger Token gefunden. Bitte erneut einloggen.');
            window.location.href = 'login.html';
            return;
        }

        // Token-Validierung
        fetch('api/verify_token.php', {
            method: 'GET',
            headers: {
                'Authorization': token,
                'User-ID': userId
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Token validation failed');
            }
            return response.text();
        })
        .then(() => {
            // Get TinyMCE content
            const content = tinymce.get('content').getContent();
            const formData = new FormData(form);
            
            // Explicitly get all form values
            const title = form.querySelector('#title').value;
            const category = form.querySelector('select[name="category"]').value;
            const mainImage = form.querySelector('input[name="main_image"]').files[0];
            
            // Log all values before submission
            console.log('Submitting data:', {
                title: title,
                content: content,
                category: category,
                mainImage: mainImage ? mainImage.name : 'No image selected',
                token: token,
                userId: userId
            });
            
            // Clear and rebuild FormData
            const newFormData = new FormData();
            newFormData.append('title', title);
            newFormData.append('content', content);
            newFormData.append('category', category);
            if (mainImage) {
                newFormData.append('main_image', mainImage);
            }
            
            // Log the final FormData
            console.log('FormData contents:');
            for (const [key, value] of newFormData.entries()) {
                console.log(key, typeof value === 'object' ? 'File: ' + value.name : value);
            }

            return fetch('api/save_article.php', {
                method: 'POST',
                headers: {
                    'Authorization': token,
                    'User-ID': userId
                },
                body: newFormData
            });
        })
        .then(response => response.text())
        .then(data => {
            console.log('Raw server response:', data);
            if (data.includes('Fehler')) {
                console.error('Server response:', data);
                alert('Fehler beim Speichern des Artikels: ' + data);
            } else {
                alert('Artikel erfolgreich gespeichert.');
                form.reset();
                tinymce.get('content').setContent('');
                // Return to first step
                document.getElementById('step-1').style.display = 'block';
                document.getElementById('step-2').style.display = 'none';
                document.getElementById('step-3').style.display = 'none';
                document.getElementById('step-4').style.display = 'none';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            if (error.message === 'Token validation failed') {
                alert('Ungültiger Token. Bitte erneut einloggen.');
                window.location.href = 'login.html';
            } else {
                alert('Fehler beim Speichern des Artikels: ' + error.message);
            }
        });
    });
});