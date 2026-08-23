<?php
session_start();

// Handle the PHP redirect signal
if (isset($_GET['action']) && $_GET['action'] === 'proceed_demo') {
    $_SESSION['is_free'] = "1";
    $_SESSION['is_game_started'] = "1";
    $_SESSION['game_mode'] = "demo";
    
    header("Location: game_settings.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>The Happy House</title>
    <style>
        body, html {
            margin: 0;
            padding: 0;
            width: 100%;
            height: 100%;
            overflow: hidden;
            background-color: #000;
            font-family: 'Helvetica Neue', Arial, sans-serif;
        }

        .intro-container {
            width: 100%;
            height: 100vh;
            background: url('assets/images/intro-bg.jpg') no-repeat center center;
            background-size: cover;
            display: flex;
            justify-content: center; 
            align-items: flex-start; 
        }

        .intro-logo {
            max-width: 80%;
            height: auto;
            animation: fadeInDown 1.2s ease-out;
        }

        @keyframes fadeInDown {
            from { opacity: 0; transform: translateY(-30px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Responsive Logo Sizes */
        @media (min-width: 769px) {
            .intro-logo {
                content: url('assets/images/intro-bg-logo.png');
                width: 500px; 
            }
            .intro-container { padding-top: 100px; }
        }

        @media (max-width: 768px) {
            .intro-logo {
                content: url('assets/images/logo.png');
                width: 250px;
            }
            .intro-container { padding-top: 60px; }
        }
    </style>
</head>
<body>

    <div class="intro-container">
        <img src="assets/images/intro-bg-logo.png" alt="Logo" class="intro-logo">
    </div>

    <script>
    window.onload = function() {
        setTimeout(function() {
            
            const savedRef = localStorage.getItem('classroom_ref');
            const isLoggedIn = localStorage.getItem('logged_in_status');
            
            /* STEP BY STEP LOGIC FOR ALL DEVICES:
               1. If we have a saved classroom reference and are logged in, 
                  go to the Teacher/Classroom handler.
               2. Otherwise, everyone (Mobile, Tablet, Desktop) goes to the 
                  Demo/Entry path.
            */
            
            if (isLoggedIn === 'true' && savedRef) {
                // Return to active session
                window.location.href = "teachers_login_request_handler.php?classroomref=" + encodeURIComponent(savedRef);
            } else {
                // New users or demo users
                window.location.href = "start_demo_activities.php?action=proceed_demo";
            }
            
        }, 2000);
    };
    </script>
</body>
</html>