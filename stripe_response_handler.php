<?php
require __DIR__ . '/vendor/autoload.php';

// Load Stripe config
$stripeConfig = require __DIR__ . '/config/stripe.php';
$mode = $stripeConfig['mode'] ?? 'live';

if (empty($stripeConfig[$mode]['secret_key'])) {
    die('Stripe configuration missing.');
}

\Stripe\Stripe::setApiKey($stripeConfig[$mode]['secret_key']);

if (empty($_GET['session_id'])) {
    die('Invalid session.');
}

$session_id = $_GET['session_id'];

try {
    // 1. Retrieve Checkout Session
    $session = \Stripe\Checkout\Session::retrieve($session_id);

    if (empty($session->subscription)) {
        throw new Exception('Subscription not found.');
    }

    // 2. Get the Metadata to see what they bought
    $sub_type = isset($session->metadata->subscription_type) ? $session->metadata->subscription_type : 'wellbeing_games';

    // 3. Redirect to signup page with BOTH subscription id AND type
    // This allows signup.php to show the correct welcome message or features
    $url = "https://demo.thehappyhouse.au/signup.php?s=" . urlencode($session->subscription) . "&type=" . urlencode($sub_type);

    header("Location: $url");
    exit;

} catch (\Exception $e) {
    http_response_code(500);
    echo "Stripe Error: " . htmlspecialchars($e->getMessage());
}