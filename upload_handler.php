<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['pdf_file'])) {
    $targetDir = "uploads/";
    
    // Ensure directory exists
    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0755, true);
    }

    $docType = $_POST['doc_type']; // tips, terms, or privacy
    $fileName = $docType . ".pdf"; // Names file based on dropdown selection
    $targetFile = $targetDir . $fileName;

    // Validate file type
    $fileType = strtolower(pathinfo($_FILES["pdf_file"]["name"], PATHINFO_EXTENSION));
    
    if ($fileType != "pdf") {
        http_response_code(400);
        echo "Only PDF files allowed.";
        exit;
    }

    if (move_uploaded_file($_FILES["pdf_file"]["tmp_name"], $targetFile)) {
        echo "Success";
    } else {
        http_response_code(500);
        echo "Error moving file.";
    }
}
?>