<?php
include 'config/data.php';
include 'class/stripe.php';
include 'class/log.php';
include 'class/subscription.php';
include 'objects/schooldata.php';
include 'assets/css/pages/dynamicss.php';

$_data = new Data();
$subscription = new Subscription($_data);

// 1. Get SESSION ID from URL
$session_id = isset($_GET['s']) ? $_GET['s'] : '';
$type = isset($_GET['type']) ? $_GET['type'] : 'wellbeing_games';

if (empty($session_id)) {
    die("Invalid request. Session ID is missing.");
}

try {
    // 2. Look up the record in the TEMP table using the Session ID
    $query = "SELECT * FROM temp_school_master WHERE stripe_session_id = '$session_id' LIMIT 1";
    $temp_data = $_data->getData($query);

    if (empty($temp_data)) {
        throw new Exception("Session not found. Please ensure payment was successful.");
    }

    $temp_record = $temp_data[0];
    
    // Check if it's already paid
    if ($temp_record['payment_status'] !== 'paid') {
         throw new Exception("Payment has not been confirmed yet. Please refresh in a moment.");
    }

} catch (Exception $e) {
    echo '<script>alert("' . htmlspecialchars($e->getMessage()) . '");</script>';
    exit();
}

$validationMessage = "";

// 3. Extract data from temp record (populated by Webhook)
$email_to_use = $temp_record['email'];
$stripe_sub_id = $temp_record['stripe_subscription_id']; 
$start_dt = $temp_record['start_date'];
$end_dt = $temp_record['end_date'];
$customer_id = $temp_record['customer_id'];

// Check if user already exists in permanent school_master
$existing_query = "SELECT * FROM school_master WHERE school_admin_email = '$email_to_use' LIMIT 1";
$existing_user_results = $_data->getData($existing_query);
$is_existing_user = !empty($existing_user_results);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $schoolname = $_POST['schoolName'];
    $schoolemail = $_POST['schoolAdminEmail']; 
    
    if ($is_existing_user) {
        $hashed_password = $existing_user_results[0]['password'];
        
        // --- Subscription Type Logic for Existing Users ---
        $current_db_type = $existing_user_results[0]['subscription_type'];
        
        // If current is 'wellbeing_games' and buying 'curriculum', or vice versa, set to 'both'
        if (($current_db_type == 'wellbeing_games' && $type == 'curriculum') || 
            ($current_db_type == 'curriculum' && $type == 'wellbeing_games')) {
            $final_sub_type = 'both';
        } else {
            // Otherwise, keep current or update to current purchase if it was empty
            $final_sub_type = !empty($current_db_type) ? $current_db_type : $type;
        }
    } else {
        $password = $_POST['password'];
        $retypePassword = $_POST['retypePassword'];
        $final_sub_type = $type; // New user: type is simply what they just bought

        if ($password !== $retypePassword) {
            $validationMessage = "Passwords do not match.";
        } elseif (!preg_match('/^(?=.*[A-Z])(?=.*\d)(?=.*[!@#$%^&*])[A-Za-z\d!@#$%^&*]{8,}$/', $password)) {
            $validationMessage = "Password must be at least 8 chars with Uppercase, Number, and Special Char.";
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        }
    }

    if (empty($validationMessage)) {
        try {
            // Determine columns based on table schema for specific products
            if ($type == 'curriculum') {
                $sub_col = 'curriculum_sub_id';
                $stat_col = 'curriculum_status';
                $start_col = 'curriculum_start';
                $end_col = 'curriculum_end';
            } else {
                $sub_col = 'subscription_id';
                $stat_col = 'status';
                $start_col = 'subscription_start';
                $end_col = 'subscription_end';
            }

            if ($is_existing_user) {
                // UPDATE - Including subscription_type upgrade
                $sql = "UPDATE school_master SET 
                        school_name = '$schoolname', 
                        name = '$schoolname',
                        password = '$hashed_password', 
                        
                        $sub_col = '$stripe_sub_id',
                        $stat_col = 'active',
                        $start_col = '$start_dt',
                        $end_col = '$end_dt',
                        subscription_type = '$final_sub_type',
                        subscription_status_id = 1,
                        modified_on = NOW()
                        WHERE school_admin_email = '$schoolemail'";
            } else {
                // INSERT - Including initial subscription_type
                $sql = "INSERT INTO school_master 
                        (school_name, name, school_admin_email, email, password, 
                         $sub_col, $stat_col, $start_col, $end_col, 
                         subscription_status_id, created_on, subscription_type,customer_id) 
                        VALUES 
                        ('$schoolname', '$schoolname', '$schoolemail', '$schoolemail', '$hashed_password', 
                         '$stripe_sub_id', 'active', '$start_dt', '$end_dt', 
                         1, NOW(), '$final_sub_type','$customer_id' )";
            }

            if ($_data->execute($sql)) {
                echo '<form id="successForm" action="message.php" method="post"><input type="hidden" name="error_number" value="2001"></form>';
                echo '<script>document.getElementById("successForm").submit();</script>';
                exit();
            } else {
                // Access the error through the new public method
                $dbError = $_data->getLastError(); 

                $logMsg = "\n--- SIGNUP ERROR " . date('Y-m-d H:i:s') . " ---\n";
                $logMsg .= "Query: " . $sql . "\n";
                $logMsg .= "Error: " . $dbError . "\n";

                file_put_contents('logs/db_errors.log', $logMsg, FILE_APPEND);
                $validationMessage = "Database error: Could not save account.";
        }
        } catch (Exception $e) {
            $validationMessage = "Error: " . htmlspecialchars($e->getMessage());
        }
    }
} else {
    $schoolemail = htmlspecialchars($email_to_use, ENT_QUOTES, 'UTF-8');
    $schoolname = $is_existing_user ? htmlspecialchars($existing_user_results[0]['school_name'], ENT_QUOTES, 'UTF-8') : "";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Signup - The Happy House</title>
    <link rel="stylesheet" href="assets/css/global.css">
    <link rel="stylesheet" href="assets/css/main.css">
    <link rel="stylesheet" href="assets/css/pages/signup.css">
    <style>
        input[readonly] { background-color: #f0f0f0; cursor: not-allowed; border: 1px solid #ccc; }
        .existing-user-note { background-color: #e7f3ff; padding: 10px; border-radius: 5px; margin-bottom: 15px; font-size: 0.9em; color: #0056b3; border: 1px solid #b8daff; }
        .signup-help-message { margin: 18px 0 0; color: #666; font-size: 0.9em; line-height: 1.45; text-align: center; }
    </style>
</head>
<body>
    <section class="banner-background">
        <section class="banner">
            <img src="assets/images/banner-bg.svg" alt="Banner Background" class="banner-image">
        </section>
    </section>

    <section id="signup-section" class="section">
        <div id="signup-container">
            <img src="assets/images/The-Happy-House-Logo.svg" alt="The Happy House Logo" id="sub-page-logo">
            <p id="thank-you-message">Thank you for subscribing! Please complete your <strong><?php echo ($type == 'curriculum') ? 'Curriculum' : 'Wellbeing Games'; ?></strong> account setup.</p>
            
            <div id="signup-form-title">
                <?php echo $is_existing_user ? 'Confirm School Details' : 'Create Your School Account'; ?>
            </div>

            <?php if ($is_existing_user): ?>
                <div class="existing-user-note">
                    <strong>Welcome back!</strong> We found an account for this email. Please confirm your school name to link this subscription.
                </div>
            <?php endif; ?>

            <form id="signup-form" method="POST">
                <input type="text" name="schoolName" placeholder="School Name" required value="<?php echo $schoolname ?>">
                <input type="email" name="schoolAdminEmail" placeholder="Email" required readonly value="<?php echo $schoolemail; ?>">
                
                <?php if (!$is_existing_user): ?>
                    <input type="password" name="password" placeholder="Password" required>
                    <input type="password" name="retypePassword" placeholder="Retype Password" required>
                <?php else: ?>
                    <p style="font-size: 0.85em; color: #666; margin-top: -10px; margin-bottom: 20px;">* You will use your existing account password.</p>
                <?php endif; ?>

                <span id="validation-message" style="color:red;"><?php echo $validationMessage; ?></span>
                
                <button type="submit" id="signup-submit" class="logo-theme-button">
                    <?php echo $is_existing_user ? 'Finalize Setup' : 'Create Account'; ?>
                </button>
                <p class="signup-help-message">
                    Having problems creating your account?<br>
                    Please call Youth Dimension on (03) 9844 1944
                </p>
            </form>
        </div>
    </section>
</body>
</html>
