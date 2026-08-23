<?php
session_start();
include 'config/data.php';

// Initialize your Data class
$_data = new Data();

/**
 * 1. SECURITY & ACCESS CONTROL
 */
if (!isset($_SESSION['school_admin_email'])) {
    header("Location: login.php?msg=session_expired");
    exit;
}

$admin_email = $_SESSION['school_admin_email'];

/**
 * 2. SUBSCRIPTION VERIFICATION
 */
$sql_check = "SELECT id, school_name FROM school_master 
              WHERE school_admin_email = '$admin_email' 
              AND curriculum_status = 'active' 
              AND (curriculum_end IS NULL OR curriculum_end >= NOW()) 
              LIMIT 1";

$sub_check = $_data->getData($sql_check);

if (empty($sub_check)) {
    die("
    <div style='text-align:center; padding:100px; font-family:sans-serif; background:#f8f9fa; height:100vh;'>
        <i class='fas fa-lock' style='font-size:60px; color:#dc3545; margin-bottom:20px;'></i>
        <h1 style='color:#333;'>Access Restricted</h1>
        <p style='color:#666; font-size:18px;'>Your school does not have an active subscription to the Curriculum module.</p>
        <a href='dashboard.php' style='text-decoration:none; background:#007bff; color:#fff; padding:10px 25px; border-radius:5px;'>Return to Dashboard</a>
    </div>
    ");
}

$school_name = $sub_check[0]['school_name'];

/**
 * 3. FETCH CURRICULUM CONTENT
 */
$sql_content = "SELECT html_body FROM curriculum_content WHERE id = 1 LIMIT 1";
$res_content = $_data->getData($sql_content);
$curriculum_html = (!empty($res_content)) ? $res_content[0]['html_body'] : "<h3>Welcome!</h3><p>Content is currently being updated.</p>";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Curriculum | <?php echo htmlspecialchars($school_name); ?></title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        body, html {
            height: 100%;
            margin: 0;
            font-family: 'Segoe UI', Roboto, Arial, sans-serif;
        }

        /* 1. Background Image Setup */
        .bg-overlay {
            position: fixed;
            top: 0; left: 0;
            width: 100%; height: 100%;
            background: url('assets/images/the-happy-house-cover.jpg') no-repeat center center;
            background-size: cover;
            z-index: -1;
        }

        /* Sublte overlay to make text pop */
        .bg-overlay::after {
            content: "";
            position: absolute;
            top: 0; left: 0;
            width: 100%; height: 100%;
            background: rgba(0, 0, 0, 0.15); 
        }

        .main-wrapper {
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 40px 20px;
            min-height: 100vh;
        }

        /* 2. Top Center Logo with 450px Max Width */
        .logo-box {
            width: 100%;
            max-width: 450px;
            margin-bottom: 30px;
            text-align: center;
        }

        .intro-logo {
            width: 100%; /* Scales within the 450px max-width of parent */
            height: auto;
            filter: drop-shadow(0px 5px 15px rgba(0,0,0,0.2));
        }

        /* 3. Content Container (Glassmorphism) */
        .content-card {
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(10px);
            border-radius: 25px;
            padding: 50px;
            box-shadow: 0 15px 40px rgba(0,0,0,0.2);
            max-width: 1000px;
            width: 100%;
            border: 1px solid rgba(255, 255, 255, 0.3);
            margin-bottom: 50px;
        }

        .curriculum-body {
            color: #2c3e50;
            line-height: 1.6;
            font-size: 1.1rem;
        }

        /* Clean up Summernote paragraph gaps */
        .curriculum-body p {
            margin-bottom: 10px;
        }

        /* Ensure images inside content stay within the box */
        .curriculum-body img {
            max-width: 100%;
            height: auto;
            border-radius: 10px;
        }

        .back-nav {
            margin-top: 30px;
            text-align: center;
        }

        /* Mobile Adjustments */
        @media (max-width: 768px) {
            .logo-box { max-width: 80%; }
            .content-card { padding: 25px; border-radius: 15px; }
        }
    </style>
</head>
<body>

    <div class="bg-overlay"></div>

    <div class="main-wrapper">
        
        <!-- Center Top Logo -->
        <div class="logo-box">
            <a href="dashboard.php">
                <img src="assets/images/intro-bg-logo.png" alt="Happy House Logo" class="intro-logo">
            </a>
        </div>

        <!-- Content Box -->
        <div class="content-card">
            <div class="curriculum-body">
                <?php echo $curriculum_html; ?>
            </div>

            <div class="back-nav">
                <a href="school_admin_dashboard.php" class="btn btn-outline-primary btn-sm rounded-pill">
                    <i class="fas fa-chevron-left"></i> Back to Dashboard
                </a>
            </div>
        </div>
    </div>

</body>
</html>