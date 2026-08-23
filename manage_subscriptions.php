<?php
session_start();
require 'vendor/autoload.php';

/* ================================
   Load Stripe config
================================ */
$stripeConfig = require 'config/stripe.php';
$mode = $stripeConfig['mode'] ?? 'live';

if (empty($stripeConfig[$mode]['secret_key'])) {
    die('Stripe secret key not configured');
}
\Stripe\Stripe::setApiKey($stripeConfig[$mode]['secret_key']);

// 1. Get the Customer ID from your session (pulled from school_master during login)
$customerId = $_SESSION['stripe_customer_id'] ?? null;

if (!$customerId) {
    // If it's not in the session, you might want to fetch it from the DB here 
    // or show an error if they haven't subscribed yet.
    die("Billing record not found. Please ensure you have an active subscription.");
}

try {
    // 2. Create the Portal Session directly using the Customer ID
    // This will show BOTH Wellbeing Games and Curriculum in one list.
    $session = \Stripe\BillingPortal\Session::create([
        'customer' => $customerId,
        'return_url' => 'https://thehappyhouse.au/school_admin_dashboard.php',
    ]);

    // 3. Redirect to the Stripe-hosted management page
    header('Location: ' . $session->url);
    exit();

} catch (\Stripe\Exception\ApiErrorException $e) {
    echo "Stripe Error: " . $e->getMessage();
    exit();
} catch (Exception $e) {
    echo "Unexpected error: " . $e->getMessage();
    exit();
}