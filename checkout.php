<?php
require 'vendor/autoload.php';
include 'config/data.php'; // Include your DB connection logic
// Load Stripe config
$stripeConfig = require 'config/stripe.php';

// Read mode from config
$mode = $stripeConfig['mode'] ?? 'live';

// Validate config
if (
    empty($stripeConfig[$mode]['secret_key']) 
) {
    http_response_code(500);
    echo json_encode(['error' => 'Stripe configuration incomplete.']);
    exit;
}

// Initialize Stripe
\Stripe\Stripe::setApiKey($stripeConfig[$mode]['secret_key']);

$_data = new Data(); 
$_query = "SELECT setting_value FROM app_settings WHERE setting_key = 'stripe_price_id' LIMIT 1";


$settings = $_data->getData($_query);

if (!$settings || empty($settings[0]['setting_value'])) {
    die(json_encode(['error' => 'Stripe Price ID not configured in app settings.'])); 
}

$dynamicPriceId = $settings[0]['setting_value'];

$dynamicPriceId = 'price_1T4JJ71iDMRIBrSleAX8dvVr';

try {
    $session = \Stripe\Checkout\Session::create([
        'payment_method_types' => ['card'],
        'mode' => 'subscription',
        'line_items' => [[
            'price' => $dynamicPriceId,
            'quantity' => 1,
        ]],
        'metadata' => [
            'subscription_type' => 'curriculum'
        ],
        'success_url' => 'https://demo.thehappyhouse.au/stripe_response_handler.php?session_id={CHECKOUT_SESSION_ID}',
        'cancel_url' => 'https://thehappyhouse.au',
    ]);

    echo json_encode(['id' => $session->id]);

} catch (\Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
