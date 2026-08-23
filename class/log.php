<?php
class mLog{
	public function write($method, $page, $err){
		file_put_contents('logs.txt', date("F j, Y, g:i a")."Method: $method, Page: $page, Message: $err", FILE_APPEND);
	}
	public function writelog($message){
		file_put_contents('logs.txt',$message, FILE_APPEND);
	}
	
}

?>