<?php
session_start();
header('Content-Type: application/json');

include_once 'config/data.php';
require_once 'class/mail_helper.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $dataObj = new Data();
    
    // Sanitize input
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $product_key = isset($_POST['product_key']) ? $_POST['product_key'] : ''; 

    if (empty($email) || empty($product_key)) {
        echo json_encode(['status' => 'error', 'message' => 'Email and Product Selection are required.']);
        exit;
    }

    // 1. Check if email is already registered AND fetch school_name
    $checkSql = "SELECT id, subscription_type, status, curriculum_status, school_name 
                 FROM school_master 
                 WHERE school_admin_email = '$email' LIMIT 1";
    $existing = $dataObj->getData($checkSql);

    $found_school_name = null; 

    if (!empty($existing)) {
        $user = $existing[0];
        $found_school_name = $user['school_name']; // Captured for the Modal UI
        $isAlreadyRegistered = false;

        // Logic: Check if they already own the specific product they are trying to buy
        if ($product_key === 'wellbeing_games' && $user['status'] === 'active') {
            $isAlreadyRegistered = true;
        } elseif ($product_key === 'curriculum' && $user['curriculum_status'] === 'active') {
            $isAlreadyRegistered = true;
        } elseif ($user['subscription_type'] === 'both') {
            $isAlreadyRegistered = true;
        }

        if ($isAlreadyRegistered) {
            echo json_encode([
                'status' => 'error', 
                'message' => 'This school is already registered for this product. Please login to your dashboard.'
            ]);
            exit;
        }
    }

    // 2. Check for "Paid but Unfinished Signup" (Safety check for abandoned sessions)
    $checkPaid = $dataObj->getData("
        SELECT stripe_session_id 
        FROM temp_school_master 
        WHERE email = '$email' 
        AND payment_status = 'paid' 
        AND product_key = '$product_key' 
        ORDER BY created_at DESC LIMIT 1
    ");

    if (!empty($checkPaid)) {
        $session_id = $checkPaid[0]['stripe_session_id'];
        $emailHelper = new EmailHelper();
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
        $finishUrl = $protocol . "://" . $_SERVER['HTTP_HOST'] . "/signup.php?s=" . $session_id . "&type=" . $product_key;

        $variables = [
            'finish_url' => $finishUrl,
            'email' => $email,
            'product_name' => ($product_key == 'curriculum') ? 'Curriculum' : 'Wellbeing Games'
        ];
        
        $message = $emailHelper->getEmailTemplate('template_welcome_finish_signup.tl', $variables);
        $subject = 'Complete your Happy House Registration';
        $emailHelper->sendEmail($email, $subject, $message);

        echo json_encode([
            'status' => 'error', 
            'message' => 'Payment detected! We have re-sent a link to your email to complete your account setup.'
        ]);
        exit;
    }

    // 3. Generate 6-digit OTP
    $otp = rand(100000, 999999);
    $expires_at = date("Y-m-d H:i:s", strtotime("+10 minutes"));

    // 4. Save to temp_school_master
    $sql = "INSERT INTO temp_school_master (email, otp_code, product_key, otp_expires_at) 
            VALUES ('$email', '$otp', '$product_key', '$expires_at')";
    
    try {
        $dataObj->execute($sql);
        
        $emailHelper = new EmailHelper();
        $variables = [
            'otp' => $otp,
            'expiry_time' => '10'
        ];

        $message = $emailHelper->getEmailTemplate('template_subscription_otp.tl', $variables);
        $subject = 'Verify your email - The Happy House';
        
        $response = $emailHelper->sendEmail($email, $subject, $message);
        
        // Handling both boolean and JSON-string responses from EmailHelper
        $isSuccess = false;
        if ($response === true) {
            $isSuccess = true;
        } else {
            $responseData = json_decode($response['message'] ?? '', true);
            if (isset($responseData['status']) && $responseData['status'] == 'success') {
                $isSuccess = true;
            }
        }

        if ($isSuccess) {
            $_SESSION['pending_email'] = $email;
            
            // --- SUCCESS RESPONSE WITH SCHOOL NAME ---
            echo json_encode([
                'status' => 'success',
                'school_name' => $found_school_name // null for new, string for existing
            ]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to send OTP. Please check your email.']);
        }
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => 'Database error. Please try again.']);
    }
}