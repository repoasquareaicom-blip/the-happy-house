<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Import PHPMailer classes
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

// Create a new PHPMailer instance
$mail = new PHPMailer(true);

try {
    // Server settings
    $mail->isSMTP(); // Use SMTP
    $mail->Host = 'localhost'; // GoDaddy shared hosting uses localhost for SMTP
    $mail->SMTPAuth = false; // No authentication required for GoDaddy
    $mail->Port = 25; // GoDaddy's SMTP port

    // Sender and recipient
    $mail->setFrom('noreply@thehappyhouse.au', 'The Happy House');
    $mail->addAddress('mvk.venkatesan@gmail.com', 'Recipient Name');

    // Email content
    $mail->isHTML(true);
    $mail->Subject = 'Test Email with Embedded Image';

    // Attach the image and get its Content-ID
    $imagePath = 'assets/images/logo.png'; // Path to your image file
    $cid = 'happyhouselogo'; // Unique Content-ID for the image
    $mail->addEmbeddedImage($imagePath, $cid, 'The-Happy-House-Logo.svg');

    // Email body with embedded image
    $emailBody = "
        <h1>Hello!</h1>
        <p>This is a test email from The Happy House. image tag removed</p>
        <img src='cid:$cid' alt='The Happy House Logo' style='width: 100px; height: auto;'>
        <p>Thank you!</p>
        
    ";
    $mail->Body = $emailBody;

    // Send the email
    $mail->send();
    echo 'Email sent successfully!';
} catch (Exception $e) {
    echo "Error: {$mail->ErrorInfo}";
}
?>