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
	$record_id = $_REQUEST['record_id'];
	$name = $_REQUEST['about_name'];
	$mobile = $_REQUEST['about_mobile'];
	$email = $_REQUEST['about_email'];
	$assignuserid = '3';
	$description = $_REQUEST['about_message'];
	$subject = $_REQUEST['about_name'];
	if(!empty($_REQUEST['record_id'])){
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
		sendEmail();
	}else{
		sendEmail();
	}
	function sendEmail(){
		$to = "hameed@igec.com.au";
		$txt = "Hi!<br/>";
		$txt.= "Greeting From IGEC Mobile App!"."<br/>";
		$txt.= "Student Name :".$name."<br/>";
		$txt.= "Student Email :".$email."<br/>";
		$txt.= "Student Mobile :".$mobile."<br/>";
		$txt.= "Message :".$description."<br/>";
		$txt.= "Regards<br/>";
		$txt.= "CRM Application";
		require_once('phpmailer/PHPMailerAutoload.php');
		require_once('phpmailer/class.phpmailer.php');
		$mail = new PHPMailer();
		// Settings
		$mail->IsSMTP();
		$mail->CharSet = 'UTF-8';
		$mail->Host       = "smtp.gmail.com"; // SMTP server example
		$mail->SMTPDebug  = 3;                // enables SMTP debug information (for testing)
		$mail->SMTPAuth   = true; 
		$mail->SMTPSecure = 'tls';            // enable SMTP authentication
		$mail->Port       = 587;               // set the SMTP port for the GMAIL server
		$mail->Username   = "crmigec2@gmail.com"; 	  // SMTP account username example
		$mail->Password   = "igec321@@";       // SMTP account password example
		$mail->CharSet = 'UTF-8';
		// Content
		$mail->isHTML(true);                                  // Set email format to HTML
		$mail->Subject = 'CRM Mobile App';
		$mail->Body    = $txt;
		$mail->SetFrom('crmigec2@gmail.com', 'IGEC');
		$mail->AddAddress($to);
		if(!$mail->Send()) {
			echo $mail->ErrorInfo . '<br>';
		}
	}
}

