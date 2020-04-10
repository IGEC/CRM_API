<?php
$configs = include('igecApiBase.php');
$retData = array();

if( isset($_REQUEST['about_email']) && !empty($_REQUEST['about_email']) ){
     $reqEmail= $_REQUEST['about_email'];

    /*
         login Call
    */
    $login_parameters = array(
        "user_auth" => array(
            "user_name" => $username,
            "password" => md5($password),
            "version" => "1"
        ),
        "application_name" => "RestTest",
        "name_value_list" => array(),
    );
   
    $login_result = call("login", $login_parameters, $url);
   
    //get session id
    $session_id = $login_result->id;
    if($session_id==''){
		$retData['message']='Authentication Failed';
		echo json_encode($retData);
        die();
    }
   
	/*
		Create a Case against Student if record_id is not empty
	*/
	if(!empty($record_id)){
		$record_id = $_REQUEST['record_id'];
		$name = $_REQUEST['about_name'];
		$assignuserid = '3';
		$description = $_REQUEST['about_message'];
		$subject = $_REQUEST['about_name'];
		$params = array(
			'sessionID' => $session_id,
			'module' => 'Cases',
			"name_value_list" => array(
				"name" => $subject,
				"status" => 'Open_New',
				"contact_created_by_id" => $record_id,
				"contact_id" => $record_id,
				"priority" => "P1",
				"state" => "Open",
				"assigned_user_id" => $assignuserid,
				"description" => $description,
			), 
		);
		$retData = call('set_entry', $params, $url);
		echo json_encode($retData);
	}
}

