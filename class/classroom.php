<?php

class ClassRoom{
	
	public $data;
	
	function __construct($data) {
		$this->data = $data;
	}
	
	public function create_class($className,$year,$loginId,$password,$school_id){
		$_query = "INSERT INTO 
			class_rooom_master (
			 class_name, 
			 year, 
			 login_id, 
			 password,
			 created_on,
			 school_id
			 ) 
		VALUES (
			 '$className', 
			 '$year', 
			 '$loginId', 
			 '$password',
			 $school_id,
			 now()
		)";
	    	 //file_put_contents('logs.txt', date("F j, Y, g:i a"). " Method: Subscription-Create - DB Trans:, Page: Subscription.php, Message: $_query\n", FILE_APPEND);	
		$_status = $this->data->execute($_query);
		if($_status){
			//$_school_data->id  = $_status;
			return "Class room data inserted QUERY<$_query>";
		}
		else{
			return "Class room not inserted: QUERY<$_query>";
			
		}
	}
	
	
	
	public function get_all_classRoom()
	{
		$_query = "select * from class_rooom_master";
		return $this->data->getData($_query);
	}
	
	
	
	
	
	
}

?>