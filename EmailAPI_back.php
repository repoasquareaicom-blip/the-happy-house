<?php

// Function to load API keys from a JSON file
function getApiKeyFromJson() {
    $jsonFile = 'API_KEYS.json'; // Path to the JSON file

    // Check if the JSON file exists
    if (!file_exists($jsonFile)) {
        die("API keys file not found.");
    }

    // Get content from JSON file
    $jsonContent = file_get_contents($jsonFile);

    // Decode the JSON content to an associative array
    $data = json_decode($jsonContent, true);

    // Check if 'mail_key' exists in the JSON data
    if (isset($data['mail_key'])) {
        return $data['mail_key']; // Return the mail_key value
    } else {
        die("API key not found in the JSON file.");
    }
}

// Log function to record email sending activity
function logEmail($status, $recipient, $subject, $message, $error = null) {
    $logEntry = sprintf(
        "[%s] | IP: %s | Status: %s | Recipient: %s | Subject: %s | Message Length: %d",
        date('Y-m-d H:i:s'),
        $_SERVER['REMOTE_ADDR'] ?? 'Unknown',
        $status,
        $recipient,
        $subject,
        strlen($message) // Log message length, not full message to avoid sensitive info
    );
    if ($error) {
        $logEntry .= " | Error: $error";
    }
    $logEntry .= PHP_EOL;
    file_put_contents('mail_log.txt', $logEntry, FILE_APPEND);
}

// Set response headers for security
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Authorization, Content-Type');

// Handle POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve the API key from the JSON file
    $apiKey = getApiKeyFromJson();

    // Check API key in headers
    $headers = getallheaders();
    if (empty($headers['Authorization']) || $headers['Authorization'] !== 'Bearer ' . $apiKey) {
        $response = [
            'status' => 'error',
            'message' => 'Unauthorized access. Invalid API key.',
        ];
        echo json_encode($response);
        exit;
    }

    // Read JSON input
    $input = json_decode(file_get_contents('php://input'), true);

    // Validate required fields
    if (empty($input['recipient']) || empty($input['subject']) || empty($input['message'])) {
        $response = [
            'status' => 'error',
            'message' => 'Missing required fields: recipient, subject, or message.',
        ];
        logEmail('error', 'N/A', 'N/A', 'N/A', $response['message']);
        echo json_encode($response);
        exit;
    }

    // Extract and validate input values
    $to = filter_var($input['recipient'], FILTER_VALIDATE_EMAIL);
    if (!$to) {
        $response = [
            'status' => 'error',
            'message' => 'Invalid recipient email format.',
        ];
        logEmail('error', $input['recipient'], $input['subject'], $input['message'], $response['message']);
        echo json_encode($response);
        exit;
    }

    $subject = strip_tags($input['subject']); // Clean subject from malicious code
    $message = htmlspecialchars($input['message']); // Prevent XSS attacks by encoding special characters
    // Decode HTML entities to ensure proper rendering in email
    $message = html_entity_decode($message, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    // Prepare the headers for HTML email
    $headers = 'From: The Happy House <noreply@thehappyhouse.au>' . "\r\n" .
               'X-Mailer: PHP/' . phpversion() . "\r\n" .
               'Content-Type: text/html; charset=UTF-8' . "\r\n";

    // Send the email
    if (mail($to, $subject, $message, $headers)) {
        $response = [
            'status' => 'success',
            'message' => 'Email sent successfully.',
        ];
        logEmail('success', $to, $subject, $message);
    } else {
        $response = [
            'status' => 'error',
            'message' => 'Failed to send email.',
        ];
        logEmail('error', $to, $subject, $message, $response['message']);
    }

    // Send response
    echo json_encode($response);
} else {
    // Handle invalid request method
    $response = [
        'status' => 'error',
        'message' => 'Invalid request method. Please use POST.',
    ];
    echo json_encode($response);
}
?>
