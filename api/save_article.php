<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

$baseDir = dirname(__DIR__);
$articlesDir = $baseDir . '/articles/';

if (!file_exists($articlesDir) && !mkdir($articlesDir, 0775, true)) {
    http_response_code(500);
    echo json_encode(['error' => "Interner Serverfehler: Verzeichnis konnte nicht erstellt werden."]);
    exit;
}

if (!is_writable($articlesDir)) {
    http_response_code(500);
    echo json_encode(['error' => "Interner Serverfehler: Keine Schreibrechte im Verzeichnis."]);
    exit;
}

$title = $_POST['title'] ?? null;
$content = $_POST['content'] ?? null;
$category = $_POST['category'] ?? null;
$author = $_POST['author'] ?? 'Anonym';

if (!$title || !$content || !$category) {
    http_response_code(400);
    echo json_encode(['error' => "Ungültige oder fehlende Artikeldaten."]);
    exit;
}

$articleId = uniqid("article_", true);

$mainImage = null;
if (isset($_FILES['main_image']) && $_FILES['main_image']['error'] === UPLOAD_ERR_OK) {
    $fileInfo = pathinfo($_FILES['main_image']['name']);
    $mainImageName = $articleId . '_main.' . $fileInfo['extension'];
    $mainImagePath = $articlesDir . $mainImageName;

    if (move_uploaded_file($_FILES['main_image']['tmp_name'], $mainImagePath)) {
        chmod($mainImagePath, 0644);
        $mainImage = $mainImageName;
    } else {
        http_response_code(500);
        echo json_encode(['error' => "Fehler beim Hochladen des Hauptbildes."]);
        exit;
    }
}

$additionalImages = [];
if (isset($_FILES['images'])) {
    for ($i = 0; $i < count($_FILES['images']['name']); $i++) {
        if ($_FILES['images']['error'][$i] === UPLOAD_ERR_OK) {
            $fileInfo = pathinfo($_FILES['images']['name'][$i]);
            $newImageName = $articleId . '_' . uniqid() . '.' . $fileInfo['extension'];
            $imagePath = $articlesDir . $newImageName;

            if (move_uploaded_file($_FILES['images']['tmp_name'][$i], $imagePath)) {
                chmod($imagePath, 0644);
                $additionalImages[] = $newImageName;
            }
        }
    }
}

$newArticle = [
    "id" => $articleId,
    "title" => $title,
    "author" => $author,
    "content" => $content,
    "image" => $mainImage,
    "images" => $additionalImages,
    "date" => date('Y-m-d H:i:s'),
    "likes" => 0,
    "dislikes" => 0,
    "userInteractions" => [],
    "category" => $category
];

$articleFilePath = $articlesDir . $articleId . '.json';
$jsonData = json_encode($newArticle, JSON_PRETTY_PRINT);

if ($jsonData === false || file_put_contents($articleFilePath, $jsonData, LOCK_EX) === false) {
    http_response_code(500);
    echo json_encode(['error' => "Fehler beim Speichern des Artikels."]);
    exit;
}

chmod($articleFilePath, 0666);

echo json_encode([
    "status" => "success",
    "message" => "Artikel erfolgreich gespeichert.",
    "articleId" => $articleId,
    "mainImage" => $mainImage,
    "additionalImages" => $additionalImages
]);
?>