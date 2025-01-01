<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');

function logDebug($message, $data = null) {
    $logMessage = date('Y-m-d H:i:s') . " - " . $message;
    if ($data !== null) {
        $logMessage .= ": " . print_r($data, true);
    }
    error_log($logMessage);
}

logDebug("Script started");

// Define the absolute paths
$baseDir = dirname(__DIR__);
$articlesDir = $baseDir . '/articles/';
$tokenFilePath = $baseDir . '/api/tokens.json';

if (!file_exists($tokenFilePath) || !is_readable($tokenFilePath)) {
    echo json_encode(['error' => "Authentifizierungsfehler."]);
    http_response_code(403);
    exit;
}

// Ensure directory exists and is writable
if (!file_exists($articlesDir) && !mkdir($articlesDir, 0755, true)) {
    echo json_encode(['error' => "Interner Serverfehler."]);
    http_response_code(500);
    exit;
}

if (!is_writable($articlesDir)) {
    echo json_encode(['error' => "Interner Serverfehler."]);
    http_response_code(500);
    exit;
}

// Header auslesen
$headers = getallheaders();

// Token und Benutzer-ID prüfen
if (!isset($headers['Authorization']) || !isset($headers['User-ID'])) {
    echo json_encode(['error' => "Authentifizierungsfehler."]);
    http_response_code(401);
    exit;
}

$providedToken = $headers['Authorization'];
$providedUserId = $headers['User-ID'];

// Validate token
$tokens = json_decode(file_get_contents($tokenFilePath), true);
$valid = false;
$author = null;

if ($tokens) {
    foreach ($tokens as $entry) {
        if ($entry['token'] === $providedToken && $entry['user_id'] === $providedUserId) {
            $valid = true;
            $author = $entry['user_id'];
            break;
        }
    }
}

if (!$valid) {
    echo json_encode(['error' => "Authentifizierungsfehler."]);
    http_response_code(403);
    exit;
}

// Artikel-Daten aus POST-Request abrufen
$title = $_POST['title'] ?? null;
$content = $_POST['content'] ?? null;
$category = $_POST['category'] ?? null;

// Validierung der Artikel-Daten
if (!$title || !$content || !$category) {
    echo json_encode(['error' => "Ungültige oder fehlende Artikeldaten."]);
    http_response_code(400);
    exit;
}

// Generate article ID
$articleId = uniqid("article_", true);

// Handle main image upload
$mainImage = null;
if (isset($_FILES['main_image']) && $_FILES['main_image']['error'] === UPLOAD_ERR_OK) {
    $fileInfo = pathinfo($_FILES['main_image']['name']);
    $mainImageName = $articleId . '_main.' . $fileInfo['extension'];
    $mainImagePath = $articlesDir . $mainImageName;

    if (move_uploaded_file($_FILES['main_image']['tmp_name'], $mainImagePath)) {
        $mainImage = $mainImageName;
    } else {
        echo json_encode(['error' => "Fehler beim Hochladen des Hauptbildes."]);
        http_response_code(500);
        exit;
    }
}

// Handle additional images
$additionalImages = [];
if (isset($_FILES['images'])) {
    $fileCount = count($_FILES['images']['name']);
    for ($i = 0; $i < $fileCount; $i++) {
        if ($_FILES['images']['error'][$i] === UPLOAD_ERR_OK) {
            $fileInfo = pathinfo($_FILES['images']['name'][$i]);
            $newImageName = $articleId . '_' . uniqid() . '.' . $fileInfo['extension'];
            $imagePath = $articlesDir . $newImageName;

            if (move_uploaded_file($_FILES['images']['tmp_name'][$i], $imagePath)) {
                $additionalImages[] = $newImageName;
            }
        }
    }
}

// Create new article
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

// Generate filename for the article
$articleFilePath = $articlesDir . $articleId . '.json';

// Convert to JSON
$jsonData = json_encode($newArticle, JSON_PRETTY_PRINT);
if ($jsonData === false) {
    echo json_encode(['error' => "Fehler beim Verarbeiten der Artikeldaten."]);
    http_response_code(500);
    exit;
}

// Try to save the file
$writeResult = file_put_contents($articleFilePath, $jsonData, LOCK_EX);
if ($writeResult === false) {
    echo json_encode(['error' => "Fehler beim Speichern des Artikels."]);
    http_response_code(500);
    exit;
}

// Verify that the file was actually created
if (!file_exists($articleFilePath)) {
    echo json_encode(['error' => "Fehler beim Speichern des Artikels."]);
    http_response_code(500);
    exit;
}

// Read the file back to verify its contents
$savedContent = file_get_contents($articleFilePath);
if ($savedContent !== $jsonData) {
    echo json_encode(['error' => "Fehler beim Speichern des Artikels."]);
    http_response_code(500);
    exit;
}

// Try to set permissions
chmod($articleFilePath, 0666);

echo json_encode([
    "status" => "success",
    "message" => "Artikel erfolgreich gespeichert.",
    "articleId" => $articleId,
    "mainImage" => $mainImage,
    "additionalImages" => $additionalImages
]);
?>