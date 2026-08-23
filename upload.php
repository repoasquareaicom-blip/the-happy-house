<?php
if ($_FILES['file']) {
    $uploadDir = 'uploads/';
    $fileName = time() . '_' . basename($_FILES['file']['name']);
    $targetFile = $uploadDir . $fileName;

    if (move_uploaded_file($_FILES['file']['tmp_name'], $targetFile)) {
        echo json_encode(["success" => true, "url" => $targetFile]);
    } else {
        echo json_encode(["success" => false, "error" => "Upload failed!"]);
    }
}
?>
