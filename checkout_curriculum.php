<?php
require 'vendor/autoload.php';
include 'config/data.php'; 
$stripeConfig = require 'config/stripe.php';

$mode = $stripeConfig['mode'] ?? 'live';

if (empty($stripeConfig[$mode]['secret_key'])) {
    http_response_code(500);
    echo json_encode(['error' => 'Stripe configuration incomplete.']);
    exit;
}

\Stripe\Stripe::setApiKey($stripeConfig[$mode]['secret_key']);

$_data = new Data(); 

// 1. Fetch the CURRICULUM specific Price ID from your settings table
// Ensure you have added 'stripe_curriculum_price_id' to your database
$_query = "SELECT setting_value FROM app_settings WHERE setting_key = 'curriculum_price_id' LIMIT 1";
$settings = $_data->getData($_query);

if (!$settings || empty($settings[0]['setting_value'])) {
    die(json_encode(['error' => 'Stripe Curriculum Price ID not configured.'])); 
}

$curriculumPriceId = $settings[0]['setting_value'];

try {
    $session = \Stripe\Checkout\Session::create([
        'payment_method_types' => ['card'],
        'mode' => 'subscription',
        'line_items' => [[
            'price' => $curriculumPriceId,
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