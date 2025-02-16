<?php

    $hookman->add_hook('xhr_new_audit', array('url' => '/xhr/new_audit', 'layer' => 'page_load_pre'));

    function xhr_new_audit(&$data) {

        $response = array();

        try {

            if(!isset($_POST) || !isset($_POST['audit_data'])) {
                
                $response['xhr_response_status'] = 'error';
                $response['error'] = 'No audit data provided';

                http_response_code(401);

                echo json_encode($response);
                graceful_exit();

            }

            $audit_json = $_POST['audit_data'];

            $audit_data = json_decode($audit_json, true);

            $cms_path = '';

            $audit_http_response_body = '';

            if(isset($audit_data['raw_state']) && isset($audit_data['raw_state']['_GET']) && isset($audit_data['raw_state']['_GET']['cms_path'])) {
                $cms_path = $audit_data['raw_state']['_CMS']['path'];
            }

            if(isset($audit_data['http_response'])) {
                $audit_http_response_body = json_encode($audit_data['http_response']);
            }

            $old_record_json = '';

            if(isset($audit_data['old_record_json'])) {
                $old_record_json = json_encode($audit_data['old_record_json']);
            }

            $new_record_json = '';

            if(isset($audit_data['new_record_json'])) {
                $new_record_json = json_encode($audit_data['new_record_json']);
            }

            $row_id = 1;

            if(isset($audit_data['row_id'])) {
                $row_id = $audit_data['row_id'];
            }

            $user_id = 1;

            if(isset($audit_data['raw_state']) && isset($audit_data['raw_state']['_CMS']['user_id'])) {
                $user_id = $audit_data['raw_state']['_CMS']['user_id'];
            }

            $record_name = '';

            if(isset($audit_data['raw_state']) && isset($audit_data['raw_state']['_POST']) && isset($audit_data['raw_state']['_POST']['record_name'])) {
                $record_name = $audit_data['raw_state']['_POST']['record_name'];
            }

            $record_def_record = new Record('record_definitions', array('table_name' => array('type' => 'VARCHAR', 'value' => $record_name)));

            $record_def = $data['_CMS']['database']->get_records($record_def_record)[0];

            $table_id = $record_def->get_field('id')['value'];

            // TODO: Make time based on what's in the audit file not the current time
            $fields = array(
                'audit_event_type_id' => array('type' => 'INT', 'value' => 2),
                'user_id' => array('type' => 'INT', 'value' => $user_id),
                'table_id' => array('type' => 'INT', 'value' => $table_id),
                'row_id' => array('type' => 'INT', 'value' => $row_id),
                'old_value' => array('type' => 'TEXT', 'value' => $old_record_json),
                'new_value' => array('type' => 'TEXT', 'value' => $new_record_json),
                'raw_audit_json' => array('type' => 'TEXT', 'value' => $audit_json),
                'cms_path' => array('type' => 'TEXT', 'value' => $cms_path),
                'http_response_body' => array('type' => 'TEXT', 'value' => $audit_http_response_body)
            );

            $record = new Record('audit_events', $fields);

            $status = $data['_CMS']['database']->insert_record($record);

        } catch (Exception $e) {

            $response['xhr_response_status'] = 'error';
            $response['error'] = $e->getMessage();

            http_response_code(401);

            echo json_encode($response);
            graceful_exit();

        }

        if($status) {
            $response['xhr_response_status'] = 'success';
        } else {
            $response['xhr_response_status'] = 'error';
            $response['error'] = 'Failed to insert audit record';

            http_response_code(401);
        }

        echo json_encode($response);
        graceful_exit();

    }

?>