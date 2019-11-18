<?php
$configs = include('igecApiBase.php');
$retData=array();

if( isset($_REQUEST['contact_id']) && !empty($_REQUEST['contact_id']) ){

     $conId= $_REQUEST['contact_id'];
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
//
//    //get session id
    $session_id = $login_result->id;
    if($session_id==''){
		$retData['message']='Authentication Failed';
		$retData['opportunities']='';
		$retData['crm_record_type']='';
		$retData['crm_record_id']='';
		echo json_encode($retData);
		die();
    }

//    //search_by_module -------------------------------------------------

    $get_opp_parameters = array(
			 'session'=>$session_id,
			 'module_name' => 'Contacts',
			 'module_id' => $conId,
			 'link_field_name' => 'opportunities',
			 'related_module_query' => '',
			 'related_fields' => array(
										'id',
										'name',
										'course_level',
										'institute_name',
										'campus_name',
										'country_name',
										'sales_stage',
										'counselor_id',
										'counselor_name',
									),
			 'related_module_link_name_to_fields_array' => array(
			 ),
			 'deleted'=> '0',
			 'order_by' => '',
			 'offset' => 0,
			 'limit' => 15,
		);

	$opportunities = call("get_relationships", $get_opp_parameters, $url);

    if(empty($opportunities)){
		 $get_opp_parameters = array(
			 'session'=>$session_id,
			 'module_name' => 'Leads',
			 'module_id' => $conId,
			 'link_field_name' => 'lead_opportunities',
			 'related_module_query' => '',
			 'related_fields' => array(
										'id',
										'name',
										'course_level',
										'institute_name',
										'campus_name',
										'country_name',
										'sales_stage',
										'counselor_id',
										'counselor_name',
									),
			 'related_module_link_name_to_fields_array' => array(
			 ),
			 'deleted'=> '0',
			 'order_by' => '',
			 'offset' => 0,
			 'limit' => 15,
		);
	    $opportunities = call("get_relationships", $get_opp_parameters, $url);

	}
    if(empty($opportunities)){
        $opportunities = new stdClass();
        $retData['message']='No Opportunity associated with given contact or lead ID';
		$retData['opportunities']=$opportunities;
	}
    else{
		$retData['message']='success';
		$retData['opportunities']=$opportunities;
	}

    $typeData=getTypeById($conId,$session_id,$url);
    $retData['crm_record_type']=$typeData['crm_record_type'];
    $retData['crm_record_id']=$typeData['crm_record_id'];
	echo json_encode($retData);
}
