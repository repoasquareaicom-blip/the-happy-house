 <?php
session_start();

$selectedGameMode = $_SESSION['game_mode'] ?? null;

if (!isset($_SESSION['currentGameIndex'])) {
    $_SESSION['currentGameIndex'] = 0;
}
$currentGameIndex = $_SESSION['currentGameIndex'];


$gamePages = [
    'game_find_my_robot_hardness_selection.php',
    'game_mountain_climber_hardness_selection.php',
    'game_cave_hardness_selection.php',
    'game_scavenger_hardness_selection.php',
    'game_song_hardness_selection.php'
];

$redirectPage = 'game-selection.php';

switch ($selectedGameMode) {
    case 'sequence_conclude':
        if ($currentGameIndex < count($gamePages)) {
            $redirectPage = $gamePages[$currentGameIndex];
            $_SESSION['currentGameIndex']++;
        } else {
            $_SESSION['currentGameIndex'] = 0;
            $redirectPage = 'start_activities.php';
            echo "<script>window.top.location.href = '$redirectPage';</script>";
            exit;
        }
        break;
    case 'sequence_loop':
        $redirectPage = $gamePages[$currentGameIndex % count($gamePages)];
        $_SESSION['currentGameIndex']++;
        break;
    case 'student_choice':
    case 'demo':
        
        $redirectPage = 'game-selection.php';
        break;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>The Happy House - Game Handler</title>
    <style>
        :root {
        --color1: #99B194;
        --color2: #D9E1A4;
        --color3: #6b8167;
        }
        html, body {
            margin: 0; 
            padding: 0; 
            width: 100%; 
            height: 100%;
            height: 100svh; /* Modern fix for mobile browser bars */
            overflow: hidden; 
            font-family: Calibri, sans-serif;
            display: flex; 
            align-items: center; 
            justify-content: center;
            background-color: #FFF0C7;
        }

        .frame-container {
            position: relative; 
            width: 100%; 
            height: 100%;
            height: 100svh;
            display: flex; 
            justify-content: center; 
            align-items: center;
            overflow: hidden;
        }
        /* 1. THE BORDER */
        #border-img { 
            max-height: 100%; 
            max-width: 100%;
            width: auto; 
            height: auto;
            object-fit: contain; /* Forces the whole image to fit inside the box */
            display: block; 
            z-index: 10;
            pointer-events: none; 
        }

        /* 2. THE GAME AREA */
        .inner-content {
            position: absolute; 
            z-index: 1;
            /* Center it exactly where the image is */
            top: 50%; left: 50%;
            transform: translate(-50%, -50%);
            width: auto; height: auto;
            display: flex; align-items: center; justify-content: center;
        }

        /* INCREASED Safe Zone Percentages */
        .safe-zone {
            /* These values are now larger to fill more of the frame */
            width: 92%;  
            height: 88%; 
            display: flex; align-items: center; justify-content: center;
            background-color: #fff;
            border-radius: 10px; /* Optional: rounds the corners of the game window slightly */
            overflow: hidden;
        }

        #game-viewport {
            width: 100%; height: 100%;
            border: none; background: transparent;
        }

        /* --- LOADING OVERLAY --- */
        #loadingOverlay {
            position: fixed; 
            top: 0; left: 0; 
            width: 100%; height: 100%;
            background: #FFF0C7; 
            display: none; /* Changed to none by default */
            flex-direction: column; 
            justify-content: center; 
            align-items: center;
            z-index: 9999; /* Ensure it's above everything */
            opacity: 0;
            transition: opacity 0.4s ease; 
        }
        .spinner {
            border:4px solid rgba(0,0,0,0.1);
            border-top:4px solid #6CB34B;
            border-radius:50%; width:45px; height:45px;
            animation: spin 1s linear infinite; margin-bottom:20px;
        }
        @keyframes spin { 0% { transform:rotate(0deg); } 100% { transform:rotate(360deg); } }
        @media (orientation: portrait) {
            #border-img {
                width: 100vw !important;
                height: auto !important;
                max-height: 100vh; /* Prevents it from going off-screen vertically */
            }
        }
        @keyframes float {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
            100% { transform: translateY(0px); }
        }
        #global-speaker-toggle {
            position: absolute; 
            top: 5%;  /* Adjust these % to fit perfectly in your SVG corner */
            left: 5%; 
            z-index: 1000; 
            cursor: pointer;
        }
        #explainerVideo, #bubblespeech {
            display: none;
        }
        .bubblespeech
        {
            z-index: 999;
            position: fixed; 
            display:block;
            height: 20vh;
            width:auto;
            cursor: pointer; 
        }
        #globalExplainerVideo {
            
            /* Positioning for bottom-left */
            position: fixed; 
            /* Removed transform as it's for centering */
            z-index: 999;
            /* Speech bubble visual (adjust width and height to match your SVG aspect ratio) */
            background-image: url('assets/images/bubble_small.png');
            background-size: contain; /* Scales the image to fit without cropping */
            background-repeat: no-repeat;
            background-position: center; /* Ensures the bubble image is centered within the div */
            /* Define the size of the bubble div */
            height: 15vh; /* As per your requirement */
            width: auto;/* Adjust this width based on your SVG's aspect ratio to prevent stretching */
            border-radius: 200px;
            /* Flexbox for centering the video INSIDE the bubble */
            /* You had 'display: flex;' commented out in your original code.
            If you want internal centering, ensure 'display: flex;' is active when the video is shown.
            The JS currently sets display: flex on show. */
            justify-content: center; /* Centers horizontally */
            align-items: center; /* Centers vertically */
        }
    </style>
</head>
<body>




    
    <div id="loadingOverlay">
        <div class="spinner"></div>
        <p>Preparing Activity...</p>
    </div>

    <div class="frame-container" id="main-frame">
        <img src="assets/images/bubble_small.svg" class="bubblespeech" id="globalBubblespeech" 
         style="display:none; position:absolute; z-index:999; height:20vh; width:auto;">
    
        <video id="globalExplainerVideo" 
            style="display:none; position:absolute; z-index:1000; height:15vh; width:auto; background-image: url('assets/images/bubble_small.svg'); background-size: contain; background-repeat: no-repeat; background-position: center; justify-content: center; align-items: center;">
            <source src="assets/videos/explainer.mp4" type="video/mp4">
        </video>

        <!-- Audio Icon -->
        <audio id="mainAudioPlayer" loop preloader="auto"></audio>
        <img src="" id="border-img" alt="Theme Border" style="display:none;">
        
       <div id="global-speaker-toggle" style="display:none; position:absolute; z-index:1000; cursor:pointer;">
            <img src="assets/images/speaker-unmuted.png" id="globalSpeakerImg" 
                style="position:relative; width: auto; filter: drop-shadow(2px 2px 4px rgba(0,0,0,0.3));">
        </div>

        <canvas id="globalTimerCanvas" width="300" height="300" 
        style="position:absolute; z-index:2000; pointer-events:none; display:none;"></canvas>

        <div class="inner-content" id="game-content">
             
            <div class="safe-zone">
                <iframe id="game-viewport" src="about:blank"></iframe>
            </div>
        </div>
    </div>
    <script>
        
        
        var isExplainerStarted = false;
        document.addEventListener('DOMContentLoaded', () => {
        const mainAudio = document.getElementById('mainAudioPlayer');
        const gSpeakerDiv = document.getElementById('global-speaker-toggle');
        const gSpeakerImg = document.getElementById('globalSpeakerImg');
        
        window.addEventListener('message', (e) => {
            if (e.data.action === 'ACTIVATE_GLOBAL_AUDIO') {
                // 1. Set the source and play
                mainAudio.src = e.data.audioSrc;
                mainAudio.play();
                gSpeakerImg.src = "assets/images/speaker-unmuted.png";

                // 2. Show the persistent speaker in the top-left corner
                gSpeakerDiv.style.display = 'block';
            }
            if (e.data.action === 'ACTIVATE_GLOBAL_AUDIO_ICON_ONLY') {
                // 1. Set the source and play
                mainAudio.src = e.data.audioSrc;
                mainAudio.play();
                gSpeakerImg.src = "assets/images/speaker-unmuted.png";

                // 2. Show the persistent speaker in the top-left corner
                gSpeakerDiv.style.display = 'block';
            }
            // Inside game_engine.php message listener
            if (e.data.action === 'TOGGLE_AUDIO') {
                const audio = document.getElementById('parentAudio');
                const isPaused = audio.paused;
                
                // Sync the iframe ghost audio
                const iframe = document.getElementById('game-viewport');
                iframe.contentWindow.postMessage({
                    action: 'SYNC_AUDIO_STATE',
                    paused: !isPaused 
                }, '*');
            }
        });

        window.addEventListener('message', (e) => {
            if (e.data.action === 'UPDATE_TIMER') {
                drawGlobalTimer(e.data.remaining, e.data.total);
            }
            else if (e.data.action === 'HIDE_TIMER') {
                const gTimer = document.getElementById('globalTimerCanvas');
                if (gTimer) {
                    gTimer.style.display = 'none';
                    // Also clear the context just in case it's shown again later
                    const ctx = gTimer.getContext("2d");
                    ctx.clearRect(0, 0, gTimer.width, gTimer.height);
                }
            }
        });

        function drawGlobalTimer(remaining, total) {
    const canvas = document.getElementById("globalTimerCanvas");
    const ctx = canvas.getContext("2d");
    
    // Internal drawing center for a 300x300 canvas
    let x = 163;
    let y = (theme === "dino") ? 130 : 138;

    const center = { x: x, y: y };
    const radius = 120;
    
    canvas.style.display = 'block';

    const percent = remaining / total;
    ctx.clearRect(0, 0, canvas.width, canvas.height);

    // 1. Background Circle
    ctx.beginPath();
    ctx.arc(center.x, center.y, radius, 0, 2 * Math.PI);
    ctx.fillStyle = "rgba(0,0,0,0.6)";
    ctx.fill();

    // 2. Progress Pie Slice
    ctx.beginPath();
    ctx.moveTo(center.x, center.y);
    ctx.arc(center.x, center.y, radius, -Math.PI / 2, -Math.PI / 2 + 2 * Math.PI * percent);
    ctx.lineTo(center.x, center.y);
    // Get the dynamic color from CSS variables
    ctx.fillStyle = getComputedStyle(document.documentElement).getPropertyValue('--color1').trim() || "#99B194";
    ctx.fill();

    // 3. Countdown Text
    ctx.fillStyle = "#fff";
    ctx.font = "bold 108px 'Comic Sans MS', cursive";
    ctx.textAlign = "center";
    ctx.textBaseline = "middle";
    ctx.fillText(Math.ceil(remaining), center.x, center.y);
}
        // Simple Toggle for the rest of the session
        gSpeakerDiv.addEventListener('click', () => {
            if (mainAudio.paused) {
                mainAudio.play();
                gSpeakerImg.src = "assets/images/speaker-unmuted.png";
            } else {
                mainAudio.pause();
                gSpeakerImg.src = "assets/images/speaker-muted.png";
            }
        });

        const gVideo = document.getElementById('globalExplainerVideo');
        const gBubble = document.getElementById('globalBubblespeech');
        
        
        window.addEventListener('message', (e) => {
            if (e.data.action === 'START_EXPLAINER') {
                if (isExplainerStarted) return;

                // 1. Set the video source dynamically from the game's request
                const source = document.getElementById('globalVideoSource');
                //source.src = e.data.videoSrc;
                gVideo.load(); // Reload the video with new source

                // 2. Show and Play
                gBubble.style.display = 'block';
                gVideo.style.display = 'flex';
                gVideo.currentTime = 0;
                
                gVideo.play().then(() => {
                    isExplainerStarted = true;
                }).catch(err => console.log("Video play blocked:", err));
            }
            else if (e.data.action === 'END_EXPLAINER') {
                 gBubble.style.display = 'none';
                gVideo.style.display = 'none';
                gVideo.pause();
            }
        });

        // Hide when finished
        gVideo.addEventListener('ended', () => {
            gVideo.style.display = 'none';
            gBubble.style.display = 'none';
            isExplainerStarted = false; 
        });

        // 1. Detect whenever the iframe changes (Back, Forward, or New Link)
        document.getElementById('game-viewport').addEventListener('load', function() {
            console.log("Iframe history change detected. Resetting media...");
            stopAllMedia(); 
        });

        // 2. Detect when the entire browser window is navigated via History
        window.addEventListener('pageshow', function(event) {
            // This handles the case where the parent page itself is moved in history
            stopAllMedia();
        });

        // The Master Cleanup Function
        function stopAllMedia() {
            // Stop Background Music
            const mainAudio = document.getElementById('mainAudioPlayer');
            const gSpeakerDiv = document.getElementById('global-speaker-toggle');
            const gSpeakerImg = document.getElementById('globalSpeakerImg');

            if (mainAudio) {
                mainAudio.pause();
                mainAudio.currentTime = 0;
                mainAudio.src = ""; // Critical: clears the buffer
            }
            
            // Hide the speaker UI
            if (gSpeakerDiv) gSpeakerDiv.style.display = 'none';
            if (gSpeakerImg) gSpeakerImg.src = "assets/images/speaker-muted.png";

            // Stop Explainer Video
            const gVideo = document.getElementById('globalExplainerVideo');
            const gBubble = document.getElementById('globalBubblespeech');

            if (gVideo) {
                gVideo.pause();
                gVideo.currentTime = 0;
                gVideo.style.display = 'none';
            }
            if (gBubble) gBubble.style.display = 'none';
            
            isExplainerStarted = false; // Reset the flag
        }
    });

     function syncDimensions() {
    requestAnimationFrame(() => {
        const borderImg = document.getElementById('border-img');
        
        // Safety check: If border is hidden or not loaded, center the game
        if (!borderImg || borderImg.style.display === 'none' || !borderImg.complete) {
            const gameContent = document.getElementById('game-content');
            gameContent.style.width = '100vw';
            gameContent.style.height = '100vh';
            gameContent.style.top = '50%';
            gameContent.style.left = '50%';
            return;
        }

        const rect = borderImg.getBoundingClientRect();
        const gVideo = document.getElementById('globalExplainerVideo');
        const gBubble = document.getElementById('globalBubblespeech');
        const gSpeaker = document.getElementById('global-speaker-toggle');
        const gameContent = document.getElementById('game-content');
        const gTimer = document.getElementById('globalTimerCanvas');
        
        if (rect.width > 0 && rect.height > 0) {
            // 1. Sync Game Area
            gameContent.style.width = Math.ceil(rect.width) + 'px';
            gameContent.style.height = Math.ceil(rect.height) + 'px';
            gameContent.style.top = Math.ceil(rect.top + (rect.height / 2)) + 'px';
            gameContent.style.left = Math.ceil(rect.left + (rect.width / 2)) + 'px';

            // 2. Position Speaker (Fixed for Portrait)
            // We use a percentage of the border width/height so it scales correctly
            const offsetMultiplier = 0.05; 
            const speakerX = rect.left + (rect.width * offsetMultiplier);
            const speakerY = rect.top + (rect.height * offsetMultiplier);
            
           const gSpeakerImg = document.getElementById('globalSpeakerImg');
    
            // 1. Scale the speaker size dynamically
            // In landscape, rect.height is smaller; in portrait, it's larger.
            // 12% of the border height is usually the "sweet spot" for icons.
          // Calculate scale relative to the original designed height (1668)
            const currentScale = rect.height / 1668;
            
            // Set the speaker size: Original size (175) * current scale
            const speakerSize = 323 * currentScale; 

            // Apply the width and ensure height stays proportional
            gSpeakerImg.style.width = Math.ceil(speakerSize) + 'px';
            gSpeakerImg.style.height = 'auto';

            // 2. Position the Speaker
            // Use negative offsets if you want it to "hug" the very corner of the SVG
            const speakerTopOffset = 0;  
            const speakerLeftOffset = 0; 

            gSpeaker.style.left = (rect.left + speakerLeftOffset) + 'px';
            gSpeaker.style.top = (rect.top + speakerTopOffset) + 'px';
            
// --- 5. POSITION EXPLAINER (BOTTOM-RIGHT) ---
// Scaled based on your 418px design height
const explainerHeight = 418 * currentScale;

// 1. Set the Bubble (Callout Image)
gBubble.style.height = Math.ceil(explainerHeight) + 'px';
gBubble.style.width = 'auto'; // Let the image maintain its natural aspect ratio

// We must get the bubble's width to align both elements to the right
const bubbleWidth = gBubble.offsetWidth || (explainerHeight * 1.3); 

const targetX = rect.right - bubbleWidth;
const targetY = rect.bottom - explainerHeight;

// 2. Position the Bubble
gBubble.style.left = targetX + 'px';
gBubble.style.top = targetY + 'px';

// 3. Set the Video to match the Bubble EXACTLY
gVideo.style.width = Math.ceil(bubbleWidth) + 'px';
gVideo.style.height = Math.ceil(explainerHeight) + 'px';
gVideo.style.left = targetX + 'px';
gVideo.style.top = targetY + 'px';

// 4. Shape the Video
// We use 50% for an oval/circle shape. 
// object-fit: cover ensures the video fills the bubble without stretching.
gVideo.style.borderRadius = '55%'; 
gVideo.style.objectFit = 'cover';
gVideo.style.position = 'absolute';
gVideo.style.zIndex = '1001'; // Ensure it sits on top of the bubble image

            /// Calculate scale relative to the original designed height (1668)


            // 1. Set the Timer Size: Original size (323) * current scale
            const timerSize = 323 * currentScale; 

            const gTimer = document.getElementById('globalTimerCanvas');
            gTimer.style.width = Math.ceil(timerSize) + 'px';
            gTimer.style.height = Math.ceil(timerSize) + 'px';

            // 2. Position it at the Top-Right corner of the border
            // We subtract the timerSize from rect.right to keep it inside the corner
            gTimer.style.top = rect.top + 'px';
            gTimer.style.left = (rect.right - timerSize) + 'px';
        }
    });
}
    </script>
    <script>
        const theme = localStorage.getItem('selectedTheme') || 'train'; 
        const borderImg = document.getElementById('border-img');
        const gameContent = document.getElementById('game-content');
        const iframe = document.getElementById('game-viewport');
        const overlay = document.getElementById('loadingOverlay');

        const themeColors = {
            dino: { color1: '#99B194', color2: '#D9E1A4', color3: '#6b8167' },
            fairy: { color1: '#7366C6', color2: '#C885D0', color3: '#4f4691' },
            pet: { color1: '#E78780', color2: '#ECCC96', color3: '#b3534c' },
            train: { color1: '#8AA9DA', color2: '#B0D0E4', color3: '#5c7aad' }
        };
        const { color1, color2, color3 } = themeColors[theme] || { color1: '#000', color2: '#FFF', color3: '#FFF' };
        document.documentElement.style.setProperty('--color1', color1);
        document.documentElement.style.setProperty('--color2', color2);
        document.documentElement.style.setProperty('--color3', color3);

        

        


        function initTheme() {
            
            //borderImg.src = `assets/images/${theme}.svg`;
            if (theme && theme !== "null" && theme.trim() !== "") {
                borderImg.src = `assets/images/${theme}.svg`;
                borderImg.style.display = 'block'; // Only show if we have a file
            } else {
                // No theme? Keep it hidden so the broken icon doesn't show
                borderImg.removeAttribute('src'); 
                borderImg.style.display = 'none';
                console.log("No theme found. Staying in borderless mode.");
            }
        }

       

        // Ensure it updates when the phone is rotated
        window.addEventListener('orientationchange', () => {
            setTimeout(syncDimensions, 300); 
        });
        function showLoading() {
            overlay.style.display = 'flex';
            // Trigger reflow to ensure the transition works
            void overlay.offsetWidth; 
            overlay.style.opacity = '1';
        }

        function hideLoading() {
            overlay.style.opacity = '0';
            setTimeout(() => {
                overlay.style.display = 'none';
            }, 400); // Matches the CSS transition time
        }
        function loadNextGame() {
            const nextUrl = "<?= $redirectPage ?>";
            
            // 1. Show overlay BEFORE changing the source
            showLoading();

            // 2. Set the new source
            iframe.src = nextUrl;

            // 3. Handle the load event
            iframe.onload = () => {
                // Add a slight delay so the user can actually see the "Preparing" message
                // This prevents a "flicker" on very fast connections
                setTimeout(hideLoading, 600);
            };
        }

        // Optional: If a game inside the iframe triggers a redirect itself, 
        // you can catch it here to show the loading screen again.
        iframe.addEventListener('unload', () => {
            showLoading();
        });

        window.addEventListener('load', () => {
            
            
            initTheme();
            borderImg.onload = () => {
                syncDimensions();
                loadNextGame();
            };
        });

        window.addEventListener('resize', () => {
            
            syncDimensions();
        });
    </script>
</body>
</html>