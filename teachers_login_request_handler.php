<?php
session_start();


include 'config/data.php';
include 'class/log.php';
include 'class/class_teachers.php';
// Dynamic CSS
include 'assets/css/pages/dynamicss.php';
$_data = new Data();
$teachers = new Teachers($_data);
$error_message = "";
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
            //header("Location: start_activities.php");
            // Password is correct
            $class_room_ref = isset($_GET['classroomref']) ? trim($_GET['classroomref']) : ($_SESSION['classroom_ref_persistent'] ?? '');
            echo "<script>
                localStorage.setItem('logged_in_status', 'true');
                localStorage.setItem('classroom_ref', " . json_encode($class_room_ref) . ");
                window.location.href = 'start_activities.php';
            </script>";
            exit();
        } else {
            $login_error_message = "Invalid password. Please try again.";
        }
    }
    else{
        $login_error_message = "Invalid password. Please try again.";
    }
}
// Get and sanitize classroomref from query string
$class_room_ref = isset($_GET['classroomref']) ? trim($_GET['classroomref']) : '';
if (!empty($class_room_ref)) {
    // Validate classroomref
    $classroom_details = $teachers->validateClassRoomRef($class_room_ref);

    if ($classroom_details) {
        // Since you have LIMIT 1, we can just grab the first (and only) result
        $classroom_data = $classroom_details[0];
        
        // 1. Check if the subscription status is expired
        if ($classroom_data['subscription_status'] === 'expired') {
            $expiry_date = date("d M Y", strtotime($classroom_data['subscription_end']));
            $error_message = "⚠️ This school subscription expired on **$expiry_date**. Please contact your administrator to renew.";
            $_SESSION['temp_expiry_date'] = $expiry_date;
            header("Location: subscription_expired.php");	
        } else {
            // 2. Subscription is active, proceed with session setup
            $_SESSION['is_free'] = "0";
            $_SESSION['class_group_id'] = $classroom_data['id'];
            $_SESSION['school_id'] = $classroom_data['school_id'];
            $_SESSION['year_level_id'] = $classroom_data['year_id'];
            $_SESSION['class_room_caption'] = $classroom_data['class_group_caption'];
            $_SESSION['is_game_started'] = "1";

            session_write_close();
        }
    } else {
        // Case: The GUID simply doesn't exist in the database
        $error_message = "⚠️ Invalid Classroom Reference. Please check and try again.";
        $_SESSION['game_mode'] = "demo";
        header("Location: game_settings.php");	
        exit();
    }
} else {
    $error_message = "⚠️ Missing Classroom Reference. Please enter a valid reference.";
    $_SESSION['game_mode'] = "demo";
    header("Location: game_settings.php");	
    exit();
    
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teacher Login</title>
    <link rel="stylesheet" href="assets/css/global.css">
    <link rel="stylesheet" href="assets/css/main.css">
    <link rel="stylesheet" href="assets/css/pages/teachers.css">
    <link rel="stylesheet" href="assets/css/error.css"> <!-- Error Styling -->
    <!-- Favicons -->
    <link rel="icon" href="./favicons/favicon.ico" sizes="any" />
    <link rel="icon" href="./favicons/icon.svg" type="image/svg+xml" />
    <link rel="apple-touch-icon" href="./favicons/apple-touch-icon.png" />
    <link rel="manifest" href="./favicons/manifest.webmanifest" />
    <script src="assets/js/teachers.js"></script>
    <!-- Essential META Tags -->
    <meta property="og:title" content="The Happy House">
    <meta property="og:type" content="website" />
    <meta property="og:image" content="https://thehappyhouse.au/dev/assets/images/The-Happy-House-Logo.svg">
    <meta property="og:url" content="https://thehappyhouse.au">
     <style>
        body{
            background:url("assets/images/bg_game_settings.jpeg");
            background-size: cover;
            width:100%;
        }
        .input-with-icon {
            position: relative;
            width: 100%;
        }

        .input-icon-url, .input-icon-lock {
            position: absolute;
            left: 8px;
            top: 50%;
            transform: translateY(-50%);
            pointer-events: none; /* Allows clicking the input through the icon */
            transition: opacity 0.3s ease;
            font-size: 1.2em;
        }

        /* Ensure the input has padding on the right so text doesn't overlap the icon */
        .login-input {
            padding-right: 40px !important;
        }
    </style>
</head>
<body>
<?php if (!empty($error_message)) : ?>
    <div class="error-container">
        <div class="error-icon">⚠️</div>
        <div class="error-text">
            <?= $error_message; ?>
        </div>
    </div>
<?php 
    // Only exit if the error is "Missing Reference" or "Invalid URL"
    // If it's just "Expired", we might want to let them see the screen
    if (strpos($error_message, 'Missing') !== false || strpos($error_message, 'Invalid') !== false) {
        exit();
    }
endif; 
?>
    <div class="login-container page-container" id="loginContainer">
       <div class="login-box">
            <div class="login-logo"></div>

            <div id="passwordForm">
                <form method="post">
                    <div class="input-with-icon">
                        <input type="password" class="login-input" id="password" name="password" placeholder="Enter Password" oninput="toggleIcon()" />
                        <span class="input-icon-lock">🔒</span>
                    </div>
                    <button class="game-button" id="loginButton">Submit</button>
                    <?php if (!empty($login_error_message)) : ?>
                        <label class="error-message"><?= htmlspecialchars($login_error_message, ENT_QUOTES, 'UTF-8'); ?></label>
                    <?php endif; ?>
                </form>
                <div style="text-align:center; margin-top: 15px;">
                    <a href="#" id="troubleLink" style="color: #856404; font-size: 0.9em; text-decoration: underline;">Trouble Logging In?</a>
                </div>
            </div>

            <div id="urlHelperForm" style="display:none;">
                <p style="color:#856404;font-size: 0.9em; margin-bottom: 15px; text-align: center; line-height: 1.4;">
                    Every classroom has its own teacher login URL. Enter the teacher's login URL for your classroom below. Once entered, you will be able to enter the associated password.
                </p>
                <div class="input-with-icon">
                    <input type="text" class="login-input" id="teacherUrlInput" placeholder="Enter Teacher Login Link" oninput="toggleUrlIcon()" />
                    <span class="input-icon-url">🔗</span>
                </div>
                <button type="button" class="game-button" id="submitUrlButton">Submit</button>
                <div style="text-align:center; margin-top: 15px;">
                    <a href="#" id="backToLogin" style="color:#856404;opacity:1; font-size: 0.8em; text-decoration: none;">← Back to Password</a>
                </div>
            </div>
        </div>
    </div>
    <div class="student-container hidden page-container" id="studentContainer">
        <div class="student-box">
            <div class="login-logo"></div> <!-- Reusing the same logo -->
            <input type="text" class="login-input" placeholder="Enter Student Name">
            <button class="game-button" id="enterButton">Next</button>
        </div>
    </div>
    <div class="container-box" id="videoQuestionContainer" style="display:none">
        <!-- Video Section -->
        <div class="video-container">
            <video id="videoPlayer">
                <source src="http://commondatastorage.googleapis.com/gtv-videos-bucket/sample/ForBiggerMeltdowns.mp4" type="video/mp4">
                Your browser does not support the video tag.
            </video>
        </div>
        <!-- Question -->
        <h4 class="mt-4" style="color:#fff!important;font-size:1.4em;text-align:center">How are you feeling today on a scale from 0 to 10?</h4>
       <!-- Mood Selection with Emojis -->
       <div class="emoji-container">
            <span class="emoji-option" data-value="0">😞</span>
            <span class="emoji-option" data-value="1">😢</span>
            <span class="emoji-option" data-value="2">😟</span>
            <span class="emoji-option" data-value="3">🙁</span>
            <span class="emoji-option" data-value="4">😐</span>
            <span class="emoji-option" data-value="5">🙂</span>
            <span class="emoji-option" data-value="6">😊</span>
            <span class="emoji-option" data-value="7">😁</span>
            <span class="emoji-option" data-value="8">😃</span>
            <span class="emoji-option" data-value="9">🥳</span>
            <span class="emoji-option" data-value="10">🤩</span>
        </div>
        <!-- Selected Value Display -->
        <p class="scale-label mt-2" style="display:none">Selected: <span id="selectedValue">5</span></p>
               <!-- Start Game Button -->
        <div class="start-game-container">
            <button id="startGameBtn" class="game-button">Continue</button>
        </div>
    </div>
    <!-- Overlay -->
    <div id="welcomeOverlay" class="overlay" style="display:none">
        <div class="overlay-content">
            <p>Welcome and start wellbeing games.<br> Please click continue and pass the iPad to your student…</p>
            <button id="continueBtn" class="start-game-button">Continue</button>
        </div>
    </div>
    <div id="gameDifficulty" class="select-game-difficulty" style="display:none; text-align:center;">
        <h2>Select Difficulty Level</h2>
        <div class="slider-container">
            <div id="customSlider" class="slider-track">
                <div id="sliderThumb" class="slider-thumb"></div>
            </div>
        </div>
        <div class="progress-labels">
            <span id="label1">Beginner</span>
            <span id="label2" style="left:-10px;position:relative">Intermediate</span>
            <span id="label3">Expert</span>
        </div>
        <button id="startActualGameBtn" class="start-game-button">Start Game</button>
    </div>
    <div id="gameScreen" style="display:none">
        <h2 class="game-label">Game Work Area</h2>
    </div>
</body>
<script>
    function toggleIcon() {
    const input = document.getElementById('password');
    const icon = input.nextElementSibling; // the span.input-icon
    if (input.value.trim().length > 0) {
        icon.style.opacity = '0';
    } else {
        icon.style.opacity = '1';
    }
    }
    // Also call on page load to hide if input has prefilled value
    window.onload = () => toggleIcon();


    document.addEventListener('DOMContentLoaded', function() {
        const passwordForm = document.getElementById('passwordForm');
        const urlHelperForm = document.getElementById('urlHelperForm');
        const troubleLink = document.getElementById('troubleLink');
        const backToLogin = document.getElementById('backToLogin');
        const submitUrlButton = document.getElementById('submitUrlButton');
        const teacherUrlInput = document.getElementById('teacherUrlInput');

        // Toggle views
        troubleLink.addEventListener('click', (e) => {
            e.preventDefault();
            passwordForm.style.display = 'none';
            urlHelperForm.style.display = 'block';
        });

        backToLogin.addEventListener('click', (e) => {
            e.preventDefault();
            urlHelperForm.style.display = 'none';
            passwordForm.style.display = 'block';
        });

        // Handle the redirect
        submitUrlButton.addEventListener('click', function() {
            let inputVal = teacherUrlInput.value.trim();
            
            if (inputVal !== "") {
                // 1. Check if the URL already has a '?'
                let separator = inputVal.includes('?') ? '&' : '?';
                
                // 2. Append a unique timestamp (cache-buster) to force a refresh
                // This ensures the PHP logic re-runs every time
                let refreshUrl = inputVal + separator + "t=" + new Date().getTime();
                
                // 3. Perform the redirection
                window.location.href = refreshUrl;
            } else {
                alert("Please enter a valid Teacher Login Link.");
            }
        });
    });
    function toggleUrlIcon() {
        const input = document.getElementById('teacherUrlInput');
        const icon = input.nextElementSibling; // the span.input-icon-url
        if (input.value.trim().length > 0) {
            icon.style.opacity = '0';
        } else {
            icon.style.opacity = '1';
        }
    }
</script>

</html>
