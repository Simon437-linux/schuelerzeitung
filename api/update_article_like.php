<?php
if (!isset($_GET['article_id'])) {
    echo json_encode(['success' => false, 'message' => 'Kein Artikel angegeben.']);
    exit;
}

$articleId = $_GET['article_id'];
$articleFile = "../articles/{$articleId}.json";

if (!file_exists($articleFile)) {
    echo json_encode(['success' => false, 'message' => 'Artikel nicht gefunden.']);
    exit;
}

$article = json_decode(file_get_contents($articleFile), true);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $type = $input['type'] ?? null;
    $userId = $_SERVER['REMOTE_ADDR']; // Using IP address as user identifier

    if (!in_array($type, ['like', 'dislike'])) {
        echo json_encode(['success' => false, 'message' => 'Ungültige Anfrage.']);
        exit;
    }

    // Check if the user has already liked or disliked the article
    if (!isset($article['userInteractions'])) {
        $article['userInteractions'] = [];
    }

    $existingInteraction = null;
    foreach ($article['userInteractions'] as &$interaction) {
        if ($interaction['userId'] === $userId) {
            $existingInteraction = &$interaction;
            break;
        }
    }

    if ($existingInteraction) {
        // User has already interacted, adjust the counts accordingly
        if ($existingInteraction['type'] === 'like' && $type === 'dislike') {
            $article['likes']--;
            $article['dislikes']++;
        } elseif ($existingInteraction['type'] === 'dislike' && $type === 'like') {
            $article['dislikes']--;
            $article['likes']++;
        }
        // Update the interaction type
        $existingInteraction['type'] = $type;
    } else {
        // New interaction
        if ($type === 'like') {
            $article['likes'] = ($article['likes'] ?? 0) + 1;
        } elseif ($type === 'dislike') {
            $article['dislikes'] = ($article['dislikes'] ?? 0) + 1;
        }
        // Record the user's interaction
        $article['userInteractions'][] = ['userId' => $userId, 'type' => $type];
    }

    file_put_contents($articleFile, json_encode($article, JSON_PRETTY_PRINT));

    echo json_encode(['success' => true]);
    exit;
}

http_response_code(405);
echo json_encode(['success' => false, 'message' => 'Ungültige Anfrage.']);