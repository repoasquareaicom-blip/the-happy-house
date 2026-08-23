<?php
session_start();

// Security: Redirect if not demo and no class group
if (!isset($_SESSION['class_group_id']) && ($_SESSION['game_mode'] ?? '') != "demo") {
    header("Location: teachers_login_request_handler.php"); 
    exit;
}

$_SESSION['currentGameIndex'] = 3; 
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Scavenger - Difficulty</title>
    <style>
        :root {
            --theme-color-primary: #333;
            --theme-color-secondary: #f0f0f0;
        }
body, html {
        margin: 0; padding: 0; height: 100%; width: 100%;
        overflow: hidden; 
        background: var(--theme-color-secondary);
        font-family: 'Gill Sans', Calibri, sans-serif;
        display: flex; align-items: center; justify-content: center;
    }

    /* THE SCALER CONTAINER */
    #scaler-container {
        width: 1000px;
        height: 800px;
        display: flex;
        align-items: center;
        justify-content: center;
        transform-origin: center center;
        flex-shrink: 0;
    }

    .selection-wrapper {
        background: linear-gradient(135deg, var(--theme-color-secondary) 0%, #ffffff 100%);
        padding: 40px;
        border-radius: 40px;
        box-shadow: 0 15px 40px rgba(0, 0, 0, 0.15);
        text-align: center;
        width: 450px; /* Default for Mobile/iPad */
        border: 8px solid var(--theme-color-primary);
        transition: all 0.3s ease;
    }

    /* --- DESKTOP SPECIFIC COMPACTNESS --- */
    @media (min-width: 1025px) {
        .selection-wrapper {
            width: 320px; /* Much thinner on Desktop */
            padding: 25px;
            border-width: 6px;
        }
        .game-title {
            font-size: 38px !important; /* Smaller title */
        }
        .sub-title {
            font-size: 18px !important;
            margin-bottom: 20px !important;
        }
        .complexity-button {
            padding: 12px !important; /* Short, sleek buttons */
            font-size: 20px !important;
            border-radius: 15px !important;
        }
    }

    .game-title {
        font-size: 54px;
        font-weight: 800;
        color: var(--theme-color-primary);
        margin-bottom: 5px;
        text-shadow: 2px 2px 0px white;
    }

    .sub-title {
        font-size: 26px;
        color: #444;
        margin-bottom: 30px;
        font-weight: 500;
    }

    .button-group {
        display: flex;
        flex-direction: column;
        gap: 15px;
    }

    .complexity-button {
        padding: 22px;
        border: none;
        border-radius: 20px;
        font-size: 28px;
        font-weight: 700;
        cursor: pointer;
        color: white;
        transition: transform 0.2s ease, box-shadow 0.3s ease;
        text-transform: uppercase;
        position: relative;
    }

    /* 3D Button Colors */
    .btn-relaxed { background-color: #6CB34B; box-shadow: 0 6px 0px #4d8235; }
    .btn-normal  { background-color: #FF8C00; box-shadow: 0 6px 0px #c76d00; }
    .btn-tricky  { background-color: #4682B4; box-shadow: 0 6px 0px #315b7d; }

    /* desktop hover adjustment for 3D effect */
    @media (min-width: 1025px) {
        .btn-relaxed, .btn-normal, .btn-tricky { box-shadow: 0 4px 0px rgba(0,0,0,0.2); }
    }

    .complexity-button:active {
        transform: translateY(3px);
        box-shadow: 0 2px 0px rgba(0,0,0,0.1);
    }
    </style>
</head>
<body>

    <div id="scaler-container">
        <div class="selection-wrapper">
            <div class="game-title">Scavenger Hunt</div>
            <div class="sub-title">Select Complexity</div>

            <div class="button-group">
                <button class="complexity-button btn-relaxed" data-level="relaxed">Relaxed</button>
                <button class="complexity-button btn-normal" data-level="normal">Normal</button>
                <button class="complexity-button btn-tricky" data-level="tricky">Tricky</button>
            </div>
        </div>
    </div>

    <script>
        // 1. SCALER ENGINE: Forces the UI to fit any iframe size
        function updateScale() {
            const container = document.getElementById('scaler-container');
            const winW = window.innerWidth;
            const winH = window.innerHeight;
            
            // Calculate scale based on the 1000x600 workspace
            const scale = Math.min(winW / 1000, winH / 600) * 0.95;
            container.style.transform = `scale(${scale})`;
        }

        window.addEventListener('resize', updateScale);
        updateScale(); // Initialize

        // 2. THEME ENGINE
        const theme = localStorage.getItem('selectedTheme') || 'train';
        const themeColors = {
            dino:  { primary: '#5D7356', secondary: '#D9E1A4' },
            fairy: { primary: '#7366C6', secondary: '#F0D1F5' },
            pet:   { primary: '#B35D57', secondary: '#ECCC96' },
            train: { primary: '#4A6FA5', secondary: '#B0D0E4' }
        };

        function applyTheme() {
            const colors = themeColors[theme] || themeColors.train;
            document.documentElement.style.setProperty('--theme-color-primary', colors.primary);
            document.documentElement.style.setProperty('--theme-color-secondary', colors.secondary);
        }

        // 3. SELECTION LOGIC
        document.querySelectorAll('.complexity-button').forEach(button => {
            button.addEventListener('click', () => {
                const level = button.dataset.level;
                localStorage.setItem('gameTrickLevel', level);
                window.location.href = "game_scavenger.php";
            });
        });

        applyTheme();
    </script>
</body>
</html>