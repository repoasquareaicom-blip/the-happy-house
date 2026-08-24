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

if ($school_id <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid school.']);
    exit;
}

$_data = new Data();
$school = $_data->getData("SELECT id FROM school_master WHERE id = $school_id LIMIT 1");

if (empty($school)) {
    echo json_encode(['status' => 'error', 'message' => 'School not found.']);
    exit;
}

$updated = $_data->executePrepared(
    "UPDATE school_master
     SET failed_login_attempts = 0,
         first_failed_login_at = NULL,
         login_locked_until = NULL
     WHERE id = :school_id",
    [':school_id' => $school_id]
);

if ($updated) {
    echo json_encode(['status' => 'success', 'message' => 'School login access unlocked.']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Unable to unlock account.']);
}
