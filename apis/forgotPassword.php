<?php
$configs = include('igecApiBase.php');
$retData=array();

if( isset($_REQUEST['email_address']) && !empty($_REQUEST['email_address']) ){
     $reqEmail= $_REQUEST['email_address'];

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
        $retData['username']='';
        $retData['password']='';
        $retData['crm_record_id']='';
		echo json_encode($retData);
		die();
    }
   
    /*
        Search in mobile users module
    */

    $get_entry_list_parameters = array(

        //session id
        'session' => $session_id,

        //The name of the module from which to retrieve records
        'module_name' => 'igec_mobile_users',

        //The SQL WHERE clause without the word "where".
        'query' => " email='".$reqEmail."' ",

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
            'password',
        ),

        /*
        A list of link names and the fields to be returned for each link name.
        Example: 'link_name_to_fields_array' => array(array('name' => 'email_address', 'value' => array('id', 'email_address', 'opt_out', 'primary_address')))
        */
        'link_name_to_fields_array' => array(
        ),

        //The maximum number of results to return.
        'max_results' => '1',

        //To exclude deleted records
        'deleted' => '0',

        //If only records marked as favorites should be returned.
        'Favorites' => false,
    );

    $get_entry_list_result = call('get_entry_list', $get_entry_list_parameters, $url);

    $retData=array();
    if( $get_entry_list_result->result_count>=1 ) {
        $relRecord='';
        foreach ($get_entry_list_result->entry_list AS $val){

            if( !empty($val->name_value_list->contact_record->value) )
                $relRecord=$val->name_value_list->contact_record->value;
            else if ( !empty($val->name_value_list->lead_record->value) )
                $relRecord=$val->name_value_list->lead_record->value;

            $retData['message']='An email has sent to your email id';
            $retData['username']=$val->name_value_list->name->value;
            $retData['password']=$val->name_value_list->user_password->value;
            $retData['crm_record_id']=$relRecord;

            /*
              Update igec_mobile_users forgot email column in order to trigger workflow
            */
            $params = array(
                'sessionID' => $session_id,
                'module' => 'igec_mobile_users',
                "name_value_list" => array(
                    "id" => $val->name_value_list->id->value,
                    "send_forgot_pass_email" => "1",
                ),
            );
            $result = call('set_entry', $params, $url);
        }
    }
    else{
        $retData['message']='no user found for given email';
        $retData['username']='';
        $retData['password']='';
        $retData['crm_record_id']='';
    }
    echo json_encode($retData);
}

