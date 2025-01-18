<?php
require_once 'db_connect.php';

$sql = "SELECT id, title, likes FROM articles ORDER BY likes DESC LIMIT 3";

$result = $conn->query($sql);

$articles = [];
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $articles[] = $row;
    }
}

echo json_encode($articles);

$conn->close();
?>