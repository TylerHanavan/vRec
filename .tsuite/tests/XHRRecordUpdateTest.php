<?php

    /** TODO: Test not logged in */
    /** TODO: Test with no record_name */
    /** TODO: Test with no fields */
    /** TODO: Test with no criteria */

    function test_update_record_get_record_1($properties) {
        assertEquals('localhost:1347', $properties['endpoint_url'], 'endpoint url mismatch');

        global $session_token;
        $response = test_curl($properties['endpoint_url'] . '/xhr/update_record', array('record_name' => 'test', 'fields' => '{"t3":{"value":"7","type":"TEXT"}}', 'criteria' => '{"id":{"value":"1","type":"INT"}}'), true, $session_token);

        $response_arr = json_decode($response['response'], true);

        assertEquals(200, $response['http_code'], 'http code mismatch');

        assertTrue(isset($response_arr) && !empty($response_arr), 'response array empty');

        assertEquals('record', $response_arr['xhr_response_type'], 'mismatching xhr_response_type');
        assertEquals('success', $response_arr['xhr_response_status'], 'mismatching xhr_response_status');

    }

?>