w<?php
error_reporting(E_ALL & ~E_DEPRECATED & ~E_USER_DEPRECATED);
ini_set('display_errors', '0');
session_start();
header('Content-Type: application/json');

require 'vendor/autoload.php';
include_once 'config/data.php';
require_once 'class/mail_helper.php';

$stripeConfig = require 'config/stripe.php';
$mode = $stripeConfig['mode'] ?? 'live';
$baseUrl = $stripeConfig[$mode]['base_url'] ?? '';

if (empty($stripeConfig[$mode]['secret_key']) || empty($baseUrl)) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Stripe configuration incomplete.']);
    exit;
}

\Stripe\Stripe::setApiKey($stripeConfig[$mode]['secret_key']);

function jsonError($message) {
    echo json_encode(['status' => 'error', 'message' => $message]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    jsonError('Invalid request method.');
}

$dataObj = new Data();

$email = filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL);
$product_key = trim($_POST['product_key'] ?? '');
$allowedProducts = ['wellbeing_games', 'curriculum'];

if (empty($email) || empty($product_key)) {
    jsonError('Email and Product Selection are required.');
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    jsonError('Please enter a valid email address.');
}

if (!in_array($product_key, $allowedProducts, true)) {
    jsonError('Invalid product selection.');
}

$emailSql = addslashes($email);
$productSql = addslashes($product_key);

$existing = $dataObj->getData("
    SELECT id, subscription_type, status, curriculum_status, customer_id, school_name
    FROM school_master
    WHERE school_admin_email = '$emailSql'
    LIMIT 1
");

$existing_customer_id = null;

if (!empty($existing)) {
    $user = $existing[0];
    $alreadyOwned = false;

    if ($product_key === 'wellbeing_games' && $user['status'] === 'active') {
        $alreadyOwned = true;
    } elseif ($product_key === 'curriculum' && $user['curriculum_status'] === 'active') {
        $alreadyOwned = true;
    } elseif ($user['subscription_type'] === 'both') {
        $alreadyOwned = true;
    }

    if ($alreadyOwned) {
        jsonError('This school is already registered for this product. Please login to your dashboard.');
    }

    if (!empty($user['customer_id'])) {
        $existing_customer_id = $user['customer_id'];
    }
}

$checkPaid = $dataObj->getData("
    SELECT stripe_session_id
    FROM temp_school_master
    WHERE email = '$emailSql'
    AND payment_status = 'paid'
    AND product_key = '$productSql'
    ORDER BY created_at DESC
    LIMIT 1
");

if (!empty($checkPaid)) {
    $session_id = $checkPaid[0]['stripe_session_id'];
    $emailHelper = new EmailHelper();
    $finishUrl = rtrim($baseUrl, '/') . '/signup.php?s=' . urlencode($session_id) . '&type=' . urlencode($product_key);

    $variables = [
        'finish_url' => $finishUrl,
        'email' => $email,
        'product_name' => ($product_key === 'curriculum') ? 'Curriculum' : 'Wellbeing Games'
    ];

    $message = $emailHelper->getEmailTemplate('template_welcome_finish_signup.tl', $variables);
    $emailHelper->sendEmail($email, 'Complete your Happy House Registration', $message);

    jsonError('Payment detected! We have re-sent a link to your email to complete your account setup.');
}

$productData = $dataObj->getData("
    SELECT stripe_price_id, display_name
    FROM products_master
    WHERE product_key = '$productSql'
    AND status = 1
    LIMIT 1
");

if (empty($productData)) {
    jsonError('Product configuration not found.');
}

$price_id = $productData[0]['stripe_price_id'];
$display_name = $productData[0]['display_name'];

try {
    $insertSql = "
        INSERT INTO temp_school_master
            (email, otp_code, product_key, otp_expires_at, is_verified, payment_status)
        VALUES
            ('$emailSql', '', '$productSql', NOW(), 0, 'pending')
    ";

    $temp_id = $dataObj->execute($insertSql);

    if (!$temp_id) {
        jsonError('Database error. Please try again.');
    }

    $sessionParams = [
        'payment_method_types' => ['card'],
        'line_items' => [[
            'price' => $price_id,
            'quantity' => 1,
        ]],
        'mode' => 'subscription',
        'success_url' => rtrim($baseUrl, '/') . '/signup.php?s={CHECKOUT_SESSION_ID}&type=' . urlencode($product_key),
        'cancel_url' => rtrim($baseUrl, '/') . '/index.php',
        'metadata' => [
            'temp_db_id' => $temp_id,
            'product_key' => $product_key,
            'product_name' => $display_name
        ]
    ];

    if ($existing_customer_id) {
        $sessionParams['customer'] = $existing_customer_id;
    } else {
        $sessionParams['customer_email'] = $email;
    }

    $session = \Stripe\Checkout\Session::create($sessionParams);

    $updated = $dataObj->executePrepared(
        "UPDATE temp_school_master SET stripe_session_id = :session_id, payment_status = 'pending' WHERE id = :id",
        [
            ':session_id' => $session->id,
            ':id' => $temp_id
        ]
    );

    if (!$updated) {
        jsonError('Database error. Please try again.');
    }

    echo json_encode(['status' => 'success', 'checkout_url' => $session->url]);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'Unable to start Stripe Checkout. Please try again.']);
}
