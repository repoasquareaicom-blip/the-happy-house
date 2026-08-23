<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>The Happy House</title>
    <style>
        /* --- Framework Styles --- */
        html, body { margin: 0; padding: 0; width: 100%; height: 100%; overflow: hidden; background-color: #FFF0C7; font-family: Calibri, sans-serif; display: flex; align-items: center; justify-content: center; }
        .frame-container { position: relative; width: 100vw; max-width: 1366px; display: flex; justify-content: center; align-items: center; }
        #border-img { width: 100%; height: auto; display: block; z-index: 1; }
        .inner-content { position: absolute; z-index: 2; display: flex; flex-direction: column; align-items: center; justify-content: center; overflow: hidden; }
        
        /* The area where your existing pages will appear */
        .safe-zone { 
            width: 85%; height: 80%; 
            display: flex; flex-direction: column; 
            align-items: center; justify-content: center; 
            overflow-y: auto; /* Allows scrolling inside the border if content is long */
            opacity: 1;
            transition: opacity 0.3s ease-in-out;
        }

        /* --- Loader Styles --- */
        #page-loader {
            position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(255, 240, 199, 0.8); z-index: 100;
            display: none; align-items: center; justify-content: center;
        }
    </style>
</head>
<body>

    <audio id="bgAudio" loop preload="auto">
        <source src="assets/audio/game_bg_music.mp3" type="audio/mpeg">
    </audio>

    <div id="page-loader"><div class="spinner"></div></div>

    <div class="frame-container" id="main-frame">
        <img src="" id="border-img" alt="Theme Border">
        <div class="inner-content" id="game-content">
            <div class="safe-zone" id="app-shell">
            </div>
        </div>
    </div>

    <script>
        const appShell = document.getElementById('app-shell');
        const borderImg = document.getElementById('border-img');
        
        // 1. Function to load pages dynamically
        async function navigateTo(pageUrl) {
            appShell.style.opacity = '0'; // Fade out current content
            
            try {
                const response = await fetch(pageUrl);
                const html = await response.text();
                
                // Wait a moment for fade out
                setTimeout(() => {
                    appShell.innerHTML = html;
                    appShell.style.opacity = '1'; // Fade in new content
                    
                    // Re-run any scripts found in the injected HTML
                    const scripts = appShell.querySelectorAll("script");
                    scripts.forEach(oldScript => {
                        const newScript = document.createElement("script");
                        newScript.text = oldScript.text;
                        document.body.appendChild(newScript).parentNode.removeChild(newScript);
                    });
                }, 300);
            } catch (err) {
                console.error("Failed to load page:", err);
            }
        }

        // 2. Initialize Frame & First Page
        function init() {
            const theme = localStorage.getItem('selectedTheme') || 'train';
            borderImg.src = `assets/images/${theme}.svg`;
            
            // Load your first screen (e.g., theme selection)
            navigateTo('game_handler.php'); 
        }

        function syncDimensions() {
            const rect = borderImg.getBoundingClientRect();
            document.getElementById('game-content').style.width = rect.width + 'px';
            document.getElementById('game-content').style.height = rect.height + 'px';
        }

        window.addEventListener('load', () => { init(); borderImg.onload = syncDimensions; });
        window.addEventListener('resize', syncDimensions);
    </script>
</body>
</html>