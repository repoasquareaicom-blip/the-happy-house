<?php
session_start();
include 'config/data.php';
include 'class/log.php';
include 'class/class_teachers.php';
// Dynamic CSS
include 'assets/css/pages/dynamicss.php';

$error_message = "";
$login_error_message = "";

// Check if session exists
if (!isset($_SESSION['class_group_id']) || !isset($_SESSION['school_id'])) {
    $error_message = "Unauthorized access or invalid URL. Please contact administrator for class room access.";
}

$_data = new Data();
$teachers = new Teachers($_data);

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $password = $_POST['password'] ?? '';

    $class_group_id = $_SESSION['class_group_id'];
    $school_id = $_SESSION['school_id'];

    // Get class_room_ref using Log class method
    $class_room_details = $teachers->getClassRoomRef($class_group_id);
    $password_in_db = "";
    if ($class_room_details)
    {
        foreach ($class_room_details as $classroom_data) {
            $password_in_db =  $classroom_data['password'];
        }
        if($password === $password_in_db) {
            // Password is correct, redirect to start activity page
            header("Location: start_activities.php");
            exit();
        } else {
            $login_error_message = "Invalid password. Please try again.";
        }
    }
    else{
        $login_error_message = "Invalid password. Please try again.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teachers Admin Login</title>
    <link rel="stylesheet" href="assets/css/global.css">
    <link rel="stylesheet" href="assets/css/main.css">
    <link rel="stylesheet" href="assets/css/pages/teachers.css">
    <link rel="stylesheet" href="assets/css/error.css"> <!-- Error Styling -->

    <!-- Favicons -->
    <link rel="icon" href="./favicons/favicon.ico" sizes="any" />
    <link rel="icon" href="./favicons/icon.svg" type="image/svg+xml" />
    <link rel="apple-touch-icon" href="./favicons/apple-touch-icon.png" />
    <link rel="manifest" href="./favicons/manifest.webmanifest" />

    <!-- Essential META Tags -->
    <meta property="og:title" content="The Happy House">
    <meta property="og:type" content="website" />
    <meta property="og:image" content="https://thehappyhouse.au/dev/assets/images/The-Happy-House-Logo.svg">
    <meta property="og:url" content="https://thehappyhouse.au">
</head>
<body>

    <?php if (!empty($error_message)) : ?>
        <div class="error-container">
            <div class="error-icon">⚠️</div>
            <div class="error-text"><?= htmlspecialchars($error_message, ENT_QUOTES, 'UTF-8'); ?></div>
        </div>
    <?php endif; ?>

    <!-- Login Container -->
    <div class="login-container">
        <div class="login-logo"></div>
        <h2 class="login-title">Class Access</h2>
        <form method="POST">
            <input type="password" name="password" placeholder="Enter Password" class="login-input" required>
            <button type="submit" class="login-button">Login</button>
            <?php if (!empty($login_error_message)) : ?>
                <label class="error-message"><?= htmlspecialchars($login_error_message, ENT_QUOTES, 'UTF-8'); ?></label>
            <?php endif; ?>
        </form>
    </div>

</body>
<style>
    /* Ensure full height for proper centering */
html, body {
    height: 100%;
    margin: 0;
    padding: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-direction: column;

}
</style>
</html>

