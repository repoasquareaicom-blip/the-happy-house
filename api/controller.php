<?php
session_start();
header("Access-Control-Allow-Origin: *");

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

ini_set('max_execution_time', 0);


include_once '../config/data.php';


$_data = new Data();

if ($_POST["request_type"] == 'save_content_landing_Page'){
	$content = $_POST["content"];
	$query = "update content_master set content = '$content' where page='landing_page'";
	$_data->execute($query);
	echo '{"message": "Resords Updated", "status": "Passed"}';
	
}
else if ($_POST["request_type"] == 'delete_question'){
	
	$_question = new Question($_data);
	$que_id = $_GET["que_id"];
	
	echo $_question->delete_question($que_id);
}

else if ($_GET["request_type"] == 'upcoming_birthday'){
	
	$_member = new Member($_data);
	
	echo json_encode($_member->get_upcoming_birthday());
}

else{
	echo '{"message": "Unknown Request", "status": "failed"}';
}



function IsNullOrEmptyString($str){
    return (!isset($str) || trim($str) === '');
}

function GUID()
	{
		if (function_exists('com_create_guid') === true)
		{
			return trim(com_create_guid(), '{}');
		}
		
		return sprintf('%04X%04X-%04X-%04X-%04X-%04X%04X%04X', mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(16384, 20479), mt_rand(32768, 49151), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535));
	}


?>

