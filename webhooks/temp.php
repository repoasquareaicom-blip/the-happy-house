<?php
require '../vendor/autoload.php';
include '../config/data.php';
include '../class/stripe.php';
include '../class/log.php';
require_once '../class/mail_helper.php';

$_data = new Data();
$_log = new mLog();
$_stripe_log = new StripeLog($_data);

$stripeConfig = require '../config/stripe.php';
$mode = $stripeConfig['mode'] ?? 'live';

if (empty($stripeConfig[$mode]['secret_key'])) {
    http_response_code(500);
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
    file_put_contents('logs.txt', "Log Error: " . $e->getMessage() . "\n", FILE_APPEND);
}

/* ---------------- WEBHOOK LOGIC ---------------- */

// 1. HANDLE INITIAL PURCHASES (Checkout)
if ($event->type === 'checkout.session.completed') {
    try {
        $session = $event->data->object;
        $session_id = $session->id;
        $subscription_id = $session->subscription; 
        $customer_email = $session->customer_details->email;
        $customer_id = $session->customer;

        $subscription_obj = \Stripe\Subscription::retrieve($subscription_id);
        $start_date = date("Y-m-d H:i:s", $subscription_obj->current_period_start);
        $end_date   = date("Y-m-d H:i:s", $subscription_obj->current_period_end);

        $type = $session->metadata->type ?? 'games'; 
        $product_name = ($type === 'curriculum') ? 'Wellbeing Curriculum' : 'Wellbeing Games';

        $checkAccount = $_data->getData("SELECT id FROM school_master WHERE school_admin_email = '$customer_email' LIMIT 1");

        if (!empty($checkAccount)) {
            $school_id = $checkAccount[0]['id'];
            // EXISTING USER: Update school_master directly
            if ($type === 'curriculum') {
                $sql = "UPDATE school_master SET 
                        curriculum_sub_id = '$subscription_id', 
                        customer_id = '$customer_id',
                        curriculum_status = 'active',
                        curriculum_start = '$start_date',
                        curriculum_end = '$end_date',
                        curriculum_cancel_at_period_end = 0,
                        curriculum_cancel_at = NULL
                        WHERE id = $school_id";
            } else {
                $sql = "UPDATE school_master SET 
                        subscription_id = '$subscription_id', 
                        customer_id = '$customer_id',
                        status = 'active',
                        subscription_start = '$start_date',
                        subscription_end = '$end_date',
                        cancel_at_period_end = 0,
                        cancel_at = NULL
                        WHERE id = $school_id";
            }
            $_data->execute($sql);

            // AUDIT LOG: Capture snapshot after update
            saveAuditSnapshot($_data, $school_id, 'CHECKOUT_COMPLETED');

        } else {
            // NEW USER: Update Temp Table
            $updateTemp = "UPDATE temp_school_master SET 
                        payment_status = 'paid', 
                        stripe_subscription_id = '$subscription_id',
                        customer_id = '$customer_id',
                        start_date = '$start_date',
                        end_date = '$end_date',
                        product_type = '$type'
                        WHERE stripe_session_id = '$session_id'";
            $_data->execute($updateTemp);

            $emailHelper = new EmailHelper();
            $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
            $finishUrl = $protocol . "://" . $_SERVER['HTTP_HOST'] . "/staging_thh/signup.php?s=" . $session_id;
            
            $variables = ['finish_url' => $finishUrl, 'email' => $customer_email, 'product_name' => $product_name];
            $templatePath = dirname(__DIR__) . '/template_welcome_finish_signup.tl';
            $message = $emailHelper->getEmailTemplate($templatePath, $variables);
            
            if ($message) {
                $emailHelper->sendEmail($customer_email, "Welcome! Finalize your Happy House account", $message, 'welcome_finish_signup');
            }
        }
    } catch (Exception $e) {
        file_put_contents('logs.txt', "Checkout Error: " . $e->getMessage() . "\n", FILE_APPEND);
    }
}

// 2. HANDLE UPDATES (Cancellations/Renewals)
if ($event->type === 'customer.subscription.updated' || $event->type === 'customer.subscription.deleted') {
    try {
        $subscription = $event->data->object;
        $sub_id = $subscription->id;
        $status = $subscription->status;
        $end_date = date("Y-m-d H:i:s", $subscription->current_period_end);
        $cancel_at_period_end = $subscription->cancel_at_period_end ? 1 : 0;
        $cancel_at = $subscription->cancel_at ? date("Y-m-d H:i:s", $subscription->cancel_at) : "NULL";

        $school_id = null;

        $isGames = $_data->getData("SELECT id FROM school_master WHERE subscription_id = '$sub_id' LIMIT 1");
        if (!empty($isGames)) {
            $school_id = $isGames[0]['id'];
            $updateSql = "UPDATE school_master SET 
                          status = '$status', 
                          subscription_end = '$end_date',
                          cancel_at_period_end = '$cancel_at_period_end',
                          cancel_at = " . ($cancel_at !== "NULL" ? "'$cancel_at'" : "NULL") . "
                          WHERE id = $school_id";
        } else {
            $isCurriculum = $_data->getData("SELECT id FROM school_master WHERE curriculum_sub_id = '$sub_id' LIMIT 1");
            if (!empty($isCurriculum)) {
                $school_id = $isCurriculum[0]['id'];
                $updateSql = "UPDATE school_master SET 
                              curriculum_status = '$status', 
                              curriculum_end = '$end_date',
                              curriculum_cancel_at_period_end = '$cancel_at_period_end',
                              curriculum_cancel_at = " . ($cancel_at !== "NULL" ? "'$cancel_at'" : "NULL") . "
                              WHERE id = $school_id";
            }
        }

        if ($school_id) {
            $_data->execute($updateSql);
            // AUDIT LOG: Capture snapshot after update
            saveAuditSnapshot($_data, $school_id, 'SUBSCRIPTION_' . strtoupper($event->type));
        }
    } catch (Exception $e) {
        file_put_contents('logs.txt', "Update Error: " . $e->getMessage() . "\n", FILE_APPEND);
    }
}

/**
 * Audit Helper: Copies the current school_master state into the audit log
 */
function saveAuditSnapshot($db, $school_id, $action) {
    $currentData = $db->getData("SELECT * FROM school_master WHERE id = $school_id LIMIT 1");
    if (!empty($currentData)) {
        $r = $currentData[0];
        // Ensure strings are escaped or handled by your execute method
        $auditSql = "INSERT INTO school_master_audit_log 
            (audit_action, school_id, name, email, school_name, school_admin_email, subscription_id, curriculum_sub_id, subscription_start, curriculum_start, subscription_end, curriculum_end, curriculum_status, status, customer_id, cancel_at, cancel_at_period_end, curriculum_cancel_at, curriculum_cancel_at_period_end) 
            VALUES 
            ('$action', '{$r['id']}', '{$r['name']}', '{$r['email']}', '{$r['school_name']}', '{$r['school_admin_email']}', '{$r['subscription_id']}', '{$r['curriculum_sub_id']}', '{$r['subscription_start']}', " . ($r['curriculum_start'] ? "'{$r['curriculum_start']}'" : "NULL") . ", '{$r['subscription_end']}', " . ($r['curriculum_end'] ? "'{$r['curriculum_end']}'" : "NULL") . ", '{$r['curriculum_status']}', '{$r['status']}', '{$r['customer_id']}', " . ($r['cancel_at'] ? "'{$r['cancel_at']}'" : "NULL") . ", " . (int)$r['cancel_at_period_end'] . ", " . ($r['curriculum_cancel_at'] ? "'{$r['curriculum_cancel_at']}'" : "NULL") . ", " . (int)$r['curriculum_cancel_at_period_end'] . ")";
        
        $db->execute($auditSql);
    }
}

http_response_code(200);
?>