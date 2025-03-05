<?php

    function test_get_record_1($properties) {
        assertEquals('localhost:1347', $properties['endpoint_url'], 'endpoint url mismatch');

        global $session_token;
        $response = test_curl($properties['endpoint_url'] . '/xhr/record', array(), true, $session_token);

        $response_arr = json_decode($response['response'], true);

        assertEquals(200, $response['http_code'], 'http code mismatch');

        assertTrue(isset($response_arr) && !empty($response_arr), 'response array empty');

        assertTrue(isset($response_arr['xhr_response_status']), 'xhr_response_status is not set');
        assertTrue(isset($response_arr['xhr_response_type']), 'xhr_response_type is not set');
        assertTrue(isset($response_arr['error']), 'error is not set');

        assertEquals($response_arr['xhr_response_type'], 'record', 'bad xhr_response_type');
        assertEquals($response_arr['xhr_response_status'], 'error', 'bad xhr_response_status');
        assertEquals($response_arr['error'], 'No record name provided', 'bad xhr_response_type');
    }

    function test_get_record_2($properties) {
        assertEquals('localhost:1347', $properties['endpoint_url'], 'endpoint url mismatch');

        global $session_token;
        $response = test_curl($properties['endpoint_url'] . '/xhr/record', array('record_name' => 'test'), true, $session_token);

        $response_arr = json_decode($response['response'], true);

        var_dump($response_arr);

        assertEquals(200, $response['http_code'], 'http code mismatch');

        assertTrue(isset($response_arr) && !empty($response_arr), 'response array empty');

        assertTrue(isset($response_arr['xhr_response_status']), 'xhr_response_status is not set');
        assertTrue(isset($response_arr['xhr_response_type']), 'xhr_response_type is not set');
        assertTrue(isset($response_arr['cache_allowed']), 'cache_allowed is not set');

        assertEquals($response_arr['xhr_response_type'], 'record', 'bad xhr_response_type');
        assertEquals($response_arr['xhr_response_status'], 'success', 'bad xhr_response_status');
        assertEquals($response_arr['cache_allowed'], 1, 'bad cache_allowed');

        assertTrue(isset($response_arr['record_definition']), 'record_definition is not set');
        assertTrue(isset($response_arr['record_definition']['record_name']), 'record_definition.record_name is not set');
        assertTrue(isset($response_arr['record_definition']['record_fields']), 'record_definition.record_fields is not set');
        
        assertEquals($response_arr['record_definition']['record_name'], 'test', 'bad record_definition.record_name');

        assertTrue(isset($response_arr['record_definition']['record_fields'][0]), 'record_definition.record_fields[0] is not set');
        assertTrue(isset($response_arr['record_definition']['record_fields'][1]), 'record_definition.record_fields[1] is not set');
        assertTrue(isset($response_arr['record_definition']['record_fields'][2]), 'record_definition.record_fields[2] is not set');
        assertTrue(isset($response_arr['record_definition']['record_fields'][3]), 'record_definition.record_fields[3] is not set');

    }

?>