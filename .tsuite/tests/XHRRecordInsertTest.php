<?php

    function test_new_record_1($properties) {
        assertEquals('localhost:1347', $properties['endpoint_url'], 'endpoint url mismatch');

        global $session_token;
        $response = test_curl($properties['endpoint_url'] . '/new', array('table' => 'test', 't1' => 1, 't2' => 2, 't3' => 3), true, $session_token);

        $response_arr = json_decode($response['response'], true);

        assertEquals(200, $response['http_code'], 'http code mismatch');

        assertTrue(isset($response_arr) && !empty($response_arr), 'response array empty');

        assertEquals('success', $response_arr['xhr_response_status'], 'mismatching xhr_response_status');

    }

    function test_new_record_2($properties) {
        assertEquals('localhost:1347', $properties['endpoint_url'], 'endpoint url mismatch');

        global $session_token;
        $response = test_curl($properties['endpoint_url'] . '/new', array('table' => 'test', 't1' => 4, 't2' => 4, 't3' => 5), true, $session_token);

        $response_arr = json_decode($response['response'], true);

        assertEquals(200, $response['http_code'], 'http code mismatch');

        assertTrue(isset($response_arr) && !empty($response_arr), 'response array empty');

        assertEquals('success', $response_arr['xhr_response_status'], 'mismatching xhr_response_status');

    }

    function test_new_record_3($properties) {
        assertEquals('localhost:1347', $properties['endpoint_url'], 'endpoint url mismatch');

        global $session_token;
        $response = test_curl($properties['endpoint_url'] . '/new', array('table' => 'test', 't1' => 5, 't2' => 6, 't3' => 7), true, $session_token);

        $response_arr = json_decode($response['response'], true);

        assertEquals(200, $response['http_code'], 'http code mismatch');

        assertTrue(isset($response_arr) && !empty($response_arr), 'response array empty');

        assertEquals('success', $response_arr['xhr_response_status'], 'mismatching xhr_response_status');

    }

?>