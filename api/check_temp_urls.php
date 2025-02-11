<?php
$baseUrl = 'https://schuelerzeitung.rswm.schule/';
$weeklyUrl = $baseUrl . 'submit_' . bin2hex(random_bytes(16)) . '.html';
$email = 'schuelerzeitung@rswm.de'; // Replace with your email

// Pfad zur ursprünglichen und neuen Datei
$originalFilePath = '../submit.html';
$newFilePath = '../' . basename($weeklyUrl);

// Überprüfen, ob die ursprüngliche Datei existiert
if (!file_exists($originalFilePath)) {
    echo 'Fehler: Die Datei submit.html existiert nicht.';
    exit;
}

// Umbenennen der Datei
if (!rename($originalFilePath, $newFilePath)) {
    echo 'Fehler: Die Datei konnte nicht umbenannt werden.';
    exit;
}

// Send email
mail($email, 'Weekly Submit URL', 'Your new submit URL is: ' . $weeklyUrl);

// Save the URL to a file
file_put_contents('current_weekly_url.txt', $weeklyUrl);
?>