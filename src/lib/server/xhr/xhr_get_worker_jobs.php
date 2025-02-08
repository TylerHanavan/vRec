<?php

    $hookman->add_hook('xhr_get_worker_jobs', array('url' => '/xhr/worker/jobs', 'layer' => 'page_load_pre'));

    function xhr_get_worker_jobs(&$data) {

        $record = new Record('worker_jobs', array());

        $records = $data['_CMS']['database']->get_records($record);

        $response = array();

        foreach($records as $record) {
            $fields = array();
            foreach($record->get_fields() as $key => $props) {
                $fields[$key] = $props['value'];
            }
            $response['worker_jobs'][] = $fields;
        }

        echo json_encode($response);

        graceful_exit();

    }
?>