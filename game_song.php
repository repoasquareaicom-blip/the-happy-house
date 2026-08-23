<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, interactive-widget=overlays-content">
  <title>Song Game</title>
  <script src="https://code.createjs.com/1.0.0/createjs.min.js"></script>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
  <style>
    :root {
      --color1: #99B194;
      --color2: #D9E1A4;
      --color3: #6b8167;
    }
    html, body {
      margin: 0;
      padding: 0;
      height: 100%;
      width: 100%;
      background: #000;
      display: flex;
      justify-content: center;
      align-items: center;
      overflow: hidden;
      font-family: Calibri, sans-serif;
    }
    #gameWrapper {
      position: relative;
      height: 100%;
      display: flex;
      justify-content: center;
      align-items: center;
    }
    canvas {
      display: block;
      background: #000;
    }
    #overlay {
      position: absolute;
      top: 0; left: 0;
      width: 100%;
      height: 100%;
      background: rgba(0,0,0,0.5);
      color: white;
      font-size: 36px;
      display: flex;
      justify-content: center;
      align-items: center;
      cursor: pointer;
      z-index: 10;
    }
    #loadingOverlay {
      position: absolute;
      top: 0; left: 0;
      width: 100%;
      height: 100%;
      background: rgba(0,0,0,0.85);
      display: flex;
      justify-content: center;
      align-items: center;
      flex-direction: column;
      color: white;
      font-size: 32px;
      z-index: 20;
    }
    #progressBar {
      width: 400px;
      height: 20px;
      background: rgba(255,255,255,0.2);
      border-radius: 10px;
      margin-top: 20px;
      overflow: hidden;
    }
    #progressFill {
      width: 0%;
      height: 100%;
      background: var(--color1);
      border-radius: 10px;
      transition: width 0.2s;
    }
  </style>
</head>
<body>
  <audio id="bgMusic" loop muted>
  </audio>
  <audio id="buzzerSound" loop muted>
    <source src="assets/game_song/audio/buzzer-or-wrong-answer-20582.mp3" type="audio/mpeg">
  </audio>
  <div id="gameWrapper">
    <canvas id="gameCanvas" width="2338" height="1668"></canvas>
    <!-- <div id="overlay">Tap to Continue</div> -->
    <div id="loadingOverlay">
      Loading...
      <div id="progressBar" style="width: 300px; height: 20px; background: #eee; border-radius: 10px; overflow: hidden; margin-top: 15px;">
          <div id="progressFill" style="height:100%; width:0%; background: linear-gradient(90deg, #007BFF, #00BFFF); transition: width 0.3s ease;"></div>
      </div>
    </div>
  </div>
  <!-- X: <input type="text" id="coordX" readonly>
Y: <input type="text" id="coordY" readonly> -->
  <script>
    const stageWidth = 2338;
    const stageHeight = 1668;
    const canvas = document.getElementById("gameCanvas");
    const stage = new createjs.Stage(canvas);
    createjs.Touch.enable(stage); // ✅ Enables touch for mobile
    let timerText, scoreText;
    let timeLeft;
    let score = 0;
    let gameStarted = false;
    let timerInterval;
    let gameEnded = false;
    var theme = localStorage.getItem("selectedTheme") || "dino";
    
    var currentDifficulty = localStorage.getItem('gameTrickLevel') || 'tricky';
    const beatTimesRelaxed = [
    8205.507, 10520.699, 12769.744, 14952.639, 17267.832,
    19583.025, 21898.217, 24213.410, 26462.454, 28777.647,
    31092.840, 33341.884, 35590.928, 37839.972, 40221.313,
    42470.358, 44785.550, 46968.446, 49283.639, 50408.161,
    51598.832, 52789.502, 53847.876, 56163.068, 58412.113,
    63042.498, 65225.394, 67606.735, 72104.823, 74420.016,
    76801.357, 81299.446, 83482.342, 85797.534, 88112.727,
    89369.546, 94992.156, 97307.349, 99490.245, 101871.586,
    104120.630, 106369.675, 108684.867, 110933.911
];
const beatTimesNormal = [
    8139, 10490, 12710, 14995, 17280,
    19566, 21851, 22373, 24202, 24724,
    26422, 27009, 28707, 29360, 30992,
    31580, 33277, 33865, 35563, 36216,
    37848, 38501, 40133, 40786, 41309,
    42484, 43006, 43594, 44704, 45291,
    45814, 46467, 47054, 47577, 48099,
    48687, 49274, 49927, 50450, 50907,
    51560, 52147, 52670, 53323, 53845,
    56130, 57306, 58481, 60701, 61876,
    62921, 65206, 66381, 67557, 69842,
    70887, 72193, 74412, 75457, 76763,
    79114, 80289, 81268, 83554, 84664,
    85904, 88189, 89299, 89822, 90475,
    92825, 94980, 97331, 98375, 99385,
    102466, 103511, 104556, 106906, 108212,
    109192, 111542, 112783, 113762
];
const beatTimesTricky = [
  7497, 8372, 9214, 9829, 10639, 11514, 12097, 12939, 13781, 14397,
  15206, 16081, 16632, 18932, 21232, 21718, 23499, 24082, 25799, 26058,
  26350, 28067, 28358, 28585, 30366, 31209, 32634, 32926, 33509, 34902,
  35063, 35485, 37234, 37396, 37785, 39534, 39728, 40052, 40344, 41769,
  41931, 42190, 42482, 42643, 44101, 44296, 44522, 44781, 44911, 45170,
  45494, 46304, 46498, 46757, 47049, 47211, 47502, 47762, 48604, 48766,
  49057, 49349, 49478, 49738, 50062, 50321, 50483, 50904, 51066, 51357,
  51681, 51811, 52070, 52329, 52621, 52815, 53204, 54046, 54920, 55504,
  56378, 57188, 57803, 58743, 59779, 60039, 61172, 61788, 62306, 63181,
  64606, 65448, 66323, 66906, 67845, 68655, 69206, 70372, 71506, 72316,
  73190, 73806, 74616, 75523, 76106, 76915, 77758, 78308, 79183, 80058,
  80673, 81450, 82293, 82941, 83783, 84657, 85208, 86050, 86893, 87476,
  88318, 89225, 89743, 92075, 94343, 95185, 96027, 96643, 97517, 98392,
  98943, 99753, 100627, 102053, 102927, 103510, 104352, 105162, 105810,
  106652, 107462, 108013, 108887, 109730, 110313, 111220, 112030, 112580
];
    let beatTimes = [];
    switch (currentDifficulty) {
        case "relaxed":
            beatTimes = beatTimesRelaxed;
            break;
        case "tricky":
            beatTimes = beatTimesTricky;
            break;
        default:
            beatTimes = beatTimesNormal;
            break;
    }
    // ✅ New code to set the dynamic background music source
    var bgMusic = document.getElementById("bgMusic");
    const audioSourcePath = `assets/game_song/audio/game_sound-${currentDifficulty}.mp3`;
    // Check if a <source> element exists, otherwise create one
    let sourceElement = bgMusic.querySelector('source');
    if (!sourceElement) {
        sourceElement = document.createElement('source');
        sourceElement.type = 'audio/mpeg';
        bgMusic.appendChild(sourceElement);
    }
    sourceElement.src = audioSourcePath;
    // Load the new source
    bgMusic.load();
    // New code to set timeLeft dynamically:
    bgMusic.addEventListener('loadedmetadata', function() {
        // Get the duration in seconds and round it to the nearest whole number
        const audioDuration = Math.round(bgMusic.duration); 
        // Set the game timer length
        timeLeft = audioDuration;
        //timeLeft = 600000;
        console.log("Audio duration set:", timeLeft); 
        // Update the timer display if it exists already
        if (timerText) {
            timerText.text = (timeLeft < 10 ? "0" + timeLeft : timeLeft);
            stage.update();
        }
        updateGlobalTimer(audioDuration,(timeLeft < 10 ? "0" + timeLeft : timeLeft));
    });     
    // Theme
    var theme = localStorage.getItem("selectedTheme") || "fairy";
    //document.getElementById("borderImage").style.backgroundImage = `url('assets/images/${theme}.svg')`;
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
    const GRADIENT_START = color1 + "AA";
    const GRADIENT_END = color2 + "25";
    let ASSETS = {
    // --- GREEN ASSETS ---
      'green': {
          'circles': [
              { "id": "green_circle_1", "src": "assets/game_song/images/green_circle_1.png" },
              { "id": "green_circle_2", "src": "assets/game_song/images/green_circle_2.png" },
              { "id": "green_circle_3", "src": "assets/game_song/images/green_circle_3.png" },
              { "id": "green_circle_4", "src": "assets/game_song/images/green_circle_4.png" },
              { "id": "green_circle_5", "src": "assets/game_song/images/green_circle_5.png" },
              { "id": "green_circle_6", "src": "assets/game_song/images/green_circle_6.png" },
              { "id": "green_circle_7", "src": "assets/game_song/images/green_circle_7.png" },
              { "id": "green_circle_8", "src": "assets/game_song/images/green_circle_8.png" },
          ],
          'curves': [
              { id: "green_curvelong", src: "assets/game_song/images/green_curvelong.png", numberOffset: { x: 720, y: 1650} },
              { id: "green_curve_short", src: "assets/game_song/images/green_curve_short.png", numberOffset: { x: 720, y: 1650 } },
              { id: "green_line_long", src: "assets/game_song/images/green_line_long.png", numberOffset: { x: 731, y: 738 } },
              { id: "green_line_short", src: "assets/game_song/images/green_line_short.png", numberOffset: { x: 705, y: 728 } }
          ],
          'ring': { "id": "green_ring", "src": "assets/game_song/images/green_ring.png", offset: { x: 720, y:1650 }  },
      },
      // --- ORANGE ASSETS ---
      'orange': {
          'circles': [
              { "id": "orange_circle_1", "src": "assets/game_song/images/orange_circle_1.png" },
              { "id": "orange_circle_2", "src": "assets/game_song/images/orange_circle_2.png" },
              { "id": "orange_circle_3", "src": "assets/game_song/images/orange_circle_3.png" },
              { "id": "orange_circle_4", "src": "assets/game_song/images/orange_circle_4.png" },
              { "id": "orange_circle_5", "src": "assets/game_song/images/orange_circle_5.png" },
              { "id": "orange_circle_6", "src": "assets/game_song/images/orange_circle_6.png" },
              { "id": "orange_circle_7", "src": "assets/game_song/images/orange_circle_7.png" },
              { "id": "orange_circle_8", "src": "assets/game_song/images/orange_circle_8.png" },
          ],
          'curves': [
              { id: "orange_curvelong", src: "assets/game_song/images/orange_curvelong.png", numberOffset: { x: 720, y: 1650} },
              { id: "orange_curve_short", src: "assets/game_song/images/orange_curve_short.png", numberOffset: { x: 720, y: 1650 } },
              { id: "orange_line_long", src: "assets/game_song/images/orange_line_long.png", numberOffset: { x: 731, y: 738 } },
              { id: "orange_line_short", src: "assets/game_song/images/orange_line_short.png", numberOffset: { x: 705, y: 728 } }
          ],
          'ring': { "id": "orange_ring", "src": "assets/game_song/images/orange_ring.png", offset: { x: 720, y:1650 } },
      },
      // --- RED ASSETS ---
      'red': {
          'circles': [
              { "id": "red_circle_1", "src": "assets/game_song/images/red_circle_1.png" },
              { "id": "red_circle_2", "src": "assets/game_song/images/red_circle_2.png" },
              { "id": "red_circle_3", "src": "assets/game_song/images/red_circle_3.png" },
              { "id": "red_circle_4", "src": "assets/game_song/images/red_circle_4.png" },
              { "id": "red_circle_5", "src": "assets/game_song/images/red_circle_5.png" },
              { "id": "red_circle_6", "src": "assets/game_song/images/red_circle_6.png" },
              { "id": "red_circle_7", "src": "assets/game_song/images/red_circle_7.png" },
              { "id": "red_circle_8", "src": "assets/game_song/images/red_circle_8.png" },
          ],
          'curves': [
              { id: "red_curvelong", src: "assets/game_song/images/red_curvelong.png", numberOffset: { x: 720, y: 1650} },
              { id: "red_curve_short", src: "assets/game_song/images/red_curve_short.png", numberOffset: { x: 720, y: 1650 } },
              { id: "red_line_long", src: "assets/game_song/images/red_line_long.png", numberOffset: { x: 731, y: 738 } },
              { id: "red_line_short", src: "assets/game_song/images/red_line_short.png", numberOffset: { x: 705, y: 728 } }
          ],
          'ring': { "id": "red_ring", "src": "assets/game_song/images/red_ring.png", offset: { x: 720, y:1650 } },
      }
  };
  function createAssetManifest(structuredAssets) {
      const manifest = [];
      const colors = ['green', 'orange', 'red',];
      colors.forEach(color => {
          const colorSet = structuredAssets[color];
          if (colorSet) {
              // Use spread syntax (...) to quickly add all items from nested arrays
              if (colorSet.circles) {
                  manifest.push(...colorSet.circles);
              }
              if (colorSet.curves) {
                  manifest.push(...colorSet.curves);
              }
              // Add the single ring asset (needs to be spread into an array first)
              if (colorSet.ring) {
                  manifest.push(colorSet.ring);
              }
          }
      });
      return manifest;
  }
  const generalAssets = [
      { id: "bgImage", src: "assets/game_song/images/song_game_bg.png" },
      { id: "char1", src: "assets/game_scavenger/characters/char1.png" },
      { id: "char2", src: "assets/game_scavenger/characters/char2.png" },
      { id: "unmuted", src: "assets/images/speaker-unmuted.png" },
      { id: "muted", src: "assets/images/speaker-muted.png" },
      { id: "song_game_mascot_body", src: "assets/game_song/images/song_game_mascot_body.png" },
      { id: "song_game_mascot_head", src: "assets/game_song/images/song_game_mascot_head.png" }
  ];
    // Flatten the color-coded assets
    const colorCodedAssets = createAssetManifest(ASSETS);
    // Combine general and color-coded assets using the spread operator on ARRAYS
    const finalManifest = [...generalAssets, ...colorCodedAssets];
    // ----------------------------------------------------------------------
    // 3. LOADER EXECUTION
    // ----------------------------------------------------------------------
    // Loader
    const queue = new createjs.LoadQueue();
    queue.installPlugin(createjs.Sound);
    // 🚨 Load the correctly flattened array!
    queue.loadManifest(finalManifest);
    const loadingOverlay = document.getElementById("loadingOverlay");
    const progressFill = document.getElementById("progressFill");
    queue.on("progress", e=>{
      progressFill.style.width = Math.floor(e.progress*100) + "%";
    });
    queue.on("complete", ()=>{
      loadingOverlay.style.display = "none";
      
      handleComplete();
    });
    var timerStarted = false;
    function handleComplete() {
        // 1. Load and display the Background Image first
        const bgImage = new createjs.Bitmap(queue.getResult("bgImage"));
        resizeToStageHeight(bgImage); // Reuse the existing scaling helper
        stage.addChild(bgImage);
// -------------------------------
        // Add Mascot (body + head)
        // -------------------------------
        const mascotBody = new createjs.Bitmap(queue.getResult("song_game_mascot_body"));
        const mascotHead = new createjs.Bitmap(queue.getResult("song_game_mascot_head"));
        const bodyBounds = mascotBody.getBounds();
        const headBounds = mascotHead.getBounds();
        const desiredHeight = 800;
        const scaleFactor = desiredHeight / bodyBounds.height;
        mascotBody.scaleX = mascotBody.scaleY = scaleFactor;
        mascotHead.scaleX = mascotHead.scaleY = scaleFactor;
        mascotBody.regX = bodyBounds.width / 2;
        mascotBody.regY = bodyBounds.height / 2;
        mascotBody.x = bodyBounds.width * scaleFactor / 2 + 50;
        mascotBody.y = stage.canvas.height - bodyBounds.height * scaleFactor / 2 - 20;
        stage.addChild(mascotBody);
        mascotHead.regX = headBounds.width / 2;
        mascotHead.regY = headBounds.height / 2;
        mascotHead.x = mascotBody.x - 50;
        mascotHead.y = mascotBody.y - (bodyBounds.height/2 * scaleFactor) + (headBounds.height/2 * scaleFactor) - 100;
        stage.addChild(mascotHead);
        window.mascotBody = mascotBody;
        window.mascotHead = mascotHead;
        animateMascotIdle();
        // 2. Add the Play Area Container (New Implementation)
        const playArea = new createjs.Shape();
        // Calculate dimensions based on percentages of stageWidth (2338) and stageHeight (1668)
        const PA_X = 0;
        const PA_Y = 0;
        const PA_W = 2338;
        const PA_H = 1668;
        playArea.graphics
            .beginFill("rgba(0, 0, 0, 0.7)") // Dark Black with 90% opacity
            .drawRect(PA_X, PA_Y, PA_W, PA_H);
        stage.addChild(playArea);
        // const border = new createjs.Bitmap(`assets/images/${theme}.png`);
        // resizeToStageHeight(border);
        // stage.addChild(border);
        const panelRadius = 140;
        const panel = new createjs.Shape();
        panel.graphics
            .setStrokeStyle(6)
            .beginStroke(color3)
            .beginRadialGradientFill([GRADIENT_START, GRADIENT_END], [0,1], 0,0,0, 0,0,panelRadius)
            .drawCircle(0,0,panelRadius);
        // panel.x = stageWidth - panelRadius - 10;
        // panel.y = panelRadius + 10;
        panel.y = 100000;
        panel.shadow = new createjs.Shadow("#000",4,4,10);
        panel.alpha = 0.7;
        stage.addChild(panel);
        timerText = new createjs.Text(timeLeft, "100px Poppins, Arial", "#FFD700");
        timerText.textAlign = "center";
        timerText.textBaseline = "middle";
        timerText.x = panel.x ;
        timerText.y = panel.y - 20;
        timerText.shadow = new createjs.Shadow("#000",2,2,5);
        stage.addChild(timerText);
      const gradient = stage.graphics
        ? stage.graphics.createLinearGradient(0, 0, 0, 50)
        : null;
        // Background box
        const scoreBox = new createjs.Shape();
        scoreBox.graphics
        .beginLinearGradientFill(
            ["rgba(255,255,255,0.15)", "rgba(255,255,255,0.05)"],
            [0, 1],
            0, 0, 0, 100 // x0, y0, x1, y1
        )
        .drawRoundRect(0, 0, 130, 100, 15);
        scoreBox.x = panel.x - 65;
        scoreBox.y = panel.y + 5;
        scoreBox.shadow = new createjs.Shadow("rgba(0,0,0,0.4)", 0, 4, 10);
        // Label: "SCORE"
        const scoreLabel = new createjs.Text("SCORE", "24px Poppins, Arial", "#cccccc");
        scoreLabel.textAlign = "center";
        scoreLabel.textBaseline = "middle";
        scoreLabel.x = panel.x;
        scoreLabel.y = scoreBox.y + 25;
        // Value
        scoreText = new createjs.Text("0", "bold 54px Poppins, Arial", "#00e0ff");
        scoreText.textAlign = "center";
        scoreText.textBaseline = "middle";
        scoreText.x = panel.x;
        scoreText.y = scoreBox.y + 70;
        scoreText.shadow = new createjs.Shadow("rgba(0,255,255,0.4)", 0, 0, 20);
        // Add to stage
        stage.addChild(scoreBox, scoreLabel, scoreText);
        // -------------------------------
        // Stage update
        // -------------------------------
        stage.update();
        // Enable mouse input
        stage.enableMouseOver();
        stage.mouseEnabled = true;
        // Audio setup
        mutedImg = queue.getResult("muted");
        unmutedImg = queue.getResult("unmuted");
        bgMusic = document.getElementById("bgMusic");
        bgMusic.muted = true; // Start muted
        bgMusic.volume = 0; 
        buzzerSound = document.getElementById("buzzerSound");
        buzzerSound.muted = true; // Start muted
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
            
            window.parent.postMessage({ 
                action: 'ACTIVATE_GLOBAL_AUDIO_ICON_ONLY', 
                audioSrc:  `assets/game_song/audio/game_sound-${currentDifficulty}.mp3`
            }, '*');
        
          bgMusic.muted = false;
           if (bgMusic.paused) {
               bgMusic.play();
               audioIcon.image = unmutedImg;
               stage.update();
             } else {
               bgMusic.pause();
               audioIcon.image = mutedImg;
               stage.update();
          }
        //   bgMusic.play().catch(e => console.log("Play blocked:", e));
            if (!gameStarted) {
                gameStarted = true;
                window.musicStartTime = performance.now();
                spawnCharacters(); // ✅ now we have 10 animated characters available
                playBeatSequence();  
            }
            // ✅ When audio actually starts playing, begin timer
            bgMusic.onplay = () => {
                if (!timerStarted) {
                    timerStarted = true;
                    startTimer();
                }
            };
            audioIcon.visible = false;
            overlay.visible = false;
            tapText.visible = false;

          // Animate icon to top-left with bounce
        //   createjs.Tween.get(audioIcon)
        //     .to({ x: 140, y: 140, scaleX: 200 / mutedImg.width, scaleY: 200 / mutedImg.height }, 800, createjs.Ease.bounceOut);
        //   // Fade out overlay + text
        //   createjs.Tween.get(overlay).to({ alpha: 0 }, 500).call(() => stage.removeChild(overlay));
        //   createjs.Tween.get(tapText).to({ alpha: 0 }, 500).call(() => stage.removeChild(tapText));
         
        });
    }
    // Example beat times in milliseconds (adjust as needed)
    // Array of all curve IDs
    const allCurveIDs = [];
    ['green', 'orange', 'red'].forEach(color => {
        ASSETS[color].curves.forEach(curve => allCurveIDs.push(curve.id));
    });
   let lastCurve = null;
   let lastCircle = null; // <-
let containerBorder;
// Define the container area
const CONTAINER = { 
    x: 300, 
    y: 300, 
    width: 1800, 
    height: 1100 
};

function updateGlobalTimer(total, timeLeft) {
    window.parent.postMessage({
        action: 'UPDATE_TIMER',
        remaining: timeLeft,
        total: total, // Your game's starting time
        themeColor: color1 // This uses your theme's primary color (e.g., #7366C6 for Fairy)
    }, '*');
}


// Draw border (once)
function drawContainerBorder() {
    containerBorder = new createjs.Shape();
    containerBorder.graphics
        .setStrokeStyle(5)
        //.beginStroke("rgba(0, 255, 0, 0.5)") // semi-transparent green
        .drawRect(CONTAINER.x, CONTAINER.y, CONTAINER.width, CONTAINER.height);
    stage.addChild(containerBorder);
    stage.update();
}
// ---- Curve spawning function ----
const colors = ['green', 'orange', 'red'];
let currentColorIndex = 0;  // start with green
let currentNumber = 1;      // start with 1
let lastRing = null;
function spawnRandomCurve(type, beatTime) {
    const color = colors[currentColorIndex];  
    const prevCurve = lastCurve;
    const prevCircle = lastCircle;
    const prevRing = lastRing;
    // ================================
    // Define pre-captured curve paths
    // ================================
     const CURVE_PATHS = {
      curvelong: [
        { x: 720, y: 1650 },{ x: 800, y: 1490 }, { x: 777, y: 1564 }, { x: 820, y: 1493 },
        { x: 863, y: 1436 }, { x: 934, y: 1350 }, { x: 1020, y: 1264 },
        { x: 1091, y: 1207 }, { x: 1206, y: 1107 }, { x: 1349, y: 1021 },
        { x: 1549, y: 950 }, { x: 1720, y: 864 }, { x: 1891, y: 821 },
        { x: 1991, y: 793 }, { x: 2106, y: 764 }, { x: 2234, y: 736 },
        { x: 2334, y: 707 }, { x: 2420, y: 693 }, { x: 2534, y: 664 },
        { x: 2649, y: 664 }, { x: 2777, y: 664 }, { x: 2891, y: 636 },
        { x: 3020, y: 636 }, { x: 3134, y: 636 }, { x: 3291, y: 636 },
        { x: 3406, y: 636 }, { x: 3534, y: 636 }, { x: 3720, y: 664 },
        { x: 3906, y: 693 }, { x: 4091, y: 721 }, { x: 4249, y: 779 },
        { x: 4391, y: 807 }, { x: 4491, y: 850 }, { x: 4620, y: 893 },
        { x: 4734, y: 936 }, { x: 4849, y: 993 }, { x: 4920, y: 1036 },
        { x: 5020, y: 1093 }, { x: 5134, y: 1164 }, { x: 5220, y: 1236 },
        { x: 5320, y: 1321 }, { x: 5391, y: 1421 }, { x: 5449, y: 1507 },
        { x: 5477, y: 1564 }, { x: 5520, y: 1636 },
      ],
      curveshort: [
        { x: 720, y: 1639 }, { x: 749, y: 1553 }, { x: 777, y: 1468 },
        { x: 849, y: 1368 }, { x: 920, y: 1253 }, { x: 992, y: 1182 },
        { x: 1049, y: 1125 }, { x: 1149, y: 1053 }, { x: 1235, y: 996 },
        { x: 1335, y: 925 }, { x: 1420, y: 896 }, { x: 1520, y: 839 },
        { x: 1649, y: 796 }, { x: 1735, y: 782 }, { x: 1835, y: 739 },
        { x: 1963, y: 739 }, { x: 2092, y: 739 }, { x: 2220, y: 739 },
        { x: 2363, y: 768 }, { x: 2449, y: 796 }, { x: 2649, y: 868 },
        { x: 2749, y: 925 }, { x: 2863, y: 996 }, { x: 2949, y: 1053 },
        { x: 3035, y: 1125 }, { x: 3135, y: 1211 }, { x: 3192, y: 1282 },
        { x: 3263, y: 1368 }, { x: 3306, y: 1453 }, { x: 3335, y: 1539 },
        { x: 3349, y: 1596 }, { x: 3363, y: 1639 }
      ],
      linelong:[
        { x: 731, y: 738 }, { x: 831, y: 738 }, { x: 931, y: 738 },
        { x: 1031, y: 738 }, { x: 1131, y: 738 }, { x: 1231, y: 738 },
        { x: 1331, y: 738 }, { x: 1431, y: 738 }, { x: 1531, y: 738 },
        { x: 1631, y: 738 }, { x: 1731, y: 738 }, { x: 1831, y: 738 },
        { x: 1931, y: 738 }, { x: 2031, y: 738 }, { x: 2131, y: 738 },
        { x: 2231, y: 738 }, { x: 2331, y: 738 }, { x: 2431, y: 738 },
        { x: 2531, y: 738 }, { x: 2631, y: 738 }, { x: 2731, y: 738 },
        { x: 2831, y: 738 }, { x: 2931, y: 738 }, { x: 3031, y: 738 },
        { x: 3131, y: 738 }, { x: 3231, y: 738 }, { x: 3331, y: 738 },
        { x: 3431, y: 738 }, { x: 3531, y: 738 }, { x: 3631, y: 738 },
        { x: 3731, y: 738 }, { x: 3831, y: 738 }, { x: 3931, y: 738 },
        { x: 4031, y: 738 }, { x: 4131, y: 738 }, { x: 4231, y: 738 },
        { x: 4331, y: 738 }, { x: 4431, y: 738 }, { x: 4531, y: 738 },
        { x: 4631, y: 738 }, { x: 4731, y: 738 }, { x: 4831, y: 738 },
        { x: 4931, y: 738 }, { x: 5031, y: 738 }, { x: 5131, y: 738 },
        { x: 5231, y: 738 }, { x: 5331, y: 738 }, { x: 5431, y: 738 },
        { x: 5507, y: 738 }
      ],
      lineshort: [
        { x: 705, y: 728 }, { x: 805, y: 728 }, { x: 905, y: 728 },
        { x: 1005, y: 728 }, { x: 1105, y: 728 }, { x: 1205, y: 728 },
        { x: 1305, y: 728 }, { x: 1405, y: 728 }, { x: 1505, y: 728 },
        { x: 1605, y: 728 }, { x: 1705, y: 728 }, { x: 1805, y: 728 },
        { x: 1905, y: 728 }, { x: 2005, y: 728 }, { x: 2105, y: 728 },
        { x: 2205, y: 728 }, { x: 2305, y: 728 }, { x: 2405, y: 728 },
        { x: 2505, y: 728 }, { x: 2605, y: 728 }, { x: 2705, y: 728 },
        { x: 2805, y: 728 }, { x: 2905, y: 728 }, { x: 3005, y: 728 },
        { x: 3105, y: 728 }, { x: 3205, y: 728 }, { x: 3305, y: 728 },
        { x: 3372, y: 728 }
      ]
    };
    // Pick random curve
    const curveSet = ASSETS[color].curves;
    const randomCurve = curveSet[Math.floor(Math.random() * curveSet.length)];
     // ✅ Use shared path for all colors
    var currentPath = CURVE_PATHS.curvelong;
    if (randomCurve.id.includes("curve_short")) {
      currentPath = CURVE_PATHS.curveshort;
    } else if (randomCurve.id.includes("line_long")) {
      currentPath = CURVE_PATHS.linelong;
    } else if (randomCurve.id.includes("line_short")) {
      currentPath = CURVE_PATHS.lineshort;
    }
    const bitmap = new createjs.Bitmap(queue.getResult(randomCurve.id));
    //const FIXED_SCALE = 0.07;
    // ---------------------------------
// Slightly larger scale multipliers
// ---------------------------------
const FIXED_SCALE = 0.09;  // ⬆️ was 0.07
    bitmap.scaleX = bitmap.scaleY = FIXED_SCALE;
    // -------------------------------
    // Position curve inside container
    // -------------------------------
    const curveW = bitmap.getBounds() ? bitmap.getBounds().width * FIXED_SCALE : 50;
    const curveH = bitmap.getBounds() ? bitmap.getBounds().height * FIXED_SCALE : 50;
    // const baseX = CONTAINER.x + Math.random() * (CONTAINER.width - curveW);
    // const baseY = CONTAINER.y + Math.random() * (CONTAINER.height - curveH);
    let baseX, baseY;
    const CURVE_RANGE_Y = 300; // vertical range for next curve
    if (!lastCurve) {
        // First curve: anywhere in container
        baseX = CONTAINER.x + Math.random() * (CONTAINER.width - curveW);
        baseY = CONTAINER.y + Math.random() * (CONTAINER.height - curveH);
    } else {
        // Next curves: X anywhere, Y within ±CURVE_RANGE_Y of previous curve
        baseX = CONTAINER.x + Math.random() * (CONTAINER.width - curveW);
        const prevY = lastCurve.y;
        const minY = Math.max(CONTAINER.y, prevY - CURVE_RANGE_Y);
        const maxY = Math.min(CONTAINER.y + CONTAINER.height - curveH, prevY + CURVE_RANGE_Y);
        baseY = minY + Math.random() * (maxY - minY);
    }
    bitmap.x = baseX;
    bitmap.y = baseY;
    bitmap.alpha = 0;
    if(type=="curve")
    stage.addChild(bitmap);
                animateMascotBeat();
                console.log("beat");
    // -------------------------------
    // Number circle
    // -------------------------------
    const circleID = `${color}_circle_${currentNumber}`;
    const circleBitmap = new createjs.Bitmap(queue.getResult(circleID));
    const NUM_SCALE = 0.09;    // ⬆️ was 0.07
    circleBitmap.scaleX = circleBitmap.scaleY = NUM_SCALE;
    const cbounds = circleBitmap.getBounds() || { width: 100, height: 100 };
    circleBitmap.regX = cbounds.width / 2;
    circleBitmap.regY = cbounds.height / 2;
    //const offset = randomCurve.numberOffset || { x: 0, y: 0 };
    // const offset = Math.random() < 0.5 
    // ? currentPath[0] 
    // : currentPath[currentPath.length - 1];
    const offset = currentPath[0] ;
    circleBitmap.x = baseX + offset.x * FIXED_SCALE;
    circleBitmap.y = baseY + offset.y * FIXED_SCALE;
    circleBitmap.alpha = 0;
    stage.addChild(circleBitmap);
    // -------------------------------
    // Ring
    // -------------------------------
    const ringAsset = ASSETS[color].ring;
    const ringBitmap = new createjs.Bitmap(queue.getResult(ringAsset.id));
    const RING_SCALE = 0.075;  // ⬆️ was 0.06
    const ringBounds = ringBitmap.getBounds() || { width: 100, height: 100 };
    ringBitmap.regX = ringBounds.width / 2;
    ringBitmap.regY = ringBounds.height / 2;
    //const ringOffset = ringAsset.offset || { x: 0, y: 0 };
    const ringOffset = offset;
    ringBitmap.x = baseX + ringOffset.x * FIXED_SCALE;
    ringBitmap.y = baseY + ringOffset.y * FIXED_SCALE;
    ringBitmap.alpha = 0;
    ringBitmap.scaleX = ringBitmap.scaleY = RING_SCALE * 1.5;
    stage.addChild(ringBitmap);
    // -------------------------------
    // Animations
    // -------------------------------
    bitmap.scaleX = bitmap.scaleY = FIXED_SCALE * 0.6;
    createjs.Tween.get(bitmap)
        .to({ alpha: 1, scaleX: FIXED_SCALE * 1.3, scaleY: FIXED_SCALE * 1.3 }, 200, createjs.Ease.quadOut)
        .to({ scaleX: FIXED_SCALE, scaleY: FIXED_SCALE }, 150, createjs.Ease.quadIn);
    circleBitmap.scaleX = circleBitmap.scaleY = NUM_SCALE * 0.6;
    createjs.Tween.get(circleBitmap)
        .to({ alpha: 1, scaleX: NUM_SCALE * 1.2, scaleY: NUM_SCALE * 1.2 }, 200, createjs.Ease.quadOut)
        .to({ scaleX: NUM_SCALE, scaleY: NUM_SCALE }, 150, createjs.Ease.quadIn);
// -------------------------------
// Ring Animation (Smooth Shrink)
// -------------------------------
ringBitmap.alpha = 1;
const TARGET_SIZE = 125;     // ✅ Final ring size in pixels
// Ensure correct bounds
const rb = ringBitmap.getBounds();
const finalRingScale = TARGET_SIZE / rb.width;
// ✅ Start very big for visible shrink effect
const startScale = finalRingScale * 6; 
// Initialize BIG & fully visible
ringBitmap.scaleX = ringBitmap.scaleY = startScale;
// ✅ Smooth zoom-in shrink to 150px
createjs.Tween.get(ringBitmap)
    .to(
        { scaleX: finalRingScale, scaleY: finalRingScale },
        SHRINK_DURATION,
        createjs.Ease.quadOut
    );
// -------------------------------
// Draw connecting line to previous circle (ensure visible early)
// -------------------------------
let connectingLine = null;
if (prevCircle && circleBitmap) {
    const line = new createjs.Shape();
    // draw connection immediately
    line.graphics
        .setStrokeStyle(6) // thicker, more visible
        .beginStroke(color)
        .moveTo(prevCircle.x, prevCircle.y)
        .lineTo(circleBitmap.x, circleBitmap.y)
        .endStroke();
    line.alpha = 0.9; // ✅ make visible immediately
    stage.addChild(line);
    connectingLine = line;
    // ✅ Update stage immediately so line shows up right away
    stage.update();
    // Optional: slight pulse effect (for visual feedback)
    createjs.Tween.get(line, { loop: false })
        .to({ alpha: 1 }, 150, createjs.Ease.quadOut)
        .to({ alpha: 0.8 }, 150, createjs.Ease.quadIn);
}
// -------------------------------
// Fade out previous curve, circle, ring, and line
// -------------------------------
if (prevCurve) {
    var FADE_DELAY = 600; // ⏳ delay in ms before fading out
    const FADE_DURATION = 600; // fade-out duration
    [prevCurve, prevCircle, prevRing, connectingLine].forEach(item => {
        if (item) {
            createjs.Tween.get(item)
                .wait(FADE_DELAY) // 👈 wait before fade
                .to({ alpha: 0, scaleX: 0, scaleY: 0 }, FADE_DURATION, createjs.Ease.quadIn)
                .call(() => stage.removeChild(item));
        }
    });
}
    // -------------------------------
    // Coordinate input updates
    // -------------------------------
    function updateCoordInputs() {
        const xInput = document.getElementById("coordX");
        const yInput = document.getElementById("coordY");
        if (xInput && yInput) {
            xInput.value = Math.round((circleBitmap.x - baseX) / FIXED_SCALE);
            yInput.value = Math.round((circleBitmap.y - baseY) / FIXED_SCALE);
        }
    }
    if (type === "number") {
        // ⬇ Tap beat timing detection
        circleBitmap.on("mousedown", (evt) => onTapCheck(evt, beatTime));
    }
    if (type === "curve") {
          circleBitmap.on("mousedown", (evt) => 
                onHoldStart(evt, beatTime, currentPath, baseX, baseY, FIXED_SCALE, SHRINK_DURATION)
            );
        circleBitmap.on("pressup", () => 
            onHoldEnd(circleBitmap, ringBitmap)
        );
    }
    // -------------------------------
    // Update counters
    // -------------------------------
    currentNumber++;
    if (currentNumber > 8) {
        currentNumber = 1;
        currentColorIndex = (currentColorIndex + 1) % colors.length;
    }
    lastCurve = bitmap;
    lastCircle = circleBitmap;
    lastRing = ringBitmap;
    stage.update();
}
function onTapCheck(evt, beatTime) {
    const tapTime = bgMusic.currentTime * 1000;
    const diff = Math.abs(tapTime - beatTime);
    const GOOD_TIMING_THRESHOLD = 500;
    console.log("Tap timing:", tapTime, "beat:", beatTime);
    const circle = evt.currentTarget;
    const ring = lastRing; // access current ring
    if (diff <= GOOD_TIMING_THRESHOLD) {
        console.log("✅ Perfect Tap!");
        increaseScore();
        playSuccessEffect(circle, ring);
    } else {
        console.log("❌ Off Beat");
        playFailureEffect(circle, ring);
    }
}
function playSuccessEffect(circle, ring) {
    // Create a green overlay flash
    const greenOverlay = new createjs.Shape();
    greenOverlay.graphics.beginFill("rgba(0,255,0,0.4)").drawCircle(0, 0, 80);
    greenOverlay.x = circle.x;
    greenOverlay.y = circle.y;
    stage.addChild(greenOverlay);
    // Gentle scale pulse (without removing)
    createjs.Tween.get(circle)
        .to(
            { scaleX: circle.scaleX * 1.2, scaleY: circle.scaleY * 1.2 },
            150,
            createjs.Ease.quadOut
        )
        .to(
            { scaleX: circle.scaleX, scaleY: circle.scaleY },
            300,
            createjs.Ease.quadIn
        );
    if (ring) {
        createjs.Tween.get(ring)
            .to(
                { scaleX: ring.scaleX * 1.1, scaleY: ring.scaleY * 1.1 },
                150,
                createjs.Ease.quadOut
            )
            .to(
                { scaleX: ring.scaleX, scaleY: ring.scaleY },
                300,
                createjs.Ease.quadIn
            );
    }
    // Fade out green overlay smoothly
    createjs.Tween.get(greenOverlay)
        .to({ alpha: 0 }, 500, createjs.Ease.quadOut)
        .call(() => stage.removeChild(greenOverlay));
    stage.update();
}
function playFailureEffect(circle, ring) {
    // Red overlay flash
    const redOverlay = new createjs.Shape();
    redOverlay.graphics.beginFill("rgba(255,0,0,0.4)").drawCircle(0, 0, 80);
    redOverlay.x = circle.x;
    redOverlay.y = circle.y;
    stage.addChild(redOverlay);
    const originalX = circle.x;
    // Gentle shake animation (no removal)
    createjs.Tween.get(circle)
        .to({ x: originalX - 10 }, 50)
        .to({ x: originalX + 10 }, 50)
        .to({ x: originalX }, 50);
    if (ring) {
        createjs.Tween.get(ring)
            .to({ alpha: 0.7 }, 100)
            .to({ alpha: 1 }, 100);
    }
    // Fade out red overlay smoothly
    createjs.Tween.get(redOverlay)
        .wait(200)
        .to({ alpha: 0 }, 400, createjs.Ease.quadOut)
        .call(() => stage.removeChild(redOverlay));
    stage.update();
}
let holdTicker = null;
let holdStartTime = 0;
let holdProgress = 0;
let isHolding = false;
function onHoldStart(evt, beatTime, currentPath, baseX, baseY, FIXED_SCALE, SHRINK_DURATION) {
    const tapTime = bgMusic.currentTime * 1000;
    const diff = Math.abs(tapTime - beatTime);
    const GOOD_TIMING_THRESHOLD = 500;
    const circle = evt.currentTarget;
    const ring = lastRing;
    // 🎯 Step 1: Check tap timing (must start near beat)
    if (diff > GOOD_TIMING_THRESHOLD) {
        console.log("❌ Bad timing — hold start failed");
        playFailureEffect(circle, ring);
        return;
    }
    console.log("✅ Hold start success — begin path movement");
    if (!currentPath || currentPath.length === 0) return;
    let progress = 0;
    let isHolding = true;
    const totalSteps = currentPath.length - 1;
    const startTime = performance.now();
    // Step 2: Start smooth movement along path
    const ticker = createjs.Ticker.on("tick", () => {
        if (!isHolding) return;
        const elapsed = performance.now() - startTime;
        progress = Math.min(elapsed / 800, 1);
        const index = Math.floor(progress * totalSteps);
        const nextIndex = Math.min(index + 1, totalSteps);
        const p1 = currentPath[index];
        const p2 = currentPath[nextIndex];
        const t = (progress * totalSteps) - index;
        const x = baseX + (p1.x + (p2.x - p1.x) * t) * FIXED_SCALE;
        const y = baseY + (p1.y + (p2.y - p1.y) * t) * FIXED_SCALE;
        circle.x = x;
        circle.y = y;
        ring.x = x;
        ring.y = y;
        stage.update();
        // Step 3: If complete, stop ticker and mark success
        if (progress >= 1) {
            createjs.Ticker.off("tick", ticker);
            if (isHolding && progress >= 0.9) {
                console.log("✅ Hold success — reached end!");
                increaseScore();
                playSuccessEffect(circle, ring);
                isHolding = false;
            }
        }
    });
    // Step 4: Handle early release
    circle.on("pressup", () => {
        if (isHolding) {
            isHolding = false;
            createjs.Ticker.off("tick", ticker);
            if (progress < 0.9) {
                console.log("❌ Early release — hold failed");
                playFailureEffect(circle, ring);
            } else {
                console.log("✅ Hold completed successfully");
                increaseScore();
                playSuccessEffect(circle, ring);
            }
        }
    });
}
function onHoldEnd(circleBitmap, ringBitmap) {
    if (!isHolding) return;
    isHolding = false;
    // Stop updating
    if (holdTicker) {
        createjs.Ticker.off("tick", holdTicker);
        holdTicker = null;
    }
    // ❌ Fail check (if not already successful)
    if (holdProgress < 0.9) {
        onHoldFail(circleBitmap, ringBitmap);
    }
}
// ✅ SUCCESS / FAIL feedback
function onHoldSuccess(circleBitmap, ringBitmap) {
    createjs.Tween.get(circleBitmap)
        .to({ scaleX: 1.5, scaleY: 1.5, alpha: 0 }, 300, createjs.Ease.quadOut);
    createjs.Tween.get(ringBitmap)
        .to({ scaleX: 1.5, scaleY: 1.5, alpha: 0 }, 300, createjs.Ease.quadOut);
    increaseScore();
    console.log("✅ Hold success!");
}
function onHoldFail(circleBitmap, ringBitmap) {
    createjs.Tween.get(circleBitmap)
        .to({ alpha: 0.4 }, 150)
        .to({ alpha: 1 }, 150);
    createjs.Tween.get(ringBitmap)
        .to({ alpha: 0.4 }, 150)
        .to({ alpha: 1 }, 150);
    console.log("❌ Released early");
}
function spawnRandomCurve3() {
    const color = colors[currentColorIndex];
    const prevCircle = lastCircle;
    const prevRing = lastRing;
    animateMascotBeat();
    console.log("beat");
    // ================================
    // Number Circle Setup
    // ================================
    const circleID = `${color}_circle_${currentNumber}`;
    const circleBitmap = new createjs.Bitmap(queue.getResult(circleID));
    const NUM_SCALE = 0.07;
    const cbounds = circleBitmap.getBounds() || { width: 100, height: 100 };
    circleBitmap.regX = cbounds.width / 2;
    circleBitmap.regY = cbounds.height / 2;
    // Random spawn position inside container
    const baseX = CONTAINER.x + Math.random() * (CONTAINER.width - cbounds.width);
    const baseY = CONTAINER.y + Math.random() * (CONTAINER.height - cbounds.height);
    circleBitmap.x = baseX + cbounds.width / 2;
    circleBitmap.y = baseY + cbounds.height / 2;
    circleBitmap.alpha = 0;
    stage.addChild(circleBitmap);
    // ================================
    // Ring Setup
    // ================================
    const ringAsset = ASSETS[color].ring;
    const ringBitmap = new createjs.Bitmap(queue.getResult(ringAsset.id));
    const RING_SCALE = 0.6;
    const rBounds = ringBitmap.getBounds() || { width: 100, height: 100 };
    ringBitmap.regX = rBounds.width / 2;
    ringBitmap.regY = rBounds.height / 2;
    ringBitmap.x = circleBitmap.x;
    ringBitmap.y = circleBitmap.y;
    stage.addChild(ringBitmap);
    // ================================
    // Number Pop-in Animation
    // ================================
const TARGET_SIZE = 150; // final width/height in pixels
// Get original bitmap dimensions
const b = circleBitmap.getBounds();
const finalScale = TARGET_SIZE / b.width;
// Start smaller (for zoom animation)
circleBitmap.scaleX = circleBitmap.scaleY = finalScale * 0.6;
// Animate zoom-out to the final 300px size
createjs.Tween.get(circleBitmap)
    .to(
        { alpha: 1, scaleX: finalScale, scaleY: finalScale },
        300,
        createjs.Ease.backOut
    );
    // ================================
    // Ring Zoom-in Animation (1s)
    // ================================
    const FINAL_SIZE = TARGET_SIZE;    // final ring matches number circle
    const START_SIZE = 600;    // starting size (larger outer ring)
    // Ensure bounds exist
    const rb = ringBitmap.getBounds();
    const finalRingScale = FINAL_SIZE / rb.width;
    const startRingScale = START_SIZE / rb.width;
    // Initialize ring
    ringBitmap.scaleX = ringBitmap.scaleY = startRingScale;
    ringBitmap.alpha = 1;
    // Animate shrink to final circle size
   createjs.Tween.get(ringBitmap)
    .to(
        { scaleX: finalRingScale, scaleY: finalRingScale },
        SHRINK_DURATION,
        createjs.Ease.quadOut
    )
    .call(() => {
        // ✅ This moment = exact beat hit
        //console.log("Hit timing for beat:", beatTime);
        // (We’ll add success/fail tap check here later)
        // ================================
        // Remove previous instantly
        // ================================
       // ✅ Fade out both number + ring in sync
        createjs.Tween.get(ringBitmap)
            .to({ alpha: 0 }, 250) // fade out in 250ms
            .call(() => stage.removeChild(ringBitmap));
        createjs.Tween.get(circleBitmap)
            .to({ alpha: 0 }, 250) // fade out in 250ms
            .call(() => {
                stage.removeChild(circleBitmap);
                stage.update();
            });
    });
    // ================================
    // Update counters
    // ================================
    currentNumber++;
    if (currentNumber > 8) {
        currentNumber = 1;
        currentColorIndex = (currentColorIndex + 1) % colors.length;
    }
    lastCircle = circleBitmap;
    lastRing = ringBitmap;
    stage.update();
}
function spawnRandomCurve2() {
    const color = colors[currentColorIndex];  
    const prevCurve = lastCurve;
    const prevCircle = lastCircle;
    const prevRing = lastRing;
    // Pick random curve
    const curveSet = ASSETS[color].curves;
    const randomCurve = curveSet[Math.floor(Math.random() * curveSet.length)];
    const bitmap = new createjs.Bitmap(queue.getResult(randomCurve.id));
    const FIXED_SCALE = 0.07;
    bitmap.scaleX = bitmap.scaleY = FIXED_SCALE;
    // -------------------------------
    // Position curve inside container
    // -------------------------------
    const curveW = bitmap.getBounds() ? bitmap.getBounds().width * FIXED_SCALE : 50;
    const curveH = bitmap.getBounds() ? bitmap.getBounds().height * FIXED_SCALE : 50;
    const baseX = CONTAINER.x + Math.random() * (CONTAINER.width - curveW);
    const baseY = CONTAINER.y + Math.random() * (CONTAINER.height - curveH);
    bitmap.x = baseX;
    bitmap.y = baseY;
    bitmap.alpha = 0;
    stage.addChild(bitmap);
    // -------------------------------
    // Number circle
    // -------------------------------
    const circleID = `${color}_circle_${currentNumber}`;
    const circleBitmap = new createjs.Bitmap(queue.getResult(circleID));
    const NUM_SCALE = 0.07;
    circleBitmap.scaleX = circleBitmap.scaleY = NUM_SCALE;
    const cbounds = circleBitmap.getBounds() || { width: 100, height: 100 };
    circleBitmap.regX = cbounds.width / 2;
    circleBitmap.regY = cbounds.height / 2;
    const offset = randomCurve.numberOffset || { x: 0, y: 0 };
    circleBitmap.x = baseX + offset.x * FIXED_SCALE;
    circleBitmap.y = baseY + offset.y * FIXED_SCALE;
    circleBitmap.alpha = 0;
    stage.addChild(circleBitmap);
    // -------------------------------
    // Ring
    // -------------------------------
    const ringAsset = ASSETS[color].ring;
    const ringBitmap = new createjs.Bitmap(queue.getResult(ringAsset.id));
    const RING_SCALE = 0.06;
    const ringBounds = ringBitmap.getBounds() || { width: 100, height: 100 };
    ringBitmap.regX = ringBounds.width / 2;
    ringBitmap.regY = ringBounds.height / 2;
    const ringOffset = ringAsset.offset || { x: 0, y: 0 };
    ringBitmap.x = baseX + ringOffset.x * FIXED_SCALE;
    ringBitmap.y = baseY + ringOffset.y * FIXED_SCALE;
    ringBitmap.alpha = 0;
    ringBitmap.scaleX = ringBitmap.scaleY = RING_SCALE * 1.5;
    stage.addChild(ringBitmap);
    // -------------------------------
    // Animations
    // -------------------------------
    bitmap.scaleX = bitmap.scaleY = FIXED_SCALE * 0.6;
    createjs.Tween.get(bitmap)
        .to({ alpha: 1, scaleX: FIXED_SCALE * 1.3, scaleY: FIXED_SCALE * 1.3 }, 200, createjs.Ease.quadOut)
        .to({ scaleX: FIXED_SCALE, scaleY: FIXED_SCALE }, 150, createjs.Ease.quadIn);
    circleBitmap.scaleX = circleBitmap.scaleY = NUM_SCALE * 0.6;
    createjs.Tween.get(circleBitmap)
        .to({ alpha: 1, scaleX: NUM_SCALE * 1.2, scaleY: NUM_SCALE * 1.2 }, 200, createjs.Ease.quadOut)
        .to({ scaleX: NUM_SCALE, scaleY: NUM_SCALE }, 150, createjs.Ease.quadIn);
    createjs.Tween.get(ringBitmap)
        .to({ alpha: 1, scaleX: RING_SCALE * 0.9, scaleY: RING_SCALE * 0.9 }, 200, createjs.Ease.quadOut)
        .to({ scaleX: RING_SCALE, scaleY: RING_SCALE }, 150, createjs.Ease.quadIn);
    // -------------------------------
    // Fade out previous curve, circle, ring
    // -------------------------------
    if (prevCurve) {
        [prevCurve, prevCircle, prevRing].forEach(item => {
            if (item) {
                createjs.Tween.get(item)
                    .to({ alpha: 0, scaleX: 0, scaleY: 0 }, 300)
                    .call(() => stage.removeChild(item));
            }
        });
    }
    // -------------------------------
    // Coordinate input updates
    // -------------------------------
    function updateCoordInputs() {
        const xInput = document.getElementById("coordX");
        const yInput = document.getElementById("coordY");
        if (xInput && yInput) {
            xInput.value = Math.round((circleBitmap.x - baseX) / FIXED_SCALE);
            yInput.value = Math.round((circleBitmap.y - baseY) / FIXED_SCALE);
        }
    }
    // -------------------------------
    // Arrow key movement
    // -------------------------------
    function onKeyDown(e) {
        const step = 1;
        switch(e.key) {
            case "ArrowRight": circleBitmap.x += step; break;
            case "ArrowLeft": circleBitmap.x -= step; break;
            case "ArrowDown": circleBitmap.y += step; break;
            case "ArrowUp": circleBitmap.y -= step; break;
        }
        updateCoordInputs();
        stage.update();
    }
    window.addEventListener("keydown", onKeyDown);
    updateCoordInputs();
    // -------------------------------
    // Mouse Drag Functionality
    // -------------------------------
    let isDragging = false;
    let dragOffsetX = 0;
    let dragOffsetY = 0;
    circleBitmap.on("mousedown", (evt) => {
        isDragging = true;
        dragOffsetX = evt.stageX - circleBitmap.x;
        dragOffsetY = evt.stageY - circleBitmap.y;
    });
    circleBitmap.on("pressmove", (evt) => {
        if (isDragging) {
            circleBitmap.x = evt.stageX - dragOffsetX;
            circleBitmap.y = evt.stageY - dragOffsetY;
            updateCoordInputs();
            stage.update();
        }
    });
    circleBitmap.on("pressup", () => {
        isDragging = false;
    });
    // -------------------------------
    // Update counters
    // -------------------------------
    currentNumber++;
    if (currentNumber > 8) {
        currentNumber = 1;
        currentColorIndex = (currentColorIndex + 1) % colors.length;
    }
    lastCurve = bitmap;
    lastCircle = circleBitmap;
    lastRing = ringBitmap;
    stage.update();
}
// Call once after stage is ready
drawContainerBorder();
const SHRINK_DURATION = 2000; 
function playBeatSequence() {
    console.log("🎵 Beat scheduling started!");
    console.log("MusicStartTime:", window.musicStartTime);
    beatTimes.sort((a, b) => a - b);
    beatTimes.forEach((beatTime, index) => {
        const spawnTime = beatTime - SHRINK_DURATION;
        if (spawnTime < 0) {
            console.warn(`⏭️ Skipping beat ${index + 1} — spawnTime < 0`, beatTime);
            return;
        }
        const now = performance.now();
        const delayFromNow = (window.musicStartTime + spawnTime) - now;
        console.log(
            `%c Beat #${index + 1} ->` +
            ` BeatTime: ${beatTime.toFixed(0)}ms |` +
            ` SpawnTime: ${spawnTime.toFixed(0)}ms |` +
            ` DelayFromNow: ${delayFromNow.toFixed(0)}ms`,
            "color:#00bfff;font-weight:bold;"
        );
        // ✅ Schedule spawn
        setTimeout(() => {
            console.log(
                `🎯 Spawn @ ${(
                    performance.now() - window.musicStartTime
                ).toFixed(0)}ms` +
                ` (Expected: ${spawnTime.toFixed(0)}ms)`
            );
            if (!gameEnded) {
                const type = Math.random() < 0.5 ? "number" : "curve"; // random pick
                spawnRandomCurve(type, beatTime);
            }
        }, Math.max(0, delayFromNow)); // avoids negative timer
    });
}
function animateMascotIdle() {
    if (!window.mascotHead || !window.mascotBody) return;
    const HEAD_BOB = 5;      // pixels up/down
    const HEAD_NOD = 5;      // degrees rotation
    const BODY_MOVE = 3;     // pixels up/down
    const DURATION = 1000;   // 1 second per cycle
    // Head bob + slight nod
    createjs.Tween.get(window.mascotHead, { loop: true })
        .to({ y: window.mascotHead.y - HEAD_BOB, rotation: -HEAD_NOD }, DURATION / 2, createjs.Ease.quadInOut)
        .to({ y: window.mascotHead.y + HEAD_BOB, rotation: HEAD_NOD }, DURATION, createjs.Ease.quadInOut)
        .to({ y: window.mascotHead.y, rotation: 0 }, DURATION / 2, createjs.Ease.quadInOut);
    // Body idle move
    createjs.Tween.get(window.mascotBody, { loop: true })
        .to({ y: window.mascotBody.y - BODY_MOVE }, DURATION / 2, createjs.Ease.quadInOut)
        .to({ y: window.mascotBody.y + BODY_MOVE }, DURATION, createjs.Ease.quadInOut)
        .to({ y: window.mascotBody.y }, DURATION / 2, createjs.Ease.quadInOut);
}
function animateMascotBeat() {
    if (!window.mascotHead || !window.mascotBody) return;
    const HEAD_MOVE = 25; // px movement
    const BODY_MOVE = 5;  // slight body move
    const DURATION = 100; // ms per beat
    // Head animation (up then back)
    createjs.Tween.get(window.mascotHead)
        .to({ y: window.mascotHead.y - HEAD_MOVE }, DURATION, createjs.Ease.quadOut)
        .to({ y: window.mascotHead.y }, DURATION, createjs.Ease.quadIn);
    // Body animation (slight up and down)
    createjs.Tween.get(window.mascotBody)
        .to({ y: window.mascotBody.y - BODY_MOVE }, DURATION, createjs.Ease.quadOut)
        .to({ y: window.mascotBody.y }, DURATION, createjs.Ease.quadIn);
}
    // // Function to play the curve sequence based on beatTimes
    // function playBeatSequence() {
    //     if (!bgMusic) return;
    //     const startTime = Date.now();
    //     beatTimes.forEach(beat => {
    //         setTimeout(() => {
    //             if (!gameEnded) spawnRandomCurve();
    //         }, beat);
    //     });
    // }
    function resizeToStageHeight(bitmap){
      const img = bitmap.image;
      const scale = stageHeight / img.height;
      bitmap.scaleX = bitmap.scaleY = scale;
      bitmap.x = (stageWidth - img.width*scale)/2;
      bitmap.y = 0;
    }
    createjs.Ticker.framerate = 60;
    createjs.Ticker.on("tick", stage);
    function startTimer(){
      timerInterval = setInterval(()=>{
        timeLeft--;
        //timerText.text = (timeLeft<10?"0"+timeLeft:timeLeft);
        updateGlobalTimer(Math.round(bgMusic.duration),(timeLeft < 10 ? "0" + timeLeft : timeLeft));
        if(timeLeft<=0){
          clearInterval(timerInterval);
          endGameDueToTimeout();
        }
      },1000);
    }
    function endGameDueToTimeout() {
      if (gameEnded) return;
      gameEnded = true;
      showEndCelebration();
    }
    function increaseScore(char) {
    score++;
    scoreText.text = score;
    createjs.Tween.get(scoreText)
        .to({scaleX: 1.15, scaleY: 1.15}, 120)
        .to({scaleX: 1, scaleY: 1}, 120);
    if (char) {
        createjs.Tween.get(char)
            .to({scaleX: 0, scaleY: 0, alpha: 0}, 300)
            .call(() => stage.removeChild(char));
    }
}
function spawnCharacters(layerBitmaps) {
}
function showEndCelebration() {
     gameEnded = true;
     bgMusic.pause();
    // // Clear stage except background/layers if needed
    // // (Or you can keep everything and overlay new content)
    // // Overlay dark transparent background
    // const overlay = new createjs.Shape();
    // overlay.graphics.beginFill("rgba(0,0,0,0.7)").drawRect(0, 0, stageWidth, stageHeight);
    // stage.addChild(overlay);
    // // Title
    // const titleText = new createjs.Text("Well Played!", "100px Poppins", "#FFD700");
    // titleText.textAlign = "center";
    // titleText.textBaseline = "middle";
    // titleText.x = stageWidth / 2;
    // titleText.y = stageHeight / 3;
    // titleText.shadow = new createjs.Shadow("#000", 5, 5, 20);
    // stage.addChild(titleText);
    // // Score
    // const scoreDisplay = new createjs.Text("Your Score: " + score, "72px Poppins", "#fff");
    // scoreDisplay.textAlign = "center";
    // scoreDisplay.textBaseline = "middle";
    // scoreDisplay.x = stageWidth / 2;
    // scoreDisplay.y = stageHeight / 2;
    // scoreDisplay.shadow = new createjs.Shadow("#FFD700", 0, 0, 25);
    // stage.addChild(scoreDisplay);
    // for (let i = 0; i < 50; i++) {
    //     const confetti = new createjs.Shape();
    //     const colors = ["#FFD700","#FF69B4","#00CED1","#ADFF2F","#FF4500"];
    //     confetti.graphics.beginFill(colors[Math.floor(Math.random()*colors.length)]).drawCircle(0,0,8);
    //     confetti.x = Math.random() * stageWidth;
    //     confetti.y = -20;
    //     stage.addChild(confetti);
    //     createjs.Tween.get(confetti)
    //         .to({y: stageHeight + 20, x: confetti.x + (Math.random()*200 - 100)}, 4000 + Math.random()*2000, createjs.Ease.linear)
    //         .call(()=>stage.removeChild(confetti));
    // }
    // stage.update();
    // localStorage.setItem('game_finished', "1");
      // After animation, redirect to the post_game.html, passing the score
    localStorage.setItem('game_finished', "1");
    setTimeout(() => {
        window.top.location.href = `process_post_game.php?score=${score}`;
    }, 1500); // Allow 1.5 seconds for "Time's Up" animation
}
// Helper: random confetti color
function getRandomColor() {
    const colors = ["#FF4081", "#3F51B5", "#4CAF50", "#FFC107", "#FF5722", "#00BCD4"];
    return colors[Math.floor(Math.random() * colors.length)];
}
    function resizeCanvas(){
      const windowHeight = window.innerHeight;
      const scale = windowHeight / stageHeight;
      canvas.style.height = windowHeight + "px";
      canvas.style.width = (stageWidth*scale) + "px";
    }
    window.addEventListener("resize",resizeCanvas);
    resizeCanvas();

    
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

  </script>
</body>
</html>
