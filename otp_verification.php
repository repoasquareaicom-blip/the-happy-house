<?php
session_start();
include 'config/data.php';
include 'class/mail_helper.php';
include 'class/subscription.php';

// dynamic css
include 'assets/css/pages/dynamicss.php';

$_data = new Data();
$subscription = new Subscription($_data);

// Redirect if they haven't come from the login page
if (!isset($_SESSION['temp_login_email'])) {
    header("Location: school_admin_login.php");
    exit();
}

$school_admin_email = $_SESSION['temp_login_email'];
$error_message = "";
$otp_expired = false;

// 1. Check if the latest OTP in the DB is still valid
$check_expiry_sql = "SELECT expiry_time FROM otp_log 
                     WHERE email = '$school_admin_email' 
                     AND is_used = 0 
                     ORDER BY created_at DESC LIMIT 1";

$expiry_data = $_data->getData($check_expiry_sql);

if (!empty($expiry_data) && isset($expiry_data[0]['expiry_time'])) {
    if (strtotime($expiry_data[0]['expiry_time']) < time()) {
        $otp_expired = true;
    }
} else {
    // If no unused record exists, treat as expired/invalid
    $otp_expired = true;
}

// 2. Resend OTP logic
if (isset($_POST['resend_otp'])) {
    try {
        $otp = rand(1000, 9999);
        $otp_expiry_seconds = 600;
        $expiry_datetime = date("Y-m-d H:i:s", time() + $otp_expiry_seconds);

        // Invalidate previous OTPs
        $_data->execute("UPDATE otp_log SET is_used = 1 WHERE email = '$school_admin_email'");
        
        // Insert new OTP record
        $insert_sql = "INSERT INTO otp_log (email, otp_code, expiry_time, is_used) 
                       VALUES ('$school_admin_email', '$otp', '$expiry_datetime', 0)";
        
        if ($_data->execute($insert_sql)) {
            // Fetch school name for email template
            $user_rows = $subscription->get_user_by_email($school_admin_email);
            if(!empty($user_rows)){
                $user_row = $user_rows[0];
                
                $emailHelper = new EmailHelper();
                $variables = [
                    'username' => $user_row['school_name'],
                    'otp' => $otp,
                    'expiry_time' => $otp_expiry_seconds / 60
                ];

                $message = $emailHelper->getEmailTemplate('template_school_admin_login_otp_message.tl', $variables);
                $emailHelper->sendEmail($school_admin_email, 'Your New OTP for Secure Login', $message);
                
                $error_message = "A new OTP has been sent to your email.";
                $otp_expired = false; 
            }
        }
    } catch (Exception $e) {
        $error_message = "Error resending OTP. Please try again.";
    }
}

// 3. Handle OTP submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['verify_otp'])) {
    $entered_otp = implode("", $_POST['otp'] ?? []);

    // Verify against database
    $verify_sql = "SELECT * FROM otp_log 
                  WHERE email = '$school_admin_email' 
                  AND otp_code = '$entered_otp' 
                  AND is_used = 0 
                  AND expiry_time > NOW() 
                  LIMIT 1";
    
    $otp_record = $_data->getData($verify_sql);

    if (!empty($otp_record)) {
        // Mark OTP as used
        $_data->execute("UPDATE otp_log SET is_used = 1 WHERE email = '$school_admin_email'");

        // Fetch user data to build the full session
        $user_rows = $subscription->get_user_by_email($school_admin_email);
        
        if (!empty($user_rows)) {
            $user_data = $user_rows[0];

            // Set Permanent Sessions for the Dashboard
            $_SESSION['school_admin_login_status'] = "true";
            $_SESSION['school_id'] = $user_data['id'];
            $_SESSION['school_name'] = $user_data['school_name'];
            $_SESSION['school_email'] = $user_data['school_admin_email'];
            
            // Product access flags from your schema
            $_SESSION['has_games'] = ($user_data['status'] === 'active');
            $_SESSION['has_curriculum'] = ($user_data['curriculum_status'] === 'active');
            $_SESSION['sub_type'] = $user_data['subscription_type'];
            $_SESSION['subscription_id'] = $user_data['subscription_id'];
            $_SESSION['curriculum_subscription_id'] = $user_data['curriculum_sub_idWhatsApp Audio 2026-03-18 at 11.11.39 AM'];

            // Clean up temporary login session
            unset($_SESSION['temp_login_email']);

            header("Location: school_admin_dashboard.php");
            exit();
        }
    } else {
        $error_message = "Invalid or expired OTP. Please try again.";
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

        function startTimer(durationInSeconds, display, resendButton) {
            let timer = durationInSeconds;
            const interval = setInterval(() => {
                const minutes = Math.floor(timer / 60);
                const seconds = timer % 60;
                display.textContent = `Resend OTP in ${minutes}:${seconds < 10 ? '0' : ''}${seconds}`;
                if (--timer < 0) {
                    clearInterval(interval);
                    display.textContent = '';
                    resendButton.style.display = 'block';
                }
            }, 1000);
        }

        document.addEventListener('DOMContentLoaded', () => {
            const timerDisplay = document.getElementById('otp-timer');
            const resendButton = document.getElementById('resend-otp');
            <?php if (!$otp_expired): ?>
                startTimer(180, timerDisplay, resendButton);
            <?php else: ?>
                resendButton.style.display = 'block';
            <?php endif; ?>
        });
    </script>
</head>
<body style="font-family: 'Calibri', sans-serif;">
    <section class="banner-background">
        <section class="happy-house-bg"></section>
    </section>

    <div id="login-container">
        <img src="assets/images/The-Happy-House-Logo.svg" alt="The Happy House Logo" id="sub-page-logo">

        <div id="login-form-title">OTP Verification</div>

        <?php if ($otp_expired && !isset($_POST['resend_otp'])) { ?>
            <div class="expired-message" style="text-align:center; color:red; margin-bottom:20px;">
                Your OTP has expired or is invalid.
            </div>
            <form method="POST">
                <button type="submit" name="resend_otp" class="logo-theme-button">Resend New OTP</button>
            </form>
        <?php } else { ?>
            <div id="otp-sent-message" style="margin-bottom: 15px; text-align:center;">
                OTP sent to <strong><?php echo htmlspecialchars($school_admin_email); ?></strong>.
            </div>

            <form id="login-form" method="POST">
                <div class="otp-container" style="display:flex; justify-content:center; gap:10px;">
                    <?php for ($i = 0; $i < 4; $i++) { ?>
                        <input type="text" name="otp[]" class="otp-box" 
                               maxlength="1" oninput="moveToNext(this)" 
                               onkeydown="handleBackspace(event, this)" 
                               style="width:40px; height:50px; text-align:center; font-size:24px;padding:0px!important"
                               oninput="this.value=this.value.replace(/[^0-9]/g,'')" required autofocus>
                    <?php } ?>
                </div>

                <div id="otp-timer" style="margin-top: 15px; text-align: center; color: #555;"></div>

                <button type="submit" name="verify_otp" id="otp-submit" class="logo-theme-button" style="margin-top: 20px;">
                    Verify OTP
                </button>

                <button type="submit" name="resend_otp" id="resend-otp" class="logo-theme-button" style="margin-top: 20px; background-color: #f39c12; display: none;">
                    Resend OTP
                </button>
                
                <?php if (!empty($error_message)) { ?>
                    <div id="error-message" style="margin-top: 15px; text-align:center; color:<?php echo (strpos($error_message, 'sent') !== false) ? 'green' : 'red'; ?>;">
                        <?php echo htmlspecialchars($error_message); ?>
                    </div>
                <?php } ?>
            </form>
        <?php } ?>
    </div>
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