<?php declare(strict_types=1);

    $hookman->add_hook('xhr_record', array('url' => '/xhr/record', 'logged_in' => true, 'layer' => 'page_load_pre'));

    function xhr_record(&$data) {

        if($data['_CMS']['path'] == '/xhr/record') {
            
            $response = array();

            $database = $data['_CMS']['database'];
            $record_name = $_POST['record_name'] ?? $_GET['record_name'] ?? null;
            $allow_cache = $_POST['allow_cache'] ?? $_GET['allow_cache'] ?? true;

            $response['xhr_response_type'] = 'record';

            if($record_name == null) {

                $response['xhr_response_status'] = 'error';
                $response['error'] = 'No record name provided';

                echo json_encode($response);

                graceful_exit();

            }

            if($allow_cache == 'true' || $allow_cache == true) {
                $response['cache_allowed'] = true;

                $hash_cache_key = hash_cache_key(array('path' => $data['_CMS']['path'], 'record_name' => $record_name));

                $hash_value = retrieve_cache($hash_cache_key);
    
                if($hash_value != false) {

                    $response = $hash_value;

                    $response['cache_hit'] = true;

                    echo json_encode($response);
    
                    graceful_exit();
                } else {
                    $response['cache_hit'] = false;
                }

            } else {
                $response['cache_allowed'] = false;
            }

            $record_definition = $database->describe_record($record_name);

            $response['record_definition'] = array();
            $response['record_definition']['record_name'] = $record_definition->get_record_name();
            $response['record_definition']['record_fields'] = array();

            foreach($record_definition->get_fields() as $field => $props) {

                $field_data = array();

                $field_data['field_name'] = $field;
                $field_data['field_type'] = $props['type'];
                $field_data['field_length'] = $props['length'];

                $response['record_definition']['record_fields'][] = $field_data;

            }

            $response['records'] = array();

            $records = $database->get_records(new Record($record_name, array()));

            foreach($records as $record) {

                $record_data = array();

                foreach($record->get_fields() as $field => $props) {
                    
                    foreach($props as $prop => $value) {

                        $record_data[$field][$prop] = $value;

                    }

                }

                $response['records'][] = $record_data;

            }

            $response['xhr_response_status'] = 'success';

            echo json_encode($response);

            if($allow_cache == 'true' || $allow_cache == true) {

                store_cache($hash_cache_key, $response, 10);

            }

            graceful_exit();

        }

    }


?>