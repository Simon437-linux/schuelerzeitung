<?php
$baseUrl = 'https://schuelerzeitung.rswm.schule/';
$weeklyUrl = $baseUrl . 'submit_' . bin2hex(random_bytes(16)) . '.html';
$email = 'schuelerzeitung@rswm.de'; // Replace with your email

// Rename the submit.html file
rename('../submit.html', '../' . basename($weeklyUrl));

// Send email
mail($email, 'Weekly Submit URL', 'Your new submit URL is: ' . $weeklyUrl);

// Save the URL to a file
file_put_contents('current_weekly_url.txt', $weeklyUrl);
?>