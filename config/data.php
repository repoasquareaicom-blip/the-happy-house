<?php

include_once 'database.php';

class Data{
	
	private $conn;

	public function __construct(){
		$database = new Database();

		$this->conn = $database -> getConnection();
	}
	
	
	public function getData($sql){
		
	 
		$_data=array();
		
		
		// prepare for execution of the stored procedure
		$stmt = $this->conn->prepare($sql);
		 
			
		 
		// execute the stored procedure
		$stmt->execute();
		
		while ($row = $stmt->fetch(PDO::FETCH_ASSOC)){
		
			extract($row);
			array_push($_data, $row);
			
		}		 		
		
		return $_data;
			
		
	}
	
	
	public function getCount($sql){
		
		$num = 0;
		
					 
		// prepare query statement
		$stmt = $this->conn->prepare($sql);
		
		// execute query
		$stmt->execute();
		
		$num = $stmt->rowCount();
			 
		
		
		return $num;
	}
	
	
	public function execute($sql) {
		// 1. Prepare
		$stmt = $this->conn->prepare($sql);
		
		// 2. Execute (This returns true on success, false on failure)
		$result = $stmt->execute();
		
		// 3. Logic: If it's an INSERT, you might want the ID. 
		// But for a general 'execute' used by Updates, we need the success status.
		if ($result) {
			$id = $this->conn->lastInsertId();
			// If there's a new ID, return it. If not (like an UPDATE), return true.
			return ($id > 0) ? $id : true;
		}
		
		return false;
	}
	public function getAppSetting($key)
	{
		$sql = "SELECT setting_value FROM app_settings WHERE setting_key = :key LIMIT 1";
		$stmt = $this->conn->prepare($sql);
		$stmt->execute([':key' => $key]);
		return $stmt->fetchColumn();
	}
	public function saveAppSetting($key, $value)
	{
		$sql = "
			INSERT INTO app_settings (setting_key, setting_value)
			VALUES (:key, :value)
			ON DUPLICATE KEY UPDATE
				setting_value = :value
		";

		$stmt = $this->conn->prepare($sql);
		return $stmt->execute([
			':key'   => $key,
			':value' => $value
		]);
	}
	public function addAuditLog($userId, $action, $entity, $oldValue, $newValue)
	{
		$sql = "
			INSERT INTO audit_logs
			(user_id, action, entity, old_value, new_value, ip_address, user_agent)
			VALUES
			(:user_id, :action, :entity, :old_value, :new_value, :ip, :agent)
		";

		$stmt = $this->conn->prepare($sql);

		return $stmt->execute([
			':user_id'   => $userId,
			':action'    => $action,
			':entity'    => $entity,
			':old_value' => $oldValue,
			':new_value' => $newValue,
			':ip'        => $_SERVER['REMOTE_ADDR'] ?? null,
			':agent'     => $_SERVER['HTTP_USER_AGENT'] ?? null
		]);
	}

	// Inside class Data
	public function getLastError() {
		if ($this->conn) {
			$errorInfo = $this->conn->errorInfo();
			// Index 2 contains the specific driver error message
			return isset($errorInfo[2]) ? $errorInfo[2] : 'Unknown PDO Error';
		}
		return 'No Connection';
	}

	public function executePrepared($sql, $params = []) {
		try {
			$stmt = $this->conn->prepare($sql);
			$result = $stmt->execute($params);
			
			// rowCount() is useful for UPDATEs to see if a row actually changed
			return $result; 
		} catch (PDOException $e) {
			// You can log $e->getMessage() here if needed
			return false;
		}
	}
	
}

?>