<?php
session_start();

include 'config/data.php';
include 'class/log.php';
include 'class/subscription.php';
include 'objects/schooldata.php';
require_once 'class/mail_helper.php';

// dynamic css
include 'assets/css/pages/dynamicss.php';

$_data = new Data();
$subscription = new Subscription($_data);
$validation_message = "";
$school_admin_email = ""; // Initialize to avoid notice

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $school_admin_email = $_POST['school_admin_email'];
    $password = $_POST['password'];
    
    try {
        $user_data = $subscription->get_user_by_email($school_admin_email);

        if ($user_data && count($user_data) > 0) {
            // Since get_user_by_email returns an array, grab the first record
            $_user = $user_data[0];
            $hashed_password = $_user['password'];

            if (password_verify($password, $hashed_password)) {
                
                // 1. Generate OTP Details
                $otp = rand(1000, 9999);
                $otp_expiry_time_in_seconds = 600; // 10 minutes
                $expiry_datetime = date("Y-m-d H:i:s", time() + $otp_expiry_time_in_seconds);

                // 2. Clear old OTPs for this email (Security best practice)
                $clear_sql = "UPDATE otp_log SET is_used = 1 WHERE email = '$school_admin_email' AND is_used = 0";
                $_data->execute($clear_sql);

                // 3. Log NEW OTP to the database
                $insert_otp_sql = "INSERT INTO otp_log (email, otp_code, expiry_time, is_used) 
                                   VALUES ('$school_admin_email', '$otp', '$expiry_datetime', 0)";
                
                if ($_data->execute($insert_otp_sql)) {
                    
                    // 4. Prepare temporary session for verification page
                    $_SESSION['temp_login_email'] = $school_admin_email;
                    // We don't log them in fully yet; we wait for OTP success

                    // 5. Send OTP via Email
                    $emailHelper = new EmailHelper();
                    $recipient = $school_admin_email;
                    $subject = 'Your OTP for Secure Login';
                    $templateFile = 'template_school_admin_login_otp_message.tl';

                    $variables = [
                        'username' => $_user['school_name'], 
                        'otp' => $otp,
                        'expiry_time' => $otp_expiry_time_in_seconds / 60
                    ];

                    $message = $emailHelper->getEmailTemplate($templateFile, $variables);
                    
                    if ($message) {
                        $response = $emailHelper->sendEmail($recipient, $subject, $message, 'School Login');
                        $responseData = json_decode($response['message'], true);
                        
                        if (isset($responseData['status']) && $responseData['status'] == 'success') {
                            header("Location: otp_verification.php");
                            exit();
                        } else {
                            $validation_message = "OTP generated, but email failed to send. Please contact support.";
                        }
                    } else {
                        $validation_message = "Email template missing.";
                    }
                } else {
                    $validation_message = "Error generating secure login token.";
                }

            } else {
                $validation_message = "Incorrect password!";
            }
        } else {
            $validation_message = "No user found with this email!";
        }

    } catch (Exception $e) {
        // Log the actual error but show a clean message to the user
        error_log($e->getMessage());
        $validation_message = "A system error occurred. Please try again later.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>School Admin Login</title>
    <link rel="stylesheet" href="assets/css/global.css">
    <link rel="stylesheet" href="assets/css/main.css">
    <link rel="stylesheet" href="assets/css/pages/login.css">

    <!-- Favicons -->
    <link rel="icon" href="./favicons/favicon.ico" sizes="any" />
    <link rel="icon" href="./favicons/icon.svg" type="image/svg+xml" />
    <link rel="apple-touch-icon" href="./favicons/apple-touch-icon.png" />
    <link rel="manifest" href="./favicons/manifest.webmanifest" />

    <meta property="og:title" content="The Happy House ">
    <meta property="og:type" content="website" />
    <meta property="og:image" content="https://thehappyhouse.au/dev/assets/images/The-Happy-House-Logo.svg">
    <meta property="og:url" content="https://thehappyhouse.au">
</head>
<body>
    <section class="banner-background">
        <section class="happy-house-bg"></section>
    </section>

    <div id="login-container">
        <img src="assets/images/The-Happy-House-Logo.svg" alt="The Happy House Logo" id="sub-page-logo">

        <div id="login-form-title">
            Login to Your School Account
        </div>

        <form id="login-form" method="POST">
            <input type="email" name="school_admin_email" placeholder="School Admin Email" required value="<?php echo htmlspecialchars($school_admin_email); ?>">
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit" id="login-submit" class="logo-theme-button">Submit</button>
            
            <?php if (!empty($validation_message)) { ?>
                <div id="validation-message" style="color: red; margin-top: 10px; font-weight: bold;">
                    <?php echo htmlspecialchars($validation_message); ?>
                </div>
            <?php } ?>
        </form>

        <div id="login-links" style="text-align:center">
            <p><a href="school_admin_forgot_password.php">Forgot Password?</a></p>
        </div>
    </div>

    <!-- Loading Spinner -->
    <div id="loading-spinner-control" style="display: none;">
        <div class="spinner-control"></div>
    </div>

    <script>
        document.getElementById('login-form').addEventListener('submit', function(event) {
            document.getElementById('loading-spinner-control').style.display = 'flex';
            document.getElementById('login-submit').disabled = true;
        });
    </script>
</body>
</html>
<style>
.happy-house-bg {
    position: relative;
    width: 100%;
    height: 100vh;
    display: flex;
    justify-content: center;
    align-items: center;
    overflow: hidden;
    background-image : url("assets/images/the-happy-house-cover.jpg");
    background-size:cover;
}
</style>