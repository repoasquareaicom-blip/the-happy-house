<?php
session_start(); // Ensure session is started to identify the user
require 'vendor/autoload.php';
include 'config/data.php'; 

// 1. Check if user is logged in
if (!isset($_SESSION['school_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized access.']);
    exit;
}

$school_id = $_SESSION['school_id'];
$stripeConfig = require 'config/stripe.php';
$mode = $stripeConfig['mode'] ?? 'live';

if (empty($stripeConfig[$mode]['secret_key'])) {
    http_response_code(500);
    echo json_encode(['error' => 'Stripe configuration incomplete.']);
    exit;
}

\Stripe\Stripe::setApiKey($stripeConfig[$mode]['secret_key']);
$_data = new Data(); 

// 2. Fetch Price ID and existing Stripe Customer ID
// We need stripe_customer_id to avoid creating duplicate customers
$_query = "SELECT 
            (SELECT setting_value FROM app_settings WHERE setting_key = 'stripe_price_id' LIMIT 1) as price_id,
            customer_id 
          FROM school_master 
          WHERE id = '$school_id' LIMIT 1";

file_put_contents('logs.txt', "... Re-subscription school data fetcing : $_query\n", FILE_APPEND);

$result = $_data->getData($_query);

if (!$result || empty($result[0]['price_id'])) {
    die(json_encode(['error' => 'Configuration or School record not found.'])); 
}

$dynamicPriceId = $result[0]['price_id'];
$existingCustomerId = $result[0]['customer_id'];

try {
    // 3. Prepare Session Parameters
    $sessionParams = [
        'payment_method_types' => ['card'],
        'mode' => 'subscription',
        'line_items' => [[
            'price' => $dynamicPriceId,
            'quantity' => 1,
        ]],
        'success_url' => 'https://thehappyhouse.au/re-subscription-success.php?session_id={CHECKOUT_SESSION_ID}',
        'cancel_url' => 'https://thehappyhouse.au',
    ];

    // 4. CRITICAL: Attach existing customer if available
    if (!empty($existingCustomerId)) {
        $sessionParams['customer'] = $existingCustomerId;
    }

    $session = \Stripe\Checkout\Session::create($sessionParams);

    echo json_encode(['id' => $session->id]);

} catch (\Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}