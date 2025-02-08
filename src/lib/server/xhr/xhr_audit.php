<?php

    $hookman->add_hook('xhr_audit', array('url' => '/xhr/audit', 'layer' => 'page_load_pre'));

    function xhr_audit(&$data) {

        $response = array();

        if(!isset($_POST) && !isset($_GET)) {
                
                $response['xhr_response_status'] = 'error';
                $response['error'] = 'No audit data provided';
    
                http_response_code(401);
    
                echo json_encode($response);
                graceful_exit();
        }

        $row_id = $_POST['row_id'] ?? $_GET['row_id'] ?? null;
        $table_id = $_POST['table_id'] ?? $_GET['table_id'] ?? null;
        $record_name = $_POST['record_name'] ?? $_GET['record_name'] ?? null;

        if($row_id == null) {
            $response['xhr_response_status'] = 'error';
            $response['error'] = 'No row_id provided';
            http_response_code(401);
            echo json_encode($response);
            graceful_exit();
        }

        if($table_id == null && $record_name == null) {
            $response['xhr_response_status'] = 'error';
            $response['error'] = 'No table_id or record_name provided';
            http_response_code(401);
            echo json_encode($response);
            graceful_exit();
        }

        if($table_id == null) {
            $records = $data['_CMS']['database']->get_records(new Record('record_definitions', array('table_name' => array('type' => 'VARCHAR', 'value' => $record_name))));

            if(sizeof($records) > 0) {
                $table_id = $records[0]->get_field('id')['value'];
            } else {
                $response['xhr_response_status'] = 'error';
                $response['error'] = 'No table_id or record_name provided';
                http_response_code(401);
                echo json_encode($response);
                graceful_exit();
            }
        }

        $records = $data['_CMS']['database']->get_records(new Record('audit_events', array('row_id' => array('type' => 'INT', 'value' => $row_id), 'table_id' => array('type' => 'INT', 'value' => $table_id))));

        foreach($records as $record) {
            $fields = array();
            foreach($record->get_fields() as $key => $props) {
                $fields[$key] = $props['value'];
            }
            $response['audit_events'][] = $fields;
        }

        echo json_encode($response);

        graceful_exit();

    }

?>