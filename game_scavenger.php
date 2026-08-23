<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, interactive-widget=overlays-content">
  <title>Scavenger Hunt Game</title>
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
      font-family: 'Poppins', sans-serif;
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
    
  </style>
</head>
<body>
  <audio id="bgMusic" loop muted>
    <source src="" type="audio/mpeg">
  </audio>
  <audio id="buzzerSound" loop muted>
    <source src="" type="audio/mpeg">
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
  <script>
    const stageWidth = 2338;
    const stageHeight = 1668;
    const canvas = document.getElementById("gameCanvas");
    const stage = new createjs.Stage(canvas);
    let timerText, scoreText;
    let timeLeft = 60;
    let score = 0;
    let gameStarted = false;
    let timerInterval;
    let gameEnded = false;
    let bgMusic; 
    // Difficulty settings
    const difficultySettings = {
        relaxed: {
            spawnInterval: 1200, // slower spawns
            lifetime: 4000,      // characters stay longer
            scale: 2.0           // big characters
        },
        normal: {
            spawnInterval: 800,
            lifetime: 3000,
            scale: 1.5
        },
        tricky: {
            spawnInterval: 400,  // faster spawns
            lifetime: 2000,      // disappear quickly
            scale: 1.0           // small characters
        }
    };
    var currentDifficulty = localStorage.getItem('gameTrickLevel') || 'relaxed';
    currentDifficulty = 'relaxed'; 
    const settings = difficultySettings[currentDifficulty] || difficultySettings.tricky;
    // Theme
    var theme = localStorage.getItem("selectedTheme") || "fairy";
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
    // Scenes
    let scenes = {
         farm: [
            { id: "farm1", src: "assets/game_scavenger/scenes/farm/farm_layer1.png", groundOffset: 1668 },
            { id: "farm2", src: "assets/game_scavenger/scenes/farm/farm_layer2.png", groundOffset: 800 },
            { id: "farm3", src: "assets/game_scavenger/scenes/farm/farm_layer3.png", groundOffset: 1000 },
            { id: "farm4", src: "assets/game_scavenger/scenes/farm/farm_layer4.png", groundOffset: 1200 },
            { id: "farm5", src: "assets/game_scavenger/scenes/farm/farm_layer5.png", groundOffset: 1500 },
        ],
        park: [
            { id: "park1", src: "assets/game_scavenger/scenes/park/park_layer1.png", groundOffset: 1668 },
            { id: "park2", src: "assets/game_scavenger/scenes/park/park_layer2.png", groundOffset: 550 },
            { id: "park3", src: "assets/game_scavenger/scenes/park/park_layer3.png", groundOffset: 850 },
            { id: "park4", src: "assets/game_scavenger/scenes/park/park_layer4.png", groundOffset: 1000 },
            { id: "park5", src: "assets/game_scavenger/scenes/park/park_layer5.png", groundOffset: 1500 },
        ],
         playground: [
            { id: "playground1", src: "assets/game_scavenger/scenes/playground/playground_layer1.png", groundOffset: 1668 },
            { id: "playground2", src: "assets/game_scavenger/scenes/playground/playground_layer2.png", groundOffset: 800 },
            { id: "playground3", src: "assets/game_scavenger/scenes/playground/playground_layer3.png", groundOffset: 850 },
            { id: "playground4", src: "assets/game_scavenger/scenes/playground/playground_layer4.png", groundOffset: 1200 },
            { id: "playground5", src: "assets/game_scavenger/scenes/playground/playground_layer5.png", groundOffset: 1500 },
        ],
        restaurant: [
            { id: "restaurant1", src: "assets/game_scavenger/scenes/restaurant/restaurant_layer1.png", groundOffset: 408 },
            { id: "restaurant2", src: "assets/game_scavenger/scenes/restaurant/restaurant_layer2.png", groundOffset: 900 },
            { id: "restaurant3", src: "assets/game_scavenger/scenes/restaurant/restaurant_layer3.png", groundOffset: 850 },
            { id: "restaurant4", src: "assets/game_scavenger/scenes/restaurant/restaurant_layer4.png", groundOffset: 1100 },
            { id: "restaurant5", src: "assets/game_scavenger/scenes/restaurant/restaurant_layer5.png", groundOffset: 1500 },
            { id: "restaurant5", src: "assets/game_scavenger/scenes/restaurant/restaurant_layer6.png", groundOffset: 1500 },
        ]
    };
    // Pick random scene
    let sceneNames = Object.keys(scenes);
    let randomSceneName = sceneNames[Math.floor(Math.random() * sceneNames.length)];
    let sceneLayers = scenes[randomSceneName];
    // Characters
    const characters = [
      { id: "char1", src: "assets/game_scavenger/characters/char1.png", fake:false },
      { id: "char2", src: "assets/game_scavenger/characters/char2.png", fake:false },
      { id: "char3", src: "assets/game_scavenger/characters/char3.png", fake:false },
      { id: "char4", src: "assets/game_scavenger/characters/char4.png", fake:false },
      { id: "char5", src: "assets/game_scavenger/characters/char5.png", fake:false },
      { id: "char6", src: "assets/game_scavenger/characters/char6.png", fake:false },
      { id: "char7", src: "assets/game_scavenger/characters/char7.png", fake:false },
      { id: "char8", src: "assets/game_scavenger/characters/char8.png", fake:false },
      { id: "char9", src: "assets/game_scavenger/characters/char9.png", fake:false },
      { id: "char10", src: "assets/game_scavenger/characters/char10.png", fake:false },
      { id: "char11", src: "assets/game_scavenger/fake_characters/blue-monster.png", fake:true },
      { id: "char12", src: "assets/game_scavenger/fake_characters/green-monster.png", fake:true },
      { id: "char13", src: "assets/game_scavenger/fake_characters/light-blue-monster.png", fake:true },
      { id: "char14", src: "assets/game_scavenger/fake_characters/red-tall-monster.png", fake:true },
      { id: "char15", src: "assets/game_scavenger/fake_characters/tall-brown-monster.png", fake:true },
      { id: "char15", src: "assets/game_scavenger/fake_characters/red-monster.png", fake:true },
      { id: "char15", src: "assets/game_scavenger/fake_characters/purple-monster.png", fake:true }
    ];
    // Loader
    const queue = new createjs.LoadQueue();
    queue.installPlugin(createjs.Sound);
    queue.loadManifest([
        { id: "fairy", src: "assets/images/fairy.png" },
        { id: "border", src: "assets/images/fairy.png" },
        { id: "bgmusic", src: "assets/game_scavenger/audio/game_bg_music_1.mp3" },
        { id: "char1", src: "assets/game_scavenger/characters/char1.png" },
        { id: "char2", src: "assets/game_scavenger/characters/char2.png" },
        { id: "unmuted", src: "assets/images/speaker-unmuted.png" },
        { id: "muted", src: "assets/images/speaker-muted.png" },
        ...sceneLayers,
        ...characters
    ]);
    createjs.Sound.registerSound("assets/game_scavenger/audio/buzzer-or-wrong-answer-20582.mp3", "wrong");
    const loadingOverlay = document.getElementById("loadingOverlay");
    const progressFill = document.getElementById("progressFill");
    queue.on("progress", e=>{
      progressFill.style.width = Math.floor(e.progress*100) + "%";
    });
    queue.on("complete", ()=>{
      loadingOverlay.style.display = "none";
      handleComplete();
    });
    let charSpriteSheets = []; // store all character spriteSheets
function handleComplete() {
    const fairy = new createjs.Bitmap(queue.getResult("fairy"));
    resizeToStageHeight(fairy);
    stage.addChild(fairy);
    const layerBitmaps = [];
    sceneLayers.forEach(layerInfo => {
        const bmp = new createjs.Bitmap(queue.getResult(layerInfo.id));
        resizeToStageHeight(bmp);
        bmp.groundOffset = layerInfo.groundOffset;
        stage.addChild(bmp);
        layerBitmaps.push(bmp);
        //drawDebugLine(layerInfo.groundOffset);
    });
    // const border = new createjs.Bitmap(`assets/images/${theme}.png`);
    // resizeToStageHeight(border);
    // stage.addChild(border);
    // ⏺️ Build spriteSheets for all characters
    characters.forEach(char => {
        const sheet = new createjs.SpriteSheet({
            images: [queue.getResult(char.id)],   // each char's spritesheet image
            frames: { width: 300, height: 300, count: 23 },
            animations: {
                run: [0, 22, "run", 0.2] // loop animation
            }
        });
        //charSpriteSheets.push(sheet);
        charSpriteSheets.push({ id: char.id, sheet:sheet, fake: char.fake });
    });
    const panelRadius = 140;
    const panel = new createjs.Shape();
    panel.graphics
        .setStrokeStyle(6)
        .beginStroke(color3)
        .beginRadialGradientFill([GRADIENT_START, GRADIENT_END], [0,1], 0,0,0, 0,0,panelRadius)
        .drawCircle(0,0,panelRadius);
    panel.x = stageWidth - panelRadius - 10;
    panel.y = panelRadius + 10;
    panel.shadow = new createjs.Shadow("#000",4,4,10);
    panel.alpha = 0.7;
    //stage.addChild(panel);
    timerText = new createjs.Text(timeLeft, "100px Poppins, Arial", "#FFD700");
    timerText.textAlign = "center";
    timerText.textBaseline = "middle";
    timerText.x = panel.x;
    timerText.y = panel.y - 20;
    timerText.shadow = new createjs.Shadow("#000",2,2,5);
    //stage.addChild(timerText);
    scoreText = new createjs.Text("Score: 0", "48px Poppins, Arial", "#fff");
    scoreText.textAlign = "center";
    scoreText.textBaseline = "middle";
    scoreText.x = panel.x;
    scoreText.y = panel.y + 45;
    scoreText.shadow = new createjs.Shadow("#000",2,2,5);
    //stage.addChild(scoreText);
    stage.update();
    // Enable mouse input
    stage.enableMouseOver();
    stage.mouseEnabled = true;
    // Audio setup
    mutedImg = queue.getResult("muted");
    unmutedImg = queue.getResult("unmuted");
    bgMusic = document.getElementById("bgMusic");
    bgMusic.muted = true; // Start muted
    bgMusic.volume = 1; 
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
      // bgMusic.muted = false;
      // bgMusic.play().catch(e => console.log("Play blocked:", e));
       audioIcon.visible = false;
      window.parent.postMessage({ 
                  action: 'ACTIVATE_GLOBAL_AUDIO', 
                  audioSrc: 'assets/game_scavenger/audio/scavenger_game_bg_music.mp3' 
      }, '*');
        if (!gameStarted) {
            gameStarted = true;
            startTimer();
            spawnCharacters(layerBitmaps); // ✅ now we have 10 animated characters available
        }
      // Animate icon to top-left with bounce
      createjs.Tween.get(audioIcon)
        .to({ x: 140, y: 140, scaleX: 200 / mutedImg.width, scaleY: 200 / mutedImg.height }, 800, createjs.Ease.bounceOut);
      // Fade out overlay + text
      createjs.Tween.get(overlay).to({ alpha: 0 }, 500).call(() => stage.removeChild(overlay));
      createjs.Tween.get(tapText).to({ alpha: 0 }, 500).call(() => stage.removeChild(tapText));
      audioIcon.image = unmutedImg;
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
    // document.getElementById("overlay").addEventListener("click", () => {
    //     overlay.style.display = "none";
    //     createjs.Sound.play("bgmusic", { loop: -1, volume: 0.1 });
    //     if (!gameStarted) {
    //         gameStarted = true;
    //         startTimer();
    //         spawnCharacters(layerBitmaps); // ✅ now we have 10 animated characters available
    //     }
    // });
}
    function drawDebugLine(y) {
      let line = new createjs.Shape();
      line.graphics.setStrokeStyle(2).beginStroke("red").moveTo(0,y).lineTo(stageWidth,y);
      stage.addChild(line);
    }
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
        updateGlobalTimer();
        timerText.text = (timeLeft<10?"0"+timeLeft:timeLeft);
        stage.update();
        if(timeLeft<=0){
          clearInterval(timerInterval);
          removeGlobalTimer();
          endGameDueToTimeout();
        }
      },1000);
    }
    function endGameDueToTimeout() {
      if (gameEnded) return;
      gameEnded = true;
      showEndCelebration();
    //   createjs.Sound.stop();
    //   clearInterval(timerInterval);
    //   const gameOverText = new createjs.Text("Time's up! Score: " + score, "72px Poppins, Arial", "#ff0000");
    //   gameOverText.textAlign = "center";
    //   gameOverText.textBaseline = "middle";
    //   gameOverText.x = stageWidth/2;
    //   gameOverText.y = stageHeight/2;
    //   stage.addChild(gameOverText);
    //   stage.update();
    }
    function updateGlobalTimer() {
        window.parent.postMessage({
            action: 'UPDATE_TIMER',
            remaining: timeLeft,
            total: 60, // Your game's starting time
            themeColor: color1 // This uses your theme's primary color (e.g., #7366C6 for Fairy)
        }, '*');
    }

 
    function removeGlobalTimer() {
        window.parent.postMessage({ 
            action: 'HIDE_TIMER' 
        }, '*');
    }
    function increaseScore(char){
      score++;
      scoreText.text = "Score: "+score;
      createjs.Tween.get(char).to({scaleX:0,scaleY:0,alpha:0},300).call(()=> stage.removeChild(char));
      createjs.Tween.get(scoreText).to({scaleX:1.2,scaleY:1.2},150).to({scaleX:1,scaleY:1},150);
    }
function spawnCharacters(layerBitmaps) {
    if (!layerBitmaps || layerBitmaps.length < 2) return;
    const numCharacters = 1;
    function spawnOne() {
        if (gameEnded) return;
        for (let i = 0; i < numCharacters; i++) {
            const randomSheet = charSpriteSheets[Math.floor(Math.random() * charSpriteSheets.length)];
            const img = new createjs.Sprite(randomSheet.sheet, "run");
            img.play();
            var targetWidth;
            if (!randomSheet.fake) targetWidth = 650;
            else targetWidth = 500;
            const scale = targetWidth / 300;
            img.scaleX = img.scaleY = scale;
            img.x = Math.random() * (stageWidth - targetWidth);
            const randomIndex = Math.floor(Math.random() * (layerBitmaps.length - 1));
            const lowerLayer = layerBitmaps[randomIndex];
            const upperLayer = layerBitmaps[randomIndex + 1];
            const upperGround = (upperLayer.groundOffset || stageHeight);
            const baseY = upperGround - 313 * scale;
            img.y = baseY;
            const lowerIndex = stage.getChildIndex(lowerLayer);
            const upperIndex = stage.getChildIndex(upperLayer);
            const insertIndex = lowerIndex + 1 + Math.floor(Math.random() * (upperIndex - lowerIndex));
            stage.addChildAt(img, insertIndex);
            img.cursor = "pointer";
            if(!randomSheet.fake)
            {
              // On click → remove and spawn next immediately
              img.on("click", () => {
                  increaseScore(img);
                  if (stage.contains(img)) {
                      if (img.idleTween) img.idleTween.setPaused(true);
                      createjs.Tween.get(img)
                          .to({ alpha: 0 }, 200)
                          .call(() => {
                              stage.removeChild(img);
                              spawnOne(); // ✅ immediately spawn next
                          });
                  }
              });
            }
            else {
              img.on("click", () => {
                // 1️⃣ visual feedback (red flash or shake)
                const originalX = img.x;
                createjs.Tween.get(img)
                  .to({ x: originalX - 20 }, 100)
                  .to({ x: originalX + 20 }, 100)
                  .to({ x: originalX }, 100);
                // 2️⃣ timeout penalty decrease 2 seconds
                timeLeft = timeLeft - 2;
                buzzerSound.play();
                // 3️⃣ Optional: sound or message
                createjs.Sound.play("wrong"); // only if you have a sound registered
                // 4️⃣ Optional: temporarily tint red or fade out
                createjs.Tween.get(img)
                  .to({ alpha: 0.5 }, 100)
                  .to({ alpha: 1 }, 200);
                   if (stage.contains(img)) {
                      if (img.idleTween) img.idleTween.setPaused(true);
                      createjs.Tween.get(img)
                          .to({ alpha: 0 }, 200)
                          .call(() => {
                              stage.removeChild(img);
                              spawnOne(); // ✅ immediately spawn next
                          });
                  }
              });
            }
            // Entry animation
            img.y = baseY + 200;
            createjs.Tween.get(img)
                .to({ y: baseY }, 600, createjs.Ease.backOut)
                .call(() => {
                    img.idleTween = startIdleAnimation(img, scale, baseY);
                });
            // Auto remove after lifetime
            setTimeout(() => {
                if (stage.contains(img)) {
                    if (img.idleTween) img.idleTween.setPaused(true);
                    createjs.Tween.get(img)
                        .to({ alpha: 0 }, 400)
                        .call(() => {
                            stage.removeChild(img);
                            spawnOne(); // ✅ spawn next after timeout
                        });
                }
            }, settings.lifetime);
        }
    }
    // Start first spawn
    spawnOne();
}
// Modified idle function returns the tween reference
function startIdleAnimation(img, scale, baseY) {
    const idleTypes = ["wave", "stretch", "bob", "pulse"];
    const idleType = idleTypes[Math.floor(Math.random() * idleTypes.length)];
    let tween;
    switch (idleType) {
        case "wave":
            tween = createjs.Tween.get(img, { loop: true })
                .to({ rotation: 10 }, 400, createjs.Ease.quadInOut)
                .to({ rotation: -10 }, 800, createjs.Ease.quadInOut)
                .to({ rotation: 0 }, 400, createjs.Ease.quadInOut);
            break;
        case "stretch":
            tween = createjs.Tween.get(img, { loop: true })
                .to({ scaleY: scale * 1.1, scaleX: scale * 0.9 }, 300, createjs.Ease.quadInOut)
                .to({ scaleY: scale, scaleX: scale }, 300, createjs.Ease.quadInOut);
            break;
        case "bob":
            tween = createjs.Tween.get(img, { loop: true })
                .to({ y: baseY - 15 }, 600, createjs.Ease.sineInOut)
                .to({ y: baseY }, 600, createjs.Ease.sineInOut);
            break;
        case "pulse":
            tween = createjs.Tween.get(img, { loop: true })
                .to({ scaleX: scale * 1.05, scaleY: scale * 1.05 }, 500, createjs.Ease.sineInOut)
                .to({ scaleX: scale, scaleY: scale }, 500, createjs.Ease.sineInOut);
            break;
    }
    return tween;
}
function showEndCelebration() {
    //  gameEnded = true;
    //  bgMusic.pause();
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
    // // // Characters row
    // // const availableChars = characters.map(c => queue.getResult(c.id));
    // // const numChars = availableChars.length;
    // // const charWidth = 200;  
    // // const spacing = 40; 
    // // const totalWidth = numChars * charWidth + (numChars - 1) * spacing;
    // // const startX = (stageWidth - totalWidth) / 2;
    // // const yPos = stageHeight - 250;
    // // availableChars.forEach((imgSrc, i) => {
    // //     const bmp = new createjs.Bitmap(imgSrc);
    // //     const scale = charWidth / bmp.image.width;
    // //     bmp.scaleX = bmp.scaleY = scale;
    // //     bmp.x = startX + i * (charWidth + spacing);
    // //     bmp.y = yPos;
    // //     stage.addChild(bmp);
    // //     // Wave bounce animation
    // //     createjs.Tween.get(bmp, {loop:true})
    // //         .wait(i * 100) // stagger delay
    // //         .to({y: yPos - 40}, 600, createjs.Ease.sineOut)
    // //         .to({y: yPos}, 600, createjs.Ease.sineIn);
    // // });
    // // 🎊 Confetti effect
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
          // After animation, redirect to the post_game.html, passing the score
    localStorage.setItem('game_finished', "1");
    setTimeout(() => {
        window.top.location.href = `process_post_game.php?score=${score}`;
        
    }, 1500); // Allow 1.5 seconds for "Time's Up" animation
}
// Show all characters in celebration
function showAllCharacters() {
    const totalChars = characters.length;
    const charWidth = 200; // fixed width
    const posY = stageHeight - 220; // bottom margin
    // available space for total characters
    const totalWidth = totalChars * charWidth;
    // spacing calculation: distribute evenly in stage
    let spacing = 20; // default spacing
    if (totalWidth + (totalChars - 1) * spacing > stageWidth) {
        spacing = (stageWidth - totalWidth) / (totalChars - 1);
    }
    // starting x so that whole group is centered
    const totalRowWidth = totalWidth + (totalChars - 1) * spacing;
    const startX = (stageWidth - totalRowWidth) / 2;
    characters.forEach((c, index) => {
        const charImg = queue.getResult(c.id);
        const img = new createjs.Bitmap(charImg);
        // Scale to exactly 200px width
        const scale = charWidth / charImg.width;
        img.scaleX = img.scaleY = scale;
        img.x = startX + index * (charWidth + spacing);
        img.y = posY;
        stage.addChild(img);
        // Bounce animation
        createjs.Tween.get(img, {loop:true})
            .to({y: img.y - 40}, 500, createjs.Ease.sineOut)
            .to({y: img.y}, 500, createjs.Ease.sineIn);
    });
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
