<?php
session_start();
if(!isset($_SESSION['is_game_started'])){
    header("Location: teachers_login_request_handler.php"); 
    exit;
}

$currentIndex = $_SESSION['currentGameIndex'] ?? 0;

$wellbeingQuestions = [
    0 => "How worthwhile do you feel right now? On a scale from 1 to 10",
    1 => "How happy are you about your future? On a scale from 1 to 10",
    2 => "How much control do you feel you have in your life on a scale from 1 to 10?",
    3 => "How happy are you about getting on with the people you know? On a scale from 1 to 10",
    4 => "How happy are you with the things you want to be good at? On a scale from 1 to 10",
    
    
];

$questionText = $wellbeingQuestions[$currentIndex] ?? "Thank you for your feedback.";

// Determine prefix based on game_mode
$mode = $_SESSION['game_mode'] ?? 'sequence_conclude';
$prefix = ($mode === "sequence_conclude" || $mode === "sequence_loop") ? 's' : 'r';
$videoFile = $prefix . $currentIndex . '.mp4';

$isSubmitted = "0";
$session_complete = "0";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['selected_emoji'])) {
    include 'config/data.php';
    include 'class/log.php';
    include 'class/class_teachers.php';
    
    $data = new Data();
    $teachers = new Teachers($data);
    $emoji = intval($_POST['selected_emoji']);
    $row_id = $_SESSION['wellbeing_rowid']; 

    $fieldMap = [
        0 => "worthwhile",
        1 => "optimism",
        2 => "control",
        3 => "relationships",
        4 => "achieving"
    ];

    if (isset($fieldMap[$currentIndex])) {
        $column = $fieldMap[$currentIndex];
        $teachers->updateStudentWellbeingReportByRowId($row_id, $column, $emoji);
    }

    $_SESSION['currentGameIndex'] = intval($currentIndex) + 1;
    $_SESSION["game_finished"] = "0"; 
    if($_SESSION['currentGameIndex']<count($wellbeingQuestions))
    {
        echo "<script>window.top.location.href = 'game_engine.php';</script>";
        exit;
    }
    else
    {
        if ($mode === "sequence_conclude") {
            $session_complete = "1";
        } else {
            echo "<script>window.top.location.href = 'game_engine.php';</script>";
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Happy House - Wellbeing</title>
  <style>
    html, body {
      margin: 0;
      padding: 0;
      height: 100%;
      overflow: hidden;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      display: flex;
      justify-content: center;
      align-items: center;
      flex-direction: column;
      background: url("assets/images/happyhouse_doorway.svg") no-repeat center center / cover;
      background-size: cover;
    }
    .post-game-screens {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      z-index: 60;
    }
    .post-game-screen {
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      display: flex;
      opacity: 0;
      transform: translateX(100%);
      transition: all 0.6s cubic-bezier(0.4, 0, 0.2, 1);
      flex-direction: column;
      justify-content: center;
      align-items: center;
      padding: 20px;
      box-sizing: border-box;
      text-align: center;
      pointer-events: none;
    }
    .post-game-screen.active {
      transform: translateX(0);
      opacity: 1;
      pointer-events: auto;
    }
    .post-game-screen.exit-left {
      transform: translateX(-100%);
      opacity: 0;
      pointer-events: none;
    }
    /* Updated container for the Survey Screen */
#surveyScreen {
    display: flex;
    flex-direction: column;
    justify-content: space-evenly; /* Evenly spaces video, text, and emojis */
    align-items: center;
    height: 100vh; /* Force full screen height */
    padding: 10px;
    box-sizing: border-box;
}

/* Make the video smaller so it doesn't "push" things off screen */
#surveyScreen video {
    height: 35vh; /* Takes up 35% of vertical screen height */
    width: auto;  /* Maintains aspect ratio */
    max-width: 90%;
    border: 5px solid #fff;
    border-radius: 15px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.2);
    background: #000;
}

/* Slimmer question label */
.question-label {
    font-size: clamp(1rem, 2.5vh, 1.4rem); /* Sizes based on height */
    margin: 10px 0;
    padding: 8px 20px;
    background-color: rgba(255, 255, 255, 0.9);
    border-radius: 30px;
    max-width: 85%;
}

/* Emoji Row - Force horizontal and fit width */
.emoji-container {
    display: flex;
    flex-direction: row;
    flex-wrap: nowrap;
    justify-content: center;
    align-items: center;
    gap: 5px;
    
    /* This makes it responsive */
    width: 95%;             /* Full width on mobile/iPad */
    max-width: 800px!important;       /* <--- REDUCES WIDTH ON DESKTOP */
    
    background-color: rgba(255, 255, 255, 0.8);
    padding: 15px;
    border-radius: 20px;
    box-sizing: border-box;
    margin: 0 auto;         /* Keeps it centered */
}

.emoji-item {
    flex: 1;
    display: flex;
    flex-direction: column;
    align-items: center;
    min-width: 0;
}

.emoji-option {
    width: 100%;
    /* Increased slightly for desktop visibility within the 800px limit */
    max-width: 60px; 
    height: auto;
    cursor: pointer;
    transition: transform 0.2s;
}

/* Optional: Add hover for desktop users */
@media (hover: hover) {
    .emoji-option:hover {
        transform: scale(1.1);
    }
}

/* --- Mobile specific tweak --- */
@media (max-width: 600px) {
    .emoji-option {
        max-width: 40px; /* Ensures all 10 fit on narrow phones */
    }
    .emoji-container {
        padding: 8px;
        gap: 2px;
    }
}
    .emoji-item:hover { transform: scale(1.1); }
    .emoji-option { width: 65px; height: 65px; cursor: pointer; }
    .emoji-number { margin-top: 8px; font-size: 1.5em; font-weight: bold; color: #222; }
    
    .score-display-container {
      background: rgba(255, 255, 255, 0.9);
      border-radius: 30px;
      padding: 40px 60px;
      box-shadow: 0 15px 35px rgba(0,0,0,0.2);
    }
    .goodbye-message {
    font-family: Calibri, Arial, sans-serif;
    font-size: 1.8em;
    color: #2c3e50;
    margin: 15px;
    line-height: 1.2;
}

    

    
    
    .disabled { opacity: 0.5; filter: grayscale(1); }
  </style>
</head>
<link rel="stylesheet" href="assets/css/pages/teachers.css">
<link rel="stylesheet" href="assets/css/global.css">
<body>
<audio id="bgAudio" loop preload="auto">
    <source src="assets/audio/game_bg_music.mp3" type="audio/mpeg">
</audio>
<div class="post-game-screens">
    <?php if ($session_complete == "1"): ?>
            <div class="post-game-screen active" 
            style="background: url('assets/images/happyhouse_doorway.svg') no-repeat center center / cover; 
                    transform: translateX(0); 
                    opacity: 1; 
                    pointer-events: auto; 
                    z-index: 9999; 
                    position: fixed;">
            
            <div class="score-display-container" style="max-width: 600px; border: 5px solid #6b8167;">
                <h1 class="goodbye-message" style="font-size: 2.8em; color: #1976d2; font-weight: bold;">Session Complete</h1>
                
                <p class="goodbye-message" style="margin: 30px 0; font-size: 1.5em;">
                    Thank you for playing in The Happy House! <br><br>
                    <strong>Please pass the iPad back to your teacher</strong> so someone else can have a turn.
                </p>

                <button onclick="resetAndStart()" class="game-button" 
                        style="background-color: #4CAF50; padding: 20px 60px; width:auto; border-radius:35px; font-size: 1.8em;">
                    Start
                </button>
            </div>
        </div>

        <script>
            function resetAndStart() {
                // Redirect to engine with a reset flag
                window.top.location.href = 'game_engine.php?reset_session=1';
            }
        </script>
    <?php elseif ($isSubmitted == "1"): ?>
        <div class="post-game-screen active">
            <div class="score-display-container">
                <p class="goodbye-message">Thank You!</p>
                <a href="game_engine.php" id="continueBtn" target="_top" style="text-decoration:none; display:inline-block;">Next</a>
            </div>
        </div>
    <?php else: ?>
        <div class="post-game-screen active" id="introScreen">
            <div class="score-display-container">
                <h1 class="goodbye-message">Thanks for playing in The Happy House!</h1>
                <button id="continueBtn" class="game-button">Continue</button>
            </div>
        </div>

        <div class="post-game-screen" id="surveyScreen">
            <video id="surveyVideo" playsinline>
                <source src="assets/videos/<?php echo $videoFile; ?>" type="video/mp4" />
            </video>
            
            <h4 class="question-label"><?= htmlspecialchars($questionText) ?></h4>
            
            <div class="emoji-container">
                <?php for($i=0; $i<=10; $i++): ?>
                <div class="emoji-item">
                    <img class="emoji-option" src="assets/images/smiley<?= $i ?>.svg" data-value="<?= $i ?>">
                    <span class="emoji-number"><?= $i ?></span>
                </div>
                <?php endfor; ?>
            </div>
            <p id="calloutMessage" style="display: none; color:red; font-weight:bold; margin-top:10px;">⚠️ Please make a selection</p>
        </div>
    <?php endif; ?>
</div>

<form method="POST" id="surveyForm">
    <input type="hidden" name="selected_emoji" id="selectedEmoji">
</form>

<script>
    const continueBtn = document.getElementById('continueBtn');
    const intro = document.getElementById('introScreen');
    const survey = document.getElementById('surveyScreen');
    const video = document.getElementById('surveyVideo');
    const callout = document.getElementById('calloutMessage');
    const form = document.getElementById('surveyForm');
    const selectedEmojiInput = document.getElementById('selectedEmoji');

    let videoFinished = false;
    let emojiSelected = false;

    // 1. Move from Intro to Survey & play video
    if (continueBtn) {
        continueBtn.addEventListener('click', () => {
            intro.classList.remove('active');
            intro.classList.add('exit-left');
            survey.classList.add('active');

            video.muted = false;
            video.play().catch(e => console.log("Playback failed:", e));
        });
    }

    // 2. Detect video end
    video.addEventListener('ended', () => {
        videoFinished = true;
        trySubmit();
    });

    // 3. Emoji selection
    document.querySelectorAll(".emoji-option").forEach(btn => {
        btn.addEventListener("click", () => {
            if (emojiSelected) return;

            emojiSelected = true;
            selectedEmojiInput.value = btn.dataset.value;
            callout.style.display = "none";

            // Disable others visually
            document.querySelectorAll(".emoji-option").forEach(b => {
                b.style.pointerEvents = "none";
                if (b !== btn) b.classList.add("disabled");
            });

            trySubmit();
        });
    });

    // 4. Submit ONLY when both conditions are met
    function trySubmit() {
        if (videoFinished && emojiSelected) {
            form.submit();
        }
    }

    // 5. Prevent moving forward if video ends but no emoji selected
    video.addEventListener('ended', () => {
        if (!emojiSelected) {
            callout.style.display = "block";
        }
    });
   if (continueBtn) {
    continueBtn.addEventListener('click', () => {
        intro.classList.remove('active');
        intro.classList.add('exit-left');
        survey.classList.add('active');

        // ▶️ Stop background audio
        const bgAudio = document.getElementById('bgAudio');
        if (bgAudio) {
            bgAudio.pause();
            bgAudio.currentTime = 0;
        }

        // ▶️ Play survey video
        video.muted = false;
        video.play().catch(e => console.log("Video playback failed:", e));
    });
}


</script>


</body>
</html>