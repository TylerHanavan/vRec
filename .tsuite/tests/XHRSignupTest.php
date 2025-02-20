<?php

    function test_xhr_signup_1($properties) {
        assertEquals('localhost:1347', $properties['endpoint_url'], 'endpoint url mismatch');

        $response = test_curl($properties['endpoint_url'] . '/xhr/signup', array(), true);

        assertEquals(400, $response['http_code'], 'http code mismatch');
        assertEquals('{"xhr_response_type":"signup","xhr_response_status":"error","error":"Missing required fields"}', $response['response'], 'response mismatch');
    }

    function test_xhr_signup_2($properties) {
        assertEquals('localhost:1347', $properties['endpoint_url'], 'endpoint url mismatch');

        $response = test_curl($properties['endpoint_url'] . '/xhr/signup', array('username' => 'ke', 'email' => 'example@example.com', 'password' => '12345678'), true);

        assertEquals(400, $response['http_code'], 'http code mismatch');
        assertEquals('{"xhr_response_type":"signup","xhr_response_status":"error","error":"Username must be between 3 and 50 characters"}', $response['response'], 'response mismatch');
    }

    function test_xhr_signup_3($properties) {
        assertEquals('localhost:1347', $properties['endpoint_url'], 'endpoint url mismatch');

        $response = test_curl($properties['endpoint_url'] . '/xhr/signup', array('username' => 'kekekekekekekekekekekekekekekekekekekekekekekekekekeke', 'email' => 'example@example.com', 'password' => '12345678'), true);

        assertEquals(400, $response['http_code'], 'http code mismatch');
        assertEquals('{"xhr_response_type":"signup","xhr_response_status":"error","error":"Username must be between 3 and 50 characters"}', $response['response'], 'response mismatch');
    }

    function test_xhr_signup_4($properties) {
        assertEquals('localhost:1347', $properties['endpoint_url'], 'endpoint url mismatch');

        $response = test_curl($properties['endpoint_url'] . '/xhr/signup', array('username' => 'kevin', 'email' => 'example@', 'password' => '123456789'), true);

        assertEquals(400, $response['http_code'], 'http code mismatch');
        assertEquals('{"xhr_response_type":"signup","xhr_response_status":"error","error":"Invalid email format"}', $response['response'], 'response mismatch');
    }

    function test_xhr_signup_5($properties) {
        assertEquals('localhost:1347', $properties['endpoint_url'], 'endpoint url mismatch');

        $response = test_curl($properties['endpoint_url'] . '/xhr/signup', array('username' => 'kevin', 'email' => 'example@example.com', 'password' => '123456'), true);

        assertEquals(400, $response['http_code'], 'http code mismatch');
        assertEquals('{"xhr_response_type":"signup","xhr_response_status":"error","error":"Password must be at least 8 characters"}', $response['response'], 'response mismatch');
    }

?>