<?php
$data = json_decode(file_get_contents('php://input'), true);
$filePath = 'tokens.json';

if (!$data || !isset($data['code']) || !isset($data['user_id'])) {
    echo "Fehler: Keine gültigen Daten empfangen.";
    exit;
}

// Bestehende JSON-Datei einlesen
$existingData = [];
if (file_exists($filePath)) {
    $fileContent = file_get_contents($filePath);
    $existingData = json_decode($fileContent, true) ?? []; // Bestehende Daten oder leeres Array
}

// Token hinzufügen
$existingData[] = [
    'token' => $data['code'],
    'user_id' => $data['user_id'], // Benutzer-ID speichern
    'ip' => $_SERVER['REMOTE_ADDR'] // IP-Adresse speichern
];

// Daten als JSON speichern
if (file_put_contents($filePath, json_encode($existingData, JSON_PRETTY_PRINT))) {
    echo "Token erfolgreich gespeichert.";
} else {
    echo "Fehler beim Speichern des Tokens.";
}
?>
