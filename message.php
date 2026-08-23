<?php
// dynamic css
include 'assets/css/pages/dynamicss.php';

$erorr_title="";
$erorr_message="";
$error_number = $_POST['error_number'];
if ($error_number == 1001)
{
    $message_title = "Access Denied";
    $message_text = "Subscription is not active.";
    $message_link_text = "Back to Home";
    $message_link_url = "index.php";
}
if ($error_number == 1002)
{
    $message_title = "Alert";
    $message_text = "School admin account is already created.";
    $message_link_text = "Login to School Account";
    $message_link_url = "school_admin_login.php";
}
if ($error_number == 2001)
{
    $message_title = "Congratulations!";
    $message_text = "Your School Account is Ready";
    $message_link_text = "Go to Dashboard";
    $message_link_url = "school_admin_login.php";

}


?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>The Happy House</title>
    <link rel="stylesheet" href="assets/css/global.css">
    <link rel="stylesheet" href="assets/css/main.css">
    <link rel="stylesheet" href="assets/css/pages/error.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>

<body>
    <section class="banner-background">
        <section class="banner">
            <img src="assets/images/banner-bg.svg" alt="Banner Background" class="banner-image" onclick="changeImage(this,'banner')" style="display: block;">
        </section>
    </section>
    <section id="error-section" class="section">
        <div class="container"></div>
    </section>
    <!-- Modal Overlay for Error Message -->
    <div id="error-modal" class="modal">
        <div class="modal-content">
        <span id="close-btn" class="close-btn">&times;</span>
        <div class="modal-icon">
            <i class="fas fa-exclamation-triangle"></i>
        </div>
        <p id="error-title"><?php echo $message_title; ?></p>
        <p id="error-message"><?php echo $message_text; ?></p>
        <a href=<?php echo $message_link_url ?> class="go-back-link">
            <i class="fas fa-arrow-left"></i> <?php echo $message_link_text ?>
        </a>
    </div>

    </div>

   
    <script>
    // Display modal on page load
    window.onload = function () {
        document.getElementById('error-modal').style.display = 'flex'; // Change to flex to show modal
    };

    // Close the modal when clicking on the close button
    document.getElementById('close-btn').onclick = function () {
        document.getElementById('error-modal').style.display = 'none';
    };

    // Close the modal if clicked outside of it
    window.onclick = function (event) {
        if (event.target == document.getElementById('error-modal')) {
            document.getElementById('error-modal').style.display = 'none';
        }
    };
</script>

</body>

</html>
