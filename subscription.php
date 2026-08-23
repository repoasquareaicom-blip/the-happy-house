<?php

ini_set('display_errors', 1);

ini_set('display_startup_errors', 1);

error_reporting(E_ALL);



class Subscription{

	

	public $data;

	

	function __construct($data) {

		$this->data = $data;

	}

	

	public function create_subscription($name, $email, $school_name, $school_admin_email, $password, $subscription_id, $subscription_start, $subscription_end, $status,$customer_id){

		$_query = "INSERT INTO 

			school_master (

			 name, 

			 email, 

			 school_name, 

			 school_admin_email, 

			 password, 

			 subscription_id, 

			 subscription_start, 

			 subscription_end, 

			 status, 

			 created_on,

			 customer_id,

			 subscription_status_id

			 ) 

		VALUES (

			 '$name', 

			 '$email', 

			 '$school_name', 

			 '$school_admin_email', 

			 '$password', 

			 '$subscription_id', 

			 FROM_UNIXTIME('$subscription_start'), 

			 FROM_UNIXTIME('$subscription_end'), 

			 '$status',

			 now(),

			 '$customer_id',

			 0

		)";

	    	 //file_put_contents('logs.txt', date("F j, Y, g:i a"). " Method: Subscription-Create - DB Trans:, Page: Subscription.php, Message: $_query\n", FILE_APPEND);	

		$_status = $this->data->execute($_query);

		if($_status){

			//$_school_data->id  = $_status;

			return "School data inserted QUERY<$_query>";

		}

		else{

			return "School data not inserted: QUERY<$_query>";

			

		}

	}

	

	public function check_exist_subscription($_subscription_id)

	{

		$_query = "select * from school_master where subscription_id = '$_subscription_id'";

		

		if($this->data->getCount($_query)>0){

			return true;

		}

		else{

			return false;

		}

	}

	

	

	public function get_subscription_byId($_subscription_id)

	{

		$_query = "select * from school_master where subscription_id = '$_subscription_id'";
		return $this->data->getData($_query);

		

	}

	

	public function get_all_subscription()

	{

		$_query = "select * from school_master";

		return $this->data->getData($_query);

	}

	

	public function delete_subscription($subscription_id,$status){

		

		$_query = "update school_master set 

					

					status='$status'

					created_on=now()

					where

					subscription_id = '$subscription_id'";

		

		$_status = $this->data->execute($_query);

		

		

		//return $_query;

		if($_status){

			$_school_data->id  = $_status;

			return "School data updated";

		}

		else{

			return "School data not updated: QUERY<$_query>";

			

		}

		//return $_school_data;

	}

	 

	

	



	public function update_subscription($name, $email, $school_name, $school_admin_email, $password, $subscription_id, $subscription_start, $subscription_end, $status,$customer_id){

		

		$_query = "update school_master set 

					subscription_start=FROM_UNIXTIME('$subscription_start'),

					subscription_end=FROM_UNIXTIME('$subscription_end'),

					status='$status'

					where

					subscription_id = '$subscription_id'";

		

		$_status = $this->data->execute($_query);

		

		

		//return $_query;

		if($_status){

			$_school_data->id  = $_status;

			return "School data updated";

		}

		else{

			return "School data not updated: QUERY<$_query>";

			

		}

		//return $_school_data;

	}

	

	

	public function update_signup($name, $email, $school_name, $school_admin_email, $password, $subscription_id, $subscription_start, $subscription_end, $status,$customer_id)

	{

		

			// Check if the email already exists for another subscription_id

			$_query = "SELECT COUNT(*) AS email_count FROM school_master 

			WHERE school_admin_email = '$school_admin_email' 

			and status='active' and  subscription_status_id = 1";

			$result =  $this->data->getData($_query);

			

			if ($result[0]['email_count'] > 0) {

				

				//"Email already exists for another subscription_id";

				return "0";

			}	

			else

			{

				

				$_query = "update school_master set 

					school_name='$school_name',

					school_admin_email='$school_admin_email',

					password='$password',

					subscription_status_id=1

					where

					subscription_id = '$subscription_id'";

		

				    $_status = $this->data->execute($_query);

					return 1;

			}

		

		// echo $_query;

		

		// if($_status){

		// 	$_school_data->id  = $_status;

		// 	return "1";

		// }

		// else{

		// 	return "0";

			

		// }

		// //return $_school_data;

	}

	

	public function get_user_by_email($email){

		$_query = "select * from school_master where school_admin_email = '$email'";

		return $this->data->getData($_query);

	}



	public function check_admin_login($email, $passoword){

		$_query = "select * from admin_users where email_id = '$email' and password = '$passoword'";

		return $this->data->getData($_query);

	}

	public function save_password_reset_token($email, $token, $expiry_time,$user_type) 

	{

			$_query = "INSERT INTO password_reset_tokens (email, token, expiry_time, user_type) VALUES ('$email', '$token', '$expiry_time','$user_type') ON DUPLICATE KEY UPDATE token = '$token', expiry_time = '$expiry_time'";

                      

		file_put_contents('logs.txt', date("F j, Y, g:i a"). " Method: Subscription-Create - DB Trans:, Page: Subscription.php, Message: $_query\n", FILE_APPEND);	



		$_status = $this->data->execute($_query);



		if($_status){

			return true;

		}

		else{

			return false;

			

		}

	}

public function validate_password_reset_token($token)

{

    try {

        // Prepare the query to find the user based on the token

        $_query = "SELECT email, expiry_time FROM password_reset_tokens WHERE token = '$token' and user_type='S'";

		file_put_contents('logs.txt', date("F j, Y, g:i a"). " Method: Reset Password Validate Token - DB Trans:, Page: reset_password.php, Query: $_query\n", FILE_APPEND);	



		try {

				$result = $this->data->getData($_query);

				$email = null; // Initialize variable to avoid undefined variable errors

			

				foreach ($result as $_user) {

					$email = $_user['email'];

					$expiry_time = $_user['expiry_time'];

				}

			

				if (!$email) {

					throw new Exception("No email found in the result.");

				}

		

			

			} catch (Exception $e) {

			return $e->getMessage();

			}

		



        // Check if the token exists

        if ($email) {

        

            // Check if the token is expired

            if (time() > strtotime($expiry_time)) {

                // Token has expired

                return false;

            }



            // Token is valid

            return $email;

        } else {

            // Token not found

            return false;

        }

    } catch (Exception $e) {

        // Handle exception

        

		file_put_contents('error_log.txt', date("F j, Y, g:i a"). " Method: validate_password_reset_token - DB Trans:, Page: Subscription.php, Query: $_query Message: $e\n", FILE_APPEND);	

        return false;

    }

}

public function update_password_school_admin($email, $password) 

{

	$_query = "UPDATE school_master SET password = '$password' WHERE school_admin_email = '$email'";			

	file_put_contents('error_log.txt', date("F j, Y, g:i a"). " Method: School admin password update - DB Trans:, Page: Subscription.php, Query: $_query", FILE_APPEND);	

	$_status = $this->data->execute($_query);



	if($_status){

		return true;

	}

	else{

		return false;

	}

}



}



?>