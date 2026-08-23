<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

class Teachers{
	
	public $data;
	
	function __construct($data) {
		$this->data = $data;
	}
	
	public function validateClassRoomRef($class_room_ref){
        $_query = "SELECT 
		cg.*, 
		sm.subscription_end,
		-- Calculate status on the fly based on the current time
		CASE 
			WHEN sm.subscription_end < NOW() THEN 'expired' 
			ELSE 'active' 
		END AS subscription_status
	FROM class_groups cg
	INNER JOIN school_master sm ON cg.school_id = sm.id
	WHERE cg.guid = '$class_room_ref'
	LIMIT 1;";
		return $this->data->getData($_query);
	}
	public function getClassRoomRef($class_group_id) {
		$_query = "select * from class_groups where id='$class_group_id'";
		return $this->data->getData($_query);
    }
	public function getWellbegingQuestions($class_group_id) {
		$_query = "select * from activity_master";
		return $this->data->getData($_query);
    }
	public function generateGUID() {
		return sprintf(
			'%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
			mt_rand(0, 0xffff), mt_rand(0, 0xffff),
			mt_rand(0, 0xffff),
			mt_rand(0, 0x0fff) | 0x4000,
			mt_rand(0, 0x3fff) | 0x8000,
			mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
		);
	}

public function insertStudentWellbeingReport($rowid, $student_name, $class_id, $overall_wellbeing)
{
    // Set the timezone to Sydney/Melbourne
	date_default_timezone_set('Australia/Sydney');

	$report_date = date("Y-m-d"); // Will be AU Date
	$start_time  = date("H:i:s"); // Will be AU Time

	// Your existing Query...
	$_query = "
		INSERT INTO student_wellbeing_report 
		(rowid, student_name, class_id, report_date, start_time, overall_wellbeing)
		VALUES 
		('$rowid', '$student_name', '$class_id', '$report_date', '$start_time', '$overall_wellbeing')
	";

    return $this->data->execute($_query);
}
public function updateStudentWellbeingReportByRowId($row_id, $column, $value)
{
    $sql = "UPDATE student_wellbeing_report
            SET $column = '$value' where rowid ='$row_id'";
            

    
    return $this->data->execute($sql);
}




	

}

?>