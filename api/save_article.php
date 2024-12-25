<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Eingaben lesen
    $title = htmlspecialchars($_POST['title'], ENT_QUOTES, 'UTF-8');
    $author = htmlspecialchars($_POST['author'], ENT_QUOTES, 'UTF-8');
    $content = $_POST['content']; // Do not use htmlspecialchars here to keep HTML tags
    $password = $_POST['password'];
    $category = htmlspecialchars($_POST['category'], ENT_QUOTES, 'UTF-8');

    // Passwort überprüfen
    $correctPassword = 'Schülerzeitung'; // Set your secure password here
    if ($password !== $correctPassword) {
        http_response_code(403);
        echo json_encode(['error' => 'Ungültiges Passwort.']);
        exit;
    }

    // Validierung
    if (!$title || !$author || !$content || !$category) {
        http_response_code(400);
        echo json_encode(['error' => 'Alle Felder müssen korrekt ausgefüllt werden, einschließlich der Kategorie.']);
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

    // Hauptbild speichern
    $mainImage = '';
    if (isset($_FILES['main_image']) && $_FILES['main_image']['error'] === UPLOAD_ERR_OK) {
        $mainImageName = basename($_FILES['main_image']['name']);
        $mainImagePath = $articlesDir . '/' . $mainImageName;
        if (move_uploaded_file($_FILES['main_image']['tmp_name'], $mainImagePath)) {
            $mainImage = $mainImageName;
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Das Hauptbild konnte nicht hochgeladen werden.']);
            exit;
        }
    }

    // Zusätzliche Bilder speichern
    $imageNames = [];
    if (isset($_FILES['images'])) {
        foreach ($_FILES['images']['tmp_name'] as $key => $tmp_name) {
            $imageName = basename($_FILES['images']['name'][$key]);
            $targetFile = $articlesDir . '/' . $imageName;
            if (move_uploaded_file($tmp_name, $targetFile)) {
                $imageNames[] = $imageName;
            } else {
                http_response_code(500);
                echo json_encode(['error' => 'Ein Bild konnte nicht hochgeladen werden.']);
                exit;
            }
        }
    }

    // Artikel-Daten vorbereiten
    $article = [
        'id' => uniqid('article_'),
        'title' => $title,
        'author' => $author,
        'content' => $content,
        'image' => $mainImage,
        'images' => $imageNames,
        'date' => date('Y-m-d H:i:s'),
        'likes' => 0,
        'dislikes' => 0,
        'userInteractions' => [],
        'category' => $category
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