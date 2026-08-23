<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0, interactive-widget=overlays-content">
  <title>Cave Game</title>
  <style>
    :root {
      --color1: #99B194;
      --color2: #D9E1A4;
      --color3: #6b8167;
    }
    html, body {
      margin: 0; padding: 0;
      width: 100%; height: 100%;
      overflow: hidden; background: #000;
    }
    #game-container { position: relative; width: 100vw; height: 100vh; }
    #timerCanvas {
      position: absolute; top: -1px; right: -4px; z-index: 20;
    }
    #gameWrapper {
      position: absolute; width: 2338px; height: 1668px;
      top: 0; left: 0; transform-origin: top left;
    }
    #gameCanvas {
      width: 100%; height: 100%; position: absolute; top: 0; left: 0; z-index: 1;
    }
    #borderImage {
      position: absolute; width: 100%; height: 100%;
      top: 0; left: 0; z-index: 3; background-size: 100% 100%;
      background-repeat: no-repeat; background-position: center; pointer-events: none;
    }
    /* Loading */
    @keyframes spin { 0% { transform: rotate(0deg);} 100% { transform: rotate(360deg);} }
    /* Mic button */
    #micBtn {
      position: absolute;
      top:-500px;
      left: 50%;
      transform: translateX(360px);
      z-index: 30;
      display: none;
      border: 0; border-radius: 999px;
      padding: 14px 18px; font-size: 18px; cursor: pointer;
      background: #1e90ff; color: white;
      box-shadow: 0 8px 20px rgba(0,0,0,.25);
    }
    #micBtn.listening { background: #ff9500; }
    .audio-toggle
    {
       position: absolute;
      top: 50px;
      left: 50px;
      font-size: 50px;
      color: red;
      z-index: 10000;
    }
  </style>
</head>
<body>
  <audio id="bgMusic" loop muted>
    <source src="assets/game_cave/audio/cave_game_bg_music.mp3" type="audio/mpeg">
  </audio>
  <div id="game-container">
    <input type="text" id="hiddenInput" autocorrect="off" autocomplete="off"
       autocapitalize="none" 
       spellcheck="false"
  style="position: absolute; top: -500; left: 0; opacity: 1; pointer-events: none; z-index: -1;">
  <div id="borderImage" style="display:none"></div>
    <div id="gameWrapper">
      <canvas id="gameCanvas" width="2338" height="1668"></canvas>
      <canvas id="timerCanvas" width="300" height="300"></canvas>
      <div id="loading-screen" style="
          position: fixed; top: 0; left: 0; width: 2338px; height: 1668px;
          display: flex; justify-content: center; align-items: center; flex-direction: column;
          z-index: 9999; font-family: Arial, sans-serif; font-size: 24px; color: #333;">
        <div class="spinner" style="
            width: 40px; height: 40px; border: 6px solid #ccc;
            border-top: 6px solid #007BFF; border-radius: 50%;
            animation: spin 1s linear infinite; margin-bottom: 15px;"></div>
        <div id="loading-progress" style="color:#fff;font-size:50px;z-index: 1;">0%</div>
        <div style="width: 300px; height: 20px; background: #eee; border-radius: 10px; overflow: hidden; margin-top: 15px;">
          <div id="progress-bar" style="height:100%; width:0%; background: linear-gradient(90deg, #007BFF, #00BFFF); transition: width 0.3s ease;"></div>
        </div>
      </div>
    </div>
    <!-- Results overlay -->
    <div id="resultPage" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%;
         background-color: rgba(0, 0, 0, 0.8); display: flex; flex-direction: column;
         justify-content: center; align-items: center; opacity: 0; visibility: hidden;
         transition: opacity 1s ease-in-out; z-index: 1000;">
      <h2 style="color: white; font-size: 80px; text-align: center; padding: 20px;">
        Game Results!<br>You finished strong!
      </h2>
    </div>
    <!-- Question section placeholder (kept) -->
    <div id="questionSection" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%;
         background-color: #fff; display: flex; flex-direction: column; justify-content: center;
         align-items: center; opacity: 0; visibility: hidden; transition: opacity 1s ease-in-out; z-index: 1001;">
      <h2 style="color: #333; font-size: 80px; text-align: center; padding: 20px;">Well Being Question Section</h2>
      <button onclick="startGame()" style="padding: 20px 40px; font-size: 48px; cursor: pointer; margin-top: 30px;">Start New Game</button>
    </div>
    <!-- Mic button -->
    <button id="micBtn" type="button">🎤 Speak</button>
  </div>
  <!-- CreateJS libraries -->
  <script src="https://code.createjs.com/1.0.0/easeljs.min.js"></script>
  <script src="https://code.createjs.com/1.0.0/preloadjs.min.js"></script>
  <script src="https://code.createjs.com/1.0.0/soundjs.min.js"></script>
  <script src="https://code.createjs.com/1.0.0/tweenjs.min.js"></script>
  <script>
    const canvas = document.getElementById("gameCanvas");
    const stage = new createjs.Stage(canvas);
    createjs.Touch.enable(stage);
    let tutorialComplete = false;
    let isClimbing = false;
    let gamePaused = false; // NEW: pauses background + climber during encounters
    let setTotalTimount = 21;
    let countdownRemaining = setTotalTimount;
    const totalAnswerTime = setTotalTimount;
    let countdownInterval;
    const timerCanvas = document.getElementById("timerCanvas");
    const timerCtx = timerCanvas.getContext("2d");
    const timerRadius = 130;
    const timerCenter = { x: 150, y: 150 };
    let climberSprite;
    let bg1, bg2, backgroundHeight = 0;
    let bgmSoundInstance;
    let bgMusic; 
    // Theme
    var theme = localStorage.getItem("selectedTheme") || "dino";
    
    const gameContainer = document.getElementById('borderImage');
     if (borderImage) {
                // Create a temporary canvas to generate a blank image the size of the window
                const canvas = document.createElement('canvas');
                canvas.width = window.innerWidth;
                canvas.height = window.innerHeight;

                // Set the src to the blank canvas data
                borderImage.src = canvas.toDataURL();

                // Optional: Ensure the image stretches if the window is resized
                borderImage.style.width = "100vw";
                borderImage.style.height = "100vh";
                borderImage.style.objectFit = "fill";
                
              
            } else {
                console.error("Border image element not found!");
            }

    const themeColors = {
      dino: { color1: '#99B194', color2: '#D9E1A4', color3: '#6b8167' },
      fairy: { color1: '#7366C6', color2: '#C885D0', color3: '#4f4691' },
      pet:   { color1: '#E78780', color2: '#ECCC96', color3: '#b3534c' },
      train: { color1: '#8AA9DA', color2: '#B0D0E4', color3: '#5c7aad' }
    };
    const { color1, color2, color3 } = themeColors[theme] || { color1: '#000', color2: '#FFF', color3: '#FFF' };
    document.documentElement.style.setProperty('--color1', color1);
    document.documentElement.style.setProperty('--color2', color2);
    document.documentElement.style.setProperty('--color3', color3);
    // Difficulty -> lines (5 per level)
    var currentDifficulty = localStorage.getItem('gameTrickLevel') ;
    
     const LEVEL_LINES = {
      relaxed: [
          { monster: "You can't do it", answer: "Yes I can", audio: "relaxed_01" },
          { monster: "It's impossible", answer: "It is not", audio: "relaxed_02" },
          { monster: "You're too weak", answer: "I am not", audio: "relaxed_03" },
          { monster: "You can't escape the dark", answer: "I can", audio: "relaxed_04" },
          { monster: "You should give up", answer: "I will not", audio: "relaxed_05" },
          { monster: "This won't end", answer: "It will", audio: "relaxed_06" },
          { monster: "It's too scary", answer: "I can do it", audio: "relaxed_07" },
          { monster: "It's easier to stop", answer: "I will not", audio: "relaxed_08" },
          { monster: "This will last forever", answer: "It will not", audio: "relaxed_09" },
          { monster: "It's too hard", answer: "I can do it", audio: "relaxed_10" }
      ],
      relaxedFinal: { text: "I hope I can get out", audio: "relaxed_final" },

      normal: [
          { monster: "This cave will never end", answer: "I don't believe you", audio: "normal_01" },
          { monster: "You're too weak", answer: "I haven't failed yet", audio: "normal_02" },
          { monster: "You can't escape the dark", answer: "That's not true", audio: "normal_03" },
          { monster: "You can't win", answer: "I can try", audio: "normal_04" },
          { monster: "You can't get through this", answer: "I hope I will", audio: "normal_05" },
          { monster: "You should just give up", answer: "It's not over yet", audio: "normal_06" },
          { monster: "This darkness will last forever", answer: "That's not true", audio: "normal_07" },
          { monster: "This is too scary for you", answer: "I can get through it", audio: "normal_08" },
          { monster: "It's impossible to get out", answer: "I don't know that unless I try", audio: "normal_09" },
          { monster: "Stop trying, it's easier", answer: "I'll keep trying no matter what", audio: "normal_10" }
      ],
      normalFinal: { text: "If I hope in getting out of the dark, things will be better", audio: "normal_final" },

      tricky: [
          { monster: "It's easier if you stop trying", answer: "I won't get out if I don't try", audio: "tricky_01" },
          { monster: "It's impossible for you to get out of this cave", answer: "It's only seems impossible, but I can do it", audio: "tricky_02" },
          { monster: "This is too scary for you to handle", answer: "Even though it's scary, I know if I get through it will be over", audio: "tricky_03" },
          { monster: "This darkness will last forever", answer: "I don't believe you, it will end", audio: "tricky_04" },
          { monster: "You should just give up", answer: "I will never escape if I do", audio: "tricky_05" },
          { monster: "You can't get through this", answer: "I need to keep trying just in case I do", audio: "tricky_06" },
          { monster: "You're too weak", answer: "I can keep going so that makes me strong", audio: "tricky_07" },
          { monster: "This cave will never end", answer: "I don't know until I look for the light", audio: "tricky_08" },
          { monster: "You can't do it", answer: "I won't know that I can't until I try my hardest", audio: "tricky_09" },
          { monster: "You will never escape the dark", answer: "There is still a chance that I can", audio: "tricky_10" }
      ],
      trickyFinal: { 
          text: "If I hope that things will get better in the future then I can get out of this dark and difficult time", 
          audio: "tricky_final" 
      }
  };
    function resizeGame() {
      const baseWidth = 2338;
      const baseHeight = 1668;
      const scale = Math.min(window.innerWidth / baseWidth, window.innerHeight / baseHeight);
      const wrapper = document.getElementById("gameWrapper");
      wrapper.style.transform = `scale(${scale})`;
      wrapper.style.left = `${(window.innerWidth - baseWidth * scale) / 2}px`;
      wrapper.style.top = `${(window.innerHeight - baseHeight * scale) / 2}px`;
    }
    window.addEventListener("resize", resizeGame);
    resizeGame();
    // Preloader
    const loader = new createjs.LoadQueue(true);
    loader.installPlugin(createjs.Sound);
    loader.on("progress", (evt) => {
      const percent = Math.floor(evt.progress * 100);
      document.getElementById("loading-progress").innerText = `${percent}%`;
      document.getElementById("progress-bar").style.width = `${percent}%`;
    });
    loader.loadManifest([
      { id: "bg", src: "assets/game_cave/images/cave.jpg" },
      { id: "climberSheet", src: "assets/game_cave/images/cave_climber.png" },
      { id: "bgm", src: "assets/game_mountain_climber/audio/game_bg_music_1.mp3" },
      { id: "border", src: "assets/images/fairy.png" },
      { id: "unmuted", src: "assets/images/speaker-unmuted.png" },
      { id: "muted", src: "assets/images/speaker-muted.png" },
      /* 7 monsters (300x300 per frame; 1–10 walk, 11–20 bully) */
      { id: "monster1Sheet", src: "assets/game_cave/images/red-tall-monster.png" },
      { id: "monster1Audio", src: "assets/game_cave/audio/monster1_audio.mp3" },
      { id: "monster2Sheet", src: "assets/game_cave/images/tall-brown-monster.png" },
      { id: "monster2Audio", src: "assets/game_cave/audio/monster2_audio.mp3" },
      { id: "monster3Sheet", src: "assets/game_cave/images/red-monster.png" },
      { id: "monster3Audio", src: "assets/game_cave/audio/monster3_audio.mp3" },
      { id: "monster4Sheet", src: "assets/game_cave/images/blue-monster.png" },
      { id: "monster4Audio", src: "assets/game_cave/audio/monster4_audio.mp3" },
      { id: "monster5Sheet", src: "assets/game_cave/images/green-monster.png" },
      { id: "monster5Audio", src: "assets/game_cave/audio/monster5_audio.mp3" },
      { id: "monster6Sheet", src: "assets/game_cave/images/purple-monster.png" },
      { id: "monster6Audio", src: "assets/game_cave/audio/monster6_audio.mp3" },
      { id: "monster7Sheet", src: "assets/game_cave/images/light-blue-monster.png" },
      { id: "monster7Audio", src: "assets/game_cave/audio/monster7_audio.mp3" },
      { id: "cave_finished", src: "assets/game_cave/images/cave_finished.png" },
      { id: "cave_door_opened", src: "assets/game_cave/images/cave_door_opened.png" },
      { id: "cave_outside", src: "assets/game_cave/images/cave_outside.png" },
      
      { id: "relaxed_01", src: "assets/game_cave/audio/relaxed_01.mp3" },
      { id: "relaxed_02", src: "assets/game_cave/audio/relaxed_02.mp3" },
      { id: "relaxed_03", src: "assets/game_cave/audio/relaxed_03.mp3" },
      { id: "relaxed_04", src: "assets/game_cave/audio/relaxed_04.mp3" },
      { id: "relaxed_05", src: "assets/game_cave/audio/relaxed_05.mp3" },
      { id: "relaxed_06", src: "assets/game_cave/audio/relaxed_06.mp3" },
      { id: "relaxed_07", src: "assets/game_cave/audio/relaxed_07.mp3" },
      { id: "relaxed_08", src: "assets/game_cave/audio/relaxed_08.mp3" },
      { id: "relaxed_09", src: "assets/game_cave/audio/relaxed_09.mp3" },
      { id: "relaxed_10", src: "assets/game_cave/audio/relaxed_10.mp3" },
      { id: "relaxed_11", src: "assets/game_cave/audio/relaxed_11.mp3" },

      // Normal Levels (01 to 11)
      { id: "normal_01", src: "assets/game_cave/audio/normal_01.mp3" },
      { id: "normal_02", src: "assets/game_cave/audio/normal_02.mp3" },
      { id: "normal_03", src: "assets/game_cave/audio/normal_03.mp3" },
      { id: "normal_04", src: "assets/game_cave/audio/normal_04.mp3" },
      { id: "normal_05", src: "assets/game_cave/audio/normal_05.mp3" },
      { id: "normal_06", src: "assets/game_cave/audio/normal_06.mp3" },
      { id: "normal_07", src: "assets/game_cave/audio/normal_07.mp3" },
      { id: "normal_08", src: "assets/game_cave/audio/normal_08.mp3" },
      { id: "normal_09", src: "assets/game_cave/audio/normal_09.mp3" },
      { id: "normal_10", src: "assets/game_cave/audio/normal_10.mp3" },
      { id: "normal_11", src: "assets/game_cave/audio/normal_11.mp3" },

      // Tricky Levels (01 to 11)
      { id: "tricky_01", src: "assets/game_cave/audio/tricky_01.mp3" },
      { id: "tricky_02", src: "assets/game_cave/audio/tricky_02.mp3" },
      { id: "tricky_03", src: "assets/game_cave/audio/tricky_03.mp3" },
      { id: "tricky_04", src: "assets/game_cave/audio/tricky_04.mp3" },
      { id: "tricky_05", src: "assets/game_cave/audio/tricky_05.mp3" },
      { id: "tricky_06", src: "assets/game_cave/audio/tricky_06.mp3" },
      { id: "tricky_07", src: "assets/game_cave/audio/tricky_07.mp3" },
      { id: "tricky_08", src: "assets/game_cave/audio/tricky_08.mp3" },
      { id: "tricky_09", src: "assets/game_cave/audio/tricky_09.mp3" },
      { id: "tricky_10", src: "assets/game_cave/audio/tricky_10.mp3" },
      { id: "tricky_11", src: "assets/game_cave/audio/tricky_11.mp3" },

      { id: "monster_relaxed_01", src: "assets/game_cave/audio/monster/relaxed_01.mp3" },
      { id: "monster_relaxed_02", src: "assets/game_cave/audio/monster/relaxed_02.mp3" },
      { id: "monster_relaxed_03", src: "assets/game_cave/audio/monster/relaxed_03.mp3" },
      { id: "monster_relaxed_04", src: "assets/game_cave/audio/monster/relaxed_04.mp3" },
      { id: "monster_relaxed_05", src: "assets/game_cave/audio/monster/relaxed_05.mp3" },
      { id: "monster_relaxed_06", src: "assets/game_cave/audio/monster/relaxed_06.mp3" },
      { id: "monster_relaxed_07", src: "assets/game_cave/audio/monster/relaxed_07.mp3" },
      { id: "monster_relaxed_08", src: "assets/game_cave/audio/monster/relaxed_08.mp3" },
      { id: "monster_relaxed_09", src: "assets/game_cave/audio/monster/relaxed_09.mp3" },
      { id: "monster_relaxed_10", src: "assets/game_cave/audio/monster/relaxed_10.mp3" },
      { id: "monster_relaxed_11", src: "assets/game_cave/audio/monster/relaxed_11.mp3" },

      // Normal Levels (ID: monster_normal_XX | Path: audio/monster/)
      { id: "monster_normal_01", src: "assets/game_cave/audio/monster/normal_01.mp3" },
      { id: "monster_normal_02", src: "assets/game_cave/audio/monster/normal_02.mp3" },
      { id: "monster_normal_03", src: "assets/game_cave/audio/monster/normal_03.mp3" },
      { id: "monster_normal_04", src: "assets/game_cave/audio/monster/normal_04.mp3" },
      { id: "monster_normal_05", src: "assets/game_cave/audio/monster/normal_05.mp3" },
      { id: "monster_normal_06", src: "assets/game_cave/audio/monster/normal_06.mp3" },
      { id: "monster_normal_07", src: "assets/game_cave/audio/monster/normal_07.mp3" },
      { id: "monster_normal_08", src: "assets/game_cave/audio/monster/normal_08.mp3" },
      { id: "monster_normal_09", src: "assets/game_cave/audio/monster/normal_09.mp3" },
      { id: "monster_normal_10", src: "assets/game_cave/audio/monster/normal_10.mp3" },
      { id: "monster_normal_11", src: "assets/game_cave/audio/monster/normal_11.mp3" },

      // Tricky Levels (ID: monster_tricky_XX | Path: audio/monster/)
      { id: "monster_tricky_01", src: "assets/game_cave/audio/monster/tricky_01.mp3" },
      { id: "monster_tricky_02", src: "assets/game_cave/audio/monster/tricky_02.mp3" },
      { id: "monster_tricky_03", src: "assets/game_cave/audio/monster/tricky_03.mp3" },
      { id: "monster_tricky_04", src: "assets/game_cave/audio/monster/tricky_04.mp3" },
      { id: "monster_tricky_05", src: "assets/game_cave/audio/monster/tricky_05.mp3" },
      { id: "monster_tricky_06", src: "assets/game_cave/audio/monster/tricky_06.mp3" },
      { id: "monster_tricky_07", src: "assets/game_cave/audio/monster/tricky_07.mp3" },
      { id: "monster_tricky_08", src: "assets/game_cave/audio/monster/tricky_08.mp3" },
      { id: "monster_tricky_09", src: "assets/game_cave/audio/monster/tricky_09.mp3" },
      { id: "monster_tricky_10", src: "assets/game_cave/audio/monster/tricky_10.mp3" },
      { id: "monster_tricky_11", src: "assets/game_cave/audio/monster/tricky_11.mp3" }

      
    ]);
    loader.on("complete", handleComplete);
    let bgImage;
    function handleComplete() {
  document.getElementById("loading-screen").style.display = "none";
  // Background
  bgImage = loader.getResult("bg");
  bg1 = new createjs.Bitmap(bgImage);
  bg2 = new createjs.Bitmap(bgImage);
  const scale = canvas.width / bgImage.width;
  bg1.scaleX = bg2.scaleX = bg1.scaleY = bg2.scaleY = scale;
  backgroundHeight = bgImage.height * scale;
  const overlap = 1;
  bg1.y = 0;
  bg2.y = bg1.y - backgroundHeight + overlap;
  stage.addChild(bg1, bg2);
  // Climber
  const climberSheetImage = loader.getResult("climberSheet");
  const spriteSheetData = {
    images: [climberSheetImage],
    frames: { width: 300, height: 533, count: 31, regX: 150, regY: 0 },
    animations: {
      idle: { 
        frames: [0,1,2,3,4,5,6,7,8,9,10,11,11,11,11,11,11,11,11,11,11,11,11,11,11,11,11,11,11,11,11,11,11,11,11,11], 
        next: "idle",  
        speed: 0.1 
      },
      climb: { frames: [12,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30], next: "climb", speed: 0.1 }
    }
  };
  const climberSpriteSheet = new createjs.SpriteSheet(spriteSheetData);
  climberSprite = new createjs.Sprite(climberSpriteSheet, "idle");
  climberSprite.scaleX = climberSprite.scaleY = 1.3;
  climberSprite.x = canvas.width / 2;
  climberSprite.y = canvas.height - (climberSprite.getBounds().height) - climberSprite.getBounds().height * 0.3 - 100;
  climberSprite.alpha = 0;
  stage.addChild(climberSprite);
  // Add border image
  // const border = new createjs.Bitmap(`assets/images/${theme}.png`);
  // border.x = 0;
  // border.y = 0;
  // border.scaleX = canvas.width / borderImage.width;
  // border.scaleY = canvas.height / borderImage.height;
  // stage.addChild(border);
  // Enable mouse input
  stage.enableMouseOver();
  stage.mouseEnabled = true;
  // Audio setup
  mutedImg = loader.getResult("muted");
  unmutedImg = loader.getResult("unmuted");
  bgMusic = document.getElementById("bgMusic");
  bgMusic.muted = true; // Start muted
  // Overlay
  const overlay = new createjs.Shape();
  overlay.graphics.beginFill("rgba(0,0,0,0.6)").drawRect(0, 0, canvas.width, canvas.height);
  stage.addChild(overlay);
  // Speaker icon in center (bigger)
  audioIcon = new createjs.Bitmap(mutedImg);
  audioIcon.regX = mutedImg.width / 2;
  audioIcon.regY = mutedImg.height / 2;
  audioIcon.x = canvas.width / 2;
  audioIcon.y = canvas.height / 2 - 50;
  audioIcon.scaleX = audioIcon.scaleY = 1.0; // bigger size
  audioIcon.cursor = "pointer";
  stage.addChild(audioIcon);
  // Instruction text below speaker
  const tapText = new createjs.Text("Tap Speaker to Continue", "28px Arial", "#ffffff");
  tapText.textAlign = "center";
  tapText.x = canvas.width / 2;
  tapText.y = audioIcon.y + mutedImg.height * 0.6;
  stage.addChild(tapText);
  // Initial click handler (center button)
  audioIcon.on("click", () => {
    // bgMusic.muted = false;
    // bgMusic.play().catch(e => console.log("Play blocked:", e));
    audioIcon.visible = false;
    window.parent.postMessage({ 
                action: 'ACTIVATE_GLOBAL_AUDIO', 
                audioSrc: 'assets/game_cave/audio/cave_game_bg_music.mp3' 
    }, '*');
    // Animate icon to top-left with bounce
    createjs.Tween.get(audioIcon)
      .to({ x: 140, y: 140, scaleX: 200 / mutedImg.width, scaleY: 200 / mutedImg.height }, 800, createjs.Ease.bounceOut);
    // Fade out overlay + text
    createjs.Tween.get(overlay).to({ alpha: 0 }, 500).call(() => stage.removeChild(overlay));
    createjs.Tween.get(tapText).to({ alpha: 0 }, 500).call(() => stage.removeChild(tapText));
handleTap();
    // Change to toggle behavior
    audioIcon.removeAllEventListeners("click");
    audioIcon.on("click", () => {
      if (bgMusic.paused) {
        bgMusic.play();
        audioIcon.image = unmutedImg;
      } else {
        bgMusic.pause();
        audioIcon.image = mutedImg;
      }
    });
  });
 // Background scroll intro
const scrollDistance = canvas.height * 0.2;
const scrollDuration = 2500;
createjs.Tween.get(bg1)
  .to({ y: scrollDistance }, scrollDuration, createjs.Ease.cubicOut);
createjs.Tween.get(bg2)
  .to({ y: (bg1.y - backgroundHeight) + scrollDistance + overlap }, scrollDuration, createjs.Ease.cubicOut)
  .call(() => {
    // Just fade in the climber, no TapToContinue message
    createjs.Tween.get(climberSprite).to({ alpha: 1 }, 500);
  });
  createjs.Ticker.framerate = 60;
  createjs.Ticker.addEventListener("tick", handleTick);
}
    function createTapToContinueMessage() {
      const tapText = new createjs.Text("Tap to Continue", "80px Arial", "#FFF");
      tapText.x = canvas.width / 2;
      tapText.y = canvas.height / 2;
      tapText.textAlign = "center";
      tapText.name = "tapToContinue";
      stage.addChild(tapText);
      stage.on("stagemousedown", handleTap);
    }
    function handleTap() {
      if (!tutorialComplete) {
        const tapText = stage.getChildByName("tapToContinue");
        if (tapText) stage.removeChild(tapText);
        tutorialComplete = true;
        isClimbing = true;
        // // // Unmute + start audio
        // // bgMusic.muted = false;
        // // bgMusic.play().catch(e => console.log("Play blocked:", e));
        // // audioIcon.image = unmutedImg;
        // // isMuted = false;
        // // stage.update();
        // // Change audio icon
        audioIcon.image = unmutedImg;
        stage.update(); 
        climberSprite.gotoAndPlay("climb");
        stage.removeEventListener("stagemousedown", handleTap);
        // Start the monster cycle
        scheduleNextMonster();
      }
    }
    function handleTick(event) {
      if (tutorialComplete) {
        const speed = 2;
        if (!gamePaused) {
          bg1.y += speed;
          bg2.y += speed;
          const overlap = 1;
          if (bg1.y >= backgroundHeight) bg1.y = bg2.y - backgroundHeight + overlap;
          if (bg2.y >= backgroundHeight) bg2.y = bg1.y - backgroundHeight + overlap;
        }
      }
      stage.update(event);
    }
    /* ==================== MONSTER ENCOUNTER SYSTEM ==================== */
     const MONSTERS = [
      { sheetId: 'monster1Sheet', audioId: 'monster1Audio', total:"42",walk:15,bullie:"27" },
      { sheetId: 'monster2Sheet', audioId: 'monster1Audio', total:"31",walk:11,bullie:"20" },
      { sheetId: 'monster3Sheet', audioId: 'monster3Audio', total:"27",walk:8,bullie:"19" },
      { sheetId: 'monster4Sheet', audioId: 'monster4Audio', total:"32",walk:13,bullie:"19" },
      { sheetId: 'monster5Sheet', audioId: 'monster1Audio', total:"38",walk:6,bullie:"32" },
      { sheetId: 'monster6Sheet', audioId: 'monster1Audio', total:"32",walk:10,bullie:"22" },
      { sheetId: 'monster7Sheet', audioId: 'monster1Audio', total:"32",walk:15,bullie:"17" }
    ];
    const totalMonstersThisGame = 6;
    let monstersDefeated = 0;
    let monsterActive = false;
    let monsterOrder = shuffleArray([...MONSTERS]); // copy & shuffle
    let monsterIndex = 0;
    const micBtn = document.getElementById('micBtn');
    let recognition = null;
    try {
      const SR = window.SpeechRecognition || window.webkitSpeechRecognition;
      if (SR) {
        recognition = new SR();
        recognition.lang = 'en-US';
        recognition.interimResults = false;
      }
    } catch (_) {}
    // Build a sprite sheet for a 300x300 monster (1–10 walk, 11–20 bully)
    function buildMonsterSpriteSheet(image, walk, bullie) {
      return new createjs.SpriteSheet({
        images: [image],
        frames: { width: 300, height: 300, regX: 150, regY: 300 }, // anchor: feet center
        animations: {
          walk:  { frames: Array.from({length:8}, (_,i)=>i),     next: "walk",  speed: 0.1 },
          bully: { frames: Array.from({length:bullie}, (_,i)=>i+walk),  next: "bully", speed: 0.1 },
        }
      });
    }
    // Reusable containers
    let monsterContainer = null;
    let monsterSprite = null;
    let bubbleContainer = null;
    let answerContainer = null;
    let currentAnswer = "";
    let correctAnswer = "";
    function randInt(min, max) { return Math.floor(Math.random()*(max-min+1))+min; }
    function pauseGameForEncounter() {
      gamePaused = true;
      if (climberSprite) climberSprite.gotoAndPlay("idle");
    }
    function resumeGameAfterEncounter() {
        gamePaused = false;
        if (climberSprite) climberSprite.gotoAndPlay("climb");
      }
      function scheduleNextMonster() {
          if (monstersDefeated >= totalMonstersThisGame) {
            // Already finished
            return;
          }
          // If the next one is the LAST monster (final phrase)
          if (monstersDefeated === totalMonstersThisGame - 1) {
            gamePaused = true;
            setTimeout(spawnMonster, 0); // immediate
          } else {
            const delayMs = randInt(5000, 8000); // 5–20 sec for normal
            setTimeout(spawnMonster, delayMs);
          }
  }
  function drawCircularTimer(countdownRemaining, totalClimbTime) {

          updateTimerUI(countdownRemaining, totalClimbTime);

          // const percent = remaining / total;
          // timerCtx.clearRect(0, 0, timerCanvas.width, timerCanvas.height);
          // timerCtx.beginPath();
          // timerCtx.arc(timerCenter.x, timerCenter.y, timerRadius, 0, 2 * Math.PI);
          // timerCtx.fillStyle = "rgba(0,0,0,0.6)";
          // timerCtx.fill();
          // timerCtx.beginPath();
          // timerCtx.moveTo(timerCenter.x, timerCenter.y);
          // timerCtx.arc(timerCenter.x, timerCenter.y, timerRadius, -Math.PI / 2, -Math.PI / 2 + 2 * Math.PI * percent);
          // timerCtx.closePath();
          // timerCtx.fillStyle = color1;
          // timerCtx.fill();
          // timerCtx.fillStyle = "#fff";
          // timerCtx.font = "bold 108px Comic Sans MS";
          // timerCtx.textAlign = "center";
          // timerCtx.textBaseline = "middle";
          // //timerCtx.fillText(remaining, timerCenter.x, timerCenter.y);
          // timerCtx.fillText(total - remaining, timerCenter.x, timerCenter.y);
      }
      function updateTimerUI(remaining, total) {
    window.parent.postMessage({
        action: 'UPDATE_TIMER',
        remaining: remaining,
        total: total
    }, '*');
}
    function removeCircularTimer() {
        // 1. Clear the local iframe canvas (if still in use)
        if (timerCtx && timerCanvas) {
            timerCtx.clearRect(0, 0, timerCanvas.width, timerCanvas.height);
        }

        // 2. Tell the Parent Engine to hide the Global Timer
        window.parent.postMessage({ 
            action: 'HIDE_TIMER' 
        }, '*');
    }
    function startCountdownTimer() {
        if (countdownInterval) {
            clearInterval(countdownInterval);
        }
        drawCircularTimer(countdownRemaining, totalAnswerTime);
        countdownInterval = setInterval(() => {
            // Only decrement the timer if the player is actively holding the climber
                countdownRemaining--;
                drawCircularTimer(countdownRemaining, totalAnswerTime);
                if (countdownRemaining <= 0) {
                    clearInterval(countdownInterval);
                     // ✅ call the globally stored finishSuccess
                    if (typeof window.currentFinishSuccess === "function") {
                      window.currentFinishSuccess();
                    }
                    countdownRemaining = setTotalTimount;
                    removeCircularTimer();
                    }
        }, 1000);
    }
  function shuffleArray(arr) {
    for (let i = arr.length - 1; i > 0; i--) {
      const j = Math.floor(Math.random() * (i + 1));
      [arr[i], arr[j]] = [arr[j], arr[i]];
    }
    return arr;
  }
  const script = LEVEL_LINES[currentDifficulty];
  let questionOrder = shuffleArray([...script]);  // copy & shuffle
  if (currentDifficulty === "relaxed") {
     finalPhrase = "I hope I can get out";
  } else if (currentDifficulty === "normal") {
     finalPhrase = "If I hope in getting out of the dark, things will be better";
  } else {
     finalPhrase = "If I hope that things will get better in the future then I can get out of this dark and difficult time";
  }
   questionOrder[totalMonstersThisGame - 1] = {
    ...questionOrder[totalMonstersThisGame - 1], // keep original monster text etc.
    answer: finalPhrase                          // override answer only
  };
  let questionIndex = 0;
  monsterOrder = shuffleArray([...MONSTERS]); // reshuffle if needed
  monsterIndex = 0;
  let choice;
  let isFinal = false;
  function spawnMonster() {
      if (monsterActive || monstersDefeated >= totalMonstersThisGame) return;
      monsterActive = true;
      pauseGameForEncounter();
      choice = monsterOrder[monsterIndex++];
      if (questionIndex >= questionOrder.length) {
        questionOrder = shuffleArray([...script]); // reshuffle if you want looping
        questionIndex = 0;
      }
      const { monster: lineText, answer: ansText, audio: audioForText } = questionOrder[questionIndex++];
      correctAnswer = ansText;
      
      
      
      
      // --- Check if FINAL monster (last phrase) ---
      isFinal = (monstersDefeated === totalMonstersThisGame - 1);
      //bgmSoundInstance.paused = true;
      if (isFinal) {
      // Overlay cave door opened image
      const doorImg = loader.getResult("cave_door_opened");
      if (!doorImg) return;
      const doorBmp = new createjs.Bitmap(doorImg);
      const scale = Math.max(canvas.width / doorImg.width, canvas.height / doorImg.height);
      doorBmp.scaleX = doorBmp.scaleY = scale;
      doorBmp.x = (canvas.width - doorImg.width * scale) / 2;
      doorBmp.y = (canvas.height - doorImg.height * scale) / 2;
      doorBmp.alpha = 0; // fade in
      stage.addChild(doorBmp); // added on top
      createjs.Tween.get(doorBmp).to({ alpha: 1 }, 600);
      // Add climber sprite on top
      const climberImg = loader.getResult("climberSheet");
      const spriteSheetData = {
        images: [climberImg],
        frames: { width: 300, height: 533, count: 32, regX: 150, regY: 0 },
        animations: {
          idle:  { frames: [...Array(10).keys()].concat(Array(22).fill(9)), next: "idle",  speed: 0.1 },
          climb: { frames: Array.from({length: 22}, (_, i) => i+10), next: "climb", speed: 0.1 }
        }
      };
      const climberSheet = new createjs.SpriteSheet(spriteSheetData);
      const climberSprite = new createjs.Sprite(climberSheet, "idle");
      climberSprite.scaleX = climberSprite.scaleY = 1.3;
      climberSprite.x = canvas.width / 2;
      climberSprite.y = canvas.height - (climberSprite.getBounds().height) - climberSprite.getBounds().height * 0.3 - 100;
      climberSprite.alpha = 0;
      stage.addChild(climberSprite);
      createjs.Tween.get(climberSprite)
        .to({ alpha: 1 }, 600)
        .call(() => {
          // Show answer box on top
          const ansUI = makeAnswerBox(ansText);
          answerContainer = ansUI.cont;
          answerContainer.x = canvas.width / 2;
          answerContainer.y = canvas.height / 2;
          answerContainer.alpha = 0;
          stage.addChild(answerContainer);
          createjs.Tween.get(answerContainer).to({ alpha: 1 }, 500);
          enableAnswerInput(ansUI);
          micBtn.style.display = recognition ? "inline-block" : "none";
        });
      return; // skip monster logic
}
  // Play the voice over audio

  // 1. Identify the IDs
let responseID = audioForText; // e.g., "normal_01"
let monsterID = "monster_" + responseID; // e.g., "monster_normal_01"

// 2. Play the Monster Audio first
let monsterVoice = createjs.Sound.play(monsterID, { 
    volume: 1, 
    delay: 0 
});

// 3. Wait for the Monster to finish, then play the response
monsterVoice.on("complete", function() {
    createjs.Sound.play(responseID, { 
        volume: 1, 
        delay:0 // Slight gap between voices for realism
    });
});

  startCountdownTimer();
  // ------------------ Normal monster case ------------------
  console.log("Monster id: " + choice.sheetId);
  const img = loader.getResult(choice.sheetId);
  const sheet = buildMonsterSpriteSheet(img, choice.walk, choice.bullie); // regY is feet (300)
  monsterSprite = new createjs.Sprite(sheet, "walk");
  monsterSprite.scale = 1.5; // Set monster scale
  // Start at random X at the top
  monsterSprite.x = randInt(0, canvas.width - 300);
  monsterSprite.y = -300; // above the view
  monsterSprite.alpha = 0;
  // Target: horizontal middle, vertical 55%
  const targetX = canvas.width / 2; // center, 300px wide sprite
  const targetY = canvas.height * 0.48;   // feet Y because regY=300
  // Build UI
  const bubble   = makeBubble(lineText);         
  bubbleContainer = bubble.cont;
  const ansUI    = makeAnswerBox(ansText);       
  answerContainer = ansUI.cont;
  // Position bubble above head
  const headY = targetY - 450; // head Y
  bubbleContainer.x = targetX  - (bubble.boxW / 2);
  bubbleContainer.y = headY - 20;
  // Position answer box below feet
  answerContainer.x = targetX ;
  answerContainer.y = targetY + 70;
  // Group in z-order
  monsterContainer = new createjs.Container();
  monsterContainer.addChild(answerContainer); 
  monsterContainer.addChild(monsterSprite);   
  monsterContainer.addChild(bubbleContainer); 
  stage.addChild(monsterContainer);
  // Tween monster to target, then show UI & audio
// Monster tween
createjs.Tween.get(monsterSprite)
  .to({ alpha: 1, x: targetX, y: targetY }, 1500, createjs.Ease.cubicOut)
  .call(() => {
    monsterSprite.gotoAndPlay("bully");
    //if (choice.audioId) createjs.Sound.play(choice.audioId, { volume: 0.9 });
    enableAnswerInput(ansUI);
    micBtn.style.display = recognition ? "inline-block" : "none";
  });
// Bubble and answer containers fade in with a small delay
createjs.Tween.get(bubbleContainer)
  .wait(600) // delay before showing
  .to({ alpha: 1 }, 250);
createjs.Tween.get(answerContainer)
  .wait(600)
  .to({ alpha: 1 }, 250);
}
    // Speech bubble
    function makeBubble(textStr) {
      const cont = new createjs.Container();
      const paddingX = 28, paddingY = 22, maxWidth = 900;
      const txt = new createjs.Text(textStr, "bold 64px Arial", "#000");
      txt.lineWidth = maxWidth; txt.textAlign = "left"; txt.x = paddingX; txt.y = paddingY;
      const w = Math.min(maxWidth, txt.getMeasuredWidth()) + paddingX*2;
      const h = txt.getMeasuredHeight() + paddingY*2;
      const shape = new createjs.Shape();
      shape.graphics.beginFill("#ffffff").drawRoundRect(0,0,w,h,24).endFill();
      const tail = new createjs.Shape();
      tail.graphics.beginFill("#ffffff")
        .moveTo(80, h).lineTo(140, h).lineTo(110, h+40).closePath().endFill();
      cont.addChild(shape, txt, tail);
      cont.regX = 0; cont.regY = h; cont.alpha = 0; cont.name = "bubble";
      return { cont, txt, boxW: w, boxH: h };
    }
    // Blue answer box: white ghost + black typed overlay
    function makeAnswerBox(answerStr, isFinal = false) {
  const cont = new createjs.Container();
  // Font settings
  const fontSpec = "bold 45px Arial";  
  // Measure text width dynamically
  const tempText = new createjs.Text(answerStr, fontSpec, "#ffffff");
  const textBounds = tempText.getBounds();
  const paddingX = 50;
  const paddingY = 30;
  const w = (textBounds ? textBounds.width : 0) + paddingX * 2;
  const h = (textBounds ? textBounds.height : 0) + paddingY * 2;
  // Draw background
  const bg = new createjs.Shape();
  const bgColor = isFinal ? "#90ee90" : "#1976d2";  
  bg.graphics.beginFill(bgColor).drawRoundRect(0, 0, w, h, 22).endFill();
  // "Ghost" (full answer in white for reference)
  const ghost = new createjs.Text(answerStr, fontSpec, "#ffffff");
  ghost.x = paddingX;
  ghost.y = paddingY;
  // "Typed" (player’s input text)
  const typed = new createjs.Text("", fontSpec, "#000000");
  typed.x = paddingX;
  typed.y = paddingY;
  // Build container
  cont.addChild(bg, ghost, typed);
  cont.regX = w / 2;
  cont.regY = h / 2;
  cont.alpha = 0;
  cont.name = "answerBox";
  // 🔑 Tap handler: focus hidden input
  cont.on("click", () => {
    const hiddenInput = document.getElementById("hiddenInput");
    if (hiddenInput) {
      hiddenInput.value = "";
      hiddenInput.focus();   // will open keyboard (works on iOS if tapped!)
      // Keep updating "typed" text as the user types
      hiddenInput.oninput = () => {
        typed.text = hiddenInput.value;
      };
    }
  });
  return { cont, ghost, typed, w, h };
}
   function enableAnswerInput(ansUI) {
  // Reset UI
  const target = correctAnswer;
  const typedText = ansUI.typed;
  typedText.text = "";
  const hiddenInput = document.getElementById("hiddenInput");
  hiddenInput.value = "";
  hiddenInput.focus(); // Immediately bring up the keyboard on mobile
  let progress = 0; // number of correct leading chars accepted
  function finishSuccess() {
    hiddenInput.removeEventListener('input', onInput);
    hiddenInput.blur(); // Hide the keyboard
    if (recognition) {
      recognition.onresult = null;
      recognition.onerror = null;
      try { recognition.stop(); } catch (_) {}
    }
    micBtn.style.display = "none";
    bubbleSayAhhAndFadeOut();
  }
  window.currentFinishSuccess = finishSuccess;
  function onInput(e) {
    if (!monsterActive) return;

    // 1. Normalize the input: Replace curly quotes with straight quotes
    // This ensures that when an iPad types ’ it is treated as '
    let inputValue = hiddenInput.value
      .replace(/[\u2018\u2019]/g, "'") // Single curly quotes
      .replace(/[\u201C\u201D]/g, '"'); // Double curly quotes

    // Sync the hidden input so the browser state matches our logic
    hiddenInput.value = inputValue;

    let newProgress = progress;
    console.log("target " + target);

    // 2. Check for match using normalized value
    if (inputValue.length > progress && 
        inputValue.toLowerCase().startsWith(target.slice(0, inputValue.length).toLowerCase())) {
        newProgress = inputValue.length;
    } else {
        // Handle backspace or incorrect input
        newProgress = 0;
        let longestPrefix = 0;
        for (let i = 0; i < inputValue.length; i++) {
            // IMPORTANT: Also normalize the character comparison here
            let charTyped = inputValue[i].toLowerCase();
            let charTarget = target[i].toLowerCase();
            
            if (charTyped === charTarget) {
                longestPrefix = i + 1;
            } else {
                break;
            }
        }
        newProgress = longestPrefix;
        hiddenInput.value = target.slice(0, newProgress); // Snap back to correct prefix
    }

    progress = newProgress;
    typedText.text = target.slice(0, progress);

    if (progress >= target.length) {
      finishSuccess();
    }
  }
  // Use 'input' event for real-time updates (mobile-friendly)
  hiddenInput.addEventListener('input', onInput);
  // ---- Speech input (optional) ----
  if (recognition) {
    micBtn.onclick = () => {
      // Your existing speech recognition logic remains here
      // ... (no changes needed for this part)
      if (micBtn.classList.contains('listening')) {
        try { recognition.stop(); } catch (_) {}
        micBtn.classList.remove('listening'); micBtn.textContent = "🎤 Speak";
        return;
      }
      hiddenInput.blur(); // Hide keyboard when mic is used
      micBtn.classList.add('listening'); micBtn.textContent = "🛑 Stop";
      recognition.start();
    };
    recognition.onresult = (evt) => {
      const said = (evt.results[0][0].transcript || "");
      const norm = s => s.replace(/\s+/g, ' ').trim().toLowerCase();
      const a = norm(said);
      const b = norm(target);
      let i = 0;
      while (i < a.length && i < b.length && a[i] === b[i]) i++;
      progress = i; // update progress based on speech
      typedText.text = target.slice(0, progress); // update visual text
      if (progress >= target.length) {
          finishSuccess();
      }
      micBtn.classList.remove('listening'); micBtn.textContent = "🎤 Speak";
    };
    recognition.onerror = () => { micBtn.classList.remove('listening'); micBtn.textContent = "🎤 Speak"; };
    recognition.onend   = () => { micBtn.classList.remove('listening'); micBtn.textContent = "🎤 Speak"; };
  }
}


  document.addEventListener("click", (e) => {
      const hiddenInput = document.getElementById("hiddenInput");
      
      if (monsterActive && hiddenInput && document.activeElement !== hiddenInput) {
          hiddenInput.focus();
      }
  }, true); //


    function bubbleSayAhhAndFadeOut() {
  // If monsterContainer is missing (final stage), skip animation
  if (!monsterContainer) {
    monstersDefeated++;
    if (monstersDefeated >= totalMonstersThisGame) {
      // Final stage → trigger cave_outside after delay
      setTimeout(() => showCaveOutside(), 500);
      localStorage.setItem('game_finished', "1");
        setTimeout(() => {
            window.top.location.href = `process_post_game.php`;
        }, 1500); // Allow 1.5 seconds for "Time's Up" animation
    } else {
      clearInterval(countdownInterval);
      removeCircularTimer();
      resumeGameAfterEncounter();
      scheduleNextMonster();
    }
    return;
  }
  // Change bubble text to "Ahhhh!"
  let textNode = null;
  for (let i = 0; i < bubbleContainer.children.length; i++) {
    if (bubbleContainer.children[i] instanceof createjs.Text) {
      textNode = bubbleContainer.children[i];
      break;
    }
  }
  if (textNode) textNode.text = "Ahhhh!";
  clearInterval(countdownInterval);
  countdownRemaining = setTotalTimount;
  //bgmSoundInstance.paused = false;
  //createjs.Sound.play("bgm", { loop: -1, volume: 0.4 });
  removeCircularTimer();
  // Fade out monsterContainer
  createjs.Tween.get(monsterContainer)
    .wait(350)
    .to({ alpha: 0 }, 500)
    .call(() => {
      stage.removeChild(monsterContainer);
      monsterContainer = null;
      monsterActive = false;
      monstersDefeated++;
      if (monstersDefeated >= totalMonstersThisGame) {
        // Final stage → show cave_outside after delay
        setTimeout(() => showCaveOutside(), 500);
        // After animation, redirect to the post_game.html, passing the score
        localStorage.setItem('game_finished', "1");
        setTimeout(() => {
            window.top.location.href = `process_post_game.php`;
        }, 1500); // Allow 1.5 seconds for "Time's Up" animation
      } else {
        resumeGameAfterEncounter();
        scheduleNextMonster();
      }
    });
}
function showCaveOutside() {
  const img = loader.getResult("cave_outside");
  if (!img) return;
  const bmp = new createjs.Bitmap(img);
  bmp.alpha = 0; // start invisible
  const scale = Math.max(canvas.width / img.width, canvas.height / img.height);
  bmp.scaleX = bmp.scaleY = scale;
  bmp.x = (canvas.width - img.width * scale) / 2;
  bmp.y = (canvas.height - img.height * scale) / 2;
  // Add on top of all layers
  stage.addChild(bmp);
  // Cloud/fade-in effect
  createjs.Tween.get(bmp)
    .to({ alpha: 1 }, 2000, createjs.Ease.cubicOut);
}
   function showFinishImage() {
  // Cloud overlay
  const cloud = new createjs.Shape();
  cloud.graphics.beginFill("white").drawRect(0, 0, canvas.width, canvas.height);
  cloud.alpha = 0;
  stage.addChild(cloud);
  // Fade in cloud
  createjs.Tween.get(cloud)
    .to({ alpha: 1 }, 1000)
    .call(() => {
      // Swap background to cave_outside
      stage.removeAllChildren();
      const img = loader.getResult("cave_outside");
      const bmp = new createjs.Bitmap(img);
      const scale = Math.max(canvas.width / img.width, canvas.height / img.height);
      bmp.scaleX = bmp.scaleY = scale;
      bmp.x = (canvas.width - img.width * scale) / 2;
      bmp.y = (canvas.height - img.height * scale) / 2;
      stage.addChild(bmp);
    })
    .to({ alpha: 0 }, 1000) // fade out cloud
    .call(() => stage.removeChild(cloud));
}

    window.addEventListener('pagehide', function() {
        window.parent.postMessage({ action: 'HIDE_TIMER' }, '*');
    });

    // 2. Detects when a page is shown (Back button landing)
    window.addEventListener('pageshow', function() {
        // If we are NOT in the game logic, hide the timer immediately
        if (typeof gameRunning === 'undefined' || !gameRunning) {
            window.parent.postMessage({ action: 'HIDE_TIMER' }, '*');
        }
    });
    /* ================== END MONSTER ENCOUNTER SYSTEM ================== */
  </script>
</body>
