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

        assertEquals('record', $response_arr['xhr_response_type'], 'bad xhr_response_type');
        assertEquals('error', $response_arr['xhr_response_status'], 'bad xhr_response_status');
        assertEquals('No record name provided', $response_arr['error'], 'bad xhr_response_type');
    }

    function test_get_record_2($properties) {
        assertEquals('localhost:1347', $properties['endpoint_url'], 'endpoint url mismatch');

        global $session_token;
        $response = test_curl($properties['endpoint_url'] . '/xhr/record', array('record_name' => 'test'), true, $session_token);

        $response_arr = json_decode($response['response'], true);

        assertEquals(200, $response['http_code'], 'http code mismatch');

        assertTrue(isset($response_arr) && !empty($response_arr), 'response array empty');

        assertTrue(isset($response_arr['xhr_response_status']), 'xhr_response_status is not set');
        assertTrue(isset($response_arr['xhr_response_type']), 'xhr_response_type is not set');
        assertTrue(isset($response_arr['cache_allowed']), 'cache_allowed is not set');
        assertTrue(isset($response_arr['cache_hit']), 'cache_hit is not set');
        assertTrue(isset($response_arr['records']), 'records is not set');

        assertEquals('record', $response_arr['xhr_response_type'], 'bad xhr_response_type');
        assertEquals('success', $response_arr['xhr_response_status'], 'bad xhr_response_status');
        assertTrue(!is_string($response_arr['cache_allowed']), 'cache_allowed returned a string');
        assertTrue(!is_int($response_arr['cache_allowed']), 'cache_allowed returned an int');

        assertTrue(isset($response_arr['record_definition']), 'record_definition is not set');
        assertTrue(isset($response_arr['record_definition']['record_name']), 'record_definition.record_name is not set');
        assertTrue(isset($response_arr['record_definition']['record_fields']), 'record_definition.record_fields is not set');
        
        assertEquals('test', $response_arr['record_definition']['record_name'], 'bad record_definition.record_name');

        assertTrue(isset($response_arr['record_definition']['record_fields'][0]), 'record_definition.record_fields[0] is not set');
        assertTrue(isset($response_arr['record_definition']['record_fields'][1]), 'record_definition.record_fields[1] is not set');
        assertTrue(isset($response_arr['record_definition']['record_fields'][2]), 'record_definition.record_fields[2] is not set');
        assertTrue(isset($response_arr['record_definition']['record_fields'][3]), 'record_definition.record_fields[3] is not set');

        assertEquals('id', $response_arr['record_definition']['record_fields'][0]['field_name'], 'record_definition.record_fields[0][\'field_name\'] is bad');
        assertEquals(0, $response_arr['record_definition']['record_fields'][0]['field_type'], 'record_definition.record_fields[0][\'field_type\'] is bad');
        assertEquals(null, $response_arr['record_definition']['record_fields'][0]['field_length'], 'record_definition.record_fields[0][\'field_length\'] is bad');

        assertEquals('t1', $response_arr['record_definition']['record_fields'][1]['field_name'], 'record_definition.record_fields[1][\'field_name\'] is bad');
        assertEquals(1, $response_arr['record_definition']['record_fields'][1]['field_type'], 'record_definition.record_fields[1][\'field_type\'] is bad');
        assertEquals(255, $response_arr['record_definition']['record_fields'][1]['field_length'], 'record_definition.record_fields[1][\'field_length\'] is bad');
        
        assertEquals('t2', $response_arr['record_definition']['record_fields'][2]['field_name'], 'record_definition.record_fields[2][\'field_name\'] is bad');
        assertEquals(0, $response_arr['record_definition']['record_fields'][2]['field_type'], 'record_definition.record_fields[2][\'field_type\'] is bad');
        assertEquals(null, $response_arr['record_definition']['record_fields'][2]['field_length'], 'record_definition.record_fields[2][\'field_length\'] is bad');

        assertEquals('t3', $response_arr['record_definition']['record_fields'][3]['field_name'], 'record_definition.record_fields[3][\'field_name\'] is bad');
        assertEquals(0, $response_arr['record_definition']['record_fields'][3]['field_type'], 'record_definition.record_fields[3][\'field_type\'] is bad');
        assertEquals(null, $response_arr['record_definition']['record_fields'][3]['field_length'], 'record_definition.record_fields[3][\'field_length\'] is bad');

    }

?>