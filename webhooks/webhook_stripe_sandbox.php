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
/* ---------------- HELPER: INSERT AUDIT ---------------- */

function insertSchoolAudit($db, $customer_id, $action) {
    $row = $db->getData("
        SELECT * FROM school_master 
        WHERE customer_id = '$customer_id'
        LIMIT 1
    ");

    if (empty($row)) {
        return;
    }

    $r = $row[0];

    // We include all Wellbeing and Curriculum columns in the snapshot
    $auditSql = "
        INSERT INTO school_master_audit (
            name, email, school_name, school_admin_email, password,
            subscription_id, subscription_start, subscription_end, status,
            cancel_at, cancel_at_period_end,
            curriculum_sub_id, curriculum_start, curriculum_end, curriculum_status,
            curriculum_cancel_at, curriculum_cancel_at_period_end,
            subscription_type, customer_id, created_on,
            audit_action, audit_source, audited_at
        ) VALUES (
            '{$r['name']}', '{$r['email']}', '{$r['school_name']}', '{$r['school_admin_email']}', '{$r['password']}',
            '{$r['subscription_id']}', '{$r['subscription_start']}', '{$r['subscription_end']}', '{$r['status']}',
            " . ($r['cancel_at'] ? "'{$r['cancel_at']}'" : "NULL") . ", '{$r['cancel_at_period_end']}',
            '{$r['curriculum_sub_id']}', '{$r['curriculum_start']}', '{$r['curriculum_end']}', '{$r['curriculum_status']}',
            " . ($r['curriculum_cancel_at'] ? "'{$r['curriculum_cancel_at']}'" : "NULL") . ", '{$r['curriculum_cancel_at_period_end']}',
            '{$r['subscription_type']}', '{$r['customer_id']}', '{$r['created_on']}',
            '$action', 'stripe_webhook', NOW()
        )
    ";

    $db->execute($auditSql);
}


switch ($event->type) {
    case 'customer.subscription.updated':
    try {
        $session = $event->data->object;
        $customer_id = $session->customer;
        $subscription_id = $session->id;
        $status = $session->status; 
        
        $subscription_info = \Stripe\Subscription::retrieve($subscription_id);
        $sub_type = isset($subscription_info->metadata->subscription_type) ? $subscription_info->metadata->subscription_type : 'wellbeing_games';

        $customer = \Stripe\Customer::retrieve($customer_id);
        $customer_email = $customer->email;
        $customer_name = $customer->name;
        
        $start_date = $subscription_info->current_period_start;
        $end_date = $subscription_info->current_period_end;
        $cancel_at = $subscription_info->cancel_at;
        $cancel_at_period_end = $subscription_info->cancel_at_period_end ? 1 : 0;

        // 1. Check if ID exists
        if ($sub_type == 'curriculum') {
            $checkSub = $_data->getData("SELECT id FROM school_master WHERE curriculum_sub_id = '$subscription_id' LIMIT 1");
        } else {
            $checkSub = $_data->getData("SELECT id FROM school_master WHERE subscription_id = '$subscription_id' LIMIT 1");
        }

        if(!empty($checkSub)) {
            // SCENARIO 1: Standard Renewal / Update
            insertSchoolAudit($_data, $customer_id, 'renewal_' . $sub_type);
            
            if ($sub_type == 'curriculum') {
                $query = "UPDATE school_master SET 
                            curriculum_status = '$status', 
                            curriculum_start = FROM_UNIXTIME('$start_date'), 
                            curriculum_end = FROM_UNIXTIME('$end_date'),
                            curriculum_cancel_at_period_end = $cancel_at_period_end,
                            curriculum_cancel_at = " . ($cancel_at ? "FROM_UNIXTIME('$cancel_at')" : "NULL") . "
                          WHERE curriculum_sub_id = '$subscription_id'";
            } else {
                $query = "UPDATE school_master SET 
                            status = '$status', 
                            subscription_start = FROM_UNIXTIME('$start_date'), 
                            subscription_end = FROM_UNIXTIME('$end_date'),
                            cancel_at_period_end = $cancel_at_period_end,
                            cancel_at = " . ($cancel_at ? "FROM_UNIXTIME('$cancel_at')" : "NULL") . "
                          WHERE subscription_id = '$subscription_id'";
            }
            $_data->execute($query);

        } else {
            // SCENARIO 2: Fresh Subscription
            $checkCustomer = $_data->getData("SELECT id, subscription_type FROM school_master WHERE customer_id = '$customer_id' LIMIT 1");
            
            if(!empty($checkCustomer)) {
                $school = $checkCustomer[0];
                insertSchoolAudit($_data, $customer_id, 'fresh_sub_' . $sub_type);

                if ($sub_type == 'curriculum') {
                    $new_type = ($school['subscription_type'] == 'wellbeing_games') ? 'both' : 'curriculum';
                    $query = "UPDATE school_master SET 
                                curriculum_sub_id = '$subscription_id', 
                                curriculum_status = '$status', 
                                curriculum_start = FROM_UNIXTIME('$start_date'), 
                                curriculum_end = FROM_UNIXTIME('$end_date'),
                                curriculum_cancel_at_period_end = $cancel_at_period_end,
                                curriculum_cancel_at = " . ($cancel_at ? "FROM_UNIXTIME('$cancel_at')" : "NULL") . ",
                                subscription_type = '$new_type'
                              WHERE customer_id = '$customer_id'";
                } else {
                    // Wellbeing Logic (Archive old WB before fresh WB start)
                    $historyQuery = "INSERT INTO subscription_history (school_id, old_subscription_id, old_status, old_subscription_start, old_subscription_end, archived_at) 
                                     SELECT id, subscription_id, status, subscription_start, subscription_end, NOW() 
                                     FROM school_master WHERE customer_id = '$customer_id'";
                    $_data->execute($historyQuery);

                    $new_type = ($school['subscription_type'] == 'curriculum') ? 'both' : 'wellbeing_games';
                    $query = "UPDATE school_master SET 
                                subscription_id = '$subscription_id', 
                                status = '$status', 
                                subscription_start = FROM_UNIXTIME('$start_date'), 
                                subscription_end = FROM_UNIXTIME('$end_date'),
                                cancel_at_period_end = $cancel_at_period_end,
                                cancel_at = " . ($cancel_at ? "FROM_UNIXTIME('$cancel_at')" : "NULL") . ",
                                subscription_type = '$new_type'
                              WHERE customer_id = '$customer_id'";
                }
                $_data->execute($query);
            } else {
                // SCENARIO 3: Brand new customer
                insertSchoolAudit($_data, $customer_id, 'new_signup_' . $sub_type);
                if ($sub_type == 'curriculum') {
                    $query = "INSERT INTO school_master (name, email, school_admin_email, curriculum_sub_id, curriculum_start, curriculum_end, curriculum_status, curriculum_cancel_at, curriculum_cancel_at_period_end, customer_id, subscription_type, created_on) 
                              VALUES ('$customer_name', '$customer_email', '$customer_email', '$subscription_id', FROM_UNIXTIME('$start_date'), FROM_UNIXTIME('$end_date'), '$status', " . ($cancel_at ? "FROM_UNIXTIME('$cancel_at')" : "NULL") . ", $cancel_at_period_end, '$customer_id', 'curriculum', NOW())";
                } else {
                    $query = "INSERT INTO school_master (name, email, school_admin_email, subscription_id, subscription_start, subscription_end, status, cancel_at, cancel_at_period_end, customer_id, subscription_type, created_on) 
                              VALUES ('$customer_name', '$customer_email', '$customer_email', '$subscription_id', FROM_UNIXTIME('$start_date'), FROM_UNIXTIME('$end_date'), '$status', " . ($cancel_at ? "FROM_UNIXTIME('$cancel_at')" : "NULL") . ", $cancel_at_period_end, '$customer_id', 'wellbeing_games', NOW())";
                }
                $_data->execute($query);
            }
        }
    } catch (Exception $e) { 
        file_put_contents('logs.txt', "Error: " . $e->getMessage() . "\n", FILE_APPEND);
    }
    break;
        http_response_code(400);
        exit();
}
http_response_code(200);
?>
