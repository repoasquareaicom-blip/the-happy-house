<?php
require '../vendor/autoload.php';
include '../config/data.php';
include '../class/stripe.php';
include '../class/log.php';
include '../class/subscription.php';
include '../objects/schooldata.php';
$_data = new Data();
$_log = new mLog();
$_stripe_log = new StripeLog($_data);
$subscription = new Subscription($_data);

$stripeConfig = require '../config/stripe.php';
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
\Stripe\Stripe::setApiKey($stripeConfig[$mode]['secret_key']);

$input = @file_get_contents('php://input');
$event = null;
try {
    $event = \Stripe\Event::constructFrom(json_decode($input, true));
} catch (Exception $e) {
    http_response_code(400);
    exit();
}
try {
    $_stripe_log->save_log($event);
} catch (Exception $e) {
    file_put_contents('logs.txt', date("F j, Y, g:i a") . " Method: Init, Page: Webhook, Message: $e\n", FILE_APPEND);
}
switch ($event->type) {
    // case 'customer.subscription.created':
        // $session = $event->data->object;
        // $customer_id = $session->customer;
        // $subscription_id = $session->id;
        // try {
            // $customer = \Stripe\Customer::retrieve($customer_id);
            // $customer_email = $customer->email;
            // $customer_name = $customer->name;
            // $status = $session->status; // Get the subscription status
            // file_put_contents('logs.txt', date("F j, Y, g:i a") . " customer.subscription.created - Name: $customer_name, Email: $customer_email, Status: $status, Sub-Id: $subscription_id\n", FILE_APPEND);
            // $dbResult = $subscription->create_subscription($customer_name, $customer_email, "", $customer_email, "", $subscription_id, "", "", $status, $customer_id);
            // file_put_contents('logs.txt', date("F j, Y, g:i a") . " Method: Subscription-Create - DB Trans:, Page: Webhook, Message: $status. .$dbResult\n", FILE_APPEND);
        // } catch (Exception $e) {
            // file_put_contents('logs.txt', date("F j, Y, g:i a") . " Method: customer.subscription.created, Page: Webhook, Message: $e\n", FILE_APPEND);
        // }
        // break;
    case 'customer.subscription.updated':
        try {
            $session = $event->data->object;
			$customer_id = $session->customer;
            $subscription_id = $session->id;
            $status = $session->status; // Updated subscription status
			$customer = \Stripe\Customer::retrieve($customer_id);
            $customer_email = $customer->email;
            $customer_name = $customer->name;
            $subscription_info = \Stripe\Subscription::retrieve($subscription_id);
            $start_date = $subscription_info->current_period_start;
            $end_date = $subscription_info->current_period_end;
			$cancel_at = $subscription_info->cancel_at;
            $cancel_at_period_end = $subscription_info->cancel_at_period_end;

            if($subscription->check_exist_subscription($subscription_id) == true) {
                file_put_contents('logs.txt', date("F j, Y, g:i a") . " Method: Subscription-Updated - Status: $status, Sub-Id: $subscription_id, Cancel_at: $cancel_at, cancel_at_period_end: $cancel_at_period_end\n", FILE_APPEND);
                $dbResult = $subscription->update_subscription("", "", "", "", "", $subscription_id, $start_date, $end_date, $cancel_at, $cancel_at_period_end, $status);
                file_put_contents('logs.txt', date("F j, Y, g:i a") . " Method: Subscription-Updated - DB Trans:, Page: Webhook, Message: $status. .$dbResult\n", FILE_APPEND);
            } else {
                // SCENARIO 2: Re-subscription
                $checkCustomer = $_data->getData("SELECT id FROM school_master WHERE customer_id = '$customer_id' LIMIT 1");
                
                if(!empty($checkCustomer)) {
                    // 1. Archive to history using your CORRECT column names
                    $historyQuery = "INSERT INTO subscription_history (
                                        school_id, 
                                        old_subscription_id, 
                                        old_status, 
                                        old_subscription_start, 
                                        old_subscription_end, 
                                        archived_at
                                    ) 
                                    SELECT 
                                        id, 
                                        subscription_id, 
                                        status, 
                                        subscription_start, 
                                        subscription_end, 
                                        NOW() 
                                    FROM school_master 
                                    WHERE customer_id = '$customer_id'";
                    $_data->execute($historyQuery);

                    // 2. Update school_master using your CORRECT column names
                    $query = "UPDATE school_master SET 
                                subscription_id = '$subscription_id', 
                                status = '$status', 
                                subscription_start = FROM_UNIXTIME('$start_date'), 
                                subscription_end = FROM_UNIXTIME('$end_date'),
                                cancel_at_period_end = 0
                                WHERE customer_id = '$customer_id'";
                    
                    $_data->execute($query);
                    file_put_contents('logs.txt', "... Re-subscription matched and updated for Customer: $customer_id\n", FILE_APPEND);
                } else {
                    // SCENARIO 3: Brand new customer
                    $dbResult = $subscription->create_subscription($customer_name, $customer_email, "", $customer_email, "", $subscription_id, $start_date, $end_date, $status, $customer_id);
                }
            }
        } catch (Exception $e) { 
        file_put_contents('logs.txt', "Error: " . $e->getMessage() . "\n", FILE_APPEND);
    }
    break;
    default:
        http_response_code(400);
        exit();
}
http_response_code(200);
?>
