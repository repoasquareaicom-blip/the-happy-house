<?php
session_start();
$game_mode = $_SESSION['game_mode'] ?? 'demo';

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Game Selection</title>
    <style>
        :root {
            --theme-color-primary: #5D7356;
            --theme-color-secondary: #D9E1A4;
        }

        body, html {
            margin: 0; padding: 0;
            width: 100%; height: 100%;
            overflow: hidden;
            background-color: var(--theme-color-secondary);
            font-family: 'Gill Sans', Calibri, sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* 1. The Virtual Scaler - keeps aspect ratio on all screens */
        #scaler-container {
            width: 1100px; /* Widened slightly to fit 3 boxes comfortably */
            height: 700px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            transform-origin: center center;
            flex-shrink: 0;
        }

        .game-settings {
            font-size: 54px;
            font-weight: 700;
            color: var(--theme-color-primary); 
            margin-bottom: 30px;
            text-shadow: 2px 2px 0px rgba(255,255,255,0.5);
        }

        /* 2. THE GRID CONTAINER */
        .games-grid {
            display: flex;
            flex-wrap: wrap;
            justify-content: center; /* Centers items horizontally */
            align-content: center;   /* Centers rows vertically */
            gap: 25px;
            width: 100%;
            max-width: 1000px; /* Limits width to force 3 items per row */
        }

        .game-box {
            width: 300px; /* Balanced size for 3 per row */
            height: 190px;
            background-size: cover;
            background-position: center;
            border-radius: 20px;
            box-shadow: 0 10px 20px rgba(0,0,0,0.2);
            position: relative;
            cursor: pointer;
            transition: all 0.2s ease;
            border: 6px solid white;
            background-color: #ddd;
            flex-shrink: 0;
            overflow: hidden;
        }

        .game-box:hover {
            transform: scale(1.05);
            border-color: var(--theme-color-primary);
            box-shadow: 0 15px 30px rgba(0,0,0,0.3);
            z-index: 10;
        }

        .game-label {
            position: absolute; 
            bottom: 0; 
            width: 100%;
            background: rgba(255, 255, 255, 0.95);
            padding: 12px 0; 
            text-align: center;
            font-weight: bold; 
            font-size: 18px;
            color: #222;
        }
    </style>
</head>
<body>

    <div id="scaler-container">
        <div class="game-settings">Select Your Game</div>
        
        <div class="games-grid">
            <?php if ($game_mode == "demo"): ?>
                <div class="game-box" style="background-image: url('assets/images/robot-game-bg.svg');" onclick="selectGame(1)">
                    <div class="game-label">Find My Robot</div>
                </div>
                <div class="game-box" style="background-image: url('assets/images/mountain_climber-game-bg.jpg');" onclick="selectGame(2)">
                    <div class="game-label">Mountain Climber</div>
                </div>
            <?php else: ?>
                <div class="game-box" style="background-image: url('assets/images/robot-game-bg.svg');" onclick="selectGame(1)">
                    <div class="game-label">Find My Robot</div>
                </div>
                <div class="game-box" style="background-image: url('assets/images/mountain_climber-game-bg.jpg');" onclick="selectGame(2)">
                    <div class="game-label">Mountain Climber</div>
                </div>
                <div class="game-box" style="background-image: url('assets/images/cave-game-bg.png');" onclick="selectGame(3)">
                    <div class="game-label">Cave Game</div>
                </div>
                <div class="game-box" style="background-image: url('assets/images/scavenger-game-bg.png');" onclick="selectGame(4)">
                    <div class="game-label">Scavenger Game</div>
                </div>
                <div class="game-box" style="background-image: url('assets/images/song-game-bg.png');" onclick="selectGame(5)">
                    <div class="game-label">Song Game</div>
                </div>
                <?php endif; ?>
        </div>
    </div>

    <script>
        function updateScale() {
            const container = document.getElementById('scaler-container');
            const winW = window.innerWidth;
            const winH = window.innerHeight;
            
            // Scaled based on a 1100x700 virtual canvas
            const scale = Math.min(winW / 1100, winH / 700) * 0.95;
            container.style.transform = `scale(${scale})`;
        }

        window.addEventListener('resize', updateScale);
        updateScale();

        const themeColors = {
            dino:  { primary: '#5D7356', secondary: '#D9E1A4' },
            fairy: { primary: '#7366C6', secondary: '#F0D1F5' },
            pet:   { primary: '#B35D57', secondary: '#ECCC96' },
            train: { primary: '#4A6FA5', secondary: '#B0D0E4' }
        };

        function applyTheme() {
            const selectedTheme = localStorage.getItem('selectedTheme') || 'train';
            const colors = themeColors[selectedTheme];
            if (colors) {
                document.documentElement.style.setProperty('--theme-color-primary', colors.primary);
                document.documentElement.style.setProperty('--theme-color-secondary', colors.secondary);
            }
        }

        function selectGame(gameID) {
            localStorage.setItem('currentGameIndex', gameID);
            let targetURL = "";
            switch (gameID) {
                case 1: targetURL = 'game_find_my_robot_hardness_selection.php'; break;
                case 2: targetURL = 'game_mountain_climber_hardness_selection.php'; break;
                case 3: targetURL = 'game_cave_hardness_selection.php'; break;
                case 4: targetURL = 'game_scavenger_hardness_selection.php'; break;
                case 5: targetURL = 'game_song_hardness_selection.php'; break;
                default: return;
            }
            window.location.href = targetURL;
        }

        applyTheme();
    </script>
</body>
</html>