<?php declare(strict_types=1);

    $hookman->add_hook('xhr_update_record', array('logged_in' => true, 'url' => '/xhr/update_record', 'layer' => 'page_load_pre'));

    function xhr_update_record(&$data) {

        $audit_response = array();
        $audit_response['raw_state'] = array();

        $audit_response['raw_state']['_GET'] = $data['_GET'];
        $audit_response['raw_state']['_POST'] = $data['_POST'];
        $audit_response['raw_state']['_CMS'] = $data['_CMS'];

        if($data['_CMS']['logged_in'] == false) {

            $response = array();

            $response['xhr_response_type'] = 'record';
            $response['xhr_response_status'] = 'error';
            $response['error'] = 'Not logged in';

            http_response_code(401);

            $audit_response['http_response'] = $response;
    
            $data['_CMS']['AUDITMON']->audit(json_encode($audit_response));

            echo json_encode($response);

            graceful_exit();

        }
        
        $response = array();

        $database = $data['_CMS']['database'];
        $record_name = $_POST['record_name'] ?? $_GET['record_name'] ?? null;

        $response['xhr_response_type'] = 'record';

        if($record_name == null) {

            $response['xhr_response_status'] = 'error';
            $response['error'] = 'No record name provided';

            $audit_response['http_response'] = $response;
    
            $data['_CMS']['AUDITMON']->audit(json_encode($audit_response));

            echo json_encode($response);

            graceful_exit();

        }

        $fields = $_POST['fields'] ?? null;

        if($fields == null) {

            $response['xhr_response_status'] = 'error';
            $response['error'] = 'No fields provided';

            $audit_response['http_response'] = $response;
    
            $data['_CMS']['AUDITMON']->audit(json_encode($audit_response));

            echo json_encode($response);

            graceful_exit();

        }

        $criteria = $_POST['criteria'] ?? null;

        if($criteria == null) {

            $response['xhr_response_status'] = 'error';
            $response['error'] = 'No criteria provided';

            $audit_response['http_response'] = $response;
    
            $data['_CMS']['AUDITMON']->audit(json_encode($audit_response));

            echo json_encode($response);

            graceful_exit();

        }

        $fields = json_decode($fields, true);

        $criteria = json_decode($criteria, true);

        $record = new Record($record_name, $fields);

        $fetch_fields = array();

        $fetch_fields['id'] = $criteria['id'];

        $fetch_record = new Record($record_name, $fetch_fields);

        $fetch_record = $database->get_records($fetch_record)[0];

        $old_record_json = array();

        foreach($fetch_record->get_fields() as $field_name => $field) {

            $old_record_json[$field_name] = $field['value'];

        }

        $audit_response['old_record_json'] = $old_record_json;

        $status = $database->update_record($record_name, $record, $criteria);

        $fetch_fields = array();

        $fetch_fields['id'] = $criteria['id'];

        $fetch_record = new Record($record_name, $fetch_fields);

        $fetch_record = $database->get_records($fetch_record)[0];

        $new_record_json = array();

        foreach($fetch_record->get_fields() as $field_name => $field) {

            $new_record_json[$field_name] = $field['value'];

        }

        $audit_response['row_id'] = $criteria['id']['value'];

        $audit_response['new_record_json'] = $new_record_json;
        
        $response['xhr_response_status'] = $status ? 'success' : 'error';

        $audit_response['http_response'] = $response;

        $data['_CMS']['AUDITMON']->audit(json_encode($audit_response));

        echo json_encode($response);

        graceful_exit();

    }


?>