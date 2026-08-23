<?php
session_start();

$selectedGameMode = $_SESSION['game_mode'] ?? null;

if (!isset($_SESSION['currentGameIndex'])) {
    $_SESSION['currentGameIndex'] = 0;
}
$currentGameIndex = $_SESSION['currentGameIndex'];

$gamePages = [
    'game_find_my_robot_hardness_selection.php',
    'game_mountain_climber_hardness_selection.php',
    'game_cave_hardness_selection.php',
    'game_scavenger_hardness_selection.php',
    'game_song_hardness_selection.php'
];

$redirectPage = 'game-selection.php';

switch ($selectedGameMode) {
    case 'sequence_conclude':
        if ($currentGameIndex < count($gamePages)) {
            $redirectPage = $gamePages[$currentGameIndex];
            $_SESSION['currentGameIndex']++;
        } else {
            $_SESSION['currentGameIndex'] = 0;
            $redirectPage = 'start_activities.php';
        }
        break;

    case 'sequence_loop':
        $redirectPage = $gamePages[$currentGameIndex % count($gamePages)];
        $_SESSION['currentGameIndex']++;
        break;

    case 'student_choice':
    case 'demo':
        $redirectPage = 'game-selection.php';
        break;
}
?>
<!DOCTYPE html>
<html>
<head>
<title>Game Handler</title>
<style>
body{
    background:#111;
    color:#fff;
    display:flex;
    justify-content:center;
    align-items:center;
    height:100vh;
    margin:0;
}
#loadingOverlay {
            position: fixed;
            top:0; left:0;
            width:100%; height:100%;
            background: rgba(0,0,0,0.85);
            display:flex;
            flex-direction:column;
            justify-content:center;
            align-items:center;
            z-index:999;
        }
        .spinner {
            border:4px solid rgba(255,255,255,0.3);
            border-top:4px solid #fff;
            border-radius:50%;
            width:45px;
            height:45px;
            animation: spin 1s linear infinite;
            margin-bottom:20px;
        }
        @keyframes spin {
            0% { transform:rotate(0deg); }
            100% { transform:rotate(360deg); }
        }
        p
        {
            font-family: Calibri, sans-serif;
            font-size: 20px;;
        }
</style>
</head>
<body>

<div style="text-align:center;" id="loadingOverlay">
    <div class="spinner"></div>
    <p >Games Loading...</p>
</div>

<script>
setTimeout(() => {
    window.location.href = "<?= $redirectPage ?>";
}, 1500);
</script>

</body>
</html>