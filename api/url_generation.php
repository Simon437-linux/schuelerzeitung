<?php
$tempUrls = json_decode(file_get_contents('temp_urls.json'), true) ?? [];
$currentTime = time();

foreach ($tempUrls as $url => $expiration) {
    if ($currentTime > $expiration) {
        // Rename the file back to submit.html
        rename('../' . basename($url), '../submit.html');
        unset($tempUrls[$url]);
    }
}

file_put_contents('temp_urls.json', json_encode($tempUrls));
?>