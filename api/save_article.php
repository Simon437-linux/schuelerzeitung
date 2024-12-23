<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Eingaben lesen
    $title = htmlspecialchars($_POST['title'], ENT_QUOTES, 'UTF-8');
    $author = htmlspecialchars($_POST['author'], ENT_QUOTES, 'UTF-8');
    $content = htmlspecialchars($_POST['content'], ENT_QUOTES, 'UTF-8');
    $image = $_FILES['image'];
    $password = $_POST['password'];

    // Passwort überprüfen
    $correctPassword = 'Schülerzeitung'; // Set your secure password here
    if ($password !== $correctPassword) {
        http_response_code(403);
        echo json_encode(['error' => 'Ungültiges Passwort.']);
        exit;
    }

    // Validierung
    if (!$title || !$author || !$content || !$image || $image['error'] !== UPLOAD_ERR_OK) {
        http_response_code(400);
        echo json_encode(['error' => 'Alle Felder müssen korrekt ausgefüllt werden, einschließlich des Bildes.']);
        exit;
    }

    // Verzeichnis für Artikel prüfen/erstellen
    $articlesDir = '../articles';
    $authorImagesDir = '../author_images';
    if (!is_dir($articlesDir)) {
        if (!mkdir($articlesDir, 0777, true)) {
            http_response_code(500);
            echo json_encode(['error' => 'Das Verzeichnis für Artikel konnte nicht erstellt werden.']);
            exit;
        }
    }

    // Bilder speichern
    $imageName = basename($image["name"]);
    $targetFile = $articlesDir . '/' . $imageName;
    if (!move_uploaded_file($image['tmp_name'], $targetFile)) {
        http_response_code(500);
        echo json_encode(['error' => 'Das Bild konnte nicht hochgeladen werden.']);
        exit;
    }

    // Autorbild automatisch zuweisen
    $authorImageName = str_replace(' ', '_', strtolower($author)) . '.jpg'; // Convert author name to lowercase and replace spaces with underscores
    if (!file_exists($authorImagesDir . '/' . $authorImageName)) {
        $authorImageName = 'default.jpg'; // Fallback to a default image if the author's image is not found
    }

    // Artikel-Daten vorbereiten
    $article = [
        'id' => uniqid('article_'),
        'title' => $title,
        'author' => $author,
        'content' => $content,
        'image' => $imageName,
        'authorImage' => $authorImageName,
        'date' => date('Y-m-d H:i:s'),
        'likes' => 0,
        'dislikes' => 0,
        'userInteractions' => []
    ];

    // Artikel in JSON-Datei speichern
    $articleFilename = $articlesDir . '/' . $article['id'] . '.json';
    if (file_put_contents($articleFilename, json_encode($article, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) === false) {
        http_response_code(500);
        echo json_encode(['error' => 'Der Artikel konnte nicht gespeichert werden.']);
        exit;
    }

    echo json_encode(['success' => true]);
} else {
    http_response_code(405); // Method Not Allowed
    echo json_encode(['error' => 'Nur POST-Anfragen sind erlaubt.']);
}