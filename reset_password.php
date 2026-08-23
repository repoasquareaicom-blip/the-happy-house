<?php
session_start();
include 'config/data.php';
include 'class/log.php';
include 'class/subscription.php';
// dynamic css
include 'assets/css/pages/dynamicss.php';


$_data = new Data();
$subscription = new Subscription($_data);
$error_message = "";
$success_message = "";

// Check if the token is provided in the URL
if (!isset($_GET['token']) || empty($_GET['token'])) {
    die("Invalid or missing token.");
}

$token = $_GET['token'];
$is_token_valid = true;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if ($password !== $confirm_password) {
        $error_message = "Passwords do not match.";
    } else {
        try {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $school_admin_email = $subscription->validate_password_reset_token($token); // user_id = mail id

            if ($school_admin_email) {
                $result = $subscription->update_password_school_admin($school_admin_email,$hashed_password);
                $success_message = "Password reset successful. You can now <a href='school_admin_login.php'>log in</a>.";
            } else {
                $error_message = "Invalid or expired token. Click <a href='school_admin_login.php'>here</a> to login ";
                $is_token_valid = false;
            }
        } catch (Exception $e) {
            $error_message = "An error occurred: " . $e->getMessage();
        }
    }
}
else {
    // Pre-check the token validity before displaying the form
    try {
        $school_admin_email = $subscription->validate_password_reset_token($token);
        if (!$school_admin_email) {
            $is_token_valid = false;
            $error_message = "Invalid or expired token. Click <a href='school_admin_login.php'>here</a> to login ";
        
        }
    } catch (Exception $e) {
        $is_token_valid = false;
        $error_message = "An error occurred: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
    <link rel="stylesheet" href="assets/css/global.css">
    <link rel="stylesheet" href="assets/css/main.css">
    <link rel="stylesheet" href="assets/css/pages/login.css">
    <!-- Favicons -->
    <link rel="icon" href="./favicons/favicon.ico" sizes="any" />
    <link rel="icon" href="./favicons/icon.svg" type="image/svg+xml" />
    <link rel="apple-touch-icon" href="./favicons/apple-touch-icon.png" />
    <link rel="manifest" href="./favicons/manifest.webmanifest" />

</head>
<body>
    <section class="banner-background">
        <section class="banner">
            <img src="assets/images/banner-bg.svg" alt="Banner Background" class="banner-image" onclick="changeImage(this,'banner')" style="display: block;">
        </section>
    </section>



    <div id="login-container">
        <img src="assets/images/The-Happy-House-Logo.svg" alt="Logo" id="sub-page-logo">

        <div id="login-form-title">
            Reset Your Password
        </div>

       

        <form id="login-form" method="POST">
            <input type="password" name="password" placeholder="New Password" required  <?php echo !$is_token_valid ? 'disabled' : ''; ?>>
            <input type="password" name="confirm_password" placeholder="Confirm Password" required  <?php echo !$is_token_valid ? 'disabled' : ''; ?>>
            <button type="submit" id="reset-password-submit" class="logo-theme-button"   <?php echo !$is_token_valid ? 'disabled' : ''; ?>>Reset Password</button>
        </form>
        <?php if (!empty($error_message)) { ?>
            <div id="validation-message" style="color: red;"><?php echo $error_message; ?></div>
        <?php } ?>
        <?php if (!empty($success_message)) { ?>
            <div id="validation-message" style="color: green;"><?php echo $success_message; ?></div>
        <?php } ?>
    </div>

</body>
</html>
