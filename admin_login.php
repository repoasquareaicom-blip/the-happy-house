<?php
session_start();
include 'config/data.php';
include 'class/stripe.php';
include 'class/log.php';
include 'class/subscription.php';
include 'objects/schooldata.php';
// Include the EmailHelper class
require_once 'class/mail_helper.php';

// dynamic css
include 'assets/css/pages/dynamicss.php';

$_data = new Data();
$subscription = new Subscription($_data);

$admin_login_status = "";
$error_message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    $email = $_POST['email'];
    $password = $_POST['password'];

    try {
        $login_result = $subscription->get_admin_user_by_email($email);
        $hashed_password = "";
        if ($login_result) {
            foreach ($login_result as $_user) {
                $hashed_password = $_user['password'];
            }
            if (password_verify($password, $hashed_password)) 
            {
                foreach ($login_result as $_user) 
                {
                    $email = $_user['email_id'];
                    $name = $_user['name'];
                    $admin_login_status = "true";
                    $_SESSION['admin_login_status'] = "true";
                    $_SESSION['admin_name'] = $name;
                    $_SESSION['user_data'] = $login_result;
                    $_SESSION['login_page'] = 'admin_login.php';
                    $_SESSION['dashboard_page'] = 'admin_dashboard.php';
                    // Generate OTP for login
                    $otp_expiry_time_in_seconds = 600; // 10 minutes expiry time
                    $otp = rand(1000, 9999);
                    $_SESSION['otp'] = $otp;
                    $_SESSION['otp_time'] = time(); // Store the OTP creation time
                    $_SESSION['expiry_time'] = $otp_expiry_time_in_seconds;

                    // Send OTP to user's email
                    $emailHelper = new EmailHelper();
                    $recipient = $email;
                    $subject = 'Your OTP for Admin Login';
                    $templateFile = 'template_admin_login_message.tl'; // Path to the HTML template file
            
                    // Variables to replace in the template (if needed)
                    $variables = [
                        'username' =>  $_user['name'], 
                        'otp' =>  $otp,
                        'expiry_time' => $otp_expiry_time_in_seconds/60
                    ];

                    // Fetch the email message from the template
                    $message = $emailHelper->getEmailTemplate($templateFile, $variables);
                    
                
                    if (!$message) {
                        echo 'Template file not found or could not be read.';
                        exit;
                    }

                    // Send the email using the template content
                    $response = $emailHelper->sendEmail($recipient, $subject, $message);
                    
                    // Parse the JSON response
                    $responseData = json_decode($response['message'], true);
                    
                    if (json_last_error() === JSON_ERROR_NONE) {
                        $status = isset($responseData['status']) ? $responseData['status'] : 'No status available';
                        $message = isset($responseData['message']) ? $responseData['message'] : 'No message available';
                        if ($status == 'success') {
                            // Redirect to OTP verification page
                            header("Location: otp_verification_admin.php");
                            exit();
                        } else {
                            echo 'Failed to send email: ' . $message;
                        }
                    } else {
                        echo 'Failed to parse the API response JSON.';
                    }
                }
            }
            else
            {
                $validation_message = "Access Denied! Invalid username or password.";
            }
        
        } else {
            $validation_message = "Access Denied! Invalid username or password.";
        }

    } catch (Exception $e) {
        echo $e;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>The Happy House - Admin Login</title>
    <link rel="stylesheet" href="assets/css/global.css">
    <link rel="stylesheet" href="assets/css/main.css">
    <link rel="stylesheet" href="assets/css/pages/login.css">
    <script src="assets/scripts/script.js"></script>
    
    <!-- Favicons -->
    <link rel="icon" href="./favicons/favicon.ico" sizes="any" />
    <link rel="icon" href="./favicons/icon.svg" type="image/svg+xml" />
    <link rel="apple-touch-icon" href="./favicons/apple-touch-icon.png" />
    <link rel="manifest" href="./favicons/manifest.webmanifest" />

    <!-- Essential META Tags -->
    <meta property="og:title" content="The Happy House">
    <meta property="og:type" content="website" />
    <meta property="og:image" content="https://thehappyhouse.au/dev/assets/images/The-Happy-House-Logo.svg">
    <meta property="og:url" content="https://thehappyhouse.au">
</head>
<body>
    <!-- Banner Section -->
    <div class="dashboard-container">
        <!-- Header -->
	<section class="banner-background">
    <section class="happy-house-bg">
    
    </section>
   </section>
    
    <div id="login-container">
        <img src="assets/images/The-Happy-House-Logo.svg" width="190px" height="190px" alt="The Happy House Logo" id="sub-page-logo">
        
        <div id="login-form-title">
            Login to Your Admin Account
        </div>
        
        <?php if (isset($error_message)) { echo "<span id='validation-message'>$error_message</span>"; } ?>
        <form id="login-form" method="POST">
            <input type="email" name="email" placeholder="Email" required value="<?php echo $email ?? ''; ?>">
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit" id="login-submit" class="logo-theme-button">Submit</button>
            <?php if (!empty($validation_message)) { ?>
            <div id="validation-message"><?php echo htmlspecialchars($validation_message); ?></div>
            <?php } ?>
        </form>
                <!-- Add the Forgot Password and Sign Up links -->
                <div id="login-links" style="text-align:center">
            <p>
                <a href="admin_forgot_password.php">Forgot Password?</a> 
            </p>
            <!-- <p>
                Don't have an account? Sign up <a href="signup.php">here.</a>
            </p> -->
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

<style>
.happy-house-bg
{
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