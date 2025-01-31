<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Authorization, User-ID, Content-Type");

function logDebug($message, $data = null) {
    $logMessage = date('Y-m-d H:i:s') . " - " . $message;
    if ($data !== null) {
        $logMessage .= ": " . print_r($data, true);
    }
    error_log($logMessage);
}

logDebug("Script started");

$baseDir = dirname(__DIR__);
$articlesDir = $baseDir . '/articles/';
$tokenFilePath = $baseDir . '/api/tokens.json';

if (!file_exists($tokenFilePath) || !is_readable($tokenFilePath)) {
    http_response_code(403);
    echo json_encode(['error' => "Authentifizierungsfehler."]);
    exit;
}

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

$headers = getallheaders();
logDebug("Received Headers", $headers);

// DEBUGGING: Header ausgeben
error_log("Empfangene Header: " . print_r($headers, true));
file_put_contents(__DIR__ . "/debug_headers.log", print_r($headers, true));

$providedToken = $headers['Authorization'] ?? $_SERVER['HTTP_AUTHORIZATION'] ?? null;
$providedUserId = $headers['User-ID'] ?? $_SERVER['HTTP_USER_ID'] ?? null;

if (!$providedToken || !$providedUserId) {
    http_response_code(401);
    echo json_encode(['error' => "Authentifizierungsfehler: Fehlender Token oder User-ID."]);
    exit;
}

tokenCheck:
$tokens = json_decode(file_get_contents($tokenFilePath), true);
$valid = false;
$author = null;

foreach ($tokens as $entry) {
    if ($entry['token'] === $providedToken && $entry['user_id'] === $providedUserId) {
        $valid = true;
        $author = $entry['user_id'];
        break;
    }
}

if (!$valid) {
    http_response_code(403);
    echo json_encode(['error' => "Authentifizierungsfehler: Ungültiges Token."]);
    exit;
}

$title = $_POST['title'] ?? null;
$content = $_POST['content'] ?? null;
$category = $_POST['category'] ?? null;

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
