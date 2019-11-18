<?php
$configs = include('igecApiBase.php');
$retData = array();
$retData=array();
$isCon=0;
$relModule='';
$ContactId='';
$LeadId='';
if( isset($_REQUEST['contact_id']) && !empty($_REQUEST['contact_id']) ) {
    $conId= $_REQUEST['contact_id'];
//******************************************************************
//Sample Code to Create Opportunity for a Lead in the CRM System
//******************************************************************
    /*
       1.  Login Call
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
    $session_id = $login_result->id;
    if($session_id==''){
        $retData['message']='Authentication Failed';
        $retData['crm_record_type']='';
        $retData['crm_record_id']='';
        echo json_encode($retData);
        die();
    }

    /*
       Search in contacts exist or not
   */

    $get_entry_list_parameters = array(
        'session' => $session_id,
        'module_name' => 'Contacts',
        'query' => " contacts.id='".$conId."' ",
        'order_by' => "",
        //The record offset from which to start.
        'offset' => '0',
        //Optional. A list of fields to include in the results.
        'select_fields' => array(
            'id',
            'assigned_user_id',
            'securitygroup_id',
            'office_user',
        ),
        /*
        A list of link names and the fields to be returned for each link name.
        Example: 'link_name_to_fields_array' => array(array('name' => 'email_address', 'value' => array('id', 'email_address', 'opt_out', 'primary_address')))
        */
        'link_name_to_fields_array' => array(
        ),
        'max_results' => '1',
        'deleted' => '0',
        'Favorites' => false,
    );

    $get_entry_list_result = call('get_entry_list', $get_entry_list_parameters, $url);

    if($get_entry_list_result->result_count>=1 ) {
       $isCon=1;
       $relModule='Contacts';
       $assUId=$get_entry_list_result->entry_list['0']->name_value_list->assigned_user_id->value;
       $assOff=$get_entry_list_result->entry_list['0']->name_value_list->securitygroup_id->value;
       $assCon=$get_entry_list_result->entry_list['0']->name_value_list->office_user->value;
    }
    else {
        $isCon = 0;
        $relModule = '';
    }
    if($isCon==0){
        /*
             Search in contacts exist or not
         */

        $get_entry_list_parameters = array(
            'session' => $session_id,
            'module_name' => 'Leads',
            'query' => " leads.id='".$conId."' ",
            'order_by' => "",
            //The record offset from which to start.
            'offset' => '0',
            //Optional. A list of fields to include in the results.
            'select_fields' => array(
                'id',
                'assigned_user_id',
                'securitygroup_id',
                'office_user',
            ),
            /*
            A list of link names and the fields to be returned for each link name.
            Example: 'link_name_to_fields_array' => array(array('name' => 'email_address', 'value' => array('id', 'email_address', 'opt_out', 'primary_address')))
            */
            'link_name_to_fields_array' => array(
            ),
            'max_results' => '1',
            'deleted' => '0',
            'Favorites' => false,
        );
        $get_entry_list_result = call('get_entry_list', $get_entry_list_parameters, $url);
        if($get_entry_list_result->result_count>=1 ) {
            $isCon=0;
            $relModule='Leads';
            $assUId=$get_entry_list_result->entry_list['0']->name_value_list->assigned_user_id->value;
            $assOff=$get_entry_list_result->entry_list['0']->name_value_list->securitygroup_id->value;
            $assCon=$get_entry_list_result->entry_list['0']->name_value_list->office_user->value;
        }
        else {
            $retData['message'] = 'No Contact Or lead found with given ID';
            $retData['crm_record_type']='';
            $retData['crm_record_id']='';
            echo json_encode($retData);
            die();
        }
    }

    if($relModule=='Leads')
        $leadId= $_REQUEST['contact_id'];
    if($relModule=='Contacts')
        $ContactId= $_REQUEST['contact_id'];

    //parameters to create Opportunity for a given Lead
    $createOppValues=array (
//        'related_to'=> 'Leads',
        'lead_id'=> $leadId,
        'contact_id'=> $ContactId,
        'country_id'=>  $_REQUEST['country_id'],
        'country_name'=>  $_REQUEST['country_name'],
        'institute_id'=>  $_REQUEST['institute_id'],
        'institute_name'=> $_REQUEST['institute_name'],
        'campus_id'=>  $_REQUEST['campus_id'],
        'campus_name'=>  $_REQUEST['campus_name'],
        'course_level_id'=>  $_REQUEST['course_level_id'],
        'course_level'=>  $_REQUEST['course_level'],
        'course_id'=>  $_REQUEST['course_id'],
        'course_name'=>  $_REQUEST['course_name'],
        'name'=>  $_REQUEST['name'],
        'course_fee'=> $_REQUEST['course_fee'],
        'course_currency'=>  $_REQUEST['course_currency'],
        'course_duration'=>  $_REQUEST['course_duration'],
        'course_startdate_calculated'=>  $_REQUEST['course_startdate_calculated'],
        'description'=> $_REQUEST['description'] ,
        'logo_url'=>  $_REQUEST['logo_url'],
        'assigned_user_id'=> '2',
        'createdbyapp'=> 'mobile',
        'assigned_user_id'=> $assUId,
        'securitygroup_id'=> $assOff,
        'counselor_id'=> $assCon
    );

    $params['name_value_list']=array();
        $params = array(
            'sessionID' => $session_id, //session id comes from the earlier LOGIN call using username and password hash
            'module' => 'Opportunities',
            "name_value_list" => $createOppValues,
        );

    $result = call('set_entry', $params, $url);
    if($result->id!=''){
        $retData['message'] = 'Course added successfully';
        $typeData=getTypeById($conId,$session_id,$url);
        $retData['crm_record_type']=$typeData['crm_record_type'];
        $retData['crm_record_id']=$typeData['crm_record_id'];
        echo json_encode($retData);
    }
}
else {
    $retData['message'] = 'Contact ID is empty';
    $retData['crm_record_type']='';
    $retData['crm_record_id']='';
    echo json_encode($retData);
    die();
}
?>