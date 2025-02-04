<?php
function authenticate() {
    $tokenFilePath = __DIR__ . '/tokens.json';
    $headers = getallheaders();

    if (!isset($headers['Authorization']) || !isset($headers['User-ID'])) {
        http_response_code(401);
        echo json_encode(['error' => "Fehlender Token oder User-ID."]);
        exit;
    }

    $providedToken = $headers['Authorization'];
    $providedUserId = $headers['User-ID'];

    if (!file_exists($tokenFilePath) || !is_readable($tokenFilePath)) {
        http_response_code(403);
        echo json_encode(['error' => "Authentifizierungsfehler."]);
        exit;
    }

    $tokens = json_decode(file_get_contents($tokenFilePath), true);
    foreach ($tokens as $entry) {
        if ($entry['token'] === $providedToken && $entry['user_id'] === $providedUserId) {
            return $providedUserId;
        }
    }

    http_response_code(403);
    echo json_encode(['error' => "Ungültiger Token oder Benutzer-ID."]);
    exit;
}
?>