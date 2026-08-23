<?php
$valid_extensions = array('png', 'mp4'); // valid extensions
$path = 'assets/images/'; // upload directory


$msg = '';
$sts = '1';
$out_file = '';
	
	//echo 'test 1';
	
	if($_FILES['image'])
	{
		$img = $_FILES['image']['name'];
		$tmp = $_FILES['image']['tmp_name'];
		// get uploaded file's extension
		$ext = strtolower(pathinfo($img, PATHINFO_EXTENSION));
		// can upload same image using rand function
		$final_image = $_POST['f_name'];
		
		// check's valid format
		if(in_array($ext, $valid_extensions)) 
		{ 
			$path = $path.strtolower($final_image); 
			
			if(move_uploaded_file($tmp,$path)) 
			{
				
				$sts = 0;
				$out_file = $path;
				
			}
			else{
				$sts = 1;
			}
		} 
		else 
		{
			$sts = 2;
		}
	}

echo $sts.'|'.$out_file;
    
	
?>