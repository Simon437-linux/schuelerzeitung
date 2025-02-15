<?php
$email = 'schuelerzeitung@rswm.de'; // E-Mail ersetzen

// Dynamisch nach vorhandenen Submit-Dateien suchen
$submissionFiles = glob('../submit_*.html');

// Ursprüngliche Datei bestimmen
if (!empty($submissionFiles)) {
    // Neueste Datei zuerst sortieren (falls mehrere)
    usort($submissionFiles, function($a, $b) {
        return filemtime($b) - filemtime($a);
    });
    $originalFilePath = $submissionFiles[0];
} else {
    $originalFilePath = '../submit.html';
}

// Neuen zufälligen Dateinamen generieren
$weeklyUrl = 'submit_' . bin2hex(random_bytes(16)) . '.html';
$newFilePath = '../' . $weeklyUrl;

// Debugging
error_log('Original File: ' . $originalFilePath);
error_log('New File: ' . $newFilePath);

// Überprüfen ob Original existiert
if (!file_exists($originalFilePath)) {
    error_log('Fehler: Datei existiert nicht');
    exit('Fehler: Datei existiert nicht');
}

// Datei umbenennen
if (!rename($originalFilePath, $newFilePath)) {
    error_log('Umbenennen fehlgeschlagen');
    exit('Fehler beim Umbenennen');
}

// E-Mail senden
$mailSent = mail(
    $email,
    'Neue Weekly Submit URL',
    'Die neue Submit-URL lautet: ' . $weeklyUrl
);

if (!$mailSent) {
    error_log('E-Mail Fehler');
    exit('E-Mail konnte nicht gesendet werden');
}

echo 'Erfolgreich: Datei wurde umbenannt und E-Mail versendet.';
?>