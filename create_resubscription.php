<?php
session_start();
require 'vendor/autoload.php';
include 'config/data.php';

$stripeConfig = require 'config/stripe.php';
$mode = $stripeConfig['mode'] ?? 'live';
\Stripe\Stripe::setApiKey($stripeConfig[$mode]['secret_key']);

$dataObj = new Data();

// Get product type from URL
$product_key = isset($_GET['type']) ? $_GET['type'] : 'wellbeing_games';
$email = $_SESSION['school_admin_email']; // Ensure this session variable is set during login

// 1. Fetch the existing Stripe Customer ID
$user = $dataObj->getData("SELECT customer_id FROM school_master WHERE school_admin_email = '$email' LIMIT 1");

if (empty($user) || empty($user[0]['customer_id'])) {
    die("Customer record not found. Please contact support.");
}

$customer_id = $user[0]['customer_id'];

// 2. Get the specific Price ID for this product
$prodData = $dataObj->getData("SELECT stripe_price_id FROM products_master WHERE product_key = '$product_key' LIMIT 1");
$price_id = $prodData[0]['stripe_price_id'];

try {
    // 3. Create the Stripe Session
    $session = \Stripe\Checkout\Session::create([
        'customer' => $customer_id, 
        'payment_method_types' => ['card'],
        'line_items' => [[
            'price' => $price_id, 
            'quantity' => 1
        ]],
        'mode' => 'subscription',
        'success_url' => $stripeConfig[$mode]['base_url'] . '/school_admin_dashboard.php?resub=success',
        'cancel_url' => $stripeConfig[$mode]['base_url'] . '/school_admin_dashboard.php',
        'metadata' => [
            'type' => $product_key // This is used by the webhook to update the correct column
        ]
    ]);

    // 4. Redirect to Stripe
    header("Location: " . $session->url);
    exit;

} catch (Exception $e) {
    echo "Stripe Error: " . $e->getMessage();
}