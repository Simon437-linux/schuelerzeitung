<?php
$baseUrl = 'https://schuelerzeitung.rswm.schule/';
$tempUrl = $baseUrl . 'submit_temp_' . bin2hex(random_bytes(16)) . '.html';
$email = 'schuelerzeitung@rswm.de'; // Replace with your email

// Rename the submit.html file
rename('../submit.html', '../' . basename($tempUrl));

// Save the URL with an expiration time (20 minutes)
$tempUrls = json_decode(file_get_contents('temp_urls.json'), true) ?? [];
$tempUrls[$tempUrl] = time() + 1200; // 20 minutes from now
file_put_contents('temp_urls.json', json_encode($tempUrls));

// Send email
mail($email, 'Temporary Submit URL', 'Your temporary submit URL is: ' . $tempUrl);

// Output the URL
echo $tempUrl;
?>