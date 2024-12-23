<?php
$articlesDir = 'articles';

foreach (glob($articlesDir . '/*.json') as $file) {
    $article = json_decode(file_get_contents($file), true);

    // Ensure all required fields are present
    $article['id'] = $article['id'] ?? uniqid('article_');
    $article['title'] = $article['title'] ?? 'Untitled';
    $article['author'] = $article['author'] ?? 'Unknown';
    $article['content'] = $article['content'] ?? '';
    $article['image'] = $article['image'] ?? 'default.jpg';
    $article['date'] = $article['date'] ?? date('Y-m-d H:i:s');
    $article['likes'] = $article['likes'] ?? 0;
    $article['dislikes'] = $article['dislikes'] ?? 0;
    $article['userInteractions'] = $article['userInteractions'] ?? [];

    // Save the updated article
    file_put_contents($file, json_encode($article, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}
echo "Articles updated successfully.";