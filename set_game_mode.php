<?php
session_start();

$mode = $_GET['mode'] ?? 'demo';

if ($mode === 'paid') {
    $_SESSION['is_free'] = "0";
    $_SESSION['game_mode'] = "paid";
    header("Location: start_activities.php");
} else {
    $_SESSION['is_free'] = "1";
    $_SESSION['game_mode'] = "demo";
    header("Location: game_settings.php");
}


exit;
