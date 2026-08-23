<?php
// Generate a 32-byte random API key
$apiKey = bin2hex(random_bytes(32));
echo "Your API Key: " . $apiKey;
?>
