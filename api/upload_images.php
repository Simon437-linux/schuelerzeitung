<?php
header('Content-Type: application/json');

$targetDir = '../uploads/';
if (!is_dir($targetDir)) {
    mkdir($targetDir, 0777, true);
}

$response = [];

if ($_FILES['file']['error'] === UPLOAD_ERR_OK) {
    $fileTmpPath = $_FILES['file']['tmp_name'];
    $fileName = basename($_FILES['file']['name']);
    $targetFilePath = $targetDir . $fileName;

    if (move_uploaded_file($fileTmpPath, $targetFilePath)) {
        $response = ['filePath' => $targetFilePath];
    } else {
        $response = ['error' => 'Fehler beim Hochladen der Datei.'];
    }
} else {
    $response = ['error' => 'Fehler beim Hochladen der Datei.'];
}

echo json_encode($response);