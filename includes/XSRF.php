<?php
// prevent cross site request forgery
// check if any data has been posted
if($_SERVER['REQUEST_METHOD'] == 'POST')
{
//  all posted data is sent as myJSON
	if (isset($_POST["myJson"]) )
	{
	// convert posted json to object
	$data = json_decode($_POST['myJson'], true);
	// check if s-token exists
	if( !isset($data["s-token"]) )
		{
			// no token
			$_POST = array();
			$_REQUEST = array();
			array_push($errorArray, "err_token");
			$resultArray["errors"] =  $errorArray;
			echo json_encode($resultArray);	
			logEntry("XSRF: no session token found","E_ERROR");
			exit;
		}	
	// check if s-token matches the session variable
		if( isset($data["s-token"]) && isset($_SESSION["s-token"]) && $data["s-token"] !== $_SESSION["s-token"])
		{
			// do not allow post data as s-token does not match
			// return error
			$_POST = array();
			$_REQUEST = array();
			array_push($errorArray, "err_token");
			$resultArray["errors"] =  $errorArray;
			echo json_encode($resultArray);	
			logEntry("XSRF: invalid session token found","E_ERROR");
			exit;
		}	
	} 
}
?>
