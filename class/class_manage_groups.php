<?php

class Groups{
	
	public $data;
	
	function __construct($data) {
		$this->data = $data;
	}
	public function add_year_group($school_id, $year_name){
		// Check if the year group already exists
		//$_check_query = "SELECT COUNT(*) as count FROM year_groups WHERE year_group_caption = '$year_name' AND school_id = $school_id AND is_deleted = 0";
		//$_result = $this->data->fetch($_check_query);
		
		$_query =  "SELECT COUNT(*) as count FROM year_groups WHERE year_group_caption = '$year_name' AND school_id = $school_id AND is_deleted = 0";
		$_result = $this->data->getData($_query);

		if ($_result[0]['count'] > 0) {
			return "Year Group already exists.";
		}
		
		// Insert new year group
		$_query = "INSERT INTO year_groups (year_group_caption, school_id, created_on, is_deleted) VALUES ('$year_name', $school_id, NOW(), 0)";
		$_status = $this->data->execute($_query);
		
		if ($_status) {
			return "OK";
		} else {
			return "0";
		}
	}
	
    public function edit_year_group($year_group_id, $year_group_name){
		$_query = "UPDATE year_groups SET year_group_caption = '$year_group_name', updated_on = NOW() WHERE id = $year_group_id";
        file_put_contents('logs.txt',"edit year  $_query\n", FILE_APPEND);	        
		$_status = $this->data->execute($_query);
		if($_status){
			return "Year Group Added Sucessfully";
		}
		else{
			return "Year Group Not Added;";
		}
	}
    public function delete_year_group($year_group_id){
		
		$_query = "UPDATE year_groups SET is_deleted=1, updated_on = NOW() WHERE id = $year_group_id";
		//file_put_contents('logs.txt', date("F j, Y, g:i a"). " Method: Year Group delete_year_group  - DB Trans:, Page: Subscription.php, Message: $_query\n", FILE_APPEND);	
		$_status = $this->data->execute($_query);
		if($_status){
			return "OK";
		}
		else{
			return $_status;
		}
	}
    public function fetch_all_year_groups($school_id)
    {
		$_query = "SELECT yg.id as id,yl.id as year_level_id, yl.year_level_caption as year_group_caption from year_groups   yg join master_year_level yl
                    on yg.year_group_caption = yl.id
                    where school_id= '$school_id' and is_deleted=0 ORDER BY year_group_caption ASC;";
		$result = $this->data->getData($_query);
        return $result;
    }
    public function fetch_year_group_by_id($id)
    {
        if (empty($id)) { 
            return []; // Return an empty array if $id is NULL or empty
        }
    
        $_query = "SELECT yg.id as id,yl.id as year_level_id, yl.year_level_caption as year_group_caption from year_groups   yg join master_year_level yl
                    on yg.year_group_caption = yl.id
                    where yg.id=$id and is_deleted=0 ORDER BY year_group_caption ASC";


        
        $result = $this->data->getData($_query);
        return $result;
    }
    
    public function fetch_year_level_master_data()
    {
		$_query = "SELECT id, year_level_caption FROM master_year_level order by id";
		$result = $this->data->getData($_query);
        return $result;
    }

	 // Class Group Methods
	 public function add_class_group($school_id, $class_name, $year_id, $password) {
        $_query = "SELECT COUNT(*) as count FROM class_groups WHERE class_group_caption = '$class_name' AND year_id = $year_id AND is_deleted = 0";
        $_result = $this->data->getData($_query);

        if ($_result[0]['count'] > 0) {
            return "Class Group already exists.";
        }

        $_query = "INSERT INTO class_groups (class_group_caption, year_id, school_id, password, created_on, is_deleted) 
                   VALUES ('$class_name', $year_id, $school_id, '$password', NOW(), 0)";
        
		$_status = $this->data->execute($_query);
		
        return $_status ? "OK" : "0";
    }

    public function edit_class_group($class_group_id, $class_group_caption, $password) {
        $_query = "UPDATE class_groups 
                   SET class_group_caption = '$class_group_caption', password = '$password', updated_on = NOW() 
                   WHERE id = $class_group_id";
	
	$_status = $this->data->execute($_query);
        return $_status ? "Class Group Updated Successfully" : "Class Group Not Updated";
    }

    public function delete_class_group($class_group_id) {
        $_query = "UPDATE class_groups SET is_deleted = 1, updated_on = NOW() WHERE id = $class_group_id";
        $_status = $this->data->execute($_query);
        return $_status ? "OK" : $_status;
    }

    public function fetch_all_class_groups($school_id) {
        $_query = "SELECT cg.*, yg.year_group_caption 
                   FROM class_groups cg 
                   JOIN year_groups yg ON cg.year_id = yg.id 
                   WHERE cg.school_id = '$school_id' AND cg.is_deleted = 0 
                   ORDER BY cg.class_group_caption ASC";
        $result = $this->data->getData($_query);
        return $result;
    }

    public function fetch_class_group_by_id($id) {
        $_query = "SELECT * FROM class_groups WHERE id = $id";
        $result = $this->data->getData($_query);
        return $result;
    }
	public function fetch_all_class_groups_by_year($school_id, $year_id) {
        
        
        $_query = "
            SELECT cg.id, cg.class_group_caption, cg.year_id, yg.year_group_caption, cg.password, cg.guid
            FROM class_groups cg
            INNER JOIN year_groups yg ON cg.year_id = yg.id
            WHERE yg.school_id = $school_id
            AND cg.year_id = $year_id
            AND cg.is_deleted = 0
            ORDER BY cg.class_group_caption DESC
        ";
		//file_put_contents('logs.txt', date("F j, Y, g:i a"). " Method: Class Group fetch_all_class_groups_by_year  - DB Trans:, Page: Subscription.php, Message: $_query\n", FILE_APPEND);	
        // Fetching the data
        $result = $this->data->getData($_query);
        
        return $result;
    }
}