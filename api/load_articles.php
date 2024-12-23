<?php
header("Content-Type: application/json");

$articlesDir = '../articles';
$articles = [];

// Überprüfe, ob das Verzeichnis existiert
if (is_dir($articlesDir)) {
    foreach (glob($articlesDir . '/*.json') as $file) {
        $article = json_decode(file_get_contents($file), true);
        // Filter out files that do not have the required article fields or have empty title/content
        if (isset($article['id'], $article['title'], $article['author'], $article['content']) &&
            !empty($article['title']) && !empty($article['content'])) {
            $articles[] = $article;
        }
    }
} else {
    echo json_encode(['error' => 'Articles directory not found.']);
    exit;
}

// Bereinigte Ausgabe
echo json_encode($articles, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);