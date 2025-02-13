<?php declare(strict_types=1);

    $hookman->add_hook('xhr_delete_record', array('url' => '/xhr/delete-record', 'layer' => 'page_load_pre', 'logged_in' => true));

    function xhr_delete_record(&$data) {

        $response = array();

        $database = $data['_CMS']['database'];
        $record_name = $_POST['record_name'] ?? $_GET['record_name'] ?? null;
        $record_id = $_POST['record_id'] ?? $_GET['record_id'] ?? null;

        $response['xhr_response_type'] = 'delete_record';

        if($record_name == null) {

            $response['xhr_response_status'] = 'error';
            $response['error'] = 'No record name provided';

            echo json_encode($response);

            graceful_exit();

        }

        if($record_id == null) {

            $response['xhr_response_status'] = 'error';
            $response['error'] = 'No record id provided';

            echo json_encode($response);

            graceful_exit();

        }

        $res = $database->delete_record($record_name, array('id' => array('type' => ColumnTypes::INT, 'value' => $record_id)));

        if($res) {
            $response['xhr_response_status'] = 'success';
        } else {
            $response['xhr_response_status'] = 'error';
            $response['error'] = 'Failed to delete record';
        }

        echo json_encode($response);

        graceful_exit();
        

    }

?>