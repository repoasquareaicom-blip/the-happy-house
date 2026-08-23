<?php
session_start();
header('Content-Type: application/json');

require 'vendor/autoload.php';
include 'config/data.php';

$stripeConfig = require 'config/stripe.php';
$mode = $stripeConfig['mode'] ?? 'live';
$baseUrl = $stripeConfig[$mode]['base_url'];

if (empty($stripeConfig[$mode]['secret_key'])) {
    http_response_code(500);
    echo json_encode(['error' => 'Stripe configuration incomplete.']);
    exit;
}

\Stripe\Stripe::setApiKey($stripeConfig[$mode]['secret_key']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $dataObj = new Data();
    
    $user_otp = isset($_POST['otp']) ? trim($_POST['otp']) : '';
    $product_key = isset($_POST['product_key']) ? $_POST['product_key'] : '';
    $email = $_SESSION['pending_email'] ?? '';

    if (empty($email)) {
        echo json_encode(['status' => 'error', 'message' => 'Session expired. Please re-enter your email on the home page.']);
        exit;
    }

    // 1. VERIFY OTP
    $sql = "SELECT id, otp_code FROM temp_school_master 
            WHERE email = '$email' 
            AND product_key = '$product_key' 
            AND is_verified = 0 
            AND otp_expires_at > NOW() 
            ORDER BY created_at DESC LIMIT 1";
    
    $pending = $dataObj->getData($sql);

    if (!empty($pending)) {
        $db_otp = $pending[0]['otp_code'];
        $temp_id = $pending[0]['id'];

        if ($user_otp === $db_otp) {
            
            // --- UPDATED SECURITY CHECK: Check product registration AND get Customer ID ---
            $checkFinal = $dataObj->getData("SELECT status, curriculum_status, customer_id FROM school_master WHERE school_admin_email = '$email' LIMIT 1");
            
            $existing_customer_id = null;

            if (!empty($checkFinal)) {
                $user = $checkFinal[0];
                $alreadyOwned = false;

                // Check based on the product they are buying
                if ($product_key === 'wellbeing_games' && $user['status'] === 'active') {
                    $alreadyOwned = true;
                } elseif ($product_key === 'curriculum' && $user['curriculum_status'] === 'active') {
                    $alreadyOwned = true;
                }

                if ($alreadyOwned) {
                    echo json_encode([
                        'status' => 'error', 
                        'message' => 'You already have an active subscription for this product. Please login to your dashboard.'
                    ]);
                    exit;
                }

                // Store the customer ID if it exists in our database
                if (!empty($user['customer_id'])) {
                    $existing_customer_id = $user['customer_id'];
                }
            }
            // --- END UPDATED SECURITY CHECK ---

            // 2. GET PRODUCT DETAILS
            $prodSql = "SELECT stripe_price_id, display_name FROM products_master WHERE product_key = '$product_key' LIMIT 1";
            $productData = $dataObj->getData($prodSql);
            
            if (empty($productData)) {
                echo json_encode(['status' => 'error', 'message' => 'Product configuration not found.']);
                exit;
            }
            
            $price_id = $productData[0]['stripe_price_id'];
            $display_name = $productData[0]['display_name'];

            try {
                // 3. CREATE STRIPE SESSION
                $sessionParams = [
                    'payment_method_types' => ['card'],
                    'line_items' => [[
                        'price' => $price_id,
                        'quantity' => 1,
                    ]],
                    'mode' => 'subscription',
                    'success_url' => $baseUrl . '/signup.php?s={CHECKOUT_SESSION_ID}&type=' . $product_key,
                    'cancel_url' => $baseUrl . '/index.php',
                    'metadata' => [
                        'temp_db_id' => $temp_id,
                        'product_key' => $product_key,
                        'product_name' => $display_name
                    ]
                ];

                // If customer exists in Stripe, use 'customer' ID. 
                // Otherwise, use 'customer_email' to let Stripe create a new one.
                if ($existing_customer_id) {
                    $sessionParams['customer'] = $existing_customer_id;
                } else {
                    $sessionParams['customer_email'] = $email;
                }

                $session = \Stripe\Checkout\Session::create($sessionParams);

                // 4. UPDATE TEMP TABLE
                $stripe_id = $session->id;
                $updateSql = "UPDATE temp_school_master SET 
                                is_verified = 1, 
                                verified_at = NOW(), 
                                stripe_session_id = '$stripe_id',
                                payment_status = 'pending' 
                              WHERE id = $temp_id";
                $dataObj->execute($updateSql);

                echo json_encode(['status' => 'success', 'checkout_url' => $session->url]);

            } catch (Exception $e) {
                echo json_encode(['status' => 'error', 'message' => 'Stripe Error: ' . $e->getMessage()]);
            }

        } else {
            echo json_encode(['status' => 'error', 'message' => 'Invalid OTP code.']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'No active verification found or OTP expired.']);
    }
}