<?php
session_start();
include 'config/data.php';
include 'class/log.php';
include 'class/class_teachers.php';


if(!isset($_SESSION['class_group_id'])){
		header("Location: teachers_login_request_handler.php");	
  
}
$_data = new Data();
$teachers = new Teachers($_data);

// Read POST values
$student_name = $_POST['student_name'] ?? '';
$overall = $_POST['overall'] ?? '';
$game_mode = $_POST['game_mode'] ?? '';
$class_id = $_SESSION['class_group_id'];
echo "student " . $student_name . '  overall ' . $overall . ' mode '. $game_mode;
if ($student_name == '' || $overall == '' || $game_mode == '') {
    echo "Missing required data";
    exit;
}

// Generate GUID
$rowid = $teachers->generateGUID();

// Insert into DB
$ok = $teachers->insertStudentWellbeingReport(
    $rowid,
    $student_name,
    $class_id,
    $overall
);

if (!$ok) {
    echo "Insert failed!";
    exit;
}

// Store for next page updates
$_SESSION['wellbeing_rowid'] = $rowid;
$_SESSION['game_mode'] = $game_mode;
$_SESSION['currentGameIndex'] = "0";

// Redirect
header("Location: game_settings.php");
exit;
?>