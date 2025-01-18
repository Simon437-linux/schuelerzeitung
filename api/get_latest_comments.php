<?php
require_once 'db_connect.php';

$sql = "SELECT c.text, c.author, a.title as articleTitle 
        FROM comments c 
        JOIN articles a ON c.article_id = a.id 
        ORDER BY c.created_at DESC 
        LIMIT 5";

$result = $conn->query($sql);

$comments = [];
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $comments[] = $row;
    }
}

echo json_encode($comments);

$conn->close();
?>