<?php
$configs = include('igecApiBase.php');
$retData = array();

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
		echo json_encode($retData);
        die();
    }
   
    /*
        Search in mobile users module before registration
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
    if($get_entry_list_result->result_count<=0 ) {

        /*
            Create a lead on CRM side when student register
        */
        $params = array(
            'sessionID' => $session_id,
            'module' => 'Leads',
            "name_value_list" => array(
                "first_name" => $_REQUEST['first_name'],
                "last_name" => $_REQUEST['last_name'],
                "email1" => $_REQUEST['email_address'],
                "phone_mobile" => $_REQUEST['mobile'],
                "primary_address_country" => $_REQUEST['address_country'],
                "lead_source" => "Mobile App",
                "assigned_user_id" => "3",
                "securitygroup_id" => "3",
                "office_user" => "3",
            ),
        );
        $result = call('set_entry', $params, $url);
        if(!empty($result->id))
            $leadId=$result->id;

        if(!empty($leadId) ) {
            /*
                 Create a mobile user on CRM side when student register
            */
            $params = array(
                'sessionID' => $session_id,
                'module' => 'igec_mobile_users',
                "name_value_list" => array(
                    "email" => $_REQUEST['email_address'],
                    "name" => $_REQUEST['email_address'],
                    "first_name" => $_REQUEST['first_name'],
                    "last_name" => $_REQUEST['last_name'],
                    "mobile" => $_REQUEST['mobile'],
                    "user_password" => $_REQUEST['std_password'],
                    "send_signup_email" => '1',
                    "lead_id" => $leadId,
                ),
            );
            $result = call('set_entry', $params, $url);
            $retData['message'] = 'Registration successfull please follow your email address for login details';
        }
    }
    else{
        $retData['message']='User already exist with same email';
    }
    echo json_encode($retData);
}

