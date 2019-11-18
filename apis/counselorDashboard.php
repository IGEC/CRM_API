<?php
$configs = include('igecApiBase.php');
$retData=array();

if( isset($_REQUEST['counselor_id']) && !empty($_REQUEST['counselor_id']) ){

    $counselorId= $_REQUEST['counselor_id'];
     /*
        login
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
//    //get session id
    $session_id = $login_result->id;
    if($session_id==''){
		$retData['message']='Authentication Failed';
		$retData['data']='';
        $retData['crm_record_type']='';
        $retData['crm_record_id']='';
		echo json_encode($retData);
		die();
    }

    $set_entry_parameters = array(
        'sessionID' => $session_id,
        'module' => 'Users',
        "name_value_list" => array(
            "user_id" => $counselorId,
        ),
    );
    $data = call("get_counselor_mobile_dashboard", $set_entry_parameters, $url);

    $retData['message']='success';
    $retData['data']=$data;
    $typeData=isCounselor($counselorId,$session_id,$url);
    $retData['crm_record_type']=$typeData['crm_record_type'];
    $retData['crm_record_id']=$typeData['crm_record_id'];
	echo json_encode($retData);
}
else{
    $retData['message']='Counselor id empty';
    $retData['data']='';
    $retData['crm_record_type']='';
    $retData['crm_record_id']='';
    echo json_encode($retData);
    die();
}