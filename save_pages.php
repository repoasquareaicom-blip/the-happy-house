<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $filename = $_POST['filename'];
    $content = $_POST['content'];

    // Whitelist the allowed files for security
    $allowed = ['helpful-tips.html', 'terms-of-use.html', 'privacy-policy.html'];
    
    if (in_array($filename, $allowed)) {
        // Clean the content if necessary or save as-is for HTML
        if (file_put_contents($filename, $content) !== false) {
            echo "success";
        } else {
            http_response_code(500);
            echo "Failed to write to file.";
        }
    } else {
        http_response_code(403);
        echo "Unauthorized file access.";
    }
}
?>