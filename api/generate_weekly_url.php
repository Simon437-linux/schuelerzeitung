<?php
$weeklyUrl = 'submit_' . bin2hex(random_bytes(16)) . '.html';
$email = 'schuelerzeitung@rswm.de'; // Replace with your email

// Pfad zur ursprünglichen und neuen Datei
$originalFilePath = '../submit' . $weeklyUrl . '.html';
$newFilePath = '../' . basename($weeklyUrl);

// Überprüfen, ob die ursprüngliche Datei existiert
if (!file_exists($originalFilePath)) {
    error_log('Fehler: Die Datei submit.html existiert nicht.');
    echo 'Fehler: Die Datei submit.html existiert nicht.';
    exit;
}

// Umbenennen der Datei
if (!rename($originalFilePath, $newFilePath)) {
    error_log('Fehler: Die Datei konnte nicht umbenannt werden.');
    echo 'Fehler: Die Datei konnte nicht umbenannt werden.';
    exit;
}

// Send email
if (!mail($email, 'Weekly Submit URL', 'Your new submit URL is: ' . $weeklyUrl)) {
    error_log('Fehler: Die E-Mail konnte nicht gesendet werden.');
    echo 'Fehler: Die E-Mail konnte nicht gesendet werden.';
    exit;
}

// Save the URL to a file
file_put_contents('current_weekly_url.txt', $weeklyUrl);
?>