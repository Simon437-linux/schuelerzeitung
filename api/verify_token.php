<?php
$filePath = 'tokens.json';
$headers = getallheaders();

if (!isset($headers['Authorization']) || !isset($headers['User-ID'])) {
    echo "Fehler: Kein Token oder keine Benutzer-ID bereitgestellt.";
    http_response_code(401);
    exit;
}

$providedToken = $headers['Authorization'];
$providedUserId = $headers['User-ID'];

// Datei einlesen und prüfen
if (file_exists($filePath)) {
    $tokens = json_decode(file_get_contents($filePath), true);
    foreach ($tokens as $entry) {
        if ($entry['token'] === $providedToken && $entry['user_id'] === $providedUserId) {
            echo "Token ist gültig.";
            exit;
        }
    }
}

echo "Ungültiger Token oder Benutzer-ID.";
http_response_code(403);
?>
