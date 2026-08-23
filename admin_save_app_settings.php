<?php
session_start();

include 'config/data.php';

$_data = new Data();

/* basic safety */
if (!isset($_POST['key'], $_POST['value'])) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Invalid request']);
    exit;
}

$key   = trim($_POST['key']);
$value = trim($_POST['value']);

/* Step A: read old value */
$oldValue = $_data->getAppSetting($key);

/* Step B: save new value */
$_data->saveAppSetting($key, $value);

/* Step C: audit log */
$_data->addAuditLog(
    $_SESSION['user_id'] ?? null,
    'UPDATE',
    $key,
    $oldValue,
    $value
);

echo json_encode(['status' => 'success']);