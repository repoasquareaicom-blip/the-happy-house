<?php
class Database{
 
    // Database credentials
    private $host = "thehappyhouse.au";
    private $db_name = "thehappyhouse";
    private $username = "thehappyhousedev";
    private $password = 'T#2#@ppy#01$';
    public $conn;
	public function getConnection(){
 
        $this->conn = null;
 
        try{
            $this->conn = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->db_name, $this->username, $this->password);
            $this->conn->exec("set names utf8");
        }catch(PDOException $exception){
            echo "Connection error: " . $exception->getMessage();
        }
 
        return $this->conn;
    }
	
	public function get_row_count($query){
		try{
						 
			// prepare query statement
			$stmt = $this->conn->prepare($query);
		 
			// execute query
			$stmt->execute();
			
			$num = $stmt->rowCount();
			
			return $num;
			 
					
		}
		catch(Exception $e){
			return 0;
		}
		
	 
	}
	
	
	public function get_data($query){
		try{
			
			
			$stmt = $this->conn->prepare($query);
		 
			// execute query
			$stmt->execute();
			
			$_data=array();
			
			while ($row = $stmt->fetch(PDO::FETCH_ASSOC)){
				// extract row
				// this will make $row['name'] to
				// just $name only
				//extract($row);
		 
				
				array_push($_data, $row);
			}
		 
			return $_data;
		}
		catch(Exception $e){
			return  $e->getMessage();
		}
		
	 
	}
	
	public function execute_data($query){
		try{
			
			 
			// prepare query statement
			$stmt = $this->conn->prepare($query);
		 
			// execute query
			$stmt->execute();
			
			
			$stmt = $this->conn->prepare("SELECT LAST_INSERT_ID()");
			$stmt->execute();
			$last_id = $stmt->fetchColumn();
			
			//$last_id = $this->conn->lastInsertId;
			
			return $last_id ;
			
		}
		catch(Exception $e){
			return 0;
		}
		
	 
	}
	
	public function GUID()
	{
		if (function_exists('com_create_guid') === true)
		{
			return trim(com_create_guid(), '{}');
		}
		
		return sprintf('%04X%04X-%04X-%04X-%04X-%04X%04X%04X', mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(16384, 20479), mt_rand(32768, 49151), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535));
	}
	
}

?>