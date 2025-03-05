<?php

    function test_record_insert_1($properties) {
        assertEquals('localhost:1347', $properties['endpoint_url'], 'endpoint url mismatch');

        $response = test_curl($properties['endpoint_url'] . '/xhr/record', array(), true);

        $response_arr = json_decode($response['response'], true);

        assertEquals(200, $response['http_code'], 'http code mismatch');

        assertTrue(isset($response_arr) && !empty($response_arr), 'response array empty');

        assertTrue(isset($response_arr['xhr_response_status']), 'xhr_response_status is not set');
        assertTrue(isset($response_arr['xhr_response_type']), 'xhr_response_type is not set');
        assertTrue(isset($response_arr['error']), 'error is not set');

        assertEquals($response_arr['xhr_response_type'], 'record', 'bad xhr_response_type');
        assertEquals($response_arr['xhr_response_status'], 'error', 'bad xhr_response_status');
        assertEquals($response_arr['error'], 'No record name provided', 'bad xhr_response_type');

        assertEquals('<p>Scanned your database, all tables are setup already!</p>', $response['response'], 'response mismatch');
    }

?>