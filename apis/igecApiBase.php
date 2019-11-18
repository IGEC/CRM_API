<?php

$configs = include('config.php');
$username=$config['username'];
$password=$config['password'];
$url=$config['crm_url'];

//function to make cURL request
function call($method, $parameters, $url){
    ob_start();
    $curl_request = curl_init();
    curl_setopt($curl_request, CURLOPT_URL, $url);
    curl_setopt($curl_request, CURLOPT_POST, 1);
    curl_setopt($curl_request, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
    curl_setopt($curl_request, CURLOPT_HEADER, 1);
    curl_setopt($curl_request, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($curl_request, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl_request, CURLOPT_FOLLOWLOCATION, 0);
    $jsonEncodedData = json_encode($parameters);

    $post = array(
        "method" => $method,
        "input_type" => "JSON",
        "response_type" => "JSON",
        "rest_data" => $jsonEncodedData
    );

    curl_setopt($curl_request, CURLOPT_POSTFIELDS, $post);
    $result = curl_exec($curl_request);
    curl_close($curl_request);

    $result = explode("\r\n\r\n", $result, 2);
    $response = json_decode($result[1]);
    ob_end_flush();
    return $response;
}

function getTypeById($conId,$session_id,$url){
    /*
      Finding either its a contact or lead
    */
    $get_entry_list_parameters = array(
        'session' => $session_id,
        'module_name' => 'Contacts',
        'query' => " contacts.id='" . $conId . "'  ",
        'order_by' => "",
        'offset' => '0',
        'select_fields' => array(
            'id',
        ),

        'link_name_to_fields_array' => array(),
        'max_results' => '1',
        'deleted' => '0',/////////////////////////////////////////
        'Favorites' => false,
    );
    $get_entry_list_result = call('get_entry_list', $get_entry_list_parameters, $url);
    $recID='';
    $recType='';
    if ($get_entry_list_result->result_count >= 1) {
        foreach ($get_entry_list_result->entry_list AS $val) {
            if (!empty($val->name_value_list->id->value)) {
                $recID = $val->name_value_list->id->value;
                $recType = 'Contact';
            }
            else {
                $get_entry_list_parameters = array(
                    'session' => $session_id,
                    'module_name' => 'Leads',
                    'query' => " leads.id='" . $conId . "'  ",
                    'order_by' => "",
                    'offset' => '0',
                    'select_fields' => array(
                        'id',
                    ),

                    'link_name_to_fields_array' => array(),
                    'max_results' => '1',
                    'deleted' => '0',/////////////////////////////////////////
                    'Favorites' => false,
                );
                $get_entry_list_result = call('get_entry_list', $get_entry_list_parameters, $url);
                foreach ($get_entry_list_result->entry_list AS $val) {
                    if (!empty($val->name_value_list->id->value)) {
                        $recID = $val->name_value_list->id->value;
                        $recType = 'Lead';
                    }
                }
            }
        }
    }
    $retArr=array();
    $retArr['crm_record_type']=$recType;
    $retArr['crm_record_id']=$recID;
    return $retArr;
}
function isCounselor($userID,$session_id,$url){
    $isCounselor='no';
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
            ),
            'deleted'=> '0',
            'order_by' => '',
            'offset' => 0,
            'limit' => 15,
        );

        $roles = call("get_relationships", $get_roles_parameters, $url);
        if(empty($roles->entry_list) ) {
            $retData['message'] = 'Authentication Failed';
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
        }
    }
    $retArr=array();
    if($isCounselor=='yes'){
        $retArr['crm_record_type']='Counselor';
        $retArr['crm_record_id']=$userID;
    }
    else{
        $retArr['crm_record_type']='';
        $retArr['crm_record_id']='';
    }
    return $retArr;
}
?>