<?php
require 'vendor/autoload.php';

include '../config/data.php';
include '../class/stripe.php';
include '../class/log.php';
include '../class/subscription.php';
include '../objects/schooldata.php';


$_data = new Data();
$_log = new mLog();
$_stripe_log = new StripeLog($_data);
$subscription = new Subscription($_data);

\Stripe\Stripe::setApiKey('sk_test_51QPb6I06kM66h4BbSzEOV2OuqXUClMU4qoOfkKmmNg9Ql0eadUojczoiJfwc8chu3FQhqo7u9jAVgcJqQh08Vawz00ygwFgiAK'); // Replace with your Test Secret Key

// Retrieve the raw POST body sent by Stripe
$input = @file_get_contents('php://input');
$event = null;

try {
    $event = \Stripe\Event::constructFrom(json_decode($input, true));
	//$write_log("Init", "Webhook", "Event recieved");
	file_put_contents('logs.txt', date("F j, Y, g:i a"). "Method: Init, Page: Webhook, Message: $event->type recieved\n", FILE_APPEND);
} catch (Exception $e) {
    http_response_code(400);
    exit(); // Invalid payload
	//$write_log("Init", "Webhook", $e);
	file_put_contents('logs.txt', date("F j, Y, g:i a"). "Method: Init, Page: Webhook, Message: $e\n", FILE_APPEND);
}


//insertingstripe log
try{
	$_stripe_log->save_log($event);
	//$write_log("Init", "Webhook", "Event record saved in db");
	file_put_contents('logs.txt', date("F j, Y, g:i a"). "Method: Init, Page: Webhook, Message: $event->type record saved in db\n", FILE_APPEND);
}
catch(Expection $e){
	 $write_log("Payment Init", "Webhook", $e);
	 file_put_contents('logs.txt', date("F j, Y, g:i a"). "Method: Init, Page: Webhook, Message: $e\n", FILE_APPEND);
}


// Handle the event
switch ($event->type) {
    case 'customer.subscription.created':
        $session = $event->data->object; // The Checkout Session object
        $customer_id = $session->customer; // Stripe Customer ID
        $subscription_id = $session->subscription; // Subscription ID
		$subscription_start = $session->current_period_start;
		$subscription_end = $session->current_period_end;
		
		

        // Retrieve customer details
        try {
			file_put_contents('logs.txt', date("F j, Y, g:i a"). "customer.subscription.created - request\n", FILE_APPEND);
            $customer = \Stripe\Customer::retrieve($customer_id);
            // Access customer details
            $customer_email = $customer->email;
            $customer_name = $customer->name;
            $customer_phone = $customer->phone; // If available
			file_put_contents('logs.txt', date("F j, Y, g:i a"). "customer.subscription.created - Name: $customer->name, Email: $customer_email\n", FILE_APPEND);
			
			
			$dbResult = $subscription->create_subscription($customer_name, $customer_email, "", $customer_email, "", $subscription_id, "", "", "Subscription Created",$customer_id);
			
			file_put_contents('logs.txt', date("F j, Y, g:i a"). "Method: Subscription-Create - DB Trans:, Page: Webhook, Message: $dbResult\n", FILE_APPEND);
			
			
			//$write_log("Subscription-Create", "Webhook", "School data added in db");
			//file_put_contents('logs.txt', date("F j, Y, g:i a"). "Method: customer.subscription.created, Page: Webhook, Message: School data added in db", FILE_APPEND);
            
        } catch (Exception $e) {
            file_put_contents('logs.txt', date("F j, Y, g:i a"). "Method: customer.subscription.created, Page: Webhook, Message: $e\n", FILE_APPEND);
        }
	case 'customer.subscription.updated':
		try{
			file_put_contents('logs.txt', date("F j, Y, g:i a"). "customer.subscription.update request\n", FILE_APPEND);

			//Getting subscription date info;
			
			
			
			file_put_contents('logs.txt', date("F j, Y, g:i a"). "Method: Subscription-Updated - subscription_start: $subscription_start, subscription_end: $subscription_end\n", FILE_APPEND);

			
			$dbResult = $subscription->update_subscription("", "", "", "", "", $subscription_id, $subscription_start, $subscription_end, "");
			
			file_put_contents('logs.txt', date("F j, Y, g:i a"). "Method: Subscription-Updated - DB Trans:, Page: Webhook, Message: $dbResult\n", FILE_APPEND);
			
			
		} catch (Exception $e) {
            //$_log->write("Subscription-Updated", "Webhook", $e);
			file_put_contents('logs.txt', date("F j, Y, g:i a"). "Method: Subscription-Updated, Page: Webhook, Message: $e", FILE_APPEND);
        }
        break;

    default:
        // Handle other event types
        http_response_code(400);
        exit();
}

http_response_code(200); // Acknowledge receipt of the event

	
?>

