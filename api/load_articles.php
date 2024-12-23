<?php
header("Content-Type: application/json");

$articlesDir = '../articles';
$articles = [];

// Überprüfe, ob das Verzeichnis existiert
if (is_dir($articlesDir)) {
    foreach (glob($articlesDir . '/*.json') as $file) {
        $article = json_decode(file_get_contents($file), true);
        if ($article) {
            $articles[] = $article;
        }
    }
} else {
    echo json_encode(['error' => 'Articles directory not found.']);
    exit;
}

// Bereinigte Ausgabe
echo json_encode($articles, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
