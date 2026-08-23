<?php
session_start();
if (!isset($_SESSION['class_group_id']) && ($_SESSION['game_mode'] ?? '') != "demo") {
    header("Location: teachers_login_request_handler.php"); 
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Build My Robot</title>
    <style>
        :root {
            --color1: #000;
            --color2: #FFF;
            --color3: #FFF;
        }

        body, html {
            margin: 0; padding: 0; height: 100%; width: 100%;
            overflow: hidden;
            background: #333; /* Dark background to focus on game area */
            display: flex; align-items: center; justify-content: center;
        }

        /* THE SCALER ENGINE WRAPPER */
        #scaler-container {
            width: 1000px;
            height: 600px;
            position: relative;
            transform-origin: center center;
            background-color: white;
            overflow: hidden;
            flex-shrink: 0;
        }

        .game-area {
            width: 100%;
            height: 100%;
            position: relative;
            display: flex;
            flex-direction: column;
        }

        .image-container {
            position: absolute;
            width: 1000px;
            height: 600px;
            z-index: 1;
        }

        .game-room-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .overlay {
            position: absolute;
            top: 0; left: 0; right: 0; bottom: 0;
            background-color: rgba(0, 0, 0, 0.4);
            pointer-events: none;
        }

        /* Left and right sides using Virtual Coordinates */
        .left-side {
            position: absolute;
            left: 50px;
            top: 20px;
            width: 450px;
            height: 560px;
            z-index: 999;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .right-side {
            position: absolute;
            left: 500px;
            top: 20px;
            width: 450px;
            height: 560px;
            z-index: 999;
        }

        .select-robots {
            height: 80px;
            background-color: var(--color1);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            width: 90%;
            border-radius: 30px;
            border: 2px solid #fff;
            margin-bottom: 15px;
            opacity: 0.9;
            margin-top: 50px;
        }

        .robot-thumbnails {
            display: flex;
            justify-content: space-evenly;
            align-items: center;
            width: 100%;
        }

        .robot-thumb {
            height: 50px;
            cursor: pointer;
            transition: transform 0.3s ease;
            border-radius: 50%;
            padding: 5px;
            animation: wave 2.2s ease-in-out infinite;
        }

        .robot-thumb.selected {
            border: 3px solid white;
            transform: scale(0.8);
            box-shadow: 0 0 10px white;
        }

        .parts-area {
            min-height: 250px;
            background-color: var(--color2);
            width: 90%;
            border-radius: 30px;
            border: 2px solid #fff;
            margin-bottom: 10px;
            opacity: 0.9;
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: center;
            font-size: 28px;
            font-weight: bold;
            color: #FF6F61;
            font-family: 'Comic Sans MS', cursive, sans-serif;
            text-align: center;
            padding: 10px;
        }

        .build-my-robot-title {
            color: #fff;
            font-size: 24px;
            text-align: center;
            width: 80%;
            font-family: Arial, sans-serif;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.5);
        }

        .robot-built-area {
            width: 100%;
            height: 450px;
            position: relative;
            margin-top: 20px;
        }

        .robot-part-thumb {
            width: 80px;
            height: 80px;
            object-fit: contain;
            margin: 5px;
            cursor: pointer;
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
            animation: bounceIn 0.6s ease forwards;
        }

        .robot-part-built-showhide {
            opacity: 0;
            transform: scale(0.8);
            transition: opacity 0.3s ease, transform 0.3s ease;
            position: absolute;
            left: 50%;
            top: 50%;
            transform: translate(-50%, -50%) scale(0.8);
            height: 100%;
        }

        .robot-part-built-showhide.visible {
            opacity: 1;
            transform: translate(-50%, -50%) scale(1);
        }

        /* Continue Button */
        .continue-btn-container {
            position: absolute;
            bottom: 75px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 1000;
        }

        #continue-btn {
            background: linear-gradient(135deg, var(--color2), var(--color2));
            color: white;
            color: white;
            font-size: 32px;
            padding: 5px 30px;
            border: 2px solid var(--color2);
            border-radius: 40px;
            cursor: pointer;
            display: none;
        }

        #continue-btn:hover { transform: scale(1.05); }

        /* Existing Animations */
        @keyframes wave { 0%, 100% { transform: translateY(0); } 50% { transform: translateY(-10px); } }
        @keyframes bounceIn { 0% { transform: scale(0.5); opacity: 0; } 60% { transform: scale(1.2); opacity: 1; } 100% { transform: scale(1); } }
        @keyframes flyInZoom { 0% { opacity: 0; transform: translate(-50%, -50%) translateX(-200px) scale(0.3); } 100% { opacity: 1; transform: translate(-50%, -50%) scale(1); } }

        /* Animation classes */
        .fly-in-zoom { animation: flyInZoom 0.8s ease-out forwards; }

        /* Loader */
        .loader-wrapper { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: #fff; display: flex; align-items: center; justify-content: center; z-index: 9999; }
        .loader { width: 64px; height: 64px; border: 8px solid #eee; border-top: 8px solid #4B9CD3; border-radius: 50%; animation: spin 1s linear infinite; }
        @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }

        /* Video Bubble */
        #explainerVideo, #bubblespeech { display: none; position: absolute; z-index: 9999; }
        .bubblespeech { height: 100px; cursor: pointer; }
        #explainerVideo { height: 80px; border-radius: 50px; background: url('assets/images/bubble_small.svg') no-repeat center; background-size: contain; }
    </style>
</head>
<body>

<div class="loader-wrapper" id="loader"><div class="loader"></div></div>

<div id="scaler-container">
    <div class="game-area" id="game-area">
        <div class="image-container">
            <img id="game-room-image" src="assets/images/build-room.svg" class="game-room-image" />
            <div class="overlay"></div>
        </div>

        <div class="left-side">
            <div class="select-robots">
                <div class="robot-thumbnails">
                    <img src="assets/images/robot1.svg" class="robot-thumb" data-id="1" />
                    <img src="assets/images/robot2.svg" class="robot-thumb" data-id="2" />
                    <img src="assets/images/robot3.svg" class="robot-thumb" data-id="3" />
                    <img src="assets/images/robot4.svg" class="robot-thumb" data-id="4" />
                    <img src="assets/images/robot5.svg" class="robot-thumb" data-id="5" />
                </div>
            </div>
            <div class="parts-area">Pick Your Favorite Robot</div>
            <div class="build-my-robot-title">Build My Robot</div>    
        </div>

        <div class="right-side">
            <div class="robot-built-area" id="robot-built-area"></div>
            <div class="continue-btn-container">
                <button id="continue-btn" onclick="enterButton_click()">Continue</button>
            </div>
        </div>

        <img src="assets/images/bubble_small.svg" class="bubblespeech" id="bubblespeech">
        <video id="explainerVideo"><source src="assets/videos/explainer.mp4" type="video/mp4"></video>
    </div>
</div>

<script>
    // --- SCALER ENGINE LOGIC ---
    function updateScale() {
        const container = document.getElementById('scaler-container');
        const winW = window.innerWidth;
        const winH = window.innerHeight;
        const scale = Math.min(winW / 1000, winH / 600);
        container.style.transform = `scale(${scale})`;
    }
    window.addEventListener('resize', updateScale);
    updateScale();

    // --- EXISTING THEME LOGIC ---
    const theme = localStorage.getItem('selectedTheme') || 'train';
    const themeColors = {
        dino: { color1: '#99B194', color2: '#D9E1A4', color3: '#6b8167' },
        fairy: { color1: '#7366C6', color2: '#C885D0', color3: '#4f4691' },
        pet: { color1: '#E78780', color2: '#ECCC96', color3: '#b3534c' },
        train: { color1: '#8AA9DA', color2: '#B0D0E4', color3: '#5c7aad' }
    };
    const { color1, color2, color3 } = themeColors[theme];
    document.documentElement.style.setProperty('--color1', color1);
    document.documentElement.style.setProperty('--color2', color2);
    document.documentElement.style.setProperty('--color3', color3);

    // --- LOADER ---
    window.addEventListener("load", () => {
        const loader = document.getElementById("loader");
        loader.style.opacity = "0";
        setTimeout(() => loader.remove(), 1000);
    });

    // --- ROBOT BUILDING LOGIC ---
    const robotThumbs = document.querySelectorAll('.robot-thumb');
    robotThumbs.forEach(thumb => {
        thumb.addEventListener('click', () => {
            robotThumbs.forEach(t => t.classList.remove('selected'));
            thumb.classList.add('selected');
            localStorage.setItem('student_built_robot', thumb.dataset.id);
            document.getElementById('continue-btn').style.display = 'none';
            loadRobotParts(thumb.dataset.id);
        });
    });

    function loadRobotParts(robotId) {
        fetch('assets/data/robot-parts.json')
        .then(res => res.json())
        .then(data => {
            const parts = data[robotId];
            const partsArea = document.querySelector('.parts-area');
            const robotBuiltArea = document.querySelector('#robot-built-area');
            partsArea.innerHTML = ''; 
            robotBuiltArea.innerHTML = '';

            const imgDefault = new Image();
            imgDefault.src = `assets/images/robots/${robotId}/build-items/${robotId}-default.svg`;
            imgDefault.classList.add('robot-part-built-showhide', 'visible', 'fly-in-zoom');
            imgDefault.style.zIndex = "2";
            imgDefault.dataset.part = "default";
            robotBuiltArea.appendChild(imgDefault);

            parts.forEach(part => {
                const img = new Image();
                img.src = `assets/images/robots/${robotId}/${robotId}-${part.name}-menu.svg`;
                img.classList.add('robot-part-thumb');
                img.onclick = () => showPart(part);
                partsArea.appendChild(img);

                const imgBuilt = new Image();
                imgBuilt.src = `assets/images/robots/${robotId}/build-items/${robotId}-${part.name}.svg`;
                imgBuilt.classList.add('robot-part-built-showhide');
                imgBuilt.style.zIndex = part.zIndex;
                imgBuilt.dataset.part = part.name;
                robotBuiltArea.appendChild(imgBuilt);
            });
        });
    }

    let selectedAccessories = '';
    function showPart(partName) {
        const part = partName.name;
        const target = document.querySelector(`.robot-part-built-showhide[data-part="${part}"]`);
        const wheelchair = document.querySelector('.robot-part-built-showhide[data-part="wheel-chair"]');
        const defaultPart = document.querySelector('.robot-part-built-showhide[data-part="default"]');

        const toggleVisibility = (element, show) => {
            if (!element) return;
            if (show) {
                selectedAccessories += part + ',';
                element.style.display = 'block';
                setTimeout(() => element.classList.add('visible'), 10);
            } else {
                element.classList.remove('visible');
                selectedAccessories = selectedAccessories.replace(part + ',', '');
                setTimeout(() => element.style.display = 'none', 300);
            }
        };

        if (part === "wheel-chair") {
            const isVisible = wheelchair?.classList.contains('visible');
            toggleVisibility(wheelchair, !isVisible);
            toggleVisibility(defaultPart, isVisible);
        } else if (target && part !== "default") {
            toggleVisibility(target, !target.classList.contains('visible'));
        }

        const continueBtn = document.getElementById('continue-btn');
        setTimeout(() => {
            const visibleCount = document.querySelectorAll('.robot-part-built-showhide.visible:not([data-part="default"])').length;
            continueBtn.style.display = visibleCount > 0 ? 'block' : 'none';
        }, 350);
    }

    // --- POST MESSAGES & NAVIGATION ---
    function enterButton_click() {
        window.parent.postMessage({ action: 'END_EXPLAINER' }, '*');
        localStorage.setItem('selected_accessories', selectedAccessories);
        window.location.href = "game_find_my_robot.php";
    }

    function triggerExplainer() {
        window.parent.postMessage({ 
            action: 'START_EXPLAINER', 
            videoSrc: 'assets/videos/explainer.mp4' 
        }, '*');
    }

    // Add global explainer triggers as per your original code
    document.body.addEventListener('click', triggerExplainer);
    document.body.addEventListener('touchend', triggerExplainer);
</script>
</body>
</html>