<?php
$configs = include('igecApiBase.php');
$retData = array();

if( isset($_REQUEST['record_id']) && !empty($_REQUEST['record_id']) ){
     $reqId= $_REQUEST['record_id'];
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
		$retData['crm_record_type']='';
		$retData['lead_id']='';
		$retData['contact_id']='';
		echo json_encode($retData);
		die();
    }

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

    /*
        Search in mobile users module
    */
    $get_entry_list_parameters = array(

        //session id
        'session' => $session_id,

        //The name of the module from which to retrieve records
        'module_name' => 'igec_mobile_users',

        //The SQL WHERE clause without the word "where".
        'query' => " igec_mobile_users.lead_id='" . $reqId . "' OR igec_mobile_users.contact_id='" . $reqId . "'  ",

        //The SQL ORDER BY clause without the phrase "order by".
        'order_by' => "",

        //The record offset from which to start.
        'offset' => '0',

        //Optional. A list of fields to include in the results.
        'select_fields' => array(
            'id',
            'lead_id',
            'contact_id',
            'name',
        ),

        /*
        A list of link names and the fields to be returned for each link name.
        Example: 'link_name_to_fields_array' => array(array('name' => 'email_address', 'value' => array('id', 'email_address', 'opt_out', 'primary_address')))
        */
        'link_name_to_fields_array' => array(),

        //The maximum number of results to return.
        'max_results' => '1',

        //To exclude deleted records
        'deleted' => '0',

        //If only records marked as favorites should be returned.
        'Favorites' => false,
    );
    $get_entry_list_result = call('get_entry_list', $get_entry_list_parameters, $url);

    if ($get_entry_list_result->result_count >= 1) {
        foreach ($get_entry_list_result->entry_list AS $val) {
            if (!empty($val->name_value_list->contact_id->value)) {
                $conID = $val->name_value_list->contact_id->value;
                $leadID = $val->name_value_list->lead_id->value;
                $relRecordType = 'Contact';
            }
            else if (!empty($val->name_value_list->lead_id->value)) {
                $conID = $val->name_value_list->contact_id->value;
                $leadID = $val->name_value_list->lead_id->value;
                $relRecordType = 'Lead';
            }
            $retData['message'] = 'Success';
            $retData['crm_record_type'] = $relRecordType;
            $retData['contact_id'] = $conID;
            $retData['lead_id'] = $leadID;
        }
    }
    else{
        $retData['message'] = 'No Login details found for given ID';
        $retData['crm_record_type'] = '';
        $retData['contact_id'] = '';
        $retData['lead_id'] = '';
    }
    echo json_encode($retData);
}

