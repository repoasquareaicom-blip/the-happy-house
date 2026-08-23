<?php
session_start();
include 'config/data.php';
include 'class/log.php';
include 'class/class_teachers.php';

if(!isset($_SESSION['class_group_id'])){
    header("Location: teachers_login_request_handler.php"); 
    exit;  
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <title>The Happy House</title>
    
    <style>
        * { box-sizing: border-box; -webkit-tap-highlight-color: transparent; }
        
        body, html {
            margin: 0; padding: 0;
            width: 100%; height: 100%;
            font-family: 'Helvetica Neue', Arial, sans-serif;
            background: url("assets/images/bg_game_settings.jpeg") no-repeat center center fixed;
            background-size: cover;
            overflow: hidden; 
        }

        .app-viewport {
            height: 100vh;
            width: 100vw;
            display: flex; 
            justify-content: center; 
            align-items: center;
        }

        /* --- SETUP CARDS (Step 1 & 2) --- */
        .main-card {
            width: 90%;
            max-width: 800px;
            background: rgba(255, 255, 255, 0.96);
            backdrop-filter: blur(15px);
            border-radius: 30px;
            padding: 30px;
            box-shadow: 0 25px 50px rgba(0,0,0,0.2);
            border: 1px solid rgba(255,255,255,0.4);
        }

        /* --- SURVEY SCREEN (Step 3) --- */
        #videoQuestionContainer {
            position: fixed;
            top: 0; left: 0;
            width: 100vw;
            height: 100vh;
            display: none; 
            flex-direction: column;
            justify-content: space-evenly; 
            align-items: center;
            padding: 20px;
            background: transparent;
            z-index: 100;
        }

        .login-logo {
            width: 160px; height: 70px;
            background: url('assets/images/The-Happy-House-Logo.svg') no-repeat center / contain;
            margin: 0 auto 20px auto;
        }

        /* VIDEO */
        .video-wrapper {
            height: 35vh; 
            width: auto;
            max-width: 90%;
            border: 5px solid #fff;
            border-radius: 20px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.2);
            overflow: hidden;
            background: #000;
            flex-shrink: 0;
        }
        video { height: 100%; width: auto; display: block; }

        /* LABEL */
        .question-label {
            font-size: clamp(1rem, 2.5vh, 1.4rem);
            margin: 10px 0;
            padding: 10px 25px;
            background-color: rgba(255, 255, 255, 0.9);
            border-radius: 30px;
            max-width: 85%;
            text-align: center;
        }

        /* EMOJIS */
        .emoji-row {
            display: flex;
            flex-direction: row;
            flex-wrap: nowrap;
            justify-content: center;
            align-items: center;
            gap: 5px;
            width: 95%;
            max-width: 800px;
            background-color: rgba(255, 255, 255, 0.8);
            padding: 15px;
            border-radius: 25px;
        }

        .emoji-item {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            min-width: 0;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .emoji-option { width: 100%; max-width: 60px; height: auto; transition: transform 0.2s; }
        .emoji-item.active { transform: scale(1.2); z-index: 10; }
        .emoji-item.disabled { opacity: 0.3; filter: grayscale(0.8); pointer-events: none; }

        .emoji-number {
            margin-top: 5px;
            font-size: clamp(0.8rem, 2vh, 1.2rem);
            font-weight: bold;
            color: #222;
        }

        /* BUTTONS & INPUTS */
        .flex-center { display: flex; flex-direction: column; align-items: center; width: 100%; }
        .login-input {
            width: 100%; max-width: 400px; padding: 15px 25px; font-size: 1.1rem;
            border-radius: 50px; border: 2px solid #eee; text-align: center; outline: none;
            margin-bottom: 20px;
        }

        .game-button {
            background: #b77c72; color: #fff; border: none; padding: 16px 50px;
            font-size: 1.2rem; font-weight: bold; border-radius: 50px; cursor: pointer;
            box-shadow: 0 8px 20px rgba(183, 124, 114, 0.3); width: auto; min-width: 250px;
        }

        .setting-option {
            display: flex; align-items: center; background: rgba(255,255,255,0.8);
            padding: 15px; border-radius: 15px; margin-bottom: 10px;
            cursor: pointer; border: 2px solid #eee;
        }

        #calloutMessage { color: #d32f2f; font-weight: bold; margin-bottom: 10px; background: rgba(255,255,255,0.8); padding: 5px 15px; border-radius: 20px; }

        .hidden { display: none !important; }
        .fade-in { animation: fadeIn 0.4s forwards; }
        @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }

        @media (max-width: 600px) {
            .emoji-option { max-width: 40px; }
            .emoji-row { padding: 8px; gap: 2px; }
        }
    </style>
</head>
<body>

<audio id="bgAudio" loop preload="auto">
    <source src="assets/audio/game_bg_music.mp3" type="audio/mpeg">
</audio>

<div class="app-viewport">
    
    <div class="main-card fade-in" id="studentContainer">
        <div class="login-logo"></div>
        <div class="flex-center">
            <input type="text" class="login-input" id="studentNameInput" placeholder="Enter Student Name">
            <button class="game-button" onclick="goToSettings()">Next</button>
        </div>
    </div>

    <div class="main-card hidden" id="gameSettings" style="max-height: 90vh; overflow-y: auto; -webkit-overflow-scrolling: touch;">
    <div class="login-logo"></div>
    <h2 style="margin-top:0; text-align:center">Select Game Mode</h2>
    
    <div class="flex-center">
        <div class="settings-list" style="width: 100%; max-width: 600px; margin-bottom:20px">
            
            <label class="setting-option" onclick="handleModeSelection()">
                <input type="radio" name="gameMode" value="sequence_conclude">
                <div class="setting-text" style="margin-left:15px">
                    <strong>Sequence and Conclude</strong><br>
                    <small style="color: #666;">Games, activities, and wellbeing questions will run in sequence and conclude after the final game.</small>
                </div>
            </label>

            <label class="setting-option" onclick="handleModeSelection()">
                <input type="radio" name="gameMode" value="sequence_loop">
                <div class="setting-text" style="margin-left:15px">
                    <strong>Continuous Loop</strong><br>
                    <small style="color: #666;">Games and questions will run in sequence and loop continuously until stopped by the teacher.</small>
                </div>
            </label>

            <label class="setting-option" onclick="handleModeSelection()">
                <input type="radio" name="gameMode" value="student_choice">
                <div class="setting-text" style="margin-left:15px">
                    <strong>Student Choice Menu</strong><br>
                    <small style="color: #666;">Students choose activities one at a time, each followed by a question, then return to the menu.</small>
                </div>
            </label>

        </div>
        
        <button id="startButton" class="game-button hidden" onclick="goToWellbeing()" style="margin-bottom: 20px;">
            Start Session
        </button>
    </div>
</div>

<script>
    function handleModeSelection() {
        const startBtn = document.getElementById('startButton');
        
        // 1. Show the button
        startBtn.classList.remove('hidden');
        
        // 2. Smoothly scroll the button into view (Perfect for mobile)
        // We use a small timeout to ensure the DOM has updated the 'hidden' class
        setTimeout(() => {
            startBtn.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }, 100);
    }
</script>

<style>
    /* Ensure the labels are easy to tap on mobile */
    .setting-option {
        display: flex;
        align-items: flex-start; /* Better for multi-line text */
        background: rgba(255,255,255,0.8);
        padding: 15px;
        border-radius: 15px;
        margin-bottom: 12px;
        cursor: pointer;
        border: 2px solid #eee;
        transition: border-color 0.3s, background 0.3s;
    }

    /* Highlight the selected option */
    .setting-option:has(input:checked) {
        border-color: #b77c72;
        background: #fff;
        box-shadow: 0 4px 12px rgba(0,0,0,0.05);
    }

    /* Responsive adjustments */
    @media (max-height: 700px) {
        .main-card {
            padding: 20px;
        }
        h2 { font-size: 1.2rem; margin-bottom: 10px; }
        .setting-text strong { font-size: 0.95rem; }
        .setting-text small { font-size: 0.8rem; }
    }
</style>

    <div id="videoQuestionContainer">
        <div class="video-wrapper">
            <video id="videoPlayer" playsinline>
                <source src="assets/videos/welcome-bird.mp4" type="video/mp4">
            </video>
        </div>

        <h2 class="question-label">How are you feeling today on a scale from 1 to 10?</h2>
        
        <div class="emoji-row">
            <?php for($i=0; $i<=10; $i++): ?>
            <div class="emoji-item" onclick="selectEmoji(this, <?= $i ?>)">
                <img class="emoji-option" src="assets/images/smiley<?= $i ?>.svg" alt="<?= $i ?>">
                <span class="emoji-number"><?= $i ?></span>
            </div>
            <?php endfor; ?>
        </div>

        <div class="flex-center">
            <p id="calloutMessage" class="hidden">⚠️ Please finish the video and pick a smiley</p>
            <button id="startGameBtn" class="game-button hidden" onclick="submitAllData()">Continue</button>
        </div>
    </div>

</div>

<script>
    let selectedEmojiValue = "";
    let emojiSelected = false;

    const video = document.getElementById('videoPlayer');
    const startBtn = document.getElementById('startGameBtn');
    const callout = document.getElementById('calloutMessage');

    // STEP 1 -> STEP 2
    function goToSettings() {
        const name = document.getElementById('studentNameInput').value.trim();
        if(!name) return;
        localStorage.setItem('student_name', name);
        document.getElementById('studentContainer').classList.add('hidden');
        document.getElementById('gameSettings').classList.remove('hidden');
        document.getElementById('gameSettings').classList.add('fade-in');
    }

    // Handle Game Mode Selection & Mobile Scroll
    document.querySelectorAll('input[name="gameMode"]').forEach(radio => {
        radio.addEventListener('change', () => {
            const startSessionBtn = document.getElementById('startButton');
            startSessionBtn.classList.remove('hidden');
            
            // Auto-scroll for mobile users so they see the button
            setTimeout(() => {
                startSessionBtn.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }, 100);
        });
    });

    // STEP 2 -> STEP 3 (Wellbeing Survey)
    function goToWellbeing() {
        const mode = document.querySelector('input[name="gameMode"]:checked').value;
        localStorage.setItem('selectedGameMode', mode);
        
        document.getElementById('gameSettings').classList.add('hidden');
        
        const vContainer = document.getElementById('videoQuestionContainer');
        vContainer.style.display = 'flex';

        // Background music handling
        const bgAudio = document.getElementById('bgAudio');
        if(bgAudio) bgAudio.pause();

        video.play().catch(e => console.log("Auto-play blocked:", e));
    }

    // Emoji selection - Instantly shows the Continue button
    function selectEmoji(el, val) {
        if(emojiSelected) return; 

        emojiSelected = true;
        selectedEmojiValue = val;

        // Visual feedback (gray out others)
        document.querySelectorAll('.emoji-item').forEach(e => {
            if(e !== el) e.classList.add('disabled');
        });
        el.classList.add('active');

        // Logic Change: Show button immediately on click
        startBtn.classList.remove('hidden');
        callout.classList.add('hidden');

        // Optional: Auto-scroll to the continue button on mobile
        setTimeout(() => {
            startBtn.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }, 100);
    }

    function submitAllData() {
        const form = document.createElement("form");
        form.method = "POST";
        form.action = "start_game_handler.php";
        form.innerHTML = `
            <input type="hidden" name="student_name" value="${localStorage.getItem('student_name')}">
            <input type="hidden" name="overall" value="${selectedEmojiValue}">
            <input type="hidden" name="game_mode" value="${localStorage.getItem('selectedGameMode')}">
        `;
        document.body.appendChild(form);
        form.submit();
    }
</script>
</body>
</html>