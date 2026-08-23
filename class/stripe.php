<?php

class StripeLog{
	
	public $data;
	
	function __construct($data) {
		$this->data = $data;
	}
	
	public function save_log($_event){
		
		$_event_name = $_event->type;
		
		
		$_query = "insert into stripe_log (event_name, event_info, created_on)
					values('". $_event_name ."', '". $_event ."', now());
						";
		$_status = $this->data->execute($_query);
		
		if($_status){			
			return "1";
		}
		else{
			return "0";
		}
		
	}
	
	
	
	
	
}

?>