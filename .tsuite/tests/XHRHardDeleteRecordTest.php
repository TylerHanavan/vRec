<?php

    function test_hard_delete_record_1($properties) {
        assertEquals('localhost:1347', $properties['endpoint_url'], 'endpoint url mismatch');

        global $session_token;
        $response = test_curl($properties['endpoint_url'] . '/xhr/delete-record', array('record_name' => 'test', 'record_id' => 2), true, $session_token);

        $response_arr = json_decode($response['response'], true);

        assertEquals(200, $response['http_code'], 'http code mismatch');

        assertTrue(isset($response_arr) && !empty($response_arr), 'response array empty');

        assertEquals('delete_record', $response_arr['xhr_response_type'], 'mismatching xhr_response_type');
        assertEquals('success', $response_arr['xhr_response_status'], 'mismatching xhr_response_status');

    }

    function test_get_record_after_hard_delete_1($properties) {
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

        assertTrue(isset($response_arr['records']), 'records not set');
        assertTrue(!empty($response_arr['records']), 'records is empty');

        $records = $response_arr['records'];

        $fields = ['id', 't1', 't2', 't3'];

        assertTrue(2, sizeof($records), 'wrong number of records returned');

        for($x = 0; $x < sizeof($records); $x++) {
            assertTrue(isset($records[$x]), "there is no record $x");

            foreach($fields as $t) {
                assertTrue(isset($records[$x][$t]), "there is no record $x field $t");
                assertTrue(isset($records[$x][$t]['type']), "there is no record $x field $t type");
                //assertTrue(isset($records[$x][$t]['length']), "there is no record $x field $t length");
                assertTrue(isset($records[$x][$t]['value']), "there is no record $x field $t value");
                assertTrue($records[$x][$t]['value'] != 2, 'record #2 still exists');
            }
        }

        assertEquals(1, $records[0]['id']['value'], 'record 0 id value is wrong');
        assertEquals('1', $records[0]['t1']['value'], 'record 0 t1 value is wrong');
        assertEquals(2, $records[0]['t2']['value'], 'record 0 t2 value is wrong');
        assertEquals(7, $records[0]['t3']['value'], 'record 0 t3 value is wrong');

        /*assertEquals(2, $records[1]['id']['value'], 'record 1 id value is wrong');
        assertEquals('4', $records[1]['t1']['value'], 'record 1 t1 value is wrong');
        assertEquals(9, $records[1]['t2']['value'], 'record 1 t2 value is wrong');
        assertEquals(5, $records[1]['t3']['value'], 'record 1 t3 value is wrong');*/

        assertEquals(3, $records[1]['id']['value'], 'record 1 id value is wrong');
        assertEquals('5', $records[1]['t1']['value'], 'record 1 t1 value is wrong');
        assertEquals(6, $records[1]['t2']['value'], 'record 1 t2 value is wrong');
        assertEquals(7, $records[1]['t3']['value'], 'record 1 t3 value is wrong');

    }

?>