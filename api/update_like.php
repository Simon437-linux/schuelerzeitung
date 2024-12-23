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
    $index = $input['index'] ?? null;
    $type = $input['type'] ?? null;
    $userId = $_SERVER['REMOTE_ADDR']; // Using IP address as user identifier

    if ($index === null || !in_array($type, ['like', 'dislike'])) {
        echo json_encode(['success' => false, 'message' => 'Ungültige Anfrage.']);
        exit;
    }

    $comments = loadComments($commentsFile);

    if (!isset($comments[$index])) {
        echo json_encode(['success' => false, 'message' => 'Kommentar nicht gefunden.']);
        exit;
    }

    // Check if the user has already liked or disliked the comment
    if (!isset($comments[$index]['userInteractions'])) {
        $comments[$index]['userInteractions'] = [];
    }

    $existingInteraction = null;
    foreach ($comments[$index]['userInteractions'] as &$interaction) {
        if ($interaction['userId'] === $userId) {
            $existingInteraction = &$interaction;
            break;
        }
    }

    if ($existingInteraction) {
        // User has already interacted, adjust the counts accordingly
        if ($existingInteraction['type'] === 'like' && $type === 'dislike') {
            $comments[$index]['likes']--;
            $comments[$index]['dislikes']++;
        } elseif ($existingInteraction['type'] === 'dislike' && $type === 'like') {
            $comments[$index]['dislikes']--;
            $comments[$index]['likes']++;
        }
        // Update the interaction type
        $existingInteraction['type'] = $type;
    } else {
        // New interaction
        if ($type === 'like') {
            $comments[$index]['likes'] = ($comments[$index]['likes'] ?? 0) + 1;
        } elseif ($type === 'dislike') {
            $comments[$index]['dislikes'] = ($comments[$index]['dislikes'] ?? 0) + 1;
        }
        // Record the user's interaction
        $comments[$index]['userInteractions'][] = ['userId' => $userId, 'type' => $type];
    }

    saveComments($commentsFile, $comments);

    echo json_encode(['success' => true]);
    exit;
}

http_response_code(405);
echo json_encode(['success' => false, 'message' => 'Ungültige Anfrage.']);