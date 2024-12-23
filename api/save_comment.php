<?php
if (!isset($_GET['article_id'])) {
    echo json_encode(['success' => false, 'message' => 'Kein Artikel angegeben.']);
    exit;
}

$articleId = $_GET['article_id'];
$commentsFile = "../articles/{$articleId}_comments.json";

function loadComments($file)
{
    if (file_exists($file)) {
        return json_decode(file_get_contents($file), true) ?? [];
    }
    return [];
}

function saveComments($file, $data)
{
    file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $comment = $input['comment'] ?? '';

    if (empty(trim($comment))) {
        echo json_encode(['success' => false, 'message' => 'Der Kommentar ist leer.']);
        exit;
    }

    $comments = loadComments($commentsFile);

    $comments[] = [
        'comment' => htmlspecialchars($comment),
        'timestamp' => date('Y-m-d H:i:s'),
        'likes' => 0,
        'dislikes' => 0
    ];

    saveComments($commentsFile, $comments);

    echo json_encode(['success' => true]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $comments = loadComments($commentsFile);
    echo json_encode($comments);
    exit;
}

http_response_code(405);
echo json_encode(['success' => false, 'message' => 'Ungültige Anfrage.']);