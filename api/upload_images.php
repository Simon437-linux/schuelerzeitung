<?php
header('Content-Type: application/json');

$targetDir = '../uploads/';
if (!is_dir($targetDir)) {
    mkdir($targetDir, 0777, true);
}

if ($_FILES['file']['error'] === UPLOAD_ERR_OK) {
    $fileTmpPath = $_FILES['file']['tmp_name'];
    $fileName = basename($_FILES['file']['name']);
    $targetFilePath = $targetDir . $fileName;

    if (move_uploaded_file($fileTmpPath, $targetFilePath)) {
        echo json_encode(['success' => true, 'filePath' => $targetFilePath]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Fehler beim Hochladen der Datei.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Fehler beim Hochladen der Datei.']);
}