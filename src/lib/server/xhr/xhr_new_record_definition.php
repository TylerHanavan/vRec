<?php declare(strict_types=1);

    $hookman->add_hook('xhr_new_record_definition', array('url' => '/xhr/new-record-definition', 'layer' => 'page_load_pre', 'logged_in' => true));

    function xhr_new_record_definition(&$data) {

        if($data['_CMS']['path'] == '/xhr/new-record-definition') {
            
            $response = array();

            $database = $data['_CMS']['database'];
            
            $record_def_name = $_POST['record_def_name'] ?? null;

            $raw_fields = array();

            $seeking = true;
            $counter = 0;

            while($seeking) {
                
                $field_name = $_POST['field_name'.$counter] ?? null;

                if($field_name !== null && in_array($field_name, array('sysr_deleted', 'sysr_created_at'))) {
                    $response['xhr_response_type'] = 'error';
                    $response['error'] = "$field_name is a reserved field name";

                    echo json_encode($response);
        
                    graceful_exit();
                }

                $field_type = $_POST['field_type'.$counter] ?? null;
                $field_length = null;

                if($field_type == 'Text') {
                    $field_type = ColumnTypes::VARCHAR;
                    $field_length = 255;
                }

                if($field_type == 'Integer') {
                    $field_type = ColumnTypes::INT;
                }

                if($field_type == 'Boolean') {
                    $field_type = ColumnTypes::BOOLEAN;
                }

                if($field_type == 'Date') {
                    $field_type = ColumnTypes::DATE;
                }

                if($field_name === null || $field_type === null) {
                    $seeking = false;
                    echo "seeking = false\n; counter = $counter\n; field_name = $field_name\n; field_type = $field_type\n";
                } else {
                    $raw_fields[$field_name] = array('type' => $field_type, 'length' => $field_length);
                }

                $counter++;
            }

            if($record_def_name == null) {
                $response['xhr_response_type'] = 'error';
                $response['error'] = 'No record definition name provided';
            } else {
                $new_table_record = new Record($record_def_name, $raw_fields);

                $result = $database->create_table($new_table_record);
                
                $response['xhr_table_create_status'] = $result;

                $new_record_def_record = new Record('record_definitions', array('table_name' => array('type' => ColumnTypes::VARCHAR, 'value' => $record_def_name, 'length' => 255)));

                $result = $database->insert_record($new_record_def_record);

                $response['xhr_record_def_insert_status'] = $result;

                $fetch_record_def_record = $database->get_records(new Record('record_definitions', array('table_name' => array('type' => ColumnTypes::VARCHAR, 'value' => $record_def_name, 'length' => 255))));
                $record_definition_id = $fetch_record_def_record[0]->get_field('id')['value'];

                var_dump($raw_fields);

                foreach($raw_fields as $field_name => $field_data) {
                    // Convert numeric field type to string representation
                    $field_type_string = ColumnTypes::translate_id($field_data['type']);

                    $new_record_field_record = new Record('record_fields', array(
                        'record_definition_id' => array('type' => ColumnTypes::INT, 'value' => $record_definition_id, 'length' => 0),
                        'field_name' => array('type' => ColumnTypes::VARCHAR, 'value' => $field_name, 'length' => 255),
                        'field_type' => array('type' => ColumnTypes::VARCHAR, 'value' => $field_type_string, 'length' => 100)
                    ));

                    $result = $database->insert_record($new_record_field_record);

                    $response['xhr_field_insert_status'][$field_name] = $result;
                }
            }

            echo json_encode($response);

            graceful_exit();
        }
    }

?>
