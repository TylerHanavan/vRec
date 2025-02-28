<?php

    function test_xhr_record_definition_1($properties) {
        $data = array();
        $data['record_def_name'] = 'test';
        $data['field_type0'] = 'Text';
        $data['field_name0'] = 't1';
        $data['field_type1'] = 'Integer';
        $data['field_name1'] = 't2';
        $data['field_type2'] = 'Boolean';
        $data['field_name2'] = 't3';
        $response = test_curl($properties['endpoint_url'] . '/xhr/new-record-definition', $data, true);
        assertEquals(200, $response['http_code'], 'http code mismatch');
        assertStrContains('<!doctype html>', $response['response'], 'response mismatch');
    }

    function test_xhr_record_definition_2($properties) {
        $data = array();
        $data['record_def_name'] = 'test';
        $data['field_type0'] = 'Text';
        $data['field_name0'] = 't1';
        $data['field_type1'] = 'Integer';
        $data['field_name1'] = 't2';
        $data['field_type2'] = 'Boolean';
        $data['field_name2'] = 't3';
        global $session_token;
        $response = test_curl($properties['endpoint_url'] . '/xhr/new-record-definition', $data, true, $session_token);
        var_dump($response);
        
        $response_array = json_decode($response['response'], true);
        
        if(isset($response_array['xhr_table_create_status']))
            assertEquals($response_array['xhr_table_create_status'], 'true', 'response status mismatch');
        else {
            //THROW ERROR LATER
        }
        
        if(isset($response_array['xhr_record_def_insert_status']))
            assertEquals($response_array['xhr_record_def_insert_status'], 'true', 'response status mismatch');
        else {
            //THROW ERROR LATER
        }

        assertEquals(200, $response['http_code'], 'http code mismatch');

    }

?>