<?php
session_start();
header('Content-Type: application/json');

include_once 'config/data.php';

if (
    !isset($_SESSION['admin_login_status']) ||
    $_SESSION['admin_login_status'] !== 'true'
) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
    exit;
}

$school_id = isset($_POST['school_id']) ? (int)$_POST['school_id'] : 0;
$new_password = isset($_POST['new_password']) ? trim($_POST['new_password']) : '';
$confirm_password = isset($_POST['confirm_password']) ? trim($_POST['confirm_password']) : '';

if ($school_id <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid school.']);
    exit;
}

if ($new_password === '' || $confirm_password === '') {
    echo json_encode(['status' => 'error', 'message' => 'Please enter and confirm the new password.']);
    exit;
}

if ($new_password !== $confirm_password) {
    echo json_encode(['status' => 'error', 'message' => 'Passwords do not match.']);
    exit;
}

if (!preg_match('/^(?=.*[A-Z])(?=.*\d)(?=.*[!@#$%^&*])[A-Za-z\d!@#$%^&*]{8,}$/', $new_password)) {
    echo json_encode(['status' => 'error', 'message' => 'Password must be at least 8 characters and include one uppercase letter, one number and one special character.']);
    exit;
}

$_data = new Data();
$school = $_data->getData("SELECT id, school_name, school_admin_email FROM school_master WHERE id = $school_id LIMIT 1");

if (empty($school)) {
    echo json_encode(['status' => 'error', 'message' => 'School not found.']);
    exit;
}

$hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
$updated = $_data->executePrepared(
    "UPDATE school_master
     SET password = :password,
         failed_login_attempts = 0,
         first_failed_login_at = NULL,
         login_locked_until = NULL
     WHERE id = :school_id",
    [
        ':password' => $hashed_password,
        ':school_id' => $school_id
    ]
);

if ($updated) {
    echo json_encode(['status' => 'success', 'message' => 'Password reset successfully. The school can now log in with the new password.']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Unable to update password.']);
}
