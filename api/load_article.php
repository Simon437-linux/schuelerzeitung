<?php
header("Content-Type: application/json");

if (!isset($_GET['id'])) {
    echo json_encode(['error' => 'Kein Artikel angegeben.']);
    exit;
}

$articleId = $_GET['id'];
$articlesDir = '../articles';
$articleFile = $articlesDir . '/' . $articleId . '.json';

if (!file_exists($articleFile)) {
    echo json_encode(['error' => 'Artikel nicht gefunden.']);
    exit;
}

$article = json_decode(file_get_contents($articleFile), true);
echo json_encode($article);

