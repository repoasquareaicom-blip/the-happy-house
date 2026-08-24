<?php
session_start();

include 'config/data.php';
include 'class/log.php';
include 'class/subscription.php';
include 'objects/schooldata.php';

// dynamic css
include 'assets/css/pages/dynamicss.php';

$_data = new Data();
$subscription = new Subscription($_data);
$validation_message = "";
$school_admin_email = ""; // Initialize to avoid notice

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $school_admin_email = trim($_POST['school_admin_email']);
    $password = $_POST['password'];
    
    try {
        $user_data = $subscription->get_user_by_email($school_admin_email);

        if ($user_data && count($user_data) > 0) {
            $_user = $user_data[0];
            $hashed_password = $_user['password'];
            $school_id = (int)$_user['id'];
            $active_lock = $_data->getData("SELECT id FROM school_master WHERE id = $school_id AND login_locked_until > NOW() LIMIT 1");

            if (!empty($active_lock)) {
                $validation_message = "Your account has been temporarily locked due to multiple unsuccessful login attempts.\n\nPlease try again after the lock period or contact Youth Dimension on (03) 9844 1944.";
            } elseif (password_verify($password, $hashed_password)) {
                $_data->execute("UPDATE school_master SET failed_login_attempts = 0, first_failed_login_at = NULL, login_locked_until = NULL WHERE id = $school_id");

                $_SESSION['school_admin_login_status'] = "true";
                $_SESSION['school_id'] = $_user['id'];
                $_SESSION['school_name'] = $_user['school_name'];
                $_SESSION['school_email'] = $_user['school_admin_email'];
                $_SESSION['has_games'] = ($_user['status'] === 'active');
                $_SESSION['has_curriculum'] = ($_user['curriculum_status'] === 'active');
                $_SESSION['sub_type'] = $_user['subscription_type'];
                $_SESSION['subscription_id'] = $_user['subscription_id'];
                $_SESSION['curriculum_subscription_id'] = $_user['curriculum_sub_id'];

                header("Location: school_admin_dashboard.php");
                exit();
            } else {
                $_data->execute("UPDATE school_master SET
                    login_locked_until = CASE
                        WHEN failed_login_attempts >= 3
                        AND first_failed_login_at IS NOT NULL
                        AND NOW() <= DATE_ADD(first_failed_login_at, INTERVAL 5 MINUTE)
                        THEN DATE_ADD(NOW(), INTERVAL 24 HOUR)
                        ELSE NULL
                    END,
                    failed_login_attempts = CASE
                        WHEN failed_login_attempts >= 1
                        AND first_failed_login_at IS NOT NULL
                        AND NOW() <= DATE_ADD(first_failed_login_at, INTERVAL 5 MINUTE)
                        THEN LEAST(failed_login_attempts + 1, 4)
                        ELSE 1
                    END,
                    first_failed_login_at = CASE
                        WHEN failed_login_attempts >= 1
                        AND first_failed_login_at IS NOT NULL
                        AND NOW() <= DATE_ADD(first_failed_login_at, INTERVAL 5 MINUTE)
                        THEN first_failed_login_at
                        ELSE NOW()
                    END
                    WHERE id = $school_id");

                $attempt_status = $_data->getData("SELECT failed_login_attempts, IF(login_locked_until > NOW(), 1, 0) AS is_locked FROM school_master WHERE id = $school_id LIMIT 1");
                $is_locked = !empty($attempt_status) && (int)$attempt_status[0]['is_locked'] === 1;

                if ($is_locked) {
                    $validation_message = "Your account has been temporarily locked for 24 hours due to multiple unsuccessful login attempts. Please contact Youth Dimension on (03) 9844 1944 if you need assistance.";
                } else {
                    $attempts = !empty($attempt_status) ? (int)$attempt_status[0]['failed_login_attempts'] : 1;
                    $remaining_attempts = max(1, 4 - $attempts);
                    $attempt_word = ($remaining_attempts === 1) ? 'attempt' : 'attempts';
                    $validation_message = "Incorrect email or password. $remaining_attempts $attempt_word remaining.";
                }
            }
        } else {
            $validation_message = "Incorrect email or password.";
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
                <div id="validation-message" style="color: red; margin-top: 10px; font-weight: bold; white-space: pre-line;">
                    <?php echo htmlspecialchars($validation_message); ?>
                </div>
            <?php } ?>
        </form>

        <div id="login-links" style="text-align:center">
            <p><a href="school_admin_forgot_password.php">Forgot Password?</a></p>
            <p class="login-support-message">Having problems logging in?<br>Please call Youth Dimension on (03) 9844 1944</p>
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
.login-support-message {
    color: #666;
    font-size: 0.9rem;
    line-height: 1.45;
    margin-top: 12px;
}
</style>
