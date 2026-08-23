<?php
session_start();
include 'config/data.php';
include 'class/log.php';
include 'class/subscription.php';
// Include the EmailHelper class
require_once 'class/mail_helper.php';

// dynamic css
include 'assets/css/pages/dynamicss.php';


$_data = new Data();
$subscription = new Subscription($_data);
$error_message = "";
$success_message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];

    try {
        $user_data = $subscription->get_admin_user_by_email($email);

        if ($user_data) {
            foreach ($user_data as $_user) {
                $user_name = $_user['name'];
            }

            // Generate a password reset token and its expiry time
            $reset_token = bin2hex(random_bytes(16));
            $reset_expiry = time() + (15 * 60); // 15 minutes from now

            // Save the token and expiry to the database
            $subscription->save_password_reset_token($email, $reset_token, $reset_expiry,'A');

            // Initialize the EmailHelper class
            $emailHelper = new EmailHelper();

            // Define the recipient and subject
            $recipient = $email;
            $subject = 'Admin Password Reset Request';

            // Define the template file
            $templateFile = 'template_admin_password_reset_mail_message.tl';

            // Variables to replace in the template
            $variables = [
                'username' => $user_name,
                'reset_link' => "https://thehappyhouse.au/reset_password_admin.php?token=$reset_token",
                'expiry_time' => 15
            ];

            // Fetch the email message from the template
            $message = $emailHelper->getEmailTemplate($templateFile, $variables);

            // Send the email
            $response = $emailHelper->sendEmail($recipient, $subject, $message);
            $responseData = json_decode($response['message'], true);

            if ($responseData['status'] == 'success') {
                $success_message = "A password reset link has been sent to your email.";
            } else {
                $error_message = "Failed to send the reset email. Please try again.";
            }
        } else {
            $error_message = "No user found with this email!";
        }
    } catch (Exception $e) {
        $error_message = "An error occurred. Please try again later.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password</title>
    <link rel="stylesheet" href="assets/css/global.css">
    <link rel="stylesheet" href="assets/css/main.css">
    <link rel="stylesheet" href="assets/css/pages/login.css">
    <!-- Favicons -->
    <link rel="icon" href="./favicons/favicon.ico" sizes="any" />
    <link rel="icon" href="./favicons/icon.svg" type="image/svg+xml" />
    <link rel="apple-touch-icon" href="./favicons/apple-touch-icon.png" />
    <link rel="manifest" href="./favicons/manifest.webmanifest" />

    <script src="assets/scripts/script.js"></script>
</head>
<body>
    <section class="banner-background">
        <section class="banner">
            <img src="assets/images/banner-bg.svg" alt="Banner Background" class="banner-image" onclick="changeImage(this,'banner')" style="display: block;">
        </section>
    </section>


    <div id="login-container">
        <img src="assets/images/The-Happy-House-Logo.svg" alt="The Happy House Logo" id="sub-page-logo">

        <div id="login-form-title">
            Forgot Password - Admin
        </div>

     
        <div id="forgot-password-message" style="text-align:center; margin-bottom: 15px; color: #555; font-size: 14px;">
            Please enter your registered email ID to reset your password.
        </div>
        <form id="login-form" method="POST">
            <input type="email" name="email" placeholder="Enter your email address" required>
            <button type="submit" id="forgot-password-submit" class="logo-theme-button">Send Reset Link</button>
        </form>
        <?php if (!empty($error_message)) { ?>
        <div id="validation-message" style="color: red;"><?php echo htmlspecialchars($error_message); ?></div>
        <?php } ?>
        <?php if (!empty($success_message)) { ?>
        <div id="validation-message" style="color: green;"><?php echo htmlspecialchars($success_message); ?></div>
        <?php } ?>
        <div id="login-links" style="text-align:center">
            <p>
                Remember your password? <a href="school_admin_login.php">Login</a>
            </p>
        </div>
    </div>

  
  <!-- Loading Spinner -->
  <div id="loading-spinner-control" style="display: none;">
        <div class="spinner-control"></div>
    </div>
</body>
<script>
    document.getElementById('login-form').addEventListener('submit', function(event) {
    // Show the loading spinner
    document.getElementById('loading-spinner-control').style.display = 'flex';
    
    // Optionally, you can disable the submit button to prevent multiple submissions
    document.getElementById('login-submit').disabled = true;
});
</script>
</html>

