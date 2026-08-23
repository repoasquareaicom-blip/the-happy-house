<?php
require 'payment/vendor/autoload.php'; // Load Stripe PHP library

// Load Stripe config
$stripeConfig = require 'config/stripe.php';

// Validate config
if (
    empty($stripeConfig[$mode]['secret_key']) 

) {
    http_response_code(500);
    echo json_encode(['error' => 'Stripe configuration incomplete.']);
    exit;
}
\Stripe\Stripe::setApiKey($stripeConfig[$mode]['secret_key']);

// Function to get the subscription status
function getSubscriptionStatus($subscriptionId) {
    try {
        // Retrieve the subscription object from Stripe
        $subscription = \Stripe\Subscription::retrieve($subscriptionId);

        // Check if the subscription is set to cancel at the end of the current period
        if ($subscription->cancel_at_period_end) {
            // This means the subscription will be canceled at the end of the current period
            $status = "This subscription will be canceled at the end of the period.";
        } else {
            // Subscription is still active
            $status = "Subscription is active and will renew.";
        }

        // Check the current period end time and compare with current time
        $currentDate = time(); // Get the current timestamp
        $currentPeriodEnd = $subscription->current_period_end; // Get the end of the current period

        // If the subscription is still active but is marked to be canceled at the end of the period
        if ($currentDate < $currentPeriodEnd && $subscription->cancel_at_period_end) {
            $status = "Subscription will be canceled at the end of the period.";
        }

        return $status;
    } catch (\Stripe\Exception\ApiErrorException $e) {
        // Handle API errors
        return "Error retrieving subscription status: " . $e->getMessage();
    }
}

// Check if the form has been submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the subscription ID from the form input
    if (isset($_POST['subscription_id']) && !empty($_POST['subscription_id'])) {
        $subscriptionId = $_POST['subscription_id']; // Retrieve subscription ID from input box

        // Get the status of the subscription
        $status = getSubscriptionStatus($subscriptionId);

        // Output the subscription status
        echo "Subscription Status: " . $status;
    } else {
        echo "Error: Subscription ID is missing.";
    }
} else {
    // Display the input form for the user to enter the subscription ID
    echo '
    <form method="POST" action="">
        <label for="subscription_id">Enter Subscription ID:</label><br>
        <input type="text" id="subscription_id" name="subscription_id" required><br><br>
        <input type="submit" value="Get Subscription Status">
    </form>';
}
?>
