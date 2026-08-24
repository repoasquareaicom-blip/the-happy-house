<?php
session_start();
include 'config/data.php';
include 'class/log.php';
include 'class/subscription.php';
require_once 'class/mail_helper.php';

header('Content-Type: application/json');

$servername = "thehappyhouse.au";
$username = "thehappyhousedev";
$password = "T#2#@ppy#01$";
$dbname = "thehappyhouse";

$conn = new mysqli($servername, $username, $password, $dbname);
$_data = new Data();
$subscription = new Subscription($_data);
if ($conn->connect_error) {
    die(json_encode(["error" => $conn->connect_error]));
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $method = $_POST['method'];

    if ($method === 'getActiveSubscription') {
        getActiveSubscription($conn);
    } else if ($method === 'getCancelledSubscription') {
        getCancelledSubscription($conn);
    } else if ($method === 'getSchoolLoginSupport') {
        requireAdminSession();
        getSchoolLoginSupport($conn);
    } else if ($method === 'getLockedSchools') {
        requireAdminSession();
        getLockedSchools($conn);
    }
    else if($method == "updateProfile") 
    {
        updateProfile($conn);
    }
    else if($method == 'school_admin_reset_password_request')
    {
        resetPasswordRequest($subscription);
    }
    else{
        echo json_encode(["error" => "Invalid method"]);
    }
} else {
    echo json_encode(["error" => "Invalid request"]);
}

function requireAdminSession()
{
    if (
        !isset($_SESSION['admin_login_status']) ||
        $_SESSION['admin_login_status'] !== 'true'
    ) {
        http_response_code(401);
        echo json_encode(['error' => 'Unauthorized']);
        exit;
    }
}

function getActiveSubscription($conn) {
    // Select schools where EITHER the Games OR the Curriculum is currently active
    $sql = "SELECT id, school_name, school_admin_email,
        subscription_id, subscription_start, subscription_end, status, cancel_at,
        curriculum_sub_id, curriculum_start, curriculum_end, curriculum_status, curriculum_cancel_at
        FROM school_master 
        WHERE subscription_end > NOW() 
        OR curriculum_end > NOW();";
    
    $result = $conn->query($sql);

    $data = [];
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
    }

    // Always set the header for JSON responses
    header('Content-Type: application/json');
    echo json_encode($data);
}
function getCancelledSubscription($conn) {
    $sql = "SELECT id, school_name, school_admin_email,
        subscription_id, subscription_start, subscription_end, status, cancel_at,
        curriculum_sub_id, curriculum_start, curriculum_end, curriculum_status, curriculum_cancel_at
        FROM school_master 
        WHERE subscription_end > NOW() 
        OR curriculum_end > NOW();";
    $result = $conn->query($sql);

    $data = [];
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
    }

    echo json_encode($data);
}
function getLockedSchools($conn) {
    $sql = "SELECT id, school_name, school_admin_email, failed_login_attempts, login_locked_until
            FROM school_master
            WHERE login_locked_until IS NOT NULL
            AND login_locked_until > NOW()
            ORDER BY login_locked_until DESC";

    $result = $conn->query($sql);

    $data = [];
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
    }

    echo json_encode($data);
}
function getSchoolLoginSupport($conn) {
    $sql = "SELECT id, school_name, school_admin_email, failed_login_attempts,
            IF(login_locked_until IS NOT NULL AND login_locked_until > NOW(), 'Locked', 'Active') AS login_status
            FROM school_master
            WHERE school_admin_email IS NOT NULL
            AND school_admin_email <> ''
            ORDER BY school_name ASC";

    $result = $conn->query($sql);

    $data = [];
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
    }

    echo json_encode($data);
}
function updateProfile($conn)
{
    try {
        $schoolName = $_POST['schoolName'];
        $contactPersonName = $_POST['contactPersonName'];
        $emailID = $_POST['emailID'];
        $schoolID = $_POST['schoolId'];
        
        $_query = "UPDATE school_master SET 
        school_admin_email='$emailID',
        name='$contactPersonName',
        school_name='$schoolName',
        modified_on = NOW()
        WHERE id = '$schoolID'";
        
        $_status = $conn->query($_query);

        header('Content-Type: application/json');
        if ($_status) {
            $logMessage = "Profile updated successfully for email: $emailID";
            file_put_contents('logs.txt', date("F j, Y, g:i a") . " Method: updateProfile - DB Trans:, Page: school_admin_dashboard.php, Message: OK $logMessage - $_query\n", FILE_APPEND);
            echo json_encode(['status' => 'OK', 'message' => $logMessage]);
        } else {
            $logMessage = "Profile update failed for email: $emailID";
            file_put_contents('logs.txt', date("F j, Y, g:i a") . " Method: updateProfile - DB Trans:, Page: school_admin_dashboard.php, Message: ERR $logMessage - $_query\n", FILE_APPEND);
            echo json_encode(['status' => 'ERROR', 'message' => $logMessage]);
        }
        exit;
    } catch (Exception $e) {
        file_put_contents('logs.txt', date("F j, Y, g:i a") . " ERR - Method: updateProfile - DB Trans:, Page: school_admin_dashboard.php, Message: " . $e->getMessage() . " - $_query\n", FILE_APPEND);
        header('Content-Type: application/json');
        echo json_encode(['status' => 'ERROR', 'message' => 'An exception occurred: ' . $e->getMessage()]);
        exit;
    }
}
function resetPasswordRequest($subscription)
{
    try {
        // Get email from POST data
        $email = $_POST['email'] ?? '';

        if (empty($email)) {
            echo json_encode(['status' => 'error', 'message' => 'Email is required.']);
            return;
        }

        // Retrieve user data
        $user_data = $subscription->get_user_by_email($email);

        if ($user_data) {
            foreach ($user_data as $_user) {
                $user_name = $_user['school_name'];
            }

            // Generate password reset token and expiry time
            $reset_token = bin2hex(random_bytes(16)); // Generate a secure random token
            $reset_expiry = time() + (15 * 60); // Reset token expiry time: 15 minutes

            // Save the token and expiry time in the database
            $subscription->save_password_reset_token($email, $reset_token, $reset_expiry, 'S');

            // Initialize the EmailHelper class
            $emailHelper = new EmailHelper();

            // Define the recipient and subject
            $recipient = $email;
            $subject = 'Admin Password Reset Request';

            // Define the template file for email message
            $templateFile = 'template_school_admin_password_reset_mail_message.tl';

            // Variables to replace in the template
            $variables = [
                'username' => $user_name,
                'reset_link' => "https://thehappyhouse.au/reset_password.php?token=$reset_token",
                'expiry_time' => 15
            ];

            // Fetch the email message from the template
            $message = $emailHelper->getEmailTemplate($templateFile, $variables);

            // Send the email
            $response = $emailHelper->sendEmail($recipient, $subject, $message);
            $responseData = json_decode($response['message'], true);

            if ($responseData['status'] === 'success') {
                echo json_encode(['status' => 'success', 'message' => 'A password reset link has been sent to your email.']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Failed to send the reset email. Please try again.']);
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => 'No user found with this email.']);
        }
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => 'An error occurred: ' . $e->getMessage()]);
    }
}


$conn->close();
?>
