<?php declare(strict_types=1);

    $hookman->add_hook('xhr_record_definition', array('url' => '/xhr/record-definition', 'logged_in' => true, 'layer' => 'page_load_pre'));

    function xhr_record_definition(&$data) {

        if($data['_CMS']['path'] == '/xhr/record-definition') {
            
            $response = array();

            $database = $data['_CMS']['database'];
            $record_name = $_POST['record_name'] ?? $_GET['record_name'] ?? null;

            $response['xhr_response_type'] = 'record_definition';

            if($record_name == null) {
                $record_definitions = array();
                $record = new Record('record_definitions', null);
                $defs = $database->get_records($record);
                $record_fields = new Record('record_fields', null);
                $fields = $database->get_records($record_fields);
                foreach($defs as $def) {
                    $record_definition_name = $def->get_field('table_name')['value'];
                    $record_definition_id = $def->get_field('id')['value'];

                    $record_definitions[$record_definition_name] = array();

                    foreach($fields as $f) {
                        if($f->get_field('record_definition_id')['value'] == $record_definition_id) {
                            $record_definitions[$record_definition_name][] = array('field_name' => $f->get_field('field_name')['value'], 'field_type' => $f->get_field('field_type')['value']);
                        }
                    }
                }
                $response['record_definitions'] = $record_definitions;

            } else {

            }

            echo json_encode($response);

            graceful_exit();

        }

    }

?>