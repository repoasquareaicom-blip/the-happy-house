<?php
// Set the API endpoint
$apiUrl = 'https://thehappyhouse.au/EmailAPI.php'; // Replace with your actual API URL

// API key (make sure this matches the key in API_KEYS.json)
$apiKey = 'e43fef2aa216ba9d4f2f1e8d33067ce004a6c92f1cc55e57613fc8e450bb3ab3'; // Replace with the actual key

// Data to send (recipient, subject, message)
$data = [
    'recipient' => 'mvk.venkatesan@gmail.com', // Replace with the recipient's email
    'subject' => 'Test Email from API',
    'message' => 'This is a test email sent from the mailtest.php script using the API.',
];

// Initialize cURL session
$ch = curl_init($apiUrl);

// Set cURL options
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // Return response as a string
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $apiKey, // Set the Authorization header
    'Content-Type: application/json', // Specify the content type as JSON
]);
curl_setopt($ch, CURLOPT_POST, true); // Set request method to POST
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data)); // Encode the data as JSON

// Execute the request and get the response
$response = curl_exec($ch);

// Check for cURL errors
if(curl_errno($ch)) {
    echo 'Error:' . curl_error($ch);
} else {
    // Display the API response
    echo "Response from API: " . $response;
}

// Close the cURL session
curl_close($ch);
?>
