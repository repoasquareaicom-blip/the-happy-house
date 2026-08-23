<?php
include 'config/data.php';
$_data = new Data();

// Set internal error reporting
ini_set('display_errors', 1);
error_reporting(E_ALL);

if (isset($_POST['content'])) {
    $content = $_POST['content'];
    
    // LOG 1: Check if data arrived
    file_put_contents('debug.log', "[" . date('Y-m-d H:i:s') . "] Received Content: " . strlen($content) . " chars\n", FILE_APPEND);

    try {
        // We use saveAppSetting style logic (Prepared Statements) 
        // to avoid manual escaping and crashes.
        $sql = "UPDATE curriculum_content SET html_body = :content WHERE id = 1";
        
        // Use a direct PDO prepare/execute since your 'execute' method 
        // doesn't currently support parameter binding.
        $db = new Database();
        $conn = $db->getConnection();
        $stmt = $conn->prepare($sql);
        $success = $stmt->execute([':content' => $content]);

        if ($success) {
            file_put_contents('debug.log', "[" . date('Y-m-d H:i:s') . "] SQL Success: Row Updated\n", FILE_APPEND);
            echo "success";
        } else {
            $error = $_data->getLastError();
            file_put_contents('debug.log', "[" . date('Y-m-d H:i:s') . "] SQL Exec failed: $error\n", FILE_APPEND);
            echo "error";
        }
    } catch (Exception $e) {
        file_put_contents('debug.log', "[" . date('Y-m-d H:i:s') . "] Catch Error: " . $e->getMessage() . "\n", FILE_APPEND);
        echo "exception";
    }
    exit;
} else {
    file_put_contents('debug.log', "[" . date('Y-m-d H:i:s') . "] Error: No content key in POST\n", FILE_APPEND);
}
?>