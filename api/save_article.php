<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Define log function
function logDebug($message, $data = null) {
    $logMessage = date('Y-m-d H:i:s') . " - " . $message;
    if ($data !== null) {
        $logMessage .= ": " . print_r($data, true);
    }
    error_log($logMessage);
}

logDebug("Script started");

// Define the absolute paths
$baseDir = dirname(dirname(__FILE__));  // Get the parent directory of api folder
$tokenFilePath = __DIR__ . '/tokens.json';
$articlesDir = $baseDir . '/articles/';
$uploadDir = $baseDir . '/uploads/';

logDebug("Paths", [
    'baseDir' => $baseDir,
    'tokenFilePath' => $tokenFilePath,
    'articlesDir' => $articlesDir,
    'uploadDir' => $uploadDir
]);

// Check directory permissions and create directories if needed
foreach ([$articlesDir, $uploadDir] as $dir) {
    if (!is_dir($dir)) {
        logDebug("Creating directory: " . $dir);
        if (!mkdir($dir, 0777, true)) {
            logDebug("Failed to create directory: " . $dir);
            echo json_encode(['error' => "Fehler beim Erstellen des Verzeichnisses: " . $dir]);
            http_response_code(500);
            exit;
        }
    }
    
    if (!is_writable($dir)) {
        logDebug("Directory not writable: " . $dir);
        echo json_encode(['error' => "Verzeichnis nicht beschreibbar: " . $dir]);
        http_response_code(500);
        exit;
    }
}

// Header auslesen
$headers = getallheaders();
logDebug("Received headers", $headers);

// Token und Benutzer-ID prüfen
if (!isset($headers['Authorization']) || !isset($headers['User-ID'])) {
    logDebug("Missing Authorization or User-ID headers");
    echo json_encode(['error' => "Kein Token oder keine Benutzer-ID bereitgestellt."]);
    http_response_code(401);
    exit;
}

$providedToken = $headers['Authorization'];
$providedUserId = $headers['User-ID'];

// Validate token
logDebug("Validating token for user", $providedUserId);

if (!file_exists($tokenFilePath)) {
    logDebug("Token file not found at: " . $tokenFilePath);
    echo json_encode(['error' => "Token-Datei nicht gefunden."]);
    http_response_code(403);
    exit;
}

$tokens = json_decode(file_get_contents($tokenFilePath), true);
$valid = false;
$author = null;

logDebug("Loaded tokens", $tokens);

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
    logDebug("Invalid token or user ID");
    echo json_encode(['error' => "Ungültiger Token oder Benutzer-ID."]);
    http_response_code(403);
    exit;
}

logDebug("Token validation successful");

// Log POST data
logDebug("POST data", $_POST);
logDebug("FILES data", $_FILES);

// Artikel-Daten aus POST-Request abrufen
$title = $_POST['title'] ?? null;
$content = $_POST['content'] ?? null;
$category = $_POST['category'] ?? null;

// Validierung der Artikel-Daten
if (!$title || !$content || !$category) {
    logDebug("Missing required fields", [
        'title' => $title ? 'present' : 'missing',
        'content' => $content ? 'present' : 'missing',
        'category' => $category ? 'present' : 'missing'
    ]);
    echo json_encode(['error' => "Ungültige oder fehlende Artikeldaten."]);
    http_response_code(400);
    exit;
}

// Generate article ID first so we can use it for image filenames
$articleId = uniqid("article_", true);

// Handle main image upload
$mainImage = null;
if (isset($_FILES['main_image']) && $_FILES['main_image']['error'] === UPLOAD_ERR_OK) {
    $fileInfo = pathinfo($_FILES['main_image']['name']);
    $mainImageName = $articleId . '_main.' . $fileInfo['extension'];
    $mainImagePath = $articlesDir . $mainImageName;
    
    if (move_uploaded_file($_FILES['main_image']['tmp_name'], $mainImagePath)) {
        $mainImage = $mainImageName;
        logDebug("Main image uploaded successfully", $mainImagePath);
    } else {
        logDebug("Failed to upload main image", $_FILES['main_image']);
        echo json_encode(['error' => "Das Hauptbild konnte nicht hochgeladen werden."]);
        http_response_code(500);
        exit;
    }
}

// Handle additional images
$additionalImages = [];
if (isset($_FILES['images']) && is_array($_FILES['images']['name'])) {
    foreach ($_FILES['images']['name'] as $key => $filename) {
        if ($_FILES['images']['error'][$key] === UPLOAD_ERR_OK) {
            $fileInfo = pathinfo($filename);
            $newImageName = $articleId . '_' . uniqid() . '.' . $fileInfo['extension'];
            $imagePath = $articlesDir . $newImageName;
            
            if (move_uploaded_file($_FILES['images']['tmp_name'][$key], $imagePath)) {
                $additionalImages[] = $newImageName;
                logDebug("zusätzlichen bilder wurden hochgeladen :)", $imagePath);
            } else {
                logDebug("die/das zusätzlich/-e Bild/-er konten nicht hochgeladen werden", [
                    'filename' => $filename,
                    'error' => $_FILES['images']['error'][$key]
                ]);
                echo json_encode(['error' => "Ein zusätzliches Bild konnte nicht hochgeladen werden."]);
                http_response_code(500);
                exit;
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

logDebug("Created new article object", $newArticle);

// Generate filename for the article
$articleFilePath = $articlesDir . $articleId . '.json';

// Convert to JSON
$jsonData = json_encode($newArticle, JSON_PRETTY_PRINT);
if ($jsonData === false) {
    logDebug("JSON encoding failed", json_last_error_msg());
    echo json_encode(['error' => "Fehler beim Encodieren der Artikeldaten."]);
    http_response_code(500);
    exit;
}

// Try to save the file
logDebug("Attempting to save article file: " . $articleFilePath);
$writeResult = file_put_contents($articleFilePath, $jsonData, LOCK_EX);
if ($writeResult === false) {
    logDebug("Failed to write article file", [
        'error' => error_get_last(),
        'file_exists' => file_exists($articleFilePath),
        'is_writable' => is_writable($articlesDir),
        'free_space' => disk_free_space($articlesDir)
    ]);
    echo json_encode(['error' => "Fehler beim Speichern der Artikeldaten."]);
    http_response_code(500);
    exit;
}

// Try to set permissions
logDebug("Setting file permissions");
chmod($articleFilePath, 0666);

logDebug("Article saved successfully");
echo json_encode([
    "status" => "success",
    "message" => "Artikel erfolgreich gespeichert.",
    "articleId" => $articleId,
    "mainImage" => $mainImage,
    "additionalImages" => $additionalImages
]);
?>