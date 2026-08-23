<?php
session_start();
include 'config/data.php';
include 'class/mail_helper.php';

// dynamic css
include 'assets/css/pages/dynamicss.php';


if (!isset($_SESSION['otp']) || !isset($_SESSION['user_data'])) {
    header("Location: " . $_SESSION['login_page']);
    exit();
}

// Check if the OTP has expired
$otp_expired = false;
if (isset($_SESSION['otp_time']) && isset($_SESSION['expiry_time'])) {
    $current_time = time();
    $otp_age = ($current_time - $_SESSION['otp_time']); // Age in seconds

    if ($otp_age > $_SESSION['expiry_time']) {
        $otp_expired = true;
        unset($_SESSION['otp'], $_SESSION['otp_time'], $_SESSION['user_data']);
    }
}

// Get the user's email for the OTP sent message
$school_admin_email = "";
if (!empty($_SESSION['user_data'])) {
    $school_admin_email = $_SESSION['user_data'][0]['email_id'] ?? '';
}

$error_message = "";

// Resend OTP logic
if (isset($_POST['resend_otp'])) {
    try {
        $otp_expiry_time_in_seconds = 600; // New expiry time
        $otp = rand(1000, 9999); // Generate a new OTP

        $_SESSION['otp'] = $otp;
        $_SESSION['otp_time'] = time();
        $_SESSION['expiry_time'] = $otp_expiry_time_in_seconds;

        // Send OTP via email
        $emailHelper = new EmailHelper();
        $recipient = $school_admin_email;
        $subject = 'Your New OTP for Secure Login';
        $templateFile = 'template_admin_login_message.tl';
        $variables = [
            'username' => $_SESSION['user_data'][0]['name'],
            'otp' => $otp,
            'expiry_time' => $otp_expiry_time_in_seconds / 60
        ];

        $message = $emailHelper->getEmailTemplate($templateFile, $variables);
        if (!$message) {
            $error_message = 'Template file not found or could not be read.';
        } else {
            $response = $emailHelper->sendEmail($recipient, $subject, $message);
            $responseData = json_decode($response['message'], true);

            if (json_last_error() === JSON_ERROR_NONE && isset($responseData['status']) && $responseData['status'] == 'success') {
                $error_message = "A new OTP has been sent to your email.";
            } else {
                $error_message = "Failed to resend OTP. Please try again later.";
            }
        }
    } catch (Exception $e) {
        $error_message = "An error occurred: " . $e->getMessage();
    }
}

// Handle OTP submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && !$otp_expired && isset($_POST['verify_otp'])) {
    $entered_otp = implode("", $_POST['otp'] ?? []); // Combine digits into a string
    if ($entered_otp == $_SESSION['otp']) {
        // OTP validated successfully
        $_SESSION['otp_verified'] = true;

        // Set session values from user data
        if (!empty($_SESSION['user_data'])) {
            $user_data = $_SESSION['user_data'][0];
            $_SESSION['admin_login_status'] = "true";
            $_SESSION['admin_name'] = $user_data['name'];
        }

        unset($_SESSION['otp'], $_SESSION['otp_time']); // Clear OTP after validation
        header("Location: " . $_SESSION['dashboard_page']);
        exit();
    } else {
        $error_message = "Invalid OTP. Please try again.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OTP Verification</title>
    <link rel="stylesheet" href="assets/css/global.css">
    <link rel="stylesheet" href="assets/css/main.css">
    <link rel="stylesheet" href="assets/css/pages/login.css">
    <link rel="stylesheet" href="assets/css/pages/otp_verification.css">
    <!-- Favicons -->
    <link rel="icon" href="./favicons/favicon.ico" sizes="any" />
    <link rel="icon" href="./favicons/icon.svg" type="image/svg+xml" />
    <link rel="apple-touch-icon" href="./favicons/apple-touch-icon.png" />
    <link rel="manifest" href="./favicons/manifest.webmanifest" />


    <script>
        function moveToNext(current) {
            const next = current.nextElementSibling;
            if (current.value.length === 1 && next && next.classList.contains('otp-box')) {
                next.focus();
            }
        }

        function handleBackspace(event, current) {
            if (event.key === "Backspace" && !current.value) {
                const prev = current.previousElementSibling;
                if (prev && prev.classList.contains('otp-box')) {
                    prev.focus();
                }
            }
        }

        // Countdown Timer Logic
        function startTimer(durationInSeconds, display, resendButton) {
            let timer = durationInSeconds;
            const interval = setInterval(() => {
                const minutes = Math.floor(timer / 60);
                const seconds = timer % 60;
                display.textContent = `Resend OTP in ${minutes}:${seconds < 10 ? '0' : ''}${seconds}`;
                if (--timer < 0) {
                    clearInterval(interval);
                    display.textContent = ''; // Clear the timer message
                    resendButton.style.display = 'block'; // Show the Resend OTP button
                }
            }, 1000);
        }

        document.addEventListener('DOMContentLoaded', () => {
            const timerDisplay = document.getElementById('otp-timer');
            const resendButton = document.getElementById('resend-otp');
            resendButton.style.display = 'none'; // Hide Resend OTP button initially

            // Start a 3-minute timer (180 seconds)
            startTimer(180, timerDisplay, resendButton);
        });
    </script>
</head>
<body>
<div class="dashboard-container">
    <section class="banner-background">
        <section class="happy-house-bg">
            
        </section>
    </section>

    <div id="login-container">
        <img src="assets/images/The-Happy-House-Logo.svg" alt="The Happy House Logo" id="sub-page-logo">

        <div id="login-form-title">
            OTP Verification
        </div>

        <?php if ($otp_expired) { ?>
            <div class="expired-message">
                OTP has expired. Please <a href="<?php echo isset($_SESSION['login_page']) ? $_SESSION['login_page'] : 'login.php'; ?>">log in again</a>.
            </div>
        <?php } else { ?>
            <?php if ($school_admin_email) { ?>
                <div id="otp-sent-message" style="margin-bottom: 15px; text-align:center;">
                    OTP has been sent to <strong><?php echo htmlspecialchars($school_admin_email); ?></strong>.
                </div>
            <?php } ?>

            <form id="login-form" method="POST">
                <div class="otp-container">
                    <?php for ($i = 0; $i < 4; $i++) { ?>
                        <input type="text" name="otp[]" class="otp-box" 
                               maxlength="1" oninput="moveToNext(this)" 
                               onkeydown="handleBackspace(event, this)" 
                               oninput="this.value=this.value.replace(/[^0-9]/g,'')" autofocus>
                    <?php } ?>
                </div>

                <!-- Timer Display -->
                <div id="otp-timer" style="margin-top: 15px; text-align: center; color: #555;"></div>

                <button type="submit" name="verify_otp" id="otp-submit" class="logo-theme-button" style="margin-top: 20px;">
                    Verify OTP
                </button>

                <button type="submit" name="resend_otp" id="resend-otp" class="logo-theme-button" style="margin-top: 20px; background-color: #f39c12; display: none;">
                    Resend OTP
                </button>

                <?php if (!empty($error_message)) { ?>
                    <div id="error-message" style="margin-top: 10px; text-align:center; color:red;">
                        <?php echo htmlspecialchars($error_message); ?>
                    </div>
                <?php } ?>
            </form>
        <?php } ?>
    </div>
</div>    
</body>
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