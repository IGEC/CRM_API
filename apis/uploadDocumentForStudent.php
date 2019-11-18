<?php
$configs = include('igecApiBase.php');
$retData = array();

if( isset($_REQUEST['contact_id']) && !empty($_REQUEST['contact_id']) ) {
    $conId = $_REQUEST['contact_id'];

    if (empty($_FILES['fileToUpload']['size']))
        die('No File uploaded');
    else {
        $imageFileName = $_FILES["fileToUpload"]["name"];
        $imageFileType = $_FILES["fileToUpload"]["type"];
        $contents = file_get_contents($_FILES["fileToUpload"]["tmp_name"]);
    }

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

        //session id
        'session' => $session_id,

        //The name of the module from which to retrieve records
        'module_name' => 'Contacts',

        //The SQL WHERE clause without the word "where".
        'query' => " contacts.id='".$conId."' ",

        //The SQL ORDER BY clause without the phrase "order by".
        'order_by' => "",

        //The record offset from which to start.
        'offset' => '0',

        //Optional. A list of fields to include in the results.
        'select_fields' => array(
            'id',
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
        $retData['message']='No contact found for given Id';
        $retData['crm_record_type']='';
        $retData['crm_record_id']='';
		echo json_encode($retData);
        die();
	}
   
    /*
        2. create document
    */
    $set_entry_parameters = array(
        "session" => $session_id,
        "module_name" => "Documents",
        "name_value_list" => array(
            array("name" => "document_name", "value" => $imageFileName),
            array("name" => "revision", "value" => "1"),
            array("name" => "category_id", "value" => "General"),
        ),
    );
    $set_entry_result = call("set_entry", $set_entry_parameters, $url);
    $document_id = $set_entry_result->id;

    /*
        3. create document revision
    */
    $set_document_revision_parameters = array(
        "session" => $session_id,
        //The attachment details
        "note" => array(
            'id' => $document_id,
            'file' => base64_encode($contents),
            'filename' => $imageFileName,
            'revision' => '1',
        ),
    );

    $set_document_revision_result = call("set_document_revision", $set_document_revision_parameters, $url);

    /*
        associating doc with contact
     */
    $set_entry_parameters = array(
        'sessionID' => $session_id,
        'module' => 'Documents',
        "name_value_list" => array(
            "id" => $document_id,
            "contact_id" => $conId,
        ),
    );

    $set_entry_result = call("relate_doc_contacts", $set_entry_parameters, $url);

    if ($set_document_revision_result->id != '')
		$retData['message']='Doc uploaded Successfully';
    else
		$retData['message']='Error uploading Doc';

    $typeData=getTypeById($conId,$session_id,$url);
    $retData['crm_record_type']=$typeData['crm_record_type'];
    $retData['crm_record_id']=$typeData['crm_record_id'];
	echo json_encode($retData);

	

}
?>