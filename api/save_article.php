<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Eingaben lesen
    $title = htmlspecialchars($_POST['title'], ENT_QUOTES, 'UTF-8');
    $author = htmlspecialchars($_POST['author'], ENT_QUOTES, 'UTF-8');
    $content = htmlspecialchars($_POST['content'], ENT_QUOTES, 'UTF-8');
    $image = $_FILES['image'];

    // Validierung
    if (!$title || !$author || !$content || !$image || $image['error'] !== UPLOAD_ERR_OK) {
        http_response_code(400);
        echo json_encode(['error' => 'Alle Felder müssen korrekt ausgefüllt werden, einschließlich des Bildes.']);
        exit;
    }

    // Verzeichnis für Artikel prüfen/erstellen
    $articlesDir = '../articles';
    if (!is_dir($articlesDir)) {
        if (!mkdir($articlesDir, 0777, true)) {
            http_response_code(500);
            echo json_encode(['error' => 'Das Verzeichnis für Artikel konnte nicht erstellt werden.']);
            exit;
        }
    }

    // Bild speichern
    $imageName = basename($image["name"]);
    $targetFile = $articlesDir . '/' . $imageName;
    if (!move_uploaded_file($image['tmp_name'], $targetFile)) {
        http_response_code(500);
        echo json_encode(['error' => 'Das Bild konnte nicht hochgeladen werden.']);
        exit;
    }

    // Artikel-Daten vorbereiten
    $article = [
        'id' => uniqid('article_'),
        'title' => $title,
        'author' => $author,
        'content' => $content,
        'image' => $imageName,
        'date' => date('Y-m-d H:i:s'),
    ];

    // Artikel in JSON-Datei speichern
    $articleFilename = $articlesDir . '/' . $article['id'] . '.json';
    if (file_put_contents($articleFilename, json_encode($article, JSON_PRETTY_PRINT)) === false) {
        http_response_code(500);
        echo json_encode(['error' => 'Der Artikel konnte nicht gespeichert werden.']);
        exit;
    }

    echo json_encode(['success' => true]);
} else {
    http_response_code(405); // Method Not Allowed
    echo json_encode(['error' => 'Nur POST-Anfragen sind erlaubt.']);
}
