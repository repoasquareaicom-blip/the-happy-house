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
/* ---------------- 1. CHECKOUT SESSION COMPLETED ---------------- */
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

        $type = $session->metadata->product_key ?? 'wellbeing_games'; 
        $product_name = ($type === 'curriculum') ? 'Wellbeing Curriculum' : 'Wellbeing Games';

        $checkAccount = $_data->getData("SELECT id FROM school_master WHERE school_admin_email = '$customer_email' LIMIT 1");

        if (!empty($checkAccount)) {
            $school_id = $checkAccount[0]['id'];
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
            saveAuditSnapshot($_data, $school_id, 'CHECKOUT_COMPLETED');

        } else {
            $updateTemp = "UPDATE temp_school_master SET 
                        payment_status = 'paid', 
                        stripe_subscription_id = '$subscription_id',
                        customer_id = '$customer_id',
                        start_date = '$start_date',
                        end_date = '$end_date',
                        product_key = '$type' 
                        WHERE stripe_session_id = '$session_id'";
            $_data->execute($updateTemp);
            
            $emailHelper = new EmailHelper();
            $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
            $finishUrl = $protocol . "://" . $_SERVER['HTTP_HOST'] . "/signup.php?s=" . $session_id;
            
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

/* ---------------- 2. SUBSCRIPTION UPDATED / DELETED ---------------- */
if ($event->type === 'customer.subscription.updated' || $event->type === 'customer.subscription.deleted') {
    try {
        $subscription = $event->data->object;
        $sub_id = $subscription->id;
        $status = $subscription->status;
        $end_date = date("Y-m-d H:i:s", $subscription->current_period_end);
        $cancel_at_period_end = $subscription->cancel_at_period_end ? 1 : 0;
        
        // FIXED: Handle NULL properly for SQL
        $cancel_at_val = $subscription->cancel_at ? "'" . date("Y-m-d H:i:s", $subscription->cancel_at) . "'" : "NULL";

        $isGames = $_data->getData("SELECT id FROM school_master WHERE subscription_id = '$sub_id' LIMIT 1");
        if (!empty($isGames)) {
            $school_id = $isGames[0]['id'];
            $updateSql = "UPDATE school_master SET 
                          status = '$status', 
                          subscription_end = '$end_date',
                          cancel_at_period_end = $cancel_at_period_end,
                          cancel_at = $cancel_at_val
                          WHERE id = $school_id";
            $_data->execute($updateSql);
            saveAuditSnapshot($_data, $school_id, 'SUBSCRIPTION_UPDATE_GAMES');
        } else {
            $isCurriculum = $_data->getData("SELECT id FROM school_master WHERE curriculum_sub_id = '$sub_id' LIMIT 1");
            if (!empty($isCurriculum)) {
                $school_id = $isCurriculum[0]['id'];
                $updateSql = "UPDATE school_master SET 
                              curriculum_status = '$status', 
                              curriculum_end = '$end_date',
                              curriculum_cancel_at_period_end = $cancel_at_period_end,
                              curriculum_cancel_at = $cancel_at_val
                              WHERE id = $school_id";
                $_data->execute($updateSql);
                saveAuditSnapshot($_data, $school_id, 'SUBSCRIPTION_UPDATE_CURRICULUM');
            }
        }
    } catch (Exception $e) {
        file_put_contents('logs.txt', "Update Error: " . $e->getMessage() . "\n", FILE_APPEND);
    }
}

/**
 * Audit Helper: Copies the current school_master state into the audit log
 */
function saveAuditSnapshot($db, $school_id, $action) {
    // 1. Fetch the absolute latest state of the record
    $currentData = $db->getData("SELECT * FROM school_master WHERE id = $school_id LIMIT 1");
    
    if (!empty($currentData)) {
        $r = $currentData[0];

        // 2. Prepare the values, handling NULLs for dates and integers
        // We use the exact column names from your school_master schema
        $auditSql = "INSERT INTO school_master_audit_log (
            audit_action, 
            id, 
            name, 
            email, 
            school_name, 
            school_admin_email, 
            password, 
            subscription_id, 
            curriculum_sub_id, 
            subscription_start, 
            curriculum_start, 
            subscription_end, 
            curriculum_end, 
            curriculum_status, 
            status, 
            subscription_type, 
            created_on, 
            customer_id, 
            subscription_status_id, 
            cancel_at, 
            cancel_at_period_end, 
            modified_on, 
            curriculum_cancel_at, 
            curriculum_cancel_at_period_end
        ) VALUES (
            '$action',
            '{$r['id']}',
            '" . addslashes($r['name']) . "',
            '" . addslashes($r['email']) . "',
            '" . addslashes($r['school_name']) . "',
            '" . addslashes($r['school_admin_email']) . "',
            '{$r['password']}',
            '{$r['subscription_id']}',
            " . ($r['curriculum_sub_id'] ? "'{$r['curriculum_sub_id']}'" : "NULL") . ",
            '{$r['subscription_start']}',
            " . ($r['curriculum_start'] ? "'{$r['curriculum_start']}'" : "NULL") . ",
            '{$r['subscription_end']}',
            " . ($r['curriculum_end'] ? "'{$r['curriculum_end']}'" : "NULL") . ",
            '{$r['curriculum_status']}',
            '{$r['status']}',
            '{$r['subscription_type']}',
            '{$r['created_on']}',
            '{$r['customer_id']}',
            " . (int)$r['subscription_status_id'] . ",
            " . ($r['cancel_at'] ? "'{$r['cancel_at']}'" : "NULL") . ",
            " . (isset($r['cancel_at_period_end']) ? (int)$r['cancel_at_period_end'] : "NULL") . ",
            " . ($r['modified_on'] ? "'{$r['modified_on']}'" : "NULL") . ",
            " . ($r['curriculum_cancel_at'] ? "'{$r['curriculum_cancel_at']}'" : "NULL") . ",
            " . (int)$r['curriculum_cancel_at_period_end'] . "
        )";
        
        $db->execute($auditSql);
    }
}
http_response_code(200);
?>