<?php
$configs = include('igecApiBase.php');
$retData = array();

if( isset($_REQUEST['email_address']) && !empty($_REQUEST['email_address']) ){
     $reqEmail= $_REQUEST['email_address'];
     $reqPass= $_REQUEST['user_password'];
     $recordName='';

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
        $retData['message'] = 'Authentication Failed';
        $retData['crm_record_id'] = '';
        $retData['crm_record_type'] = '';
        $retData['lead_id'] = '';
        $retData['user_name']='';
		echo json_encode($retData);
		die();
    }

    if( isset($_REQUEST['email_address']) && !empty($_REQUEST['email_address']) ) {
        $reqEmail = $_REQUEST['email_address'];

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
            'query' => " email='" . $reqEmail . "' AND user_password ='" . $reqPass . "' ",

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
            $relRecord = '';
            foreach ($get_entry_list_result->entry_list AS $val) {
                if (!empty($val->name_value_list->contact_id->value)) {
                    $relRecord = $val->name_value_list->contact_id->value;
                    $leadID = $val->name_value_list->lead_id->value;
                    $relRecordType = 'Contact';

                }
                else if (!empty($val->name_value_list->lead_id->value)) {
                    $relRecord = $val->name_value_list->lead_id->value;
                    $leadID = $val->name_value_list->lead_id->value;
                    $relRecordType = 'Lead';
                }
                else {
                    $relRecord = $val->name_value_list->id->value;
                    $relRecordType = 'Mobile User';
                }

                $retData['message'] = 'Login Successful';
                $retData['crm_record_id'] = $relRecord;
                $retData['crm_record_type'] = $relRecordType;
                $retData['lead_id'] = $leadID;
                $retData['user_name']='';
            }
        }
        /*
             If not a student go for counselor
        */
        else{
            $username= $_REQUEST['email_address'];
            $password= $_REQUEST['user_password'];
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

            $userID=$login_result->name_value_list->user_id->value;

            //get session id
            $session_id = $login_result->id;
            if($session_id==''){
                $retData['message'] = 'Authentication Failed';
                $retData['crm_record_id'] = '';
                $retData['crm_record_type'] = '';
                $retData['lead_id'] = '';
                $retData['user_name']='';
                echo json_encode($retData);
                die();
            }

            if( !empty($userID) ) {
                $isCounselor='no';
                $get_roles_parameters = array(
                    'session'=>$session_id,
                    'module_name' => 'Users',
                    'module_id' => $userID,
                    'link_field_name' => 'aclroles',
                    'related_module_query' => '',
                    'related_fields' => array(
                        'id',
                        'name',
                    ),
                    'related_module_link_name_to_fields_array' => array(
                        'users'
                    ),
                    'deleted'=> '0',
                    'order_by' => '',
                    'offset' => 0,
                    'limit' => 15,
                );

                $roles = call("get_relationships", $get_roles_parameters, $url);
                if(empty($roles->entry_list) ) {
                    $retData['message'] = 'Authentication Failed';
                    $retData['crm_record_id'] = '';
                    $retData['crm_record_type'] = '';
                    $retData['lead_id'] = '';
                    $retData['user_name']='';
                }
                else {
                    foreach ($roles->entry_list AS $role){
                        if($role->name_value_list->name->value=='Counselor') {
                            $isCounselor = 'yes';
                            break;
                        }
                        else
                            $isCounselor='no';

                    }
                    if($isCounselor=='yes'){
                        $retData['message'] = 'Login Successful';
                        $retData['crm_record_id'] = $userID;
                        $retData['crm_record_type'] = 'Counselor';
                        $retData['lead_id'] = $userID;
                        $retData['user_name']='';

                        $get_entry_list_parameters = array(
                            'session' => $session_id,
                            'module_name' => 'Users',
                            'query' => " users.id='" . $userID . "' ",
                            'order_by' => "",
                            'offset' => '0',
                            'select_fields' => array(
                                'id',
                                'first_name',
                                'last_name',
                            ),
                            'link_name_to_fields_array' => array(),
                            'max_results' => '1',
                            'deleted' => '0',
                            'Favorites' => false,
                        );
                        $get_entry_list_result = call('get_entry_list', $get_entry_list_parameters, $url);

                        foreach ($get_entry_list_result->entry_list AS $val) {
                            $recordName = $val->name_value_list->first_name->value . ' ' . $val->name_value_list->last_name->value;
                            break;
                        }
                    }
                    else{
                        $retData['message'] = 'No student or counselor exist with given username password';
                        $retData['crm_record_id'] = '';
                        $retData['crm_record_type'] = '';
                        $retData['lead_id'] = '';
                        $retData['user_name']='';
                    }
                }
            }
        }

        /*
            Getting record detail
        */
        $modu='';
        if($relRecordType=='Contact')
            $modu='Contacts';
        else if ($relRecordType=='Lead')
            $modu='Leads';


        if($modu!='') {
            $tbl=strtolower($modu);
            $get_entry_list_parameters = array(
                'session' => $session_id,
                'module_name' => $modu,
                'query' => " $tbl.id='" . $relRecord . "' ",
                'order_by' => "",
                'offset' => '0',
                'select_fields' => array(
                    'id',
                    'first_name',
                    'middle_name',
                    'last_name',
                ),
                'link_name_to_fields_array' => array(),
                'max_results' => '1',
                'deleted' => '0',
                'Favorites' => false,
            );
            $get_entry_list_result = call('get_entry_list', $get_entry_list_parameters, $url);
            foreach ($get_entry_list_result->entry_list AS $val) {
                $recordName = $val->name_value_list->first_name->value . ' ' . $val->name_value_list->middle_name->value . ' ' . $val->name_value_list->last_name->value;
                break;
            }

        }

    }
    $retData['user_name']=$recordName;
    echo json_encode($retData);
}

