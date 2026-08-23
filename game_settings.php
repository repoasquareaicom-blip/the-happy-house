<?php
session_start();
$game_mode = $_SESSION['game_mode']; // default non-free

include 'config/data.php';
$_data = new Data();

/* fetch classroom setup toggle */
$classroom_enabled = $_data->getAppSetting('enable_classroom_setup') ?? '0';

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Game Settings</title>
  <style>
    html, body {
      height: 100%;
      margin: 0;
      padding: 0;
      background-color: #FFF0C7;
      background-image: url('/assets/images/game_bg.png');
      background-size: cover;
      background-position: center;
      font-family: 'calibri', cursive, sans-serif;
      overflow: hidden;
    }
    .container {
      position: relative;
      width: 100%;
      height: 100%;
      overflow: hidden;
      justify-content: center; /* horizontal center */
    }
    .screen {
      position: absolute;
      width: 100%;
      height: 100%;
      top: 0;
      left: 0;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      gap: 30px;
      transition: transform 0.6s ease-in-out;
    }
    .screen-hidden {
      transform: translateX(100%);
    }
    .screen-active {
      transform: translateX(0);
    }
    .screen-out {
      transform: translateX(-100%);
    }
    .game-settings {
      font-size: 40px;
      font-weight: bold;
      color: #333;
      text-shadow: 2px 2px 5px rgba(0,0,0,0.3);
    }
    .themes-grid, .games-grid {
      display: grid;
      gap: 25px;
    }
    .themes-grid {
      grid-template-columns: repeat(2, 1fr);
    }
    .games-grid {
      grid-template-columns: repeat(2, 1fr);
    }
    .theme-box, .game-box {
      width: calc(25vh);
      height: calc(25vh);
      font-size: 2.5vh; /* Set base font-size based on viewport height */
      display: flex;
      align-items: center;
      justify-content: center;
      background-size: cover;
      background-position: center;
      border-radius: 12px;
      cursor: pointer;
      transition: transform 0.3s ease, box-shadow 0.3s ease;
      border: 4px solid #fff;
      box-shadow: 5px 5px 5px rgba(0, 0, 0, 0.2);
    }
    .theme-label {
      color: white;
      font-size: 1em; /* Now scales with the .theme-box size */
      font-weight: bold;
      text-shadow: 1px 1px 4px rgba(0, 0, 0, 0.6);
      text-align: center;
    }
    .theme-box:active, .game-box:active {
      transform: scale(0.95);
    }
    .games-extra {
      grid-column: span 2;
      justify-self: center;
    }
    #game-screen {
    height: 100vh;
    background-size: contain;
    background-repeat: no-repeat;
    background-position: center;
    margin: 0 auto; /* center horizontally if needed */
    margin-left: auto;
    margin-right: auto;
    position: relative;
    }
    .theme-label-bottom {
    margin-top: 20px;
    font-size: 22px;
    font-weight: bold;
    color: #222;
    background: rgba(255,255,255,0.8);
    padding: 10px 20px;
    border-radius: 10px;
    }
    .game-box {
    position: relative;
    display: flex;
    align-items: flex-end;
    justify-content: center;
    padding-bottom: 10px;
    box-shadow: 0 4px 10px rgba(0,0,0,0.2);
    }
    .game-label {
    background: rgba(255, 255, 255, 0.85);
    padding: 6px 12px;
    border-radius: 10px;
    font-size: 16px;
    font-weight: bold;
    text-align: center;
    margin-bottom: 5px;
    }
    @keyframes slideLeft {
    from {
        transform: translateX(0);
    }
    to {
        transform: translateX(-100%);
        opacity: 0;
    }
    }
    .slide-left {
    animation: slideLeft 0.5s ease forwards;
    }
    @media (max-width: 600px) {
      .themes-grid, .games-grid {
        grid-template-columns: 1fr 1fr;
      }
      .theme-box, .game-box {
        width: 120px;
        height: 120px;
      }
    }
    /* Demo Welcome Overlay */
    #demo-overlay {
      position: fixed;
      inset: 0;
      background: url("assets/images/demo-welcome-bg.jpg") no-repeat center center / cover;
      display: flex;
      align-items: center;
      justify-content: center;
      z-index: 9999;
      animation: fadeIn 0.6s ease;
    }
    .demo-content {
    text-align: center;
    background: rgba(255,255,255,0.85);
    padding: 40px;
    border-radius: 20px;
    max-width: 600px;
    font-family: Calibri, "Segoe UI", Tahoma, Arial, sans-serif;
}
    .demo-logo {
      width: 140px;
      margin-bottom: 20px;
    }
    .demo-content h1 {
      font-size: 32px;
      margin-bottom: 15px;
    }
    .demo-content p {
      font-size: 18px;
      line-height: 1.4;
      margin-bottom: 30px;
    }
    .demo-continue-btn {
      font-size: 20px;
      padding: 12px 40px;
      border-radius: 30px;
      border: none;
      background: #ff9f1c;
      color: #fff;
      cursor: pointer;
    }
    /* Fade out */
   .fade-out {
      opacity: 0;
      transition: opacity 0.6s ease;
    }
    @keyframes fadeIn {
      from { opacity: 0 }
      to { opacity: 1 }
    }
    #theme-loader {
      position: fixed;
      inset: 0;
      background: rgba(0,0,0,0.5);
      display: none;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      z-index: 10000; /* higher than overlay */
    }
    /* Dots container */
    .happy-loader {
      display: flex;
      gap: 12px;
    }
    /* Individual dots */
    .happy-loader span {
      width: 18px;
      height: 18px;
      background: #ff9f1c;
      border-radius: 50%;
      animation: bounce 0.6s infinite alternate;
    }
    .happy-loader span:nth-child(2) {
      animation-delay: 0.2s;
    }
    .happy-loader span:nth-child(3) {
      animation-delay: 0.4s;
    }
    @keyframes bounce {
      from {
        transform: translateY(0);
        opacity: 0.5;
      }
      to {
        transform: translateY(-20px);
        opacity: 1;
      }
    }
    /* Classroom Info Button (Demo Overlay Bottom) */
    .classroom-info-btn {
      position: fixed;
      bottom: 20px;
      left: 50%;
      transform: translateX(-50%);
      background: #d9d9d9;
      color: #333;
      padding: 12px 24px;
      border-radius: 30px;
      font-size: 20px;
      font-family: 'calibri', cursive, sans-serif;
      display: flex;
      align-items: center;
      gap: 10px;
      cursor: default;
      box-shadow: 0 4px 10px rgba(0,0,0,0.2);
      z-index: 10001;
      cursor:pointer;
    }

    .classroom-info-btn .info-icon {
      font-size: 18px;
    }
    .classroom-modal {
  position: fixed;
  inset: 0;
  background: rgba(0,0,0,0.5);
  display: none;
  align-items: center;
  justify-content: center;
  z-index: 10001;
}

.classroom-modal-content {
  background: #fff;
  width: 90%;
  max-width: 700px;
  border-radius: 20px;
  padding: 30px 35px;
  text-align: center;
  animation: fadeIn 0.3s ease;
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.modal-title {
  font-size: 22px;
  font-weight: bold;
}

.modal-close {
  font-size: 26px;
  cursor: pointer;
}

.modal-text {
  margin: 30px 0 20px;
  font-size: 18px;
  
}

.modal-input-wrap {
  display: flex;
  gap: 10px;
  justify-content: center;
  flex-wrap: wrap;
}

.modal-input-wrap input {
  width: 320px;
  max-width: 100%;
  padding: 12px 15px;
  border-radius: 8px;
  border: 1px solid #ccc;
  font-size: 16px;
}

.modal-continue-btn {
  padding: 12px 28px;
  border-radius: 25px;
  border: none;
  background: #ff9f1c;
  color: #fff;
  font-size: 16px;
  cursor: pointer;
}

.modal-footer-text {
  margin-top: 25px;
  font-size: 13px;
  color: #777;
}
  </style>
</head>
<body>
  <audio id="bgAudio" loop preload="auto">
    <source src="assets/audio/game_bg_music.mp3" type="audio/mpeg">
</audio>
  <?php if ($game_mode == "demo"): ?>
  <div id="demo-overlay">
    <div class="demo-content">
      <img src="assets/images/logo.png" class="demo-logo" alt="Logo">
      <h1>Welcome to The Happy House!</h1>
      <p>
        Nurturing Young Minds Through Calm, Well-being, <br>
        & Engaging Game Activities.
      </p>
      <button class="demo-continue-btn" onclick="continueDemo()">Continue</button>
    </div>
    <?php if ($classroom_enabled == '1'): ?>
      <div class="classroom-info-btn">
        <!-- <span class="info-icon">ℹ️</span> -->
        <span>Classroom Setup Info</span>
      </div>
    <?php endif; ?>
  </div>
  <?php endif; ?>
  <div class="container">
    <!-- Theme Selection Screen -->
    <div class="screen screen-active" id="theme-screen">
      <div class="game-settings">Choose Your Theme</div>
      <div class="themes-grid">
       <div class="theme-box" style="background-image: url('assets/images/trains-thumb.svg');" onclick="goToGameSelection('train')">
          <span class="theme-label">Trains</span>
        </div>
        <div class="theme-box" style="background-image: url('assets/images/pets-thumb.svg');" onclick="goToGameSelection('pet')">
          <span class="theme-label">Pets</span>
        </div>
        <div class="theme-box" style="background-image: url('assets/images/fantasy-thumb.svg');" onclick="goToGameSelection('fairy')">
          <span class="theme-label">Fantasy</span>
        </div>
        <div class="theme-box" style="background-image: url('assets/images/dino-thumb.svg');" onclick="goToGameSelection('dino')">
          <span class="theme-label">Dinosaurs</span>
        </div>
      </div>
    </div>
 </div>
 <script>
  let selectedTheme = '';
  function goToGames(theme) {
    selectedTheme = theme;
    const themeColors = {
    dino: {
        color1: '#99B194',
        color2: '#D9E1A4' 
    },
    fairy: {
        color1: '#7366C6',
        color2: '#C885D0' 
    },
    pet: {
        color1: '#E78780',
        color2: '#ECCC96' 
    },
    train:{
        color1:'#8AA9DA',
        color2:'#B0D0E4'
    }
    // Add more themes as needed
    };
    const { color1, color2 } = themeColors[theme] || {
    color1: '#000',
    color2: '#FFF'
    };
    document.documentElement.style.setProperty('--color1', color1);
    document.documentElement.style.setProperty('--color2', color2);
    const themeScreen = document.getElementById('theme-screen');
    const gameScreen = document.getElementById('game-screen');
    const backScreen = document.getElementById('back-btn');
    // Change game screen background to the selected theme.svg
    gameScreen.style.backgroundImage = `url('assets/images/${theme}.svg')`;
    const image = new Image();
    image.src = `assets/images/${theme}.svg`;
    image.onload = () => {
      const aspectRatio = image.width / image.height;
      const height = window.innerHeight;
      const width = height * aspectRatio;
      gameScreen.style.width = `${width}px`;
      gameScreen.style.backgroundImage = `url('assets/images/${theme}.svg')`;
    };
    gameScreen.style.backgroundColor = `${color1}`;
    backScreen.style.backgroundColor = `${color2}`;
    themeScreen.classList.remove('screen-active');
    themeScreen.classList.add('screen-out');
    gameScreen.classList.remove('screen-hidden');
    gameScreen.classList.add('screen-active');
  }
let isNavigating = false;
function goToGameSelection(themeid) {
  if (isNavigating) return;
  isNavigating = true;
  localStorage.setItem('selectedTheme', themeid);
  const loader = document.getElementById('theme-loader');
  loader.style.display = 'flex';
  requestAnimationFrame(() => {
    setTimeout(() => {
      window.location.href = "game_engine.php";
    }, 1000);
  });
}
  function goBackToThemes() {
  const themeScreen = document.getElementById('theme-screen');
  const gameScreen = document.getElementById('game-screen');
  // Reset background image of game screen (optional)
  gameScreen.style.backgroundImage = '';
  gameScreen.classList.remove('screen-active');
  gameScreen.classList.add('screen-hidden');
  themeScreen.classList.remove('screen-out');
  themeScreen.classList.add('screen-active');
}
function continueDemo() {
  const overlay = document.getElementById('demo-overlay');
  overlay.classList.add('fade-out');
  setTimeout(() => {
    overlay.remove();
  }, 600);
}
window.addEventListener("load", () => {
    const bgAudio = document.getElementById("bgAudio");
    bgAudio.volume = 0.4;
    bgAudio.play();
});
</script>
<div id="theme-loader">
  <div class="happy-loader">
    <span></span>
    <span></span>
    <span></span>
  </div>
</div>
<!-- Classroom Setup Modal -->
<div id="classroomModal" class="classroom-modal">
  <div class="classroom-modal-content demo-content" >
    
    <!-- Header -->
    <div class="modal-header">
      
        <h1 style="width:100%">Classroom Setup Info
    </h1>
      <span class="modal-close" onclick="closeClassroomModal()">×</span>
    </div>

    <!-- Body -->
    <p class>
      Classrooms that are part of The Happy House Wellbeing Program
      can enter their classroom URL below.
    </p>

    <div class="modal-input-wrap">
      <input type="text" placeholder="Enter your classroom URL" id="classroomUrl" />
      <button class="modal-continue-btn" id="classroomContinueBtn">Continue</button>
    </div>

    <!-- Footer -->
    <p >
      If you are a school and would like more information about
      The Happy House Wellbeing Program, please visit The Happy House online.
    </p>

  </div>
</div>
</body>
<script>

document.addEventListener('DOMContentLoaded', () => {
  const infoBtn = document.querySelector('.classroom-info-btn');
  if (infoBtn) {
    infoBtn.addEventListener('click', openClassroomModal);
  }
});

function openClassroomModal() {
  document.getElementById('classroomModal').style.display = 'flex';
}

function closeClassroomModal() {
  document.getElementById('classroomModal').style.display = 'none';
}

document.getElementById('classroomContinueBtn').addEventListener('click', () => {
  const url = document.getElementById('classroomUrl').value.trim();

  if (!url) return; // do nothing if empty (no alert for now)

  // open URL
  window.open(url, '_self'); // use _blank if you want new tab
});

</script>
</html>
