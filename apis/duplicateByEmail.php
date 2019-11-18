<?php
$configs = include('igecApiBase.php');

// $_REQUEST['email_address']='someone49494@hotmail.com';
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
        die('Authentication Failed');
    }
   
    /*
        Search in contacts,lead for given email address
    */
   
    $search_by_module_parameters = array(
        "session" => $session_id,
        'search_string' => $reqEmail,
        'modules' => array(
            'Contacts',
            'Leads',
        ),
        'offset' => 0,
        'max_results' => 1,
        'assigned_user_id' => '',
        'select_fields' => array('id'),
        'unified_search_only' => false,
        'favorites' => false
    );
   
    $search_by_module_results = call('search_by_module', $search_by_module_parameters, $url);

    $record_ids = array();
    foreach ($search_by_module_results->entry_list as $results) {
        $module = $results->name;
        foreach ($results->records as $records) {
            foreach($records as $record) {
                if ($record->name = 'id') {
                    $record_ids[$module][] = $record->value;
                    //skip any additional fields
                    break;
                }
            }
        }
    }
    $get_entries_results = array();
    $modules = array_keys($record_ids);
   
    foreach($modules as $module) {
        $get_entries_parameters = array(
            'session' => $session_id,

            //The name of the module from which to retrieve records
            'module_name' => $module,
   
            //An array of record IDs
            'ids' => $record_ids[$module],
   
            //The list of fields to be returned in the results
            'select_fields' => array(
                'id',
                'first_name',
                'last_name',
            ),
   
            //A list of link names and the fields to be returned for each link name
            'link_name_to_fields_array' => array(
                array(
                    'name' => 'email_address',
                    'value' => array(
                        'email_address',
                        'opt_out',
                        'primary_address'
                    ),
                ),
            ),
   
            //Flag the record as a recently viewed item
            'track_view' => false,
        );
   
        $get_entries_results[$module] = call('get_entries', $get_entries_parameters, $url);
    }
    $retData=array();
    if( !empty($get_entries_results) ) {
        foreach ($get_entries_results AS $key => $val){
            $retData['message']='duplicate found';
            $retData['module']=$key;
            $retData['crm_record_id']=$val->entry_list['0']->id;
        }
    }
    else{
        $retData['message']='no duplicate found';
        $retData['module']='';
        $retData['crm_record_id']='';
    }
    echo json_encode($retData);
}

