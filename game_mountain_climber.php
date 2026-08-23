<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Mountain Climber Game</title>
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
      overflow: hidden;
      background: #000;
    }
    #game-container {
      position: relative;
      width: 100vw;
      height: 100vh;
    }
    #timerCanvas {
    position: absolute;
    top: -1px;
    right: -4px;
    z-index: 20;
    }
    #gameWrapper {
      position: absolute;
      width: 2338px;
      height: 1668px;
      top: 0;
      left: 0;
      transform-origin: top left;
    }
    #gameCanvas {
      width: 100%;
      height: 100%;
      position: absolute;
      top: 0;
      left: 0;
      z-index: 1;
    }
    #borderImage {
      position: absolute;
      width: 100%;
      height: 100%;
      top: 0;
      left: 0;
      z-index: 3;
      background-size: 100% 100%;
      background-repeat: no-repeat;
      background-position: center;
      pointer-events: none;
    }
    #startOverlay {
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,0.75);
    display: flex;
    justify-content: center;
    align-items: center;   /* center vertically */
    z-index: 9999;
    pointer-events: none;   /* allow clicks to pass through to speaker */
}
/* Speaker icon */
#startSpeaker {
    width: 100px;
    position: fixed;        
    z-index: 10000;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    cursor: pointer;
    transition: transform 0.8s cubic-bezier(.34,1.56,.64,1); /* bounce */
}
/* Text immediately below speaker */
#tapText {
    position: fixed;
    z-index: 9999;
    color: white;
    font-size: 16px;
    font-family: Arial;
    top: calc(50% + 60px); /* 60px below center (half of speaker height + small margin) */
    left: 50%;
    transform: translateX(-50%);
    pointer-events: none; /* so clicks go to speaker */
}
  </style>
</head>
<body>
    <!-- Speaker outside overlay -->
    <img id="startSpeaker" src="assets/images/speaker-muted.png">
    <audio id="audioPlayerBGM" src="assets/game_mountain_climber/audio/mountain_game_bg_music.mp3" loop></audio>
    <!-- Overlay with only text -->
    <div id="startOverlay" class="start-overlay">
        <p id="tapText">Tap Speaker to Continue</p>
    </div>
  <div id="game-container">
    <div id="gameWrapper">
      <canvas id="gameCanvas" width="2338" height="1668"></canvas>
      <div id="borderImage"></div>
      <canvas id="timerCanvas" width="300" height="300"></canvas>
      <div id="loading-screen" style="
    position: fixed;
    top: 0;
    left: 0;
    width: 2338px;
    height: 1668px;
    display: flex;
    justify-content: center;
    align-items: center;
    flex-direction: column;
    z-index: 9999;
    font-family: Arial, sans-serif;
    font-size: 24px;
    color: #333;
">
    <div class="spinner" style="
        width: 40px;
        height: 40px;
        border: 6px solid #ccc;
        border-top: 6px solid #007BFF;
        border-radius: 50%;
        animation: spin 1s linear infinite;
        margin-bottom: 15px;
    "></div>
    <div id="loading-progress" style="color:#fff;font-size:50px">0%</div>
     <div style="width: 300px; height: 20px; background: #eee; border-radius: 10px; overflow: hidden; margin-top: 15px;">
        <div id="progress-bar" style="
            height: 100%;
            width: 0%;
            background: linear-gradient(90deg, #007BFF, #00BFFF);
            transition: width 0.3s ease;
        "></div>
    </div>
</div>
    </div>
    <div id="resultPage" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(0, 0, 0, 0.8); display: flex; flex-direction: column; justify-content: center; align-items: center; opacity: 0; visibility: hidden; transition: opacity 1s ease-in-out; z-index: 1000;">
        <h2 style="color: white; font-size: 80px; text-align: center; padding: 20px;">Game Results!<br>You finished strong!</h2>
        </div>
    <div id="questionSection" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background-color: #fff; display: flex; flex-direction: column; justify-content: center; align-items: center; opacity: 0; visibility: hidden; transition: opacity 1s ease-in-out; z-index: 1001;">
        <h2 style="color: #333; font-size: 80px; text-align: center; padding: 20px;">Well Being Question Section</h2>
        <button onclick="startGame()" style="padding: 20px 40px; font-size: 48px; cursor: pointer; margin-top: 30px;">Start New Game</button>
    </div>
  </div>
<style>
@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}
</style>
  <!-- CreateJS libraries -->
<script src="https://code.createjs.com/1.0.0/easeljs.min.js"></script>
<script src="https://code.createjs.com/1.0.0/preloadjs.min.js"></script>
<script src="https://code.createjs.com/1.0.0/soundjs.min.js"></script>
<script src="https://code.createjs.com/1.0.0/tweenjs.min.js"></script>
<script>
    const canvas = document.getElementById("gameCanvas");
    const stage = new createjs.Stage(canvas);
    createjs.Touch.enable(stage); // ✅ Enables touch for mobile
    let handImage, tutorialComplete = false;
    let holding = false;
    const timerCanvas = document.getElementById("timerCanvas");
    const timerCtx = timerCanvas.getContext("2d");
    const timerRadius = 130;
    const timerCenter = { x: 150, y: 150 };
    let climberSprite; // This holds our animated climber
    let skierSprite;
    let snowboarderSprite
    let skierSpriteSheet, snowboarderSpriteSheet;
    let isClimbing = false; // State variable to manage animation based on climber's activity
    let countdownRemaining = 60;
    const totalClimbTime = 60;
    let countdownInterval;
    let bgmSoundInstance;
    let isPlayerDisabled = false;
    let playerDisabledTimeout;
    let obstacleHitThisRound = null;
    var theme = localStorage.getItem("selectedTheme") || "dino";
    //document.getElementById("borderImage").style.backgroundImage = `url('assets/images/${theme}.png')`;
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
        pet: { color1: '#E78780', color2: '#ECCC96', color3: '#b3534c' },
        train: { color1: '#8AA9DA', color2: '#B0D0E4', color3: '#5c7aad' }
    };
    const { color1, color2, color3 } = themeColors[theme] || { color1: '#000', color2: '#FFF', color3: '#FFF' };
    document.documentElement.style.setProperty('--color1', color1);
    document.documentElement.style.setProperty('--color2', color2);
    document.documentElement.style.setProperty('--color3', color3);
    var currentDifficulty = localStorage.getItem('gameTrickLevel') || 'tricky';
    currentDifficulty = 'tricky'; // You can remove this line if you want to use the saved value
    const difficultySettings = {
        relaxed: { maxObjects: 5, types: [1, 2, 5], frequency: 15000, minCounts: 4 },
        normal: { maxObjects: 7, types: [1, 2, 3, 5, 6], frequency: 10000, minCounts: 10 },
        tricky: { maxObjects: 15, types: [1, 2, 3, 4, 5, 6, 7], frequency: 1000, minCounts: 15 },
        xtricky: { maxObjects: 12, types: [1], frequency: 5000, minCounts: 100 }
    };
    const difficulty = difficultySettings[currentDifficulty];
    // Tracking unique appearances
    const typeAppearanceCounts = {};
    difficulty.types.forEach(t => typeAppearanceCounts[t] = 0);
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
    const loader = new createjs.LoadQueue(true);
    loader.installPlugin(createjs.Sound);
    loader.on("progress", function (event) {
       const percent = Math.floor(event.progress * 100);
        document.getElementById("loading-progress").innerText = `${percent}%`;
        document.getElementById("progress-bar").style.width = `${percent}%`;
    });
    loader.loadManifest([
        { id: "bg", src: "assets/game_mountain_climber/images/mountain_bg.png" },
        { id: "climber", src: "assets/game_mountain_climber/images/climber.png" }, // Old static climber, can be removed if not used elsewhere
        { id: "climberSheet", src: "assets/game_mountain_climber/images/climber_sprite_sheet_v2.png" }, // Changed to .jpg
        { id: "gameFailSound", src: "assets/game_mountain_climber/audio/game-fail.mp3" }, // ADD THIS LINE
        { id: "hand", src: "assets/game_mountain_climber/images/hand.png" },
        { id: "finish", src: "assets/game_mountain_climber/images/finish_line.png" },
        { id: "tree", src: "assets/game_mountain_climber/images/tree.png" },
        { id: "log", src: "assets/game_mountain_climber/images/log.png" },
        { id: "large_rock", src: "assets/game_mountain_climber/images/large_rock.png" },
        { id: "small_rock", src: "assets/game_mountain_climber/images/small_rock.png" },
        { id: "goat", src: "assets/game_mountain_climber/images/goat.png" },
        { id: "goatSheet", src: "assets/game_mountain_climber/images/goat_sprite_sheet_v2.png" },
        { id: "boulder", src: "assets/game_mountain_climber/images/boulder.png" },
        { id: "snowball", src: "assets/game_mountain_climber/images/snowball.png" },
        { id: "skier", src: "assets/game_mountain_climber/images/skier-right.png" },
        { id: "skierSheet", src: "assets/game_mountain_climber/images/skier_sprite_sheet.png" },
        { id: "snowboarder", src: "assets/game_mountain_climber/images/toboggin.png" },
        { id: "snowboarderSheet", src: "assets/game_mountain_climber/images/toboggan_sprite_sheet.png" },
        { id: "gameEndScreen", src: "assets/game_mountain_climber/images/game_end_screen.png" },
    ]);
    loader.on("complete", function () {
        document.getElementById("loading-screen").style.display = "none";
        handleComplete(); // ⬅️ your existing game setup function
    });
    let bg1, bg2, bgScrollStarted = false, backgroundHeight = 0;
    let finishLine, obstacles = [], obstacleTimer;
    // -----------------------------------------------------------------------------
    function handleComplete() {
        const bgImage = loader.getResult("bg");
        bg1 = new createjs.Bitmap(bgImage);
        bg2 = new createjs.Bitmap(bgImage);
        const scale = canvas.width / bgImage.width;
        bg1.scaleX = bg2.scaleX = bg1.scaleY = bg2.scaleY = scale;
        backgroundHeight = bgImage.height * scale;
        bg1.y = -canvas.height;
        bg2.y = bg1.y - backgroundHeight;
        stage.addChild(bg1, bg2);
        finishLine = new createjs.Bitmap(loader.getResult("finish"));
        finishLine.scaleX = canvas.width / finishLine.image.width;
        finishLine.scaleY = finishLine.scaleX;
        finishLine.alpha = 0;
        finishLine.y = 0;
        stage.addChild(finishLine);
        // --- NEW: Climber Sprite Sheet Setup ---
        const climberSheetImage = loader.getResult("climberSheet");
        const spriteSheetData = {
            images: [climberSheetImage],
            frames: {
                width: 300,  // **CRITICAL: Ensure this matches ONE frame's width**
                height: 533, // **CRITICAL: Ensure this matches ONE frame's height**
                count: 32,
                regX: 150,    // Center X of the frame
                regY: 0    // Bottom Y of the frame (for character's feet)
            },
          animations: {
                idle: {
                    frames: [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 9, 9, 9, 9, 9, 9, 9, 9, 9, 9, 9, 9, 9, 9, 9, 9, 9, 9, 9, 9, 9, 9, 9, 9 ],
                    next: "idle",
                    speed: 0.1
                },
                climb: {
                    frames: [10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23, 24, 25, 26, 27, 28, 29, 30, 31],
                    next: "climb",
                    speed: 0.1
                }
            }
        };
        const climberSpriteSheet = new createjs.SpriteSheet(spriteSheetData);
        climberSprite = new createjs.Sprite(climberSpriteSheet, "idle");
        // --- END NEW Climber Sprite Sheet Setup ---
        // ⭐ Goat Sprite Sheet Setup with correct dimensions ⭐
        const goatSheetImage = loader.getResult("goatSheet");
        if (!goatSheetImage) {
            console.error("Goat sprite sheet 'goatSheet' not loaded. Check path and ID in manifest.");
            return;
        }
        const goatSpriteSheetData = {
            images: [goatSheetImage],
            frames: {
                width: 300,
                height: 300,
                count: 40,
                regX: 150,
                regY: 300
            },
            animations: {
                idle: { frames: [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19], next: null, speed: 0.5 },
                walk: { frames: [20, 21, 22, 23, 24, 25, 26, 27, 28, 29, 30, 31, 32, 33, 34, 35, 36, 37, 38, 39], next: "walk", speed: 0.1 }
            }
        };
        goatSpriteSheet = new createjs.SpriteSheet(goatSpriteSheetData); // Assign to the global variable
        // ⭐ Skier Sprite Sheet Setup with correct dimensions ⭐
        const skierSheetImage = loader.getResult("skierSheet");
        if (!skierSheetImage) {
            console.error("Goat sprite sheet 'skierSheet' not loaded. Check path and ID in manifest.");
            return;
        }
        const skierSpriteSheetData = {
            images: [skierSheetImage],
            frames: {
                width: 400,
                height: 332,
                count: 21,
                regX: 200,
                regY: 332
            },
            animations: {
                idle: { frames: [0], next: null, speed: 0.3 }, // ✅ Fix: stay idle
                walk: { frames: [1, 2, 3, 4, 5, 6, 7, 8, 9,10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20], next: "walk", speed: 0.1 }
            }
        };
        skierSpriteSheet = new createjs.SpriteSheet(skierSpriteSheetData); // Assign to the global variable
        // ⭐ Snowboarder Sprite Sheet Setup with correct dimensions ⭐
        const snowboarderSheetImage = loader.getResult("snowboarderSheet");
        if (!snowboarderSheetImage) {
            console.error("Goat sprite sheet 'snowboarderSheet' not loaded. Check path and ID in manifest.");
            return;
        }
        const snowboarderSpriteSheetData = {
            images: [snowboarderSheetImage],
            frames: {
                width: 446,
                height: 431,
                count: 11,
                regX: 223,
                regY: 431
            },
            animations: {
                idle: { frames: [0], next: null, speed: 0.3 }, // ✅ Fix: stay idle
                walk: { frames: [1, 2, 3, 4, 5, 6, 7, 8, 9,10], next: "walk", speed: 0.1 }
            }
        };
        snowboarderSpriteSheet = new createjs.SpriteSheet(snowboarderSpriteSheetData); // Assign to the global variable
        //createjs.Sound.play("bgm", { loop: -1, volume: 0.4 });
        createjs.Tween.get(bg1).to({ y: 0 }, 4000, createjs.Ease.getPowInOut(2)).call(() => {
            bg2.y = -backgroundHeight;
            showClimber(); // Will now correctly position and add the animated sprite
            showHandHint();
            // startCountdownTimer(); // Keep this commented if you start it after tutorial
        });
        createjs.Ticker.framerate = 60;
        createjs.Ticker.addEventListener("tick", stage);
    }
    // -----------------------------------------------------------------------------
    function showClimber() {
        const scale = 1; // Scale factor for the climber
        // --- OLD: Remove/Comment out these lines that create and add the static Bitmap ---
        // climberImage = new createjs.Bitmap(loader.getResult("climber")); // This is the old static image
        // climberImage.scaleX = climberImage.scaleY = scale;
        // climberImage.x = canvas.width / 2 - (climberImage.image.width * scale) / 2;
        // climberImage.y = canvas.height - (climberImage.image.height * scale) - 130;
        // stage.addChild(climberImage);
        // ---------------------------------------------------------------------------------
        // --- NEW: Position and scale the animated climberSprite ---
        climberSprite.scaleX = climberSprite.scaleY = scale;
        // Position x (center of canvas using its regX)
        climberSprite.x = canvas.width / 2;
        // Position y (adjusted from bottom, considering the scaled height and registration point)
        climberSprite.y = canvas.height - (climberSprite.getBounds().height * scale) - 100;
        // Ensure the climberSprite is added to the stage
        if (!stage.contains(climberSprite)) {
            stage.addChild(climberSprite);
        }
        // Set initial animation state and enable dragging
        climberSprite.gotoAndPlay("idle"); // Start with the idle animation when shown
        isClimbing = false; // Reset climb state
        enableClimberDrag(climberSprite); // This function will now be updated to work with climberSprite
    }
    // -----------------------------------------------------------------------------
    function showHandHint() {
        handImage = new createjs.Bitmap(loader.getResult("hand"));
        handImage.scaleX = handImage.scaleY = 0.4;
        handImage.x = canvas.width / 2 - (handImage.image.width * 0.4) / 2;
        handImage.y = canvas.height - (handImage.image.height * 0.4) - 150;
        createjs.Tween.get(handImage, { loop: true })
            .to({ x: handImage.x + 100 }, 1000, createjs.Ease.sineInOut)
            .to({ x: handImage.x }, 1000, createjs.Ease.sineInOut);
        stage.addChild(handImage);
    }
    function enableClimberDrag(sprite) {
            holding = false,
            dragOffsetX = 0,
            pressStartTime = 0;
        sprite.on("mousedown", function (e) {
            if (isPlayerDisabled) return;
            holding = true;
            dragOffsetX = e.stageX - sprite.x;
            pressStartTime = Date.now();
            // Start climb animation
            if (sprite.currentAnimation !== "climb") {
                sprite.gotoAndPlay("climb");
                isClimbing = true;
            }
            // Remove tutorial hand
            if (!tutorialComplete && handImage && stage.contains(handImage)) {
                createjs.Tween.removeTweens(handImage);
                stage.removeChild(handImage);
                handImage = null;
                stage.update();
            }
        });
        sprite.on("pressmove", function (e) {
            if (!holding || isPlayerDisabled) return;
            const newX = e.stageX - dragOffsetX;
            const scaledHalfWidth = (sprite.getBounds().width * sprite.scaleX) / 2;
            // Clamp to canvas width
            sprite.x = Math.max(
                scaledHalfWidth,
                Math.min(canvas.width - scaledHalfWidth, newX)
            );
            // Stay in climbing animation
            if (sprite.currentAnimation !== "climb") {
                sprite.gotoAndPlay("climb");
                isClimbing = true;
            }
            // Complete tutorial on hold
            if (!tutorialComplete) {
                const heldDuration = Date.now() - pressStartTime;
                if (heldDuration >= 10) {
                    tutorialComplete = true;
                    startBackgroundScroll();
                    startCountdownTimer();
                    startObstacleSpawner();
                    // bgmSoundInstance = createjs.Sound.play("bgm", {
                    //     loop: -1,
                    //     volume: 0.4
                    // });
                }
            }
            stage.update();
        });
        sprite.on("pressup", function () {
            holding = false;
            if (sprite.currentAnimation !== "idle") {
                sprite.gotoAndPlay("idle");
                isClimbing = false;
            }
            stage.update();
        });
        // Optional: Start showing stats if needed
        setInterval(showTypeStats, 2000);
    }
    // -----------------------------------------------------------------------------
    function enableClimberDragx() {
        holding = false, dragOffsetX = 0, pressStartTime = 0;
        // Use climberSprite's events directly for more accurate interaction with the sprite itself
        // rather than the canvas globally. This will also ensure correct hit detection for the sprite.
        climberSprite.addEventListener("mousedown", (e) => {
            if (isPlayerDisabled) {
                return;
            }
            // `e.localX` and `e.localY` are relative to the display object (climberSprite)
            holding = true;
            dragOffsetX = e.localX; // Offset relative to the sprite's x
            pressStartTime = Date.now();
            // Start climbing animation immediately on mousedown
            if (climberSprite.currentAnimation !== "climb") {
                climberSprite.gotoAndPlay("climb");
                isClimbing = true;
            }
            // Remove the hand hint if tutorial is not complete
            if (!tutorialComplete && handImage && stage.contains(handImage)) {
                 createjs.Tween.removeTweens(handImage); // Stop any tweens on hand
                 stage.removeChild(handImage);
                 handImage = null;
                 stage.update();
            }
            // Attach listeners to stage to ensure drag continues even if mouse leaves sprite
            stage.addEventListener("stagemousemove", handleClimberDragMove);
            stage.addEventListener("stagemouseup", handleClimberDragUp);
        });
        // Function for mousemove during drag
       function handleClimberDragMove(e) {
            if (!holding || isPlayerDisabled) {
                return;
            }
            // Calculate the new X position relative to the sprite's registration point (its center due to regX: 300).
            // e.stageX is the current mouse X position on the canvas.
            // dragOffsetX is the initial click's X offset *relative to the sprite's local coordinates*.
            // We scale dragOffsetX to match the sprite's current display size.
            const newX = e.stageX - (dragOffsetX * climberSprite.scaleX);
            // Get the actual half width of the *scaled* climber sprite.
            // getBounds() returns the unscaled dimensions, so we multiply by scaleX.
            const scaledHalfWidth = (climberSprite.getBounds().width * climberSprite.scaleX) / 2;
            // Clamp climberSprite.x within canvas bounds.
            // climberSprite.x represents the center of the sprite.
            // Left boundary: The center cannot go further left than its half width from the canvas's left edge (0).
            // Right boundary: The center cannot go further right than (canvas.width - its half width).
            climberSprite.x = Math.max(
                scaledHalfWidth,
                Math.min(
                    canvas.width - scaledHalfWidth,
                    newX
                )
            );
            // Ensure animation is playing if moving
            if (climberSprite.currentAnimation !== "climb") {
                climberSprite.gotoAndPlay("climb");
                isClimbing = true;
            }
            // If tutorial is not complete, check for held duration here
            if (!tutorialComplete) {
                const heldDuration = Date.now() - pressStartTime;
                if (heldDuration >= 10) {
                    tutorialComplete = true;
                    // No need to remove handImage here, already done on mousedown
                    startBackgroundScroll();
                    startCountdownTimer();
                    startObstacleSpawner();
                    // bgmSoundInstance = createjs.Sound.play("bgm", { loop: -1, volume: 0.4 });
                }
            }
            stage.update();
        }
        setInterval(showTypeStats, 2000); // Logs every 10 seconds
        // Function for mouseup after drag
        function handleClimberDragUp(e) {
            holding = false;
            // Stop climbing animation on mouseup
            if (climberSprite.currentAnimation !== "idle") {
                climberSprite.gotoAndPlay("idle");
                isClimbing = false;
            }
            // Remove stage listeners after drag ends
            stage.removeEventListener("stagemousemove", handleClimberDragMove);
            stage.removeEventListener("stagemouseup", handleClimberDragUp);
            stage.update();
        }
        // Note: The global canvas listeners (pointerdown, pointermove, pointerup) are replaced
        // by the direct `climberSprite` listeners and `stage` listeners for drag control.
        // This is generally a better practice for precise sprite interaction.
    }
    // -----------------------------------------------------------------------------
    function startBackgroundScroll() {
        if (bgScrollStarted) return;
        bgScrollStarted = true;
        createjs.Ticker.addEventListener("tick", scrollBackgroundLoop);
    }
    function scrollBackgroundLoop(event) {
    if (!tutorialComplete) {
        stage.update(event);
        return;
    }
    console.log("isPlayerDisabled: " + isPlayerDisabled + " | holding: " + holding);
    const speed = 2;
    if (!isPlayerDisabled && holding) {
        // ✅ Background scrolling
        bg1.y += speed;
        bg2.y += speed;
        if (bg1.y >= canvas.height) bg1.y = bg2.y - backgroundHeight;
        if (bg2.y >= canvas.height) bg2.y = bg1.y - backgroundHeight;
        // ✅ Timer runs
        drawCircularTimer(countdownRemaining, totalClimbTime);
        // ✅ Climber climbs
        if (!isClimbing) {
            climberSprite.gotoAndPlay("climb");
            isClimbing = true;
        }
    } else {
        // ⛔ Not holding or disabled → Pause climb and timer
        if (climberSprite.currentAnimation !== "idle") {
            climberSprite.gotoAndPlay("idle");
            isClimbing = false;
        }
    }
    // ✅ Obstacles always move
    updateObstacles();
    // ✅ Collision detection
    const climberActualWidth = climberSprite.getBounds().width * climberSprite.scaleX;
    const climberActualHeight = climberSprite.getBounds().height * climberSprite.scaleY;
    const climberBounds = climberSprite.getTransformedBounds();
    for (let i = obstacles.length - 1; i >= 0; i--) {
        const obj = obstacles[i];
        const obstacleBitmap = obj.bitmap;
        const obstacleBounds = obstacleBitmap.getTransformedBounds();
        if (climberBounds && obstacleBounds && isCollidingGeneral(climberBounds, obstacleBounds)) {
            if (!isPlayerDisabled) {
                handlePlayerHit(obj);
                break;
            }
        }
    }
    // ✅ Always update stage
    stage.update(event);
}
   let borderShape = null; // Declare this globally or accessible to handlePlayerHit
function handlePlayerHit(hitObstacle) {
    // Immediately return if player is already disabled
    if (isPlayerDisabled) {
        return;
    }
    isPlayerDisabled = true;
    obstacleHitThisRound = hitObstacle;
    createjs.Sound.play("gameFailSound");
    // Clear existing timeouts/intervals to prevent re-triggering during disabled state
    if (playerDisabledTimeout) clearTimeout(playerDisabledTimeout);
    if (countdownInterval) clearInterval(countdownInterval);
    // Remove any existing borderShape *before* potentially adding a new one
    if (borderShape && stage.contains(borderShape)) {
        stage.removeChild(borderShape);
        borderShape = null;
    }
    // Pause all obstacle tweens and stop goat animations
    obstacles.forEach(obj => {
        // ⭐ MODIFIED: Remove ALL tweens for all obstacles when hit to prevent conflicts ⭐
        createjs.Tween.removeTweens(obj.bitmap);
        createjs.Tween.get(obj.bitmap).setPaused(true); // Pause any new ones if they start immediately
        if (obj.type === 1) { // If it's a goat (a Sprite)
            obj.bitmap.stop();
        }
    });
    // Flash effect on climberSprite
    createjs.Tween.get(climberSprite, { override: true })
        .to({ alpha: 0.5 }, 100)
        .to({ alpha: 1 }, 100);
    // Draw the red border around the hit obstacle
    if (obstacleHitThisRound && obstacleHitThisRound.bitmap) {
        const bmp = obstacleHitThisRound.bitmap;
        // Ensure borderShape is declared (if not global)
        borderShape = new createjs.Shape();
        const obstacleBounds = bmp.getTransformedBounds();
        if (obstacleBounds) {
            borderShape.graphics
                .setStrokeStyle(4)
                .beginStroke("red")
                .drawRect(0, 0, obstacleBounds.width, obstacleBounds.height);
            borderShape.x = obstacleBounds.x;
            borderShape.y = obstacleBounds.y;
            borderShape.alpha = 1; // Ensure border starts fully opaque
            stage.addChild(borderShape);
            stage.update();
        } else {
            console.warn("Could not get bounds for hit obstacle to draw border.");
        }
    }
    // Define fade duration and disable time
    const fadeOutDuration = 20; // milliseconds for fade
    const disableTime = 30;    // milliseconds for player disable (3 seconds)
    // Set a timeout to re-enable the player and resume game elements
    playerDisabledTimeout = setTimeout(() => {
        isPlayerDisabled = false;
        console.log("Player re-enabled. Initiating obstacle fade-out.");
        startCountdownTimer();
        startObstacleSpawner();
        // Resume tweens and animations for other obstacles (not the hit one)
        obstacles.forEach(obj => {
            if (obj && obj.bitmap && obj !== obstacleHitThisRound) { // Ensure it's not the hit obstacle
                // For obstacles that use tweens for movement, you might need to re-initiate their tweens
                // as removeTweens() was called at the start.
                // For now, just ensure goat animations resume:
                if (obj.type === 1) { // If it's a goat
                    const targetX = stage.canvas.width / 2;
                    if (Math.abs(obj.bitmap.x - targetX) > 5) {
                        obj.bitmap.gotoAndPlay("walk");
                    } else {
                        obj.bitmap.gotoAndPlay("idle");
                    }
                }
                // If type 4 obstacles have their own target-seeking logic in handleTick,
                // they will automatically resume when isPlayerDisabled becomes false.
            }
        });
        // ⭐ CRITICAL FIX: Ensure obstacle and border fade/removal is handled reliably ⭐
        if (obstacleHitThisRound && obstacleHitThisRound.bitmap) {
            createjs.Tween.removeTweens(obstacleHitThisRound.bitmap); // Ensure no other tweens interfere
            if (obstacleHitThisRound.type === 1) {
                obstacleHitThisRound.bitmap.stop(); // Stop goat animation before fading
            }
            // Start fading the hit obstacle
            createjs.Tween.get(obstacleHitThisRound.bitmap)
                .to({ alpha: 0 }, fadeOutDuration);
            // If borderShape exists, fade it and use its .call() for ALL cleanup
            if (borderShape && stage.contains(borderShape)) {
                createjs.Tween.get(borderShape)
                    .to({ alpha: 0 }, fadeOutDuration) // Fade border simultaneously
                    .call(() => {
                        // All cleanup happens here after both have faded
                        if (obstacleHitThisRound && stage.contains(obstacleHitThisRound.bitmap)) {
                            stage.removeChild(obstacleHitThisRound.bitmap);
                        }
                        if (borderShape && stage.contains(borderShape)) {
                            stage.removeChild(borderShape);
                        }
                        borderShape = null; // Clear borderShape reference
                        // Remove obstacle from array and clear its reference
                        const index = obstacles.indexOf(obstacleHitThisRound);
                        if (index > -1) {
                            obstacles.splice(index, 1);
                        }
                        obstacleHitThisRound = null; // Clear reference AFTER removal from array
                        stage.update(); // Update stage to reflect removals
                    });
            } else {
                // Fallback: If no borderShape, just fade the obstacle and clean up
                createjs.Tween.get(obstacleHitThisRound.bitmap)
                    .to({ alpha: 0 }, fadeOutDuration)
                    .call(() => {
                        if (obstacleHitThisRound && stage.contains(obstacleHitThisRound.bitmap)) {
                            stage.removeChild(obstacleHitThisRound.bitmap);
                        }
                        const index = obstacles.indexOf(obstacleHitThisRound);
                        if (index > -1) {
                            obstacles.splice(index, 1);
                        }
                        obstacleHitThisRound = null;
                        stage.update();
                    });
            }
        }
        if (climberSprite.currentAnimation !== "idle") {
            climberSprite.gotoAndPlay("idle");
            // Assuming isClimbing is a flag you manage for climber animation state
            // isClimbing = false;
        }
        // No need for an extra stage.update() here, as it's handled within the fade.call()
    }, disableTime); // The timeout duration is 3 seconds
    console.log("Player hit an obstacle! Game paused for 3 seconds.");
}
// ⭐ IMPORTANT: Make sure `borderShape` is declared outside the function scope,
// for example, near `playerDisabledTimeout` and `countdownInterval`.
// E.g., `let borderShape = null;` at the top of your script.
    function drawCircularTimer(remaining, total) {


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
        // timerCtx.fillText(remaining, timerCenter.x, timerCenter.y);
    }
    let graceCounter = 0;
    const GRACE_LIMIT = 20; // Seconds to wait before "Ghost Hold" kicks in

    function startCountdownTimer() {
        if (countdownInterval) {
            clearInterval(countdownInterval);
        }
        
        drawCircularTimer(countdownRemaining, totalClimbTime);

        countdownInterval = setInterval(() => {
            // Condition: Run the logic if the player IS holding 
            // OR if they haven't held for longer than the grace limit
            if (holding || graceCounter >= GRACE_LIMIT) {
                
                // Logic for "Holding Functions"
                countdownRemaining--;
                drawCircularTimer(countdownRemaining, totalClimbTime);
                
                // If the player starts holding again, reset the grace counter
                if (holding) {
                    graceCounter = 0; 
                }

                if (countdownRemaining <= 0) {
                    clearInterval(countdownInterval);
                    showFinishLineFade();
                }
            } else {
                // If NOT holding and NOT yet at grace limit, just count the idle time
                graceCounter++;
                console.log("Idle grace time: " + graceCounter);
            }
        }, 1000);
    }
    function showFinishLineFade() {
    createjs.Ticker.removeEventListener("tick", scrollBackgroundLoop);
    clearInterval(countdownInterval);
    clearInterval(obstacleTimer);
    if (playerDisabledTimeout) {
        clearTimeout(playerDisabledTimeout);
        playerDisabledTimeout = null;
    }
    isPlayerDisabled = false;
    // Clear obstacles
    obstacles.forEach(obj => {
        if (createjs.Tween.hasActiveTweens(obj.bitmap)) {
            createjs.Tween.removeTweens(obj.bitmap);
        }
        if (stage.contains(obj.bitmap)) {
            stage.removeChild(obj.bitmap);
        }
        // Removed obj.borderShape as it's typically managed by obstacleHitThisRound
        // If an obstacle itself has a borderShape property for other reasons, ensure it's handled properly.
        // For standard hit border, it's covered by obstacleHitThisRound's cleanup.
    });
    obstacles = [];
    // Clear hit obstacle (if it's still present)
    if (obstacleHitThisRound && obstacleHitThisRound.bitmap && stage.contains(obstacleHitThisRound.bitmap)) {
        createjs.Tween.removeTweens(obstacleHitThisRound.bitmap);
        stage.removeChild(obstacleHitThisRound.bitmap);
        // Ensure its borderShape is also removed if it exists
        if (borderShape && stage.contains(borderShape)) { // Use the global borderShape for hit obstacles
            createjs.Tween.removeTweens(borderShape);
            stage.removeChild(borderShape);
            borderShape = null; // Clear global reference
        }
        obstacleHitThisRound = null;
    }
    // Set climber to idle animation immediately when finish line appears
    if (climberSprite && stage.contains(climberSprite)) {
        createjs.Tween.removeTweens(climberSprite);
        climberSprite.gotoAndPlay("idle");
        isClimbing = false;
    }
    // Fade out background music
    // if (bgmSoundInstance) {
    //     createjs.Tween.get(bgmSoundInstance)
    //         .to({ volume: 0 }, 2000)
    //         .call(() => {
    //             bgmSoundInstance.stop();
    //             bgmSoundInstance = null;
    //         });
    // }
    // Tween the finish line to fade in
    createjs.Tween.get(finishLine).to({ alpha: 1 }, 1000);
    // Sequence for end game
    setTimeout(() => {
        // Remove finish line from stage (after its initial fade-in delay)
        if (stage.contains(finishLine)) {
            createjs.Tween.removeTweens(finishLine);
            stage.removeChild(finishLine);
        }
        // Hide the climber (fade out)
        if (climberSprite && stage.contains(climberSprite)) {
            createjs.Tween.get(climberSprite)
                .to({ alpha: 0 }, 500)
                .call(() => {
                    stage.removeChild(climberSprite);
                    climberSprite.alpha = 1; // Reset alpha for future use if needed
                });
        }
        // Display game end screen
        const gameEndScreenImage = loader.getResult("gameEndScreen");
        if (gameEndScreenImage) {
            const endScreenBitmap = new createjs.Bitmap(gameEndScreenImage);
            // Scale to fill canvas
            endScreenBitmap.scaleX = canvas.width / gameEndScreenImage.width;
            endScreenBitmap.scaleY = canvas.height / gameEndScreenImage.height;
            endScreenBitmap.x = 0;
            endScreenBitmap.y = 0;
            endScreenBitmap.alpha = 0; // Start invisible
            stage.addChild(endScreenBitmap);
            createjs.Tween.get(endScreenBitmap).to({ alpha: 1 }, 1000).call(() => {
                // Add "You did it!" text
                const textString = "🏁 You did it!\nEven though it was hard, now you can enjoy the view";
                const fontSize = "64px Comic Sans MS";
                const outlineColor = "#fff";
                const outlineThickness = 5;
                const outlineText = new createjs.Text(textString, "bold " + fontSize, outlineColor);
                outlineText.outline = outlineThickness;
                outlineText.x = canvas.width / 2;
                outlineText.y = canvas.height / 4;
                outlineText.textAlign = "center";
                outlineText.textBaseline = "middle";
                outlineText.alpha = 0; // Start invisible
                // Assuming 'color3' is a globally defined color variable
                const fillText = new createjs.Text(textString, "bold " + fontSize, color3);
                fillText.x = canvas.width / 2;
                fillText.y = canvas.height / 4;
                fillText.textAlign = "center";
                fillText.textBaseline = "middle";
                fillText.alpha = 0; // Start invisible
                fillText.shadow = new createjs.Shadow("rgba(0,0,0,0.5)", 3, 3, 5);
                stage.addChild(outlineText, fillText);
                // Fade in text
                createjs.Tween.get(outlineText).to({ alpha: 1 }, 1000);
                createjs.Tween.get(fillText).to({ alpha: 1 }, 1000);
                stage.update(); // Update stage after adding text
                // ⭐ MODIFIED: Fade out everything and redirect after a delay ⭐
                setTimeout(() => {
                    // Fade out the game end screen and its text
                    createjs.Tween.get(endScreenBitmap).to({ alpha: 0 }, 500).call(() => {
                        stage.removeChild(endScreenBitmap);
                    });
                    createjs.Tween.get(outlineText).to({ alpha: 0 }, 500).call(() => {
                        stage.removeChild(outlineText);
                    });
                    createjs.Tween.get(fillText).to({ alpha: 0 }, 500).call(() => {
                        stage.removeChild(fillText);
                        // ⭐ FINAL ACTION: Redirect to post_game_actions.html ⭐
                        localStorage.setItem('game_finished', "1");
                        window.top.location.href = `process_post_game.php`;
                    });
                }, 3000); // Delay before fading out (e.g., show message for 3 seconds)
            });
        } else {
            console.error("Game end screen image 'gameEndScreen' not found in loader results. Displaying fallback text.");
            const msgFallback = new createjs.Text("🏁 You did it!\nEven though it was hard, now you can enjoy the view", "bold 64px Comic Sans MS", color3);
            msgFallback.x = canvas.width / 2;
            msgFallback.y = canvas.height / 4;
            msgFallback.textAlign = "center";
            msgFallback.textBaseline = "middle";
            msgFallback.alpha = 0;
            stage.addChild(msgFallback);
            createjs.Tween.get(msgFallback).to({ alpha: 1 }, 1000).call(() => {
                stage.update(); // Ensure fallback text is drawn
                setTimeout(() => {
                    createjs.Tween.get(msgFallback).to({ alpha: 0 }, 500).call(() => {
                        stage.removeChild(msgFallback);
                        // ⭐ FINAL ACTION: Redirect to post_game_actions.html for fallback ⭐
                        window.top.location.href = `process_post_game.php`;
                    });
                }, 3000); // Delay before fading out fallback text
            });
        }
        stage.update(); // Final update for removals before next sequence
    }, 3000); // Initial delay for finish line display (e.g., 3 seconds before game end screen appears)
}

function updateTimerUI(remaining, total) {
    window.parent.postMessage({
        action: 'UPDATE_TIMER',
        remaining: remaining,
        total: total
    }, '*');
}


    function spawnObstacle() {
        if (!tutorialComplete) return;
        const types = difficulty.types;
        let underrepresentedTypes = types.filter(t => typeAppearanceCounts[t] < difficulty.minCounts);
        let type;
        if (underrepresentedTypes.length > 0) {
            type = underrepresentedTypes[Math.floor(Math.random() * underrepresentedTypes.length)];
        } else {
            type = types[Math.floor(Math.random() * types.length)];
        }
        // Track appearance count
        typeAppearanceCounts[type] = (typeAppearanceCounts[type] || 0) + 1;
        console.log(`Spawned type ${type} (Count so far: ${typeAppearanceCounts[type]})`);
        let obstacleDisplayObject; // Can be Bitmap or Sprite
        let initialScale = 0.8; // Default scale for most obstacles
        let imgId;
        if (type !== 1 && type !== 3 && type !== 4) { // Only set imgId if it's not a goat
            imgId = type === 2 ? (Math.random() < 0.5 ? 'boulder' : 'snowball') :
                    type === 5 ? 'log' :
                    type === 6 ? 'tree' :
                    type === 7 ? 'large_rock' : 'small_rock';
        }
        console.log("ID picked " + type);
        if (type === 1) { // ⭐ Goat (type 1): Use the SpriteShee ⭐
            if (!goatSpriteSheet) {
                console.error("goatSpriteSheet is not defined. Make sure it's initialized in handleComplete.");
                return;
            }
            obstacleDisplayObject = new createjs.Sprite(goatSpriteSheet, "walk"); // Start with the 'idle' animation
            // ⭐ CHANGE THIS LINE BACK ⭐
            initialScale = 1; // Adjust this value (e.g., 0.5, 0.6, 0.7) to control the goat's size
            obstacleDisplayObject.scaleX = obstacleDisplayObject.scaleY = initialScale;
            // Apply goat-specific initial positioning
            const targetX = stage.canvas.width / 2; // Center X
            const startX = stage.canvas.width - 100; // Start from offscreen right
            const randomY = Math.random() * (stage.canvas.height / 2 - 300) + 0; // Spawns between y=0 and y=midpoint - 300
            //const startX = 500;
            obstacleDisplayObject.x = startX;
            obstacleDisplayObject.y = randomY;
            //  // Goat walks to center
            // createjs.Tween.get(obstacleDisplayObject)
            //     .to({ x: targetX }, 10000, createjs.Ease.sineOut)
            //     .call(() => {
            //         obstacleDisplayObject.gotoAndPlay("idle"); // Switch to idle after reaching target
            //     });
            obstacleDisplayObject.gotoAndPlay("walk"); // Start walking animation
            console.log("Spawned animated goat! target x " + targetX + ' start x ' + startX + ' random y ' + randomY);
        }
        else if (type === 3) { // ⭐ Goat (type 1): Use the SpriteShee ⭐
            if (!skierSpriteSheet) {
                console.error("skierSprite is not defined. Make sure it's initialized in handleComplete.");
                return;
            }
            obstacleDisplayObject = new createjs.Sprite(skierSpriteSheet, "walk"); // Start with the 'idle' animation
            // ⭐ CHANGE THIS LINE BACK ⭐
            initialScale = 1.2; // Adjust this value (e.g., 0.5, 0.6, 0.7) to control the goat's size
            obstacleDisplayObject.scaleX = obstacleDisplayObject.scaleY = initialScale;
            // Apply goat-specific initial positioning
            const targetX = 0;
            const startX = stage.canvas.width - 100; // Start from offscreen right
            const randomY = Math.random() * (stage.canvas.height / 2 - 300) + 0; // Spawns between y=0 and y=midpoint - 300
            //const startX = 500;
            obstacleDisplayObject.x = startX;
            obstacleDisplayObject.y = randomY;
            //  // Goat walks to center
            // createjs.Tween.get(obstacleDisplayObject)
            //     .to({ x: targetX }, 10000, createjs.Ease.sineOut)
            //     .call(() => {
            //         obstacleDisplayObject.gotoAndPlay("idle"); // Switch to idle after reaching target
            //     });
            obstacleDisplayObject.gotoAndPlay("walk"); // Start walking animation
            console.log("Spawned animated skier! target x " + targetX + ' start x ' + startX + ' random y ' + randomY);
        }
        else if (type === 4) { // ⭐ Goat (type 1): Use the SpriteShee ⭐
            if (!snowboarderSpriteSheet) {
                console.error("snowboarderSpriteSheet is not defined. Make sure it's initialized in handleComplete.");
                return;
            }
            obstacleDisplayObject = new createjs.Sprite(snowboarderSpriteSheet, "walk"); // Start with the 'idle' animation
            // ⭐ CHANGE THIS LINE BACK ⭐
            initialScale = .8; // Adjust this value (e.g., 0.5, 0.6, 0.7) to control the goat's size
            obstacleDisplayObject.scaleX = obstacleDisplayObject.scaleY = initialScale;
            // Apply goat-specific initial positioning
            const targetX = 0;
            const startX = 0; // Start from offscreen right
            const randomY = Math.random() * (stage.canvas.height / 2 - 300) + 0; // Spawns between y=0 and y=midpoint - 300
            //const startX = 500;
            obstacleDisplayObject.x = startX;
            obstacleDisplayObject.y = randomY;
            //  // Goat walks to center
            // createjs.Tween.get(obstacleDisplayObject)
            //     .to({ x: targetX }, 10000, createjs.Ease.sineOut)
            //     .call(() => {
            //         obstacleDisplayObject.gotoAndPlay("idle"); // Switch to idle after reaching target
            //     });
            obstacleDisplayObject.gotoAndPlay("walk"); // Start walking animation
            console.log("Spawned animated snow Boarder! target x " + targetX + ' start x ' + startX + ' random y ' + randomY);
        }
        else { // ⭐ Other Obstacles: Keep using Bitmap as before ⭐
            const image = loader.getResult(imgId);
            if (!image) {
                console.warn(`Failed to get image for ID: ${imgId}. It might not have loaded correctly or ID is wrong.`);
                return;
            }
           obstacleDisplayObject = new createjs.Bitmap(image);
            obstacleDisplayObject.scaleX = obstacleDisplayObject.scaleY = initialScale;
            // 🎯 Constrain spawn within 100px from left/right
            const leftMargin = 100;
            const rightMargin = 100;
            const maxX = canvas.width - rightMargin - obstacleDisplayObject.image.width * obstacleDisplayObject.scaleX;
            const minX = leftMargin;
            obstacleDisplayObject.x = Math.random() * (maxX - minX) + minX;
            obstacleDisplayObject.y = -obstacleDisplayObject.image.height * obstacleDisplayObject.scaleY;
        }
        stage.addChild(obstacleDisplayObject);
        obstacles.push({ bitmap: obstacleDisplayObject, type: type });
    }
    function showTypeStats() {
        const statsText = Object.entries(typeAppearanceCounts)
            .map(([type, count]) => `Type ${type}: ${count}`)
            .join(" | ");
        console.log("Obstacle Stats =>", statsText);
    }
   function updateObstacles() {
        for (let i = obstacles.length - 1; i >= 0; i--) {
            const obj = obstacles[i];
            const obstacleBitmap = obj.bitmap; // Using a clearer variable name
            // Determine effective width for off-screen check
            let effectiveWidth;
            // Check if it's a Sprite (goat) or Bitmap (others)
            if (obj.type === 1) { // It's a goat, which is a Sprite
                const bounds = obstacleBitmap.getBounds(); // Get the bounds of the current frame
                if (bounds) {
                    effectiveWidth = bounds.width * obstacleBitmap.scaleX; // Use scaled width from bounds
                } else {
                    // Fallback if bounds are not yet calculated (shouldn't happen often after init)
                    effectiveWidth = 0; // Prevent error, but might remove too early
                    console.warn("Goat sprite bounds not available yet, using 0 for width.");
                }
            }
            else  if (obj.type === 3 || obj.type === 4) { // It's a goat, which is a Sprite
                const bounds = obstacleBitmap.getBounds(); // Get the bounds of the current frame
                if (bounds) {
                    effectiveWidth = bounds.width * obstacleBitmap.scaleX; // Use scaled width from bounds
                } else {
                    // Fallback if bounds are not yet calculated (shouldn't happen often after init)
                    effectiveWidth = 0; // Prevent error, but might remove too early
                    console.warn("Skier sprite bounds not available yet, using 0 for width.");
                }
            } else { // It's a Bitmap (log, rock, etc.)
                effectiveWidth = obstacleBitmap.image.width * obstacleBitmap.scaleX;
            }
            if (!isPlayerDisabled) {
                // Your existing movement logic
                // Note: Goats (type 1) are handled by tweens, not manual Y updates in this function.
                // So, no specific `obj.type === 1` condition needed here for manual movement.
                const centerX = stage.canvas.width / 2;
                  if (obj.type === 1) {
                    if (obj.bitmap.x > centerX) {
                        obj.bitmap.x -= 2; // Move left
                    } else {
                        obj.bitmap.x = centerX; // Snap to center
                        obj.bitmap.gotoAndPlay("idle"); // Switch to idle
                    }
                }
                const speed = 50;
                if (obj.type === 2) obj.bitmap.y += speed;
                else if (obj.type === 3) {
                    obj.bitmap.x -= 5; // skier moves left
                    obj.bitmap.y += 2;
                } 
              else if (obj.type === 4) {
            // --- Step 1: Initialize target if it's the first time for this obstacle ---
            // If targetX or targetY are not yet set (e.g., when the obstacle is first created)
            // or if the obstacle has reached its current target, set a new one.
            if (obj.targetX === undefined || obj.targetY === undefined ||
                (Math.abs(obj.bitmap.x - obj.targetX) < 5 && // 5 is your moveTolerance
                Math.abs(obj.bitmap.y - obj.targetY) < 5)) { // 5 is your moveTolerance
                const minX = 0;
                const maxX = stage.canvas.width - obj.bitmap.getBounds().width;
                const minY = 0;
                const maxY = stage.canvas.height - obj.bitmap.getBounds().height;
                // Ensure valid range even if obstacle is larger than canvas dimension
                const actualMaxX = Math.max(minX, maxX);
                const actualMaxY = Math.max(minY, maxY);
                obj.targetX = Math.random() * (actualMaxX - minX) + minX;
                obj.targetY = Math.random() * (actualMaxY - minY) + minY;
                // console.log(`Obstacle (type 4) new target: (${obj.targetX.toFixed(0)}, ${obj.targetY.toFixed(0)})`); // For debugging
            }
            // --- Step 2: Move towards the current target ---
            const speed = 6.5; // You can make this a property of obj if different speeds are needed per obstacle
            const dx = obj.targetX - obj.bitmap.x;
            const dy = obj.targetY - obj.bitmap.y;
            const distance = Math.sqrt(dx * dx + dy * dy);
            if (distance > speed) {
                // Move incrementally towards the target
                obj.bitmap.x += (dx / distance) * speed;
                obj.bitmap.y += (dy / distance) * speed;
            } else {
                // If very close to the target, snap to it to prevent overshooting
                obj.bitmap.x = obj.targetX;
                obj.bitmap.y = obj.targetY;
            }
        }
                else if (obj.type === 5) {
                    obj.bitmap.y += 3;
                } else { // This includes goats IF their tween has finished and they need to fall
                    // If a goat's tween is active, we don't manually move its Y here.
                    // If its tween finished, then it can start falling like other "default" obstacles.
                    // You might need to refine this if you want goats to stay fixed at center.
                    // For now, this will make goats fall after their initial horizontal tween.
                    if (obj.type !== 1 || !createjs.Tween.hasActiveTweens(obstacleBitmap)) {
                        obstacleBitmap.y += 2;
                    }
                }
            }
            // ⭐ MODIFIED: Off-screen check using effectiveWidth ⭐
            // Adjusted the off-screen check for obj.bitmap.x < -effectiveWidth
            if (obj.bitmap.y > canvas.height || obj.bitmap.x > canvas.width || obj.bitmap.x < -effectiveWidth) {
                stage.removeChild(obstacleBitmap);
                obstacles.splice(i, 1);
            }
        }
    }
    function startObstacleSpawner() {
        clearInterval(obstacleTimer);
         // ⏱️ Spawn immediately
        if (!isPlayerDisabled && obstacles.length < difficulty.maxObjects) {
            spawnObstacle();
        }
        obstacleTimer = setInterval(() => {
            if (!isPlayerDisabled && obstacles.length < difficulty.maxObjects) {
                spawnObstacle();
            }
        }, difficulty.frequency);
    }
    // Returns true if rectangle a and b intersect
    function isCollidingGeneral(a, b) {
        // Hardcoded padding for 'a'
        const topPaddingA = 50; // reduce 50px from top
        const leftPaddingA = 0;
        const rightPaddingA = 0;
        const bottomPaddingA = 0;
        const ax = a.x + leftPaddingA;
        const ay = a.y + topPaddingA;
        const aw = a.width - leftPaddingA - rightPaddingA;
        const ah = a.height - topPaddingA - bottomPaddingA;
        // No padding for 'b'
        const bx = b.x;
        const by = b.y;
        const bw = b.width;
        const bh = b.height;
        return (
            ax < bx + bw &&
            ax + aw > bx &&
            ay < by + bh &&
            ay + ah > by
        );
    }
</script>
<script>
const speaker = document.getElementById("startSpeaker");
    const text = document.getElementById("tapText");
    const overlay = document.getElementById("startOverlay");
    const audioBGM = document.getElementById("audioPlayerBGM");
    const gameContainer = document.getElementById('borderImage');
let isPlaying = false; // music state
let gameStarted =0;
document.getElementById("startSpeaker").addEventListener("click", function () {
   if (gameStarted == 1)
   {
        if (!isPlaying) {
        audioBGM.play();
        speaker.src = "assets/images/speaker-unmuted.png";
        isPlaying = true;
        } else {
        audioBGM.pause();
        speaker.src = "assets/images/speaker-muted.png";
        isPlaying = false;
    }
   }
   else{
        // Hide only the text first
        text.style.display = "none";
        this.style.display = "none"; // Hide the local speaker icon

        // Get the target location (top-left inside gameContainer)
        const rect = gameContainer.getBoundingClientRect();
        const targetX = rect.left - 10;  // adjust padding if needed
        const targetY = rect.top - 10;
        // Animate speaker to top-left
        speaker.style.transition = "transform 0.8s cubic-bezier(.34,1.56,.64,1)"; // bounce
        speaker.style.transform = `translate(
            ${targetX - window.innerWidth / 2 + speaker.offsetWidth / 2}px,
            ${targetY - window.innerHeight / 2 + speaker.offsetHeight / 2}px
        ) scale(1)`;
        // After animation → hide overlay and start game
        setTimeout(() => {
            overlay.style.display = "none";
            window.parent.postMessage({ 
                action: 'ACTIVATE_GLOBAL_AUDIO', 
                audioSrc: 'assets/game_mountain_climber/audio/mountain_game_bg_music.mp3' 
            }, '*');
            // audioBGM.play();
            // speaker.src = "assets/images/speaker-unmuted.png";
            isPlaying = true;
            gameStarted = 1;
        }, 800); // same as transition time
        // Toggle audio
    }});

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
